<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VettingResource\Pages;
use App\Models\Candidate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VettingResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Vetting';

    protected static ?string $navigationGroup = 'Election Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Candidate Vetting';

    protected static ?string $pluralModelLabel = 'Candidate Vetting';

    protected static ?string $slug = 'vetting';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->whereIn('nomination_status', ['pending_vetting', 'approved']);

        if (!auth()->user()->is_super_admin && function_exists('current_organization_id') && current_organization_id()) {
            $query->where('organization_id', current_organization_id());
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Candidate Information')
                    ->description('Candidate details (read-only)')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Placeholder::make('photo_preview')
                                    ->label('')
                                    ->content(function ($record) {
                                        if ($record && $record->photo_path) {
                                            $url = asset('storage/' . $record->photo_path);
                                            return new \Illuminate\Support\HtmlString(
                                                '<img src="' . $url . '" class="w-24 h-24 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700" />'
                                            );
                                        }
                                        return new \Illuminate\Support\HtmlString(
                                            '<div class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                </svg>
                                            </div>'
                                        );
                                    }),

                                Forms\Components\Grid::make(1)
                                    ->schema([
                                        Forms\Components\Placeholder::make('candidate_name')
                                            ->label('Candidate Name')
                                            ->content(fn ($record) => $record?->user?->name ?? $record?->email ?? 'Unknown'),

                                        Forms\Components\Placeholder::make('candidate_email')
                                            ->label('Email')
                                            ->content(fn ($record) => $record?->email ?? $record?->user?->email ?? 'N/A'),
                                    ])
                                    ->columnSpan(2),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Placeholder::make('election_name')
                                    ->label('Election')
                                    ->content(fn ($record) => $record?->election?->title ?? 'N/A'),

                                Forms\Components\Placeholder::make('position_name')
                                    ->label('Position')
                                    ->content(fn ($record) => $record?->position?->name ?? 'N/A'),

                                Forms\Components\Placeholder::make('candidate_number_display')
                                    ->label('Candidate Number')
                                    ->content(fn ($record) => $record?->candidate_number ?? 'N/A'),
                            ]),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Vetting Assessment')
                    ->description('Evaluate the candidate\'s eligibility')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('vetting_score')
                                    ->label('Vetting Score')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('/ 100')
                                    ->helperText('Score from 0 to 100 based on vetting criteria'),

                                Forms\Components\Select::make('vetting_status')
                                    ->label('Vetting Status')
                                    ->options([
                                        'pending' => '⏳ Pending Review',
                                        'passed' => '✓ Passed',
                                        'failed' => '✗ Failed',
                                        'disqualified' => '⛔ Disqualified',
                                    ])
                                    ->required()
                                    ->native(false),
                            ]),

                        Forms\Components\Textarea::make('vetting_notes')
                            ->label('Vetting Notes')
                            ->rows(4)
                            ->placeholder('Enter detailed notes about the vetting decision, reasons for pass/fail, any concerns, etc.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?background=6366f1&color=fff&name=C'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Candidate')
                    ->description(fn ($record) => $record->email)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('election.title')
                    ->label('Election')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('position.name')
                    ->label('Position')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('vetting_score')
                    ->label('Score')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 70 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (?int $state): string => $state !== null ? $state . '/100' : 'N/A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('vetting_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'passed' => 'success',
                        'failed' => 'danger',
                        'disqualified' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => '⏳ Pending',
                        'passed' => '✓ Passed',
                        'failed' => '✗ Failed',
                        'disqualified' => '⛔ Disqualified',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('vetter.name')
                    ->label('Vetted By')
                    ->placeholder('Not yet vetted')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vetted_at')
                    ->label('Vetted At')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('election_id')
                    ->relationship('election', 'title')
                    ->label('Election')
                    ->preload(),

                Tables\Filters\SelectFilter::make('position_id')
                    ->relationship('position', 'name')
                    ->label('Position')
                    ->preload(),

                Tables\Filters\SelectFilter::make('vetting_status')
                    ->options([
                        'pending' => 'Pending',
                        'passed' => 'Passed',
                        'failed' => 'Failed',
                        'disqualified' => 'Disqualified',
                    ])
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Vet')
                    ->icon('heroicon-o-clipboard-document-check'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_passed')
                        ->label('Mark as Passed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'vetting_status' => 'passed',
                                    'nomination_status' => 'approved',
                                    'vetted_at' => now(),
                                    'vetted_by' => auth()->id(),
                                ]);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('mark_failed')
                        ->label('Mark as Failed')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'vetting_status' => 'failed',
                                    'nomination_status' => 'rejected',
                                    'vetted_at' => now(),
                                    'vetted_by' => auth()->id(),
                                ]);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading('No candidates pending vetting')
            ->emptyStateDescription('Candidates will appear here once they have been nominated and are ready for vetting.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check');
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
            'index' => Pages\ListVetting::route('/'),
            'edit' => Pages\EditVetting::route('/{record}/edit'),
        ];
    }
}
