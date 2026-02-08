<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'avatar',
        'password',
        'is_super_admin',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot([
                'voter_id',
                'allowed_email',
                'role',
                'status',
                'can_vote',
                'department',
            ])
            ->withTimestamps();
    }
    
    // Helper to get current organization membership
    public function getOrganizationMembership($organizationId)
    {
        return $this->organizations()->where('organization_id', $organizationId)->first()?->pivot;
    }

    public function organizationUser()
    {
        return $this->hasOne(OrganizationUser::class)->where('organization_id', current_organization_id());
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // 1. Super Admins can access everything everywhere
        if ($this->is_super_admin) {
            return true;
        }

        // 2. For everyone else, we need a valid organization context
        $currentOrgId = function_exists('current_organization_id') ? current_organization_id() : null;
        
        if (!$currentOrgId) {
            return false;
        }

        // 3. Check membership in the CURRENT organization
        $membership = $this->getOrganizationMembership($currentOrgId);
        
        if (!$membership) {
            \Illuminate\Support\Facades\Log::info("User Access Check Failed: User {$this->id} is not a member of Org {$currentOrgId}.");
            return false;
        }

        // 4. Only 'admin' and 'election_officer' can access the panel
        $role = $membership->role;
        $allowed = in_array($role, ['admin', 'election_officer']);
        
        if (!$allowed) {
             // Debug log removed
        } else {
             // Debug log removed
        }

        return $allowed;
    }
}
