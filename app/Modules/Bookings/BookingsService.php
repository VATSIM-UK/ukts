<?php

namespace App\Modules\Bookings;

use App\Constants\ControllerRating;
use App\Exceptions\OverlappingBookingException;
use App\Modules\Position\Position;
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

    /**
     * Validate that two given times are valid within the context of other bookings.
     *
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @param  Position  $position
     * @param  int|null  $excluded  - Booking ID to be excluded from the check.
     * @return bool
     */
    public function validateBookingTimes(Carbon $from, Carbon $to, Position $position, int $excluded = null): bool
    {
        $bookings = Booking::where([['position_id', $position->getKey()], ['id', '!=', $excluded]]);

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

    /**
     * Validates whether the booking is allowed under any conditions of any relevant SpecialEndorsements.
     *
     * @param  User  $user
     * @param  Position  $position
     * @return bool
     */
    public function validateSpecialEndorsementRequirement(User $user, Position $position): bool
    {
        $requiredEndorsements = $position->specialEndorsements;

        if ($requiredEndorsements->isEmpty()) {
            return true;
        }

        return $requiredEndorsements->every(function ($value) use (&$user) {
            return $user->specialEndorsements->contains('id', $value->id);
        });
    }

    public function createBooking(array $bookingData): Booking
    {
        ['from' => $from, 'to' => $to] = $bookingData;
        $position = Position::findOrFail($bookingData['position_id']);

        $bookingUser = $this->user::findOrFail($bookingData['user_id']);

        if (! $this->validateRatingRequirement($bookingUser, $position)) {
            throw new RatingRequirementNotMetException();
        }

        if (! $this->validateSpecialEndorsementRequirement($bookingUser, $position)) {
            throw new SpecialEndorsementNotAttainedException();
        }

        if (! $this->validateBookingTimes($from, $to, $position)) {
            throw new OverlappingBookingException();
        }

        return $bookingUser->bookings()->create([
            'user_id' => $bookingUser->id,
            'position_id' => $position->id,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function updateExistingBooking(array $newData): bool
    {
        ['from' => $from, 'to' => $to] = $newData;
        /** @var Booking $existingBooking */
        $existingBooking = Booking::findOrFail($newData['id']);

        $position = $existingBooking->position;

        if (! $this->validateBookingTimes($from, $to, $position, $existingBooking->getKey())) {
            throw new OverlappingBookingException();
        }

        return $existingBooking->update([
            'from' => $from,
            'to' => $to,
        ]);
    }
}
