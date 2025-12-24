<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminException extends Model
{
    protected $fillable = [
        'user_id',
        'booking_id',
        'exhibition_id',
        'exception_type',
        'description',
        'old_value',
        'new_value',
        'created_by',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    /**
     * Get the user (client) that this exception is for
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the booking associated with this exception
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the exhibition associated with this exception
     */
    public function exhibition(): BelongsTo
    {
        return $this->belongsTo(Exhibition::class);
    }

    /**
     * Get the admin who created this exception
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Helper method to log an admin exception
     * 
     * @param int $userId The client/user ID
     * @param int|null $bookingId The booking ID (if applicable)
     * @param int|null $exhibitionId The exhibition ID
     * @param string $exceptionType Type of exception (e.g., 'price_override', 'badge_override', 'due_date_override')
     * @param string $description Description of the override
     * @param mixed $oldValue The old value (will be JSON encoded)
     * @param mixed $newValue The new value (will be JSON encoded)
     * @param int|null $createdBy Admin user ID (defaults to current auth user)
     * @return AdminException
     */
    public static function log(
        int $userId,
        ?int $bookingId,
        ?int $exhibitionId,
        string $exceptionType,
        string $description,
        $oldValue = null,
        $newValue = null,
        ?int $createdBy = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'booking_id' => $bookingId,
            'exhibition_id' => $exhibitionId,
            'exception_type' => $exceptionType,
            'description' => $description,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'created_by' => $createdBy ?? auth()->id(),
        ]);
    }
}
