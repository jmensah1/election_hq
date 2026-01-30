<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ElectionResource\Pages;
use App\Models\Election;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ElectionResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        if (!auth()->user()->is_super_admin && function_exists('current_organization_id') && current_organization_id()) {
            $query->where('organization_id', current_organization_id());
        }
        
        return $query;
    }
    protected static ?string $model = Election::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Election Management';

    protected static ?int $navigationSort = 1;

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
                
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                
                Forms\Components\Grid::make(3)
                    ->schema([
                         Forms\Components\DateTimePicker::make('nomination_start_date')->required()->seconds(false),
                         Forms\Components\DateTimePicker::make('vetting_start_date')->required()->seconds(false),
                         Forms\Components\DateTimePicker::make('voting_start_date')->required()->seconds(false),
                         
                         Forms\Components\DateTimePicker::make('nomination_end_date')->required()->seconds(false),
                         Forms\Components\DateTimePicker::make('vetting_end_date')->required()->seconds(false),
                         Forms\Components\DateTimePicker::make('voting_end_date')->required()->seconds(false),
                    ]),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'nomination' => 'Nomination',
                        'vetting' => 'Vetting',
                        'voting' => 'Voting',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('draft'),
                
                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('require_photo'),
                        Forms\Components\TextInput::make('max_votes_per_position')
                            ->numeric()
                            ->default(1),
                    ])->columns(2),
                    
                 Forms\Components\Hidden::make('created_by')
                    ->default(fn () => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'nomination' => 'info',
                        'vetting' => 'warning',
                        'voting' => 'success',
                        'completed' => 'primary',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('voting_start_date')->date(),
                Tables\Columns\TextColumn::make('voting_end_date')->date(),
                Tables\Columns\IconColumn::make('results_published')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
            ])
            ->actions([
                Tables\Actions\Action::make('dashboard')
                    ->label('Dashboard')
                    ->icon('heroicon-o-chart-bar-square')
                    ->url(fn (Election $record) => route('filament.admin.pages.election-dashboard', ['selectedElectionId' => $record->id]))
                    ->color('info'),
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
            'index' => Pages\ListElections::route('/'),
            'create' => Pages\CreateElection::route('/create'),
            'edit' => Pages\EditElection::route('/{record}/edit'),
        ];
    }
}
