<?php

namespace App\Filament\Resources\Menus\RelationManagers;

use Filament\{
    Actions\ViewAction,
    Actions\BulkActionGroup,
    Actions\CreateAction,
    Actions\DeleteAction,
    Actions\DeleteBulkAction,
    Actions\EditAction,
    Actions\ForceDeleteAction,
    Actions\ForceDeleteBulkAction,
    Actions\RestoreAction,
    Actions\RestoreBulkAction,
    Forms\Components\Select,
    Forms\Components\TextInput,
    Forms\Components\Toggle,
    Resources\RelationManagers\RelationManager,
    Schemas\Schema,
    Tables\Columns\TextColumn,
    Tables\Filters\TrashedFilter,
    Tables\Table};
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItemRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Menu Items';

    public function isReadOnly(): bool
    {
        return false;
    }

    // sommige setups checken ook dit:
    public function canCreate(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')->label('Label')->required()->maxLength(190),
            TextInput::make('url')->label('URL')->required()->maxLength(255),
            Toggle::make('is_active')->label('Actief')->default(true),
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('label')->label('Label')->searchable(),
                TextColumn::make('url')->label('URL')->wrap(),
                TextColumn::make('parent.label')->label('Parent')->toggleable(),
            ])
            ->filters([
                TrashedFilter::make()->visible(fn () => in_array(SoftDeletes::class, class_uses_recursive(MenuItem::class))),
            ])
            ->headerActions([
                // ⬅️ Tables\Actions\CreateAction: linkt automatisch aan de relatie
                CreateAction::make()->label('Nieuw item'),
            ])
            ->recordActions([
                // ⬅️ jouw global Actions namespace hier is prima
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
