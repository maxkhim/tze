<?php

namespace App\Models;

use App\Events\OrderStatusChangedEvent;
use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $dispatchesEvents = [
        'updated' => OrderStatusChangedEvent::class
    ];

    protected $fillable = ['customer_id', 'status', 'total_amount', 'confirmed_at', 'shipped_at'];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    // Константы статусов
    public const STATUS_NEW = 'new';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    // Массив всех допустимых статусов (удобно для валидации)
    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONFIRMED,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Скоупы для фильтрации
    public function scopeByStatus($query, $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $customerId ? $query->where('customer_id', $customerId) : $query;
    }

    public function scopeByDateRange($query, $from, $to)
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }
        return $query;
    }
}