<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Election;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;

class ResultsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.results-dashboard';
    
    protected static ?string $navigationGroup = 'Reporting';

    public ?string $election_id = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('election_id')
                    ->label('Select Election')
                    ->options(Election::where('status', '!=', 'draft')->pluck('title', 'id'))
                    ->reactive()
                    ->afterStateUpdated(function () {
                        // Just trigger refresh
                    }),
            ]);
    }

    public function getViewData(): array
    {
        $results = [];
        $candidates = [];
        $positions = [];
        
        if ($this->election_id) {
            $election = Election::with(['positions', 'candidates.user'])->find($this->election_id);
            
            if ($election) {
                // Calculate results
                // Group votes by candidate_id and count
                $voteCounts = Vote::where('election_id', $election->id)
                    ->select('candidate_id', DB::raw('count(*) as total'))
                    ->groupBy('candidate_id')
                    ->pluck('total', 'candidate_id');
                
                $positions = $election->positions;
                
                foreach ($positions as $position) {
                    $posCandidates = $election->candidates->where('position_id', $position->id);
                    $candidates[$position->id] = $posCandidates;
                    
                    foreach ($posCandidates as $candidate) {
                        $results[$candidate->id] = $voteCounts[$candidate->id] ?? 0;
                    }
                }
            }
        }

        return [
            'results' => $results,
            'positions' => $positions,
            'candidates' => $candidates,
        ];
    }
}
