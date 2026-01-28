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
                
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name') // We might want to customize this query to only show organization users
                    ->searchable()
                    ->required()
                    ->label('Candidate User'),
                    
                Forms\Components\TextInput::make('candidate_number')
                    ->maxLength(20),
                
                Forms\Components\Textarea::make('manifesto')
                    ->columnSpanFull(),
                
                Forms\Components\FileUpload::make('photo_path')
                    ->image()
                    ->directory('candidates/photos'),
                
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
