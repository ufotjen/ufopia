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
    Tables\Table};
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
            Section::make('Meta')
            ->schema([
                TranslationTabs::form(),
                TextInput::make('url')->label('URL')->required()->maxLength(255),
                Toggle::make('is_active')->label('Actief')->default(true),
            ]),
            Section::make('Icon')
                ->schema([
                    Radio::make('icon_mode')
                        ->label('Icon bron')
                        ->options([
                            'none'  => 'Geen',
                            'media' => 'Upload (afbeelding / SVG)',
                            'class' => 'Icon class (thema)',
                        ])
                        ->inline()
                        ->default('none')
                        ->live(),

                    // Media upload (alleen als mode=media)
                    SpatieMediaLibraryFileUpload::make('icon')
                        ->label('Icon upload')
                        ->collection('icon')
                        ->image()
                        ->acceptedFileTypes(['image/png','image/webp','image/svg+xml'])
                        ->visible(fn ($get) => $get('icon_mode') === 'media'),

                    // Icon class (alleen als mode=class)
                    TextInput::make('icon_class')
                        ->label('Icon class')
                        ->placeholder('bv. lucide-home / heroicon-m-home / ph-house')
                        ->helperText('Kies een passende class op basis van het gekozen thema.')
                        ->visible(fn ($get) => $get('icon_mode') === 'class'),
                ])
                   ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                ...TranslationTabs::table(),
                TextColumn::make('url')->label('URL')->wrap(),
                TextColumn::make('parent.label')->label('Parent')->toggleable(),
                TextColumn::make('icon_mode')->badge(),

               I18nControls::make(),
                IconPicker::make()
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
