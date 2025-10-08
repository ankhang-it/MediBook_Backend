<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'payment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'payment_id',
        'order_id',
        'total_amount',
        'status',
        'description',
        'user_id',
        'appointment_id',
        'order_code',
        'payment_link_id',
        'checkout_url',
        'qr_code',
        'payment_info',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'payment_info' => 'array',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the appointment that owns the payment.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    /**
     * Scope a query to only include paid payments.
     */
    public function scopePaid($query)
    {
        return $query->whereNotNull('paid_at');
    }

    /**
     * Scope a query to only include unpaid payments.
     */
    public function scopeUnpaid($query)
    {
        return $query->whereNull('paid_at');
    }

    /**
     * Check if the payment is paid.
     */
    public function isPaid(): bool
    {
        return !is_null($this->paid_at);
    }

    /**
     * Get payment type from payment_info.
     */
    public function getPaymentTypeAttribute(): ?string
    {
        return $this->payment_info['payment_type'] ?? null;
    }

    /**
     * Get payment status from payment_info.
     */
    public function getPaymentStatusAttribute(): ?string
    {
        return $this->payment_info['status'] ?? null;
    }

    /**
     * Find payment by payment link ID.
     */
    public static function findByPaymentLinkId(string $paymentLinkId): ?self
    {
        return static::where('payment_link_id', $paymentLinkId)->first();
    }
}
