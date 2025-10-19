<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Filament\Components\TranslationTabs;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Tables\Actions\I18nActions;
use App\Models\Page;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->query(fn () => PageResource::getEloquentQuery()
                ->with(['site', 'headerMenu', 'footerMenu', 'sidebarMenu', 'extraMenus.menu'])
            )
            ->columns([
                TextColumn::make('author.name')->label('Auteur'),
                TextColumn::make('editor.name')->label('Laatst bewerkt door'),

                ...TranslationTabs::table(
                    fields: ['title', 'slug', 'excerpt'],     // of null = auto
                    componentMap: ['slug' => 'short', 'excerpt' => 'wrap'],
                ),

                TextColumn::make('effective_header')
                    ->label('Header')
                    ->getStateUsing(fn ($r) => $r->effectiveHeaderMenu()?->title ?? '—')
                    ->badge(),

                TextColumn::make('effective_footer')
                    ->label('Footer')
                    ->getStateUsing(fn ($r) => $r->effectiveFooterMenu()?->title ?? '—')
                    ->badge(),

                TextColumn::make('effective_sidebar')
                    ->label('Sidebar')
                    ->getStateUsing(fn ($r) => $r->effectiveSidebarMenu()?->title ?? '—')
                    ->badge(),

                IconColumn::make('has_overrides')
                    ->label('Overrides')
                    ->boolean()
                    ->state(function (?Page $record): bool {
                        if (! $record) return false;
                        return ($record->header_menu_id || $record->footer_menu_id || $record->sidebar_menu_id);
                    }),

                TextColumn::make('extra_menus_count')
                    ->badge()
                    ->label('Extra')
                    ->counts('extraMenus')
                    ->color(fn ($state) => $state ? 'info' : 'gray'),

                IconColumn::make('is_published')->label('Pub.')->boolean(),
                TextColumn::make('updated_at')->since()->label('Bijgewerkt')->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('site_id')->relationship('site', 'name')->label('Site'),
                TernaryFilter::make('is_published')->label('Gepubliceerd'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
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
