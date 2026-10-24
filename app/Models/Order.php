<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasUuids;

    protected $table = 'orders';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_email',
        'pricing_tier_id',
        'amount_idr',
        'mayar_invoice_id',
        'mayar_transaction_id',
        'payment_link',
        'payment_status',
        'raw_webhook_payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_idr' => 'integer',
            'payment_status' => PaymentStatus::class,
            'raw_webhook_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::PAID;
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatus::PAID);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatus::PENDING);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function pricingTier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class, 'pricing_tier_id', 'id');
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(UserEntitlement::class, 'order_id', 'id');
    }
}
