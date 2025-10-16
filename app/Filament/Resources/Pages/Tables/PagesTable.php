<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Filament\Resources\Pages\PageResource;
use App\Models\Menu;
use App\Models\Page;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
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
            ->query(fn() => PageResource::getEloquentQuery()
                ->with(['site', 'headerMenu', 'footerMenu', 'sidebarMenu', 'extraMenus.menu'])
            )
            ->columns([
                TextColumn::make('site.name')->label('Site')->sortable()->toggleable(),
                TextColumn::make('author.name')->label('Auteur'),
                TextColumn::make('editor.name')->label('Laatst bewerkt door'),
                TextColumn::make('title')->label('Titel')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->sortable(),
                TextColumn::make('effective_header')
                    ->label('Header')
                    ->getStateUsing(fn($record) => $record->effectiveHeaderMenu()?->title ?? '—')
                    ->badge(),
                TextColumn::make('effective_footer')
                    ->label('Footer')
                    ->getStateUsing(fn($record) => $record->effectiveFooterMenu()?->title ?? '—')
                    ->badge(),
                TextColumn::make('effective_sidebar')
                    ->label('Sidebar')
                    ->getStateUsing(fn($record) => $record->effectiveSidebarMenu()?->title ?? '—')
                    ->badge(),
                // Override-indicator
                IconColumn::make('has_overrides')
                    ->label('Overrides')
                    ->boolean()
                    ->state(function (?Page $record): bool {
                        if (! $record) return false; // 👈 guard
                        return (bool) (
                            $record->header_menu_id
                            || $record->footer_menu_id
                            || $record->sidebar_menu_id
                        );}),

                        // Extra menus count
                TextColumn::make('extra_menus_count')
                    ->badge()
                    ->label('Extra')
                    ->counts('extraMenus')
                    ->color(fn($state) => $state ? 'info' : 'gray'),
                TextColumn::make('updated_at')->since()->label('Bijgewerkt'),
                IconColumn::make('is_published')->label('Pub.')->boolean(),
                TextColumn::make('updated_at')->since()->label('Bijgewerkt')->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('site_id')->relationship('site', 'name')->label('Site'),
                TernaryFilter::make('is_published')->label('Gepubliceerd'),
            ])
            ->headerActions([
                // Laat bestaande menu's koppelen
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn(Menu $q) => $q->where('site_id', $this->getOwnerRecord()->site_id) // filter op zelfde site
                    ),

                // Optioneel: nieuw menu aanmaken vanuit de relation
                CreateAction::make()
                    ->label(__('Nieuw menu'))
                    ,
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
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
