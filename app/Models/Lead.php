<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'organization_name',
        'plan_tier',
        'billing_cycle',
        'message',
        'status', // new, contacted, converted, rejected
        'ip_address',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
