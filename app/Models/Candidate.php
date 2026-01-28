<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'election_id',
        'position_id',
        'user_id',
        'email',
        'candidate_number',
        'manifesto',
        'photo_path',
        'nomination_status',
        'nominated_at',
        'nominated_by',
        'vetting_status',
        'vetting_notes',
        'vetted_at',
        'vetted_by',
        'vote_count',
        'is_winner',
    ];

    protected $casts = [
        'nominated_at' => 'datetime',
        'vetted_at' => 'datetime',
        'is_winner' => 'boolean',
    ];

    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nominator()
    {
        return $this->belongsTo(User::class, 'nominated_by');
    }

    public function vetter()
    {
        return $this->belongsTo(User::class, 'vetted_by');
    }
}
