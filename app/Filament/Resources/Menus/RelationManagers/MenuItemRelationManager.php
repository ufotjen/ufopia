<?php

namespace App\Filament\Resources\Menus\RelationManagers;

use App\Filament\Components\TranslationTabs;
use App\Filament\Forms\Components\Reusable\I18nControls;
use App\Filament\Forms\Components\Reusable\IconPicker;
use App\Models\MenuItem;
use Filament\{Actions\BulkActionGroup,
    Actions\CreateAction,
    Actions\DeleteAction,
    Actions\DeleteBulkAction,
    Actions\EditAction,
    Actions\ForceDeleteAction,
    Actions\ForceDeleteBulkAction,
    Actions\RestoreAction,
    Actions\RestoreBulkAction,
    Actions\ViewAction,
    Forms\Components\Radio,
    Forms\Components\SpatieMediaLibraryFileUpload,
    Forms\Components\TextInput,
    Forms\Components\Toggle,
    Resources\RelationManagers\RelationManager,
    Schemas\Components\Section,
    Schemas\Schema,
    Tables\Columns\TextColumn,
    Tables\Filters\TrashedFilter,
    Tables\Table
};
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
            Section::make('Meta')->schema([
                ...I18nControls::make(),

                // ⬇️ Alleen 'label' is vertaalbaar op MenuItem
                TranslationTabs::form(
                    fields: ['label'],
                    componentMap: ['label' => 'textarea'] // of weglaten/aanpassen naar wens
                ),

                TextInput::make('url')->label('URL')->required()->maxLength(255),
                Toggle::make('is_active')->label('Actief')->default(true),
            ]),
            IconPicker::make()
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                ...TranslationTabs::table(),
                TextColumn::make('url')->label('URL')->wrap(),
                TextColumn::make('parent.title')->label('Parent')->toggleable(),
                TextColumn::make('icon_mode')->badge(),
            ])
            ->filters([
                TrashedFilter::make()->visible(fn() => in_array(SoftDeletes::class, class_uses_recursive(MenuItem::class))),
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
