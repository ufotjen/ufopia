<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Naam')
                ->required(),

            TextInput::make('email')
                ->label('E-mail')
                ->email()
                ->required(),

            TextInput::make('password')
                ->label('Wachtwoord')
                ->password()
                ->required(),
        ]);
    }
}
