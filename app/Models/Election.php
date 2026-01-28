<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'title',
        'description',
        'slug',
        'nomination_start_date',
        'nomination_end_date',
        'vetting_start_date',
        'vetting_end_date',
        'voting_start_date',
        'voting_end_date',
        'status',
        'require_photo',
        'max_votes_per_position',
        'voter_eligibility_rules',
        'results_published',
        'results_published_at',
        'created_by',
    ];

    protected $casts = [
        'nomination_start_date' => 'datetime',
        'nomination_end_date' => 'datetime',
        'vetting_start_date' => 'datetime',
        'vetting_end_date' => 'datetime',
        'voting_start_date' => 'datetime',
        'voting_end_date' => 'datetime',
        'require_photo' => 'boolean',
        'voter_eligibility_rules' => 'array',
        'results_published' => 'boolean',
        'results_published_at' => 'datetime',
    ];

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function voteConfirmations()
    {
        return $this->hasMany(VoteConfirmation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
