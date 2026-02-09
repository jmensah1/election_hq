<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'reference',
        'paystack_reference',
        'amount',
        'currency',
        'status',
        'channel',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'integer',
    ];

    /**
     * Get the lead that owns the payment.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the formatted amount in GHS.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₵' . number_format($this->amount / 100, 2);
    }

    /**
     * Check if payment was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark payment as successful.
     */
    public function markAsSuccessful(string $paystackReference, ?string $channel = null, ?array $metadata = null): void
    {
        $this->update([
            'status' => 'success',
            'paystack_reference' => $paystackReference,
            'channel' => $channel,
            'metadata' => $metadata,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }
}
