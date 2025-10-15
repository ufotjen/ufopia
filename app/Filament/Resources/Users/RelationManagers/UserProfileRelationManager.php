<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserProfileRelationManager extends RelationManager
{
    protected static string $relationship = 'profile';

    protected static ?string $title = 'Profiel';

    public static function getModelLabel(): string { return 'Profile'; }
    public static function getPluralModelLabel(): string { return 'Profile'; }

    public function form(Schema $schema): Schema
    {
        // schema zonder user-select
        return $schema->schema(UserProfileForm::schema()) ->columns(1);
    }

    public function infolist(Schema $schema ): Schema
    {
        return $schema->schema(UserProfileInfoList::schema()) ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('username')
            ->columns([
                TextColumn::make('username')->label('Gebruikersnaam'),
                IconColumn::make('is_profile_active')->label('Actief')->boolean(),
                TextColumn::make('updated_at')->since()->label('Bijgewerkt'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Create profile')
                    ->visible(fn ($livewire) => $livewire->getOwnerRecord()->profile()->doesntExist()),


            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->label('Remove profile')
                    ->visible(fn () => auth()->user()?->can('users.delete_soft') ?? false),
            ]);
    }
}
