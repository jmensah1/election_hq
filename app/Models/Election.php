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
        'self_nomination_enabled',
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
        'self_nomination_enabled' => 'boolean',
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

    /**
     * Check if nomination submission is currently allowed.
     */
    public function isNominationOpen(): bool
    {
        if ($this->status !== 'nomination') {
            return false;
        }

        $now = now();
        return $now->gte($this->nomination_start_date) && $now->lte($this->nomination_end_date);
    }

    /**
     * Check if self-nomination is currently allowed.
     */
    public function isSelfNominationOpen(): bool
    {
        return $this->self_nomination_enabled && $this->isNominationOpen();
    }

    /**
     * Check if voting is currently allowed.
     */
    public function isVotingOpen(): bool
    {
        if ($this->status !== 'voting') {
            return false;
        }

        $now = now();
        return $now->gte($this->voting_start_date) && $now->lte($this->voting_end_date);
    }

    /**
     * Check if results can be published.
     */
    public function canPublishResults(): bool
    {
        return $this->status === 'completed' && !$this->results_published;
    }

    /**
     * Publish the election results.
     */
    public function publishResults(): bool
    {
        if (!$this->canPublishResults()) {
            return false;
        }

        return $this->update([
            'results_published' => true,
            'results_published_at' => now(),
        ]);
    }
}
