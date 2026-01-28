<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory, BelongsToOrganization;

    // We explicitly manage created_at but updated_at is standard
    // Actually, migration has created_at default CURRENT_TIMESTAMP and specific fields.
    // Let's keep standard timestamps = false if we manipulate them manually or true if we want Laravel to handle it.
    // Migration has 'created_at' column but no 'updated_at'. So public $timestamps = false;
    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'user_id',
        'election_id',
        'type',
        'category',
        'recipient',
        'subject',
        'message',
        'status',
        'sent_at',
        'error_message',
        'cost_amount',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'metadata' => 'array',
        'cost_amount' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function election()
    {
        return $this->belongsTo(Election::class);
    }
}
