<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoterResource\Pages;
use App\Models\OrganizationUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VoterResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!auth()->user()->is_super_admin && function_exists('current_organization_id') && current_organization_id()) {
            $query->where('organization_id', current_organization_id());
        }

        return $query;
    }

    protected static ?string $model = OrganizationUser::class;

    protected static ?string $label = 'Voter';

    protected static ?string $pluralLabel = 'Voters';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('organization_id')
                    ->relationship('organization', 'name')
                    ->required()
                    ->hidden(fn () => function_exists('current_organization_id') && current_organization_id())
                    ->dehydrated()
                    ->default(fn () => function_exists('current_organization_id') ? current_organization_id() : null),

                Forms\Components\TextInput::make('voter_id')
                    ->required()
                    ->maxLength(50)
                    ->label('Voter ID / Student ID'),
                
                Forms\Components\TextInput::make('allowed_email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(20)
                    ->label('Phone Number')
                    ->required(fn () => function_exists('current_organization') && app(\App\Services\PlanLimitService::class)->canUseSMS(current_organization()))
                    ->helperText(fn () => function_exists('current_organization') && app(\App\Services\PlanLimitService::class)->canUseSMS(current_organization()) ? 'Required because SMS is enabled for this organization.' : 'Optional (SMS disabled).'),
                
                Forms\Components\Select::make('role')
                    ->options([
                        'voter' => 'Voter',
                        'election_officer' => 'Election Officer',
                        'admin' => 'Admin',
                    ])
                    ->required()
                    ->default('voter'),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending (Not Logged In)',
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                    ])
                    ->default('pending')
                    ->required(),
                
                Forms\Components\Toggle::make('can_vote')
                    ->default(true),
                
                Forms\Components\TextInput::make('department')
                    ->maxLength(100),
                
                // Read-only link to actual user if exists
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled()
                    ->label('Linked User Account'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('voter_id')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('allowed_email')->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'election_officer' => 'warning',
                        'voter' => 'success',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'gray',
                        'suspended' => 'danger',
                    }),
                Tables\Columns\IconColumn::make('can_vote')->boolean(),
                Tables\Columns\TextColumn::make('user.name')->label('Linked User'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'voter' => 'Voter',
                        'election_officer' => 'Election Officer',
                        'admin' => 'Admin',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending (Not Logged In)',
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('template')
                    ->label('Download Template')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        return response()->streamDownload(function () {
                            $handle = fopen('php://output', 'w');
                            fputcsv($handle, ['voter_id', 'allowed_email', 'department', 'role']);
                            fputcsv($handle, ['101010', 'john.doe@example.com', 'Computer Science', 'voter']);
                            fputcsv($handle, ['102020', 'jane.doe@example.com', 'Engineering', 'voter']);
                            fclose($handle);
                        }, 'voters_import_template.csv', [
                            'Content-Type' => 'text/csv',
                        ]);
                    }),
                Tables\Actions\Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $query = static::getEloquentQuery()
                            ->with(['user', 'organization']);
                        
                        $filename = 'voters-' . now()->format('Y-m-d') . '.csv';
                        
                        return response()->streamDownload(function () use ($query) {
                            $handle = fopen('php://output', 'w');
                            
                            fputcsv($handle, [
                                'ID', 'Voter ID', 'Email', 'Role', 'Status', 
                                'Can Vote', 'Department', 'Linked User', 'Created At'
                            ]);
                            
                            $query->chunk(100, function ($voters) use ($handle) {
                                foreach ($voters as $voter) {
                                    fputcsv($handle, [
                                        $voter->id,
                                        $voter->voter_id,
                                        $voter->allowed_email,
                                        $voter->role,
                                        $voter->status,
                                        $voter->can_vote ? 'Yes' : 'No',
                                        $voter->department,
                                        $voter->user?->name ?? 'Not linked',
                                        $voter->created_at?->format('Y-m-d H:i:s'),
                                    ]);
                                }
                            });
                            
                            fclose($handle);
                        }, $filename, [
                            'Content-Type' => 'text/csv',
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVoters::route('/'),
            'create' => Pages\CreateVoter::route('/create'),
            'edit' => Pages\EditVoter::route('/{record}/edit'),
        ];
    }
}
