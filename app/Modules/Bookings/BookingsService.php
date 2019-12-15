<?php

namespace App\Modules\Bookings;

use App\Constants\ControllerRating;
use App\Exceptions\OverlappingBookingException;
use App\Position;
use App\User;
use Carbon\Carbon;

class BookingsService implements BookingsServiceInterface
{
    /** @var mixed App\User */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Check if the the user is able to book the given position only on the rating requirement.
     *
     * @param  User  $user
     * @param  Position  $position
     * @return bool
     */
    public function validateRatingRequirement(User $user, Position $position): bool
    {
        $positionSuffix = $position->suffix;

        $ratingValue = $user->atcRating->vatsim_id;

        return ControllerRating::isValidRatingForSuffix($positionSuffix, $ratingValue);
    }

    public function validateBookingTimes(Carbon $from, Carbon $to, Position $position): bool
    {
        $bookings = Booking::where('position_id', $position->getKey());

        // Find between the times being booked for
        $bookings->where(function ($query) use (&$from, &$to) {
            // Where the start date is inside the booked time
            $query->where(function ($query) use (&$from, &$to) {
                $query->where('from', '>', $from)
                    ->where('from', '<', $to);
                // Or where the end date is inside the booked time
            })->orWhere(function ($query) use (&$from, &$to) {
                $query->where('to', '>', $from)
                    ->where('to', '<', $to);
                // Or where the times are the same
            })->orWhere(['from' => $from, 'to' => $to]);
        });

        return $bookings->doesntExist();
    }

    public function createBooking(array $bookingData): array
    {
        ['from' => $from, 'to' => $to] = $bookingData;
        $position = Position::findOrFail($bookingData['position_id']);
        $bookingUser = $this->user::findOrFail($bookingData['user_id']);

        if (! $this->validateRatingRequirement($bookingUser, $position)) {
            throw new RatingRequirementNotMetException();
        }

        if (! $this->validateBookingTimes($from, $to, $position)) {
            throw new OverlappingBookingException();
        }

        return Booking::create([
            'user_id' => $bookingUser->id,
            'position_id' => $position->id,
            'from' => $from,
            'to' => $to,
        ])->toArray();
    }
}
