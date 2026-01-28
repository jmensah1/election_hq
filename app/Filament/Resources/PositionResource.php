<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PositionResource\Pages;
use App\Models\Position;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PositionResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!auth()->user()->is_super_admin && function_exists('current_organization_id') && current_organization_id()) {
            $query->where('organization_id', current_organization_id());
        }

        return $query;
    }

    protected static ?string $model = Position::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

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
                    ->required(),
                
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('display_order')
                    ->numeric()
                    ->default(0),
                
                Forms\Components\TextInput::make('max_candidates')
                    ->numeric()
                    ->default(10),
                
                Forms\Components\TextInput::make('max_votes')
                    ->numeric()
                    ->default(1),
                    
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('election.title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('max_candidates')->numeric(),
                Tables\Columns\TextColumn::make('max_votes')->numeric(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('display_order')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('election_id')
                    ->relationship('election', 'title'),
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
            'index' => Pages\ListPositions::route('/'),
            'create' => Pages\CreatePosition::route('/create'),
            'edit' => Pages\EditPosition::route('/{record}/edit'),
        ];
    }
}
