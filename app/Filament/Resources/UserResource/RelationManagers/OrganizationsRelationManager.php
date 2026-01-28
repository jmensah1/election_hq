<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrganizationsRelationManager extends RelationManager
{
    protected static string $relationship = 'organizations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'election_officer' => 'Election Officer',
                        'voter' => 'Voter',
                    ])
                    ->required()
                    ->default('voter'),
                Forms\Components\TextInput::make('voter_id')
                    ->required()
                    ->maxLength(50)
                     ->default(fn () => 'ADMIN-' . mt_rand(1000, 9999)),
                Forms\Components\TextInput::make('allowed_email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->default(fn ($record, $livewire) => $livewire->ownerRecord->email), // Default to user's email
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('pivot.role')->label('Role'),
                Tables\Columns\TextColumn::make('pivot.voter_id')->label('Voter ID'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('role')
                            ->options([
                                'admin' => 'Admin',
                                'election_officer' => 'Election Officer',
                                'voter' => 'Voter',
                            ])
                            ->required()
                            ->default('admin'),
                        Forms\Components\TextInput::make('voter_id')
                            ->required()
                            ->default('ADMIN-' . mt_rand(10000, 99999)),
                        Forms\Components\TextInput::make('allowed_email')
                             ->required()
                             // We can't easily access parent record here in all contexts, but user should type it
                             ->label('Organization Email (for login check)'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'suspended' => 'Suspended',
                            ])
                            ->default('active'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
