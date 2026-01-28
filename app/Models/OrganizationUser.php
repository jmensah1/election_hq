<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganizationUser extends Pivot
{
    use HasFactory;

    protected $table = 'organization_user';

    public $incrementing = true;

    protected $fillable = [
        'organization_id',
        'user_id',
        'voter_id',
        'allowed_email',
        'role',
        'status',
        'can_vote',
        'department',
    ];

    protected $casts = [
        'can_vote' => 'boolean',
    ];
    
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
