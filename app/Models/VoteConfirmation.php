<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteConfirmation extends Model
{
    use HasFactory, BelongsToOrganization;

    public $timestamps = false; // logic handles voted_at manually if needed, or we user default timestamp for voted_at only

    protected $fillable = [
        'organization_id',
        'election_id',
        'position_id',
        'user_id',
        'voted_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'voted_at' => 'datetime',
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
}
