<?php

namespace App;

use App\Exceptions\OverlappingBookingException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = ['user_id', 'position_id', 'from', 'to'];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public static function canBeMade($position_id, $from, $to, $excludeID = null)
    {
        $query = self::where('position_id', $position_id)
            ->where(function ($query) use ($from, $to) {
                $query->where(function ($query) use ($from, $to) {
                    $query->where('from', '>', $from)
                        ->where('from', '<', $to);
                })->orWhere(function ($query) use ($from, $to) {
                    $query->where('to', '>', $from)
                        ->where('to', '<', $to);
                });
            })
            ->orWhere(['from' => $from, 'to' => $to]);

        if($excludeID){
            $query->where('id', '!=', $excludeID);
        }

        return !$query->exists();
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function (self $booking) {
            if (!self::canBeMade($booking->position_id, $booking->from, $booking->to)) {
                throw new OverlappingBookingException();
            }
        });

        self::updating(function (self $booking) {
            if (!self::canBeMade($booking->position_id, $booking->from, $booking->to, $booking->id)) {
                throw new OverlappingBookingException();
            }
        });
    }

}
