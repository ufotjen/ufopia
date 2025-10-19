<?php

namespace App\Filament\Resources\Menus;

use App\Filament\Resources\Menus\Pages\CreateMenu;
use App\Filament\Resources\Menus\Pages\EditMenu;
use App\Filament\Resources\Menus\Pages\ListMenus;
use App\Filament\Resources\Menus\Pages\ViewMenu;
use App\Filament\Resources\Menus\RelationManagers\MenuItemRelationManager;
use App\Filament\Resources\Menus\Schemas\MenuForm;
use App\Filament\Resources\Menus\Schemas\MenuInfolist;
use App\Filament\Resources\Menus\Tables\MenusTable;
use App\Models\Menu;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bars-3-center-left';

    protected static ?string $recordTitleAttribute = 'title';
    protected static string|null|\UnitEnum $navigationGroup = 'CMS';
    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return MenuForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MenuInfolist::configure($schema);
    }


    public static function table(Table $table): Table
    {
        return MenusTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MenuItemRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenus::route('/'),
            'create' => CreateMenu::route('/create'),
            'view' => ViewMenu::route('/{record}'),
            'edit' => EditMenu::route('/{record}/edit'),
        ];
    }
}
