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
    protected static ?string $navigationLabel = 'Published Results';

    protected static string $view = 'filament.pages.results-dashboard';
    
    protected static ?string $navigationGroup = 'Reporting';

    public static function canAccess(): bool
    {
        // Only allow Super Admins
        return auth()->user()?->is_super_admin === true;
    }

    public ?string $election_id = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('election_id')
                    ->label('Select Election')
                    ->options(function () {
                        $query = Election::query()->where('status', '!=', 'draft');
                        
                        // For non-super admins, restrict to published results or completed elections
                        if (!auth()->user()?->is_super_admin) {
                            $query->where(function ($q) {
                                $q->where('results_published', true)
                                  ->orWhere('status', 'completed');
                            });
                        }
                        
                        return $query->pluck('title', 'id');
                    })
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
                // Check if user can view these results
                $canView = auth()->user()?->is_super_admin || 
                           $election->results_published || 
                           $election->status === 'completed';

                if (!$canView) {
                     // If they can't view, return empty
                     return [
                        'results' => [],
                        'positions' => [],
                        'candidates' => [],
                     ];
                }

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
