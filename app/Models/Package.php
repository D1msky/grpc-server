<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tracking_number',
        'sender_name',
        'sender_address',
        'sender_phone',
        'recipient_name',
        'recipient_address',
        'recipient_phone',
        'weight',
        'description',
        'package_type',
        'status',
        'current_location',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    /**
     * Get the tracking history for the package.
     */
    public function trackingHistories(): HasMany
    {
        return $this->hasMany(TrackingHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Generate a unique tracking number.
     */
    public static function generateTrackingNumber(): string
    {
        do {
            $trackingNumber = 'PKG' . strtoupper(substr(uniqid(), -8)) . rand(1000, 9999);
        } while (self::where('tracking_number', $trackingNumber)->exists());

        return $trackingNumber;
    }
}
