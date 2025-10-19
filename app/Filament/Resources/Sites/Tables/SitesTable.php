<?php

namespace App\Filament\Resources\Sites\Tables;

use App\Filament\Components\TranslationTabs;
use App\Filament\Tables\Actions\I18nActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SitesTable
{
    public static function configure(Table $table): Table
    {
        $loc = app()->getLocale();

        return $table

            ->columns([
                TextColumn::make('owner.name')->label('Eigenaar')->sortable()->searchable(),

                ...TranslationTabs::table(
                    fields: [ 'slug'],
                    componentMap: ['slug' => 'short'],
                ),

                IconColumn::make('is_active')->boolean(),

                TextColumn::make('primary_domain')->searchable(),
                TextColumn::make('default_locale')->searchable(),
                TextColumn::make('theme_key')->searchable(),
                TextColumn::make('timezone')->searchable(),
                TextColumn::make('contact_email')->searchable(),

                TextColumn::make("headerMenu.title->$loc")->label('Header default')->badge()->placeholder('—'),
                TextColumn::make("footerMenu.title->$loc")->label('Footer default')->badge()->placeholder('—'),
                TextColumn::make("sidebarMenu.title->$loc")->label('Sidebar default')->badge()->placeholder('—'),

                TextColumn::make('menus_count')->label('Menus')->badge()->placeholder('—'),
                TextColumn::make('pages_count')->label('Pages')->badge()->placeholder('—'),

                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
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
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
