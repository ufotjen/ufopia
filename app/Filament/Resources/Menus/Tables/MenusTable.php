<?php

namespace App\Filament\Resources\Menus\Tables;

use App\Filament\Components\TranslationTabs;
use App\Filament\Tables\Actions\I18nActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return  $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                // 👇 spreiden, niet 1 item
                ...TranslationTabs::table(
                    fields: ['title','slug'],            // of null = auto
                    componentMap: ['slug' => 'short'],   // optioneel: 'wrap' / 'short'
                    showCompleteness: true
                ),
                TextColumn::make('key')->label('Locatie')->badge()->sortable(),
                TextColumn::make('updated_at')->since()->label('Bijgewerkt')->sortable(),
            ])
            ->filters([
                SelectFilter::make('site_id')->relationship('site', 'name')->label('Site'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                I18nActions::copyFallback(),
                I18nActions::forceTranslate(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
