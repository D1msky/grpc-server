<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingHistory extends Model
{
    protected $fillable = [
        'package_id',
        'location',
        'description',
        'status',
    ];

    /**
     * Get the package that owns the tracking history.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
