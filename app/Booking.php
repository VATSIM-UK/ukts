<?php

namespace App;

use App\Exceptions\OverlappingBookingException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = ['user_id', 'position_id', 'from', 'to'];
    protected $dates = ['from', 'to'];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public static function canBeMade($position_id, Carbon $from, Carbon $to, $excludeID = null)
    {
        $query = self::where('position_id', $position_id);

        if ($excludeID) {
            $query->where('id', '!=', $excludeID);
        }

        // Find between the times being booked for
        $query->where(function ($query) use ($from, $to) {
            // Where the start date is inside the booked time
            $query->where(function ($query) use ($from, $to) {
                $query->where('from', '>', $from)
                        ->where('from', '<', $to);
                // Or where the end date is inside the booked time
            })->orWhere(function ($query) use ($from, $to) {
                $query->where('to', '>', $from)
                        ->where('to', '<', $to);
                // Or where the times are the same
            })->orWhere(['from' => $from, 'to' => $to]);
        });

        return $query->doesntExist();
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function (self $booking) {
            if (! self::canBeMade($booking->position_id, $booking->from, $booking->to)) {
                throw new OverlappingBookingException();
            }
        });

        self::updating(function (self $booking) {
            if (! self::canBeMade($booking->position_id, $booking->from, $booking->to, $booking->id)) {
                throw new OverlappingBookingException();
            }
        });
    }
}
