<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateResource\Pages;
use App\Models\Candidate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CandidateResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!auth()->user()->is_super_admin && function_exists('current_organization_id') && current_organization_id()) {
            $query->where('organization_id', current_organization_id());
        }

        return $query;
    }

    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Election Management';

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

                Forms\Components\Select::make('election_id')
                    ->relationship('election', 'title', fn (Builder $query) => 
                        function_exists('current_organization_id') && current_organization_id() 
                            ? $query->where('organization_id', current_organization_id()) 
                            : $query
                    )
                    ->required()
                    ->reactive(),
                
                Forms\Components\Select::make('position_id')
                    ->relationship('position', 'name', fn (Builder $query, callable $get) => 
                        $query->where('election_id', $get('election_id'))
                    )
                    ->required()
                    ->hidden(fn (callable $get) => !$get('election_id')),
                
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->label('Candidate Email (Invitation)'),

                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Linked User Account')
                    ->disabled() // Admin sets email, system links user on login
                    ->dehydrated(false) // Don't wipe it out if it's disabled
                    ->hiddenOn('create'), // Hide on create, show on edit if linked
                    
                Forms\Components\TextInput::make('candidate_number')
                    ->maxLength(20),
                
                Forms\Components\Textarea::make('manifesto')
                    ->columnSpanFull(),
                
                Forms\Components\FileUpload::make('photo_path')
                    ->image()
                    ->disk('public')
                    ->directory('candidates/photos')
                    ->visibility('public'),
                
                Forms\Components\Select::make('nomination_status')
                    ->options([
                        'pending_submission' => 'Pending Submission (Invited)',
                        'pending_vetting' => 'Pending Vetting',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'withdrawn' => 'Withdrawn',
                    ])
                    ->default('pending_submission')
                    ->required(),
                
                Forms\Components\Select::make('vetting_status')
                    ->options([
                        'pending' => 'Pending',
                        'passed' => 'Passed',
                        'failed' => 'Failed',
                        'disqualified' => 'Disqualified',
                    ])
                    ->default('pending')
                    ->required(),
                
                Forms\Components\Textarea::make('vetting_notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path'),
                Tables\Columns\TextColumn::make('user.name')->label('Name')->searchable(),
                Tables\Columns\TextColumn::make('election.title')->searchable(),
                Tables\Columns\TextColumn::make('position.name')->searchable(),
                
                Tables\Columns\TextColumn::make('nomination_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending_submission' => 'gray',
                        'pending_vetting' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'withdrawn' => 'warning',
                        'pending' => 'gray', // Fallback for old records
                    }),
                    
                Tables\Columns\TextColumn::make('vetting_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'passed' => 'success',
                        'failed' => 'danger',
                        'disqualified' => 'danger',
                    }),
                    
                Tables\Columns\TextColumn::make('vote_count')->numeric()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('election_id')
                    ->relationship('election', 'title'),
                Tables\Filters\SelectFilter::make('nomination_status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'withdrawn' => 'Withdrawn',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $query = static::getEloquentQuery()
                            ->with(['user', 'election', 'position']);
                        
                        $filename = 'candidates-' . now()->format('Y-m-d') . '.csv';
                        
                        return response()->streamDownload(function () use ($query) {
                            $handle = fopen('php://output', 'w');
                            
                            // Header row
                            fputcsv($handle, [
                                'ID', 'Name', 'Email', 'Election', 'Position', 
                                'Candidate Number', 'Nomination Status', 'Vetting Status', 
                                'Vote Count', 'Is Winner', 'Created At'
                            ]);
                            
                            // Data rows
                            $query->chunk(100, function ($candidates) use ($handle) {
                                foreach ($candidates as $candidate) {
                                    fputcsv($handle, [
                                        $candidate->id,
                                        $candidate->user?->name ?? 'N/A',
                                        $candidate->email,
                                        $candidate->election?->title ?? 'N/A',
                                        $candidate->position?->name ?? 'N/A',
                                        $candidate->candidate_number,
                                        $candidate->nomination_status,
                                        $candidate->vetting_status,
                                        $candidate->vote_count,
                                        $candidate->is_winner ? 'Yes' : 'No',
                                        $candidate->created_at?->format('Y-m-d H:i:s'),
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
            'index' => Pages\ListCandidates::route('/'),
            'create' => Pages\CreateCandidate::route('/create'),
            'edit' => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }
}
