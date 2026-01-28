<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory, BelongsToOrganization;

    // CRITICAL: No timestamps to prevent timing correlation
    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'election_id',
        'position_id',
        'candidate_id',
    ];

    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
