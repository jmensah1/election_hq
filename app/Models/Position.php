<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'election_id',
        'name',
        'description',
        'display_order',
        'max_candidates',
        'max_votes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class)->orderBy('vote_count', 'desc');
    }
}
