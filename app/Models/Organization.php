<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'subdomain',
        'custom_domain',
        'logo_path',
        'timezone',
        'status',
        'subscription_plan',
        'subscription_expires_at',
        'sms_enabled',
        'sms_sender_id',
        'max_voters',
        'settings',
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
        'sms_enabled' => 'boolean',
        'settings' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_user')
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

    public function elections()
    {
        return $this->hasMany(Election::class);
    }
}
