<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Candidate;

class CandidatePortal extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Candidate Portal';
    protected static ?string $slug = 'candidate-portal';
    protected static string $view = 'filament.pages.candidate-portal';

    public static function canAccess(): bool
    {

        $user = auth()->user();
        $orgId = function_exists('current_organization_id') ? current_organization_id() : null;

        if (!$user || !$orgId) {
            return false;
        }

        // Check if user is a candidate in the current organization
        // We access the relationship we added to User model, filtered by organization
        return Candidate::where('user_id', $user->id)
            ->where('organization_id', $orgId)
            ->exists();
    }
}
