<?php

namespace App\Filament\Pages;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\OrganizationUser;
use App\Models\Position;
use App\Models\Vote;
use App\Models\VoteConfirmation;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;

class ElectionDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'Election Dashboard';
    protected static ?string $navigationGroup = 'Election Management';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.election-dashboard';

    #[\Livewire\Attributes\Url]
    public ?int $selectedElectionId = null;

    public ?Election $election = null;

    public function mount(): void
    {
        // If selectedElectionId is passed via URL, load that election
        if ($this->selectedElectionId) {
            $this->loadElection($this->selectedElectionId);
            
            // If loaded successfully, return
            if ($this->election) {
                return;
            }
        }

        // Default to the first active election or most recent one
        $query = Election::query();
        
        if (!auth()->user()?->is_super_admin && function_exists('current_organization_id')) {
            $query->where('organization_id', current_organization_id());
        }
        
        $this->election = $query->orderByDesc('created_at')->first();
        $this->selectedElectionId = $this->election?->id;
    }

    public function form(Form $form): Form
    {
        $query = Election::query();
        
        if (!auth()->user()?->is_super_admin && function_exists('current_organization_id')) {
            $query->where('organization_id', current_organization_id());
        }

        return $form
            ->schema([
                Select::make('selectedElectionId')
                    ->label('Select Election')
                    ->options($query->pluck('title', 'id'))
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->loadElection($state))
                    ->columnSpanFull(),
            ]);
    }

    public function loadElection($electionId): void
    {
        $this->election = Election::with(['positions.candidates', 'organization'])
            ->find($electionId);
    }

    public function getStats(): array
    {
        if (!$this->election) {
            return [];
        }

        $totalPositions = $this->election->positions()->count();
        $totalCandidates = $this->election->candidates()
            ->where('vetting_status', 'passed')
            ->count();
        
        // Get eligible voters count from organization members with 'voter' role
        $eligibleVoters = OrganizationUser::where('organization_id', $this->election->organization_id)
            ->where('can_vote', true)
            ->count();
        
        // Get unique voters who have voted
        $votesCast = VoteConfirmation::where('election_id', $this->election->id)
            ->distinct('user_id')
            ->count('user_id');
        
        $turnout = $eligibleVoters > 0 ? round(($votesCast / $eligibleVoters) * 100, 1) : 0;
        
        // Time remaining
        $timeRemaining = $this->getTimeRemaining();

        return [
            [
                'label' => 'Positions',
                'value' => $totalPositions,
                'icon' => 'heroicon-o-briefcase',
                'color' => 'primary',
            ],
            [
                'label' => 'Candidates',
                'value' => $totalCandidates,
                'icon' => 'heroicon-o-users',
                'color' => 'info',
            ],
            [
                'label' => 'Eligible Voters',
                'value' => number_format($eligibleVoters),
                'icon' => 'heroicon-o-user-group',
                'color' => 'gray',
            ],
            [
                'label' => 'Votes Cast',
                'value' => number_format($votesCast),
                'icon' => 'heroicon-o-hand-raised',
                'color' => 'success',
            ],
            [
                'label' => 'Voter Turnout',
                'value' => $turnout . '%',
                'icon' => 'heroicon-o-chart-pie',
                'color' => $turnout >= 50 ? 'success' : ($turnout >= 25 ? 'warning' : 'danger'),
            ],
            [
                'label' => 'Time Remaining',
                'value' => $timeRemaining,
                'icon' => 'heroicon-o-clock',
                'color' => $this->election->status === 'voting' ? 'warning' : 'gray',
            ],
        ];
    }

    protected function getTimeRemaining(): string
    {
        if (!$this->election) {
            return '-';
        }

        $now = now();
        
        if ($this->election->status === 'completed' || $this->election->status === 'cancelled') {
            return $this->election->status === 'completed' ? 'Completed' : 'Cancelled';
        }

        if ($this->election->status === 'voting') {
            if ($now->lt($this->election->voting_end_date)) {
                return $now->diffForHumans($this->election->voting_end_date, ['parts' => 2, 'short' => true]);
            }
            return 'Voting Ended';
        }

        if ($now->lt($this->election->voting_start_date)) {
            return 'Starts ' . $now->diffForHumans($this->election->voting_start_date, ['parts' => 2, 'short' => true]);
        }

        return ucfirst($this->election->status);
    }

    public function getTimeline(): array
    {
        if (!$this->election) {
            return [];
        }

        $now = now();
        
        return [
            [
                'phase' => 'Nomination',
                'start' => $this->election->nomination_start_date,
                'end' => $this->election->nomination_end_date,
                'active' => $this->election->status === 'nomination',
                'completed' => $now->gt($this->election->nomination_end_date),
                'icon' => 'heroicon-o-document-plus',
            ],
            [
                'phase' => 'Vetting',
                'start' => $this->election->vetting_start_date,
                'end' => $this->election->vetting_end_date,
                'active' => $this->election->status === 'vetting',
                'completed' => $now->gt($this->election->vetting_end_date),
                'icon' => 'heroicon-o-clipboard-document-check',
            ],
            [
                'phase' => 'Voting',
                'start' => $this->election->voting_start_date,
                'end' => $this->election->voting_end_date,
                'active' => $this->election->status === 'voting',
                'completed' => $now->gt($this->election->voting_end_date) || $this->election->status === 'completed',
                'icon' => 'heroicon-o-hand-raised',
            ],
        ];
    }

    public function getPositionResults(): array
    {
        if (!$this->election) {
            return [];
        }

        // Only show results if voting has ended or results are published
        $canShowResults = $this->election->results_published || 
                          $this->election->status === 'completed' ||
                          ($this->election->status === 'voting' && auth()->user()?->is_super_admin);

        $positions = $this->election->positions()
            ->with(['candidates' => function ($query) {
                $query->where('vetting_status', 'passed')
                      ->orderByDesc('vote_count');
            }])
            ->orderBy('display_order')
            ->get();

        return $positions->map(function ($position) use ($canShowResults) {
            $totalVotes = $position->candidates->sum('vote_count');
            
            return [
                'id' => $position->id,
                'name' => $position->name,
                'totalVotes' => $totalVotes,
                'candidates' => $position->candidates->map(function ($candidate, $index) use ($totalVotes, $canShowResults) {
                    $percentage = $totalVotes > 0 ? round(($candidate->vote_count / $totalVotes) * 100, 1) : 0;
                    
                    return [
                        'id' => $candidate->id,
                        'name' => $candidate->user?->name ?? $candidate->email,
                        'photo' => $candidate->photo_path ? asset('storage/' . $candidate->photo_path) : null,
                        'votes' => $canShowResults ? $candidate->vote_count : null,
                        'percentage' => $canShowResults ? $percentage : null,
                        'isWinner' => $candidate->is_winner,
                        'rank' => $index + 1,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    public function getVotingActivity(): array
    {
        if (!$this->election) {
            return ['labels' => [], 'data' => []];
        }

        // Get hourly voting activity for the last 24 hours
        $startTime = now()->subHours(24);
        
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $dateFormat = match ($driver) {
            'pgsql' => 'TO_CHAR(voted_at, \'YYYY-MM-DD HH24:00:00\')',
            'sqlite' => 'strftime(\'%Y-%m-%d %H:00:00\', voted_at)',
            default => 'DATE_FORMAT(voted_at, \'%Y-%m-%d %H:00:00\')', // MySQL/MariaDB
        };

        $activity = VoteConfirmation::where('election_id', $this->election->id)
            ->where('voted_at', '>=', $startTime)
            ->selectRaw("$dateFormat as hour, COUNT(*) as count")
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Generate all hours for the period
        $labels = [];
        $data = [];
        $current = $startTime->copy()->startOfHour();
        
        while ($current <= now()) {
            $hourKey = $current->format('Y-m-d H:00:00');
            $labels[] = $current->format('H:i');
            $data[] = $activity[$hourKey] ?? 0;
            $current->addHour();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    public function getRecentActivity(): array
    {
        if (!$this->election) {
            return [];
        }

        // Get recent vote confirmations (anonymized)
        $recentVotes = VoteConfirmation::where('election_id', $this->election->id)
            ->with('position')
            ->orderByDesc('voted_at')
            ->limit(10)
            ->get()
            ->map(function ($confirmation) {
                return [
                    'type' => 'vote',
                    'message' => 'Vote cast for ' . ($confirmation->position->name ?? 'Unknown Position'),
                    'time' => $confirmation->voted_at->diffForHumans(),
                    'icon' => 'heroicon-o-check-circle',
                    'color' => 'success',
                ];
            })
            ->toArray();

        return $recentVotes;
    }

    public function getStatusColor(): string
    {
        if (!$this->election) {
            return 'gray';
        }

        return match ($this->election->status) {
            'draft' => 'gray',
            'nomination' => 'info',
            'vetting' => 'warning',
            'voting' => 'success',
            'completed' => 'primary',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public function exportResults()
    {
        if (!$this->election) {
            return null;
        }

        // Only allow export if completed or results published
        if (!$this->election->results_published && $this->election->status !== 'completed') {
            return null;
        }

        $election = $this->election;
        $filename = 'election-results-' . \Illuminate\Support\Str::slug($election->title) . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($election) {
            $handle = fopen('php://output', 'w');
            
            // Header row
            fputcsv($handle, [
                'Position', 'Candidate Name', 
                'Vote Count', 'Percentage'
            ]);
            
            $positions = $election->positions()
                ->with(['candidates' => function ($query) {
                    $query->where('vetting_status', 'passed')
                          ->orderByDesc('vote_count');
                }])
                ->orderBy('display_order')
                ->get();

            foreach ($positions as $position) {
                $totalVotes = $position->candidates->sum('vote_count');
                
                foreach ($position->candidates as $candidate) {
                    $percentage = $totalVotes > 0 ? round(($candidate->vote_count / $totalVotes) * 100, 1) : 0;
                    
                    fputcsv($handle, [
                        $position->name,
                        $candidate->user?->name ?? 'N/A',
                        $candidate->vote_count,
                        $percentage . '%',
                    ]);
                }
            }
            
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('publishResults')
                ->label('Publish Results')
                ->icon('heroicon-o-megaphone')
                ->color('success')
                ->visible(fn () => $this->election?->canPublishResults())
                ->requiresConfirmation()
                ->modalHeading('Publish Election Results')
                ->modalDescription('Are you sure you want to publish the results? This will make them visible to all voters and members.')
                ->modalSubmitActionLabel('Yes, Publish Results')
                ->action(function () {
                    $this->election->publishResults();
                    \Filament\Notifications\Notification::make()
                        ->title('Results Published')
                        ->body("Results for '{$this->election->title}' are now visible to voters.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
