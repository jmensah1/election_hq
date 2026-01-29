<?php

namespace App\Filament\Widgets;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Vote;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();
        
        if ($user?->is_super_admin) {
            return $this->getSuperAdminStats();
        }
        
        return $this->getVendorStats();
    }

    protected function getSuperAdminStats(): array
    {
        $totalVendors = Organization::count();
        $activeElections = Election::where('status', 'voting')->count();
        $totalMembers = OrganizationUser::count();
        $totalVotes = Vote::count();
        
        // Calculate trends (last 7 days vs previous 7 days)
        $newVendorsThisWeek = Organization::where('created_at', '>=', now()->subDays(7))->count();
        $newMembersThisWeek = OrganizationUser::where('created_at', '>=', now()->subDays(7))->count();

        return [
            Stat::make('Total Vendors', $totalVendors)
                ->description($newVendorsThisWeek . ' new this week')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),
                
            Stat::make('Active Elections', $activeElections)
                ->description('Currently running')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
                
            Stat::make('Total Members', number_format($totalMembers))
                ->description($newMembersThisWeek . ' new this week')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
                
            Stat::make('Total Votes Cast', number_format($totalVotes))
                ->description('Across all elections')
                ->descriptionIcon('heroicon-m-hand-raised')
                ->color('warning'),
        ];
    }

    protected function getVendorStats(): array
    {
        $orgId = function_exists('current_organization_id') ? current_organization_id() : null;
        
        if (!$orgId) {
            return [
                Stat::make('Error', 'No organization context')
                    ->color('danger'),
            ];
        }

        $totalMembers = OrganizationUser::where('organization_id', $orgId)->count();
        $activeMembers = OrganizationUser::where('organization_id', $orgId)
            ->where('status', 'active')
            ->count();
        
        $activeElections = Election::where('organization_id', $orgId)
            ->where('status', 'voting')
            ->count();
        
        $totalElections = Election::where('organization_id', $orgId)->count();
        
        $electionIds = Election::where('organization_id', $orgId)->pluck('id');
        $totalVotes = Vote::whereIn('election_id', $electionIds)->count();
        
        $pendingCandidates = Candidate::whereIn('election_id', $electionIds)
            ->where('nomination_status', 'pending')
            ->count();

        return [
            Stat::make('Total Members', number_format($totalMembers))
                ->description($activeMembers . ' active')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
                
            Stat::make('Elections', $totalElections)
                ->description($activeElections . ' active')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color($activeElections > 0 ? 'success' : 'gray'),
                
            Stat::make('Total Votes', number_format($totalVotes))
                ->description('Across all elections')
                ->descriptionIcon('heroicon-m-hand-raised')
                ->color('info'),
                
            Stat::make('Pending Candidates', $pendingCandidates)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCandidates > 0 ? 'warning' : 'success'),
        ];
    }
}
