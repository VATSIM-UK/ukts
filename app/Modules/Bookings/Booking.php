<?php

namespace App\Modules\Bookings;

use App\Constants\ControllerRating;
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

    public function user()
    {
        return $this->belongsTo(resolve(User::class));
    }

    public static function canBeMade(Booking $booking, $excludeID = null)
    {
        if (!self::hasAppropriateRatingForPosition($booking)) {
            return false;
        }


        $query = self::where('position_id', $booking->position->id);

        if ($excludeID) {
            $query->where('id', '!=', $excludeID);
        }

        // Find between the times being booked for
        $query->where(function ($query) use ($booking) {
            // Where the start date is inside the booked time
            $query->where(function ($query) use ($booking) {
                $query->where('from', '>', $booking->from)
                    ->where('from', '<', $booking->to);
                // Or where the end date is inside the booked time
            })->orWhere(function ($query) use ($booking) {
                $query->where('to', '>', $booking->from)
                    ->where('to', '<', $booking->to);
                // Or where the times are the same
            })->orWhere(['from' => $booking->from, 'to' => $booking->to]);
        });

        return $query->doesntExist();
    }

    public static function hasAppropriateRatingForPosition(Booking $booking)
    {
        $positionSuffix = $booking->position->suffix;

        $ratingValue = $booking->user->atcRating->vatsim_id;

        return ControllerRating::isValidRatingForSuffix($positionSuffix, $ratingValue);
    }

    public static function boot()
    {
        parent::boot();

//        self::creating(function (self $booking) {
//            if (!self::canBeMade($booking)) {
//                throw new OverlappingBookingException();
//            }
//        });
//
//        self::updating(function (self $booking) {
//            if (!self::canBeMade($booking, $booking->id)) {
//                throw new OverlappingBookingException();
//            }
//        });
    }
}
