<?php

namespace App\Filament\Resources\Sites\RelationManagers;

use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesRelationManager extends RelationManager
{
    protected static string $relationship = 'pages';
    protected static ?string $title = 'Pages';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('title')->label('Titel')->required()->maxLength(190),
            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(190)
                ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                    return $rule->where('site_id', $this->getOwnerRecord()->id);
                }),
            Textarea::make('excerpt')->label('Samenvatting')->rows(3)->nullable(),
            Textarea::make('content')->label('Inhoud')->rows(8)->nullable(),
            Toggle::make('is_published')->label('Gepubliceerd')->default(false),
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->label('Titel')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->sortable(),
                IconColumn::make('is_published')->label('Pub.')->boolean(),
                TextColumn::make('updated_at')->since()->label('Bijgewerkt'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [MenusRelationManager::class];
    }
}
