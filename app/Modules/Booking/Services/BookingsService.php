<?php

namespace App\Modules\Booking\Services;

use Error;
use App\Modules\Booking\Booking;
use App\Modules\Booking\Exceptions\OverlappingBookingException;
use App\Modules\Booking\Exceptions\RatingRequirementNotMetException;
use App\Modules\Booking\Exceptions\SpecialEndorsementNotAttainedException;
use App\Constants\ControllerRating;
use App\Modules\Position\Position;
use App\User;
use Carbon\Carbon;

/** TODO: Users can view/amend other users bookings. We need to make sure we fix this. */

class BookingsService
{
    /** @var mixed App\User */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Check whether we've got a valid network type on our hands.
     *
     * @param  string  $type
     * @return bool
     */
    public function validateNetworkType(int $type): bool
    {
        // 0 = live network, 1 = sweatbox
        if ($type == 0 || $type == 1) return true;

        return false;
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
    public function validateBookingTimes(Carbon $from, Carbon $to, Position $position, int $network_type = 0, int $excluded = null): bool
    {
        $bookings = Booking::where([['position_id', $position->getKey()], ['network_type', $network_type], ['id', '!=', $excluded]]);

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
        $network_type = $bookingData['network_type'] ? $bookingData['network_type'] : 0;

        $bookingUser = $this->user::findOrFail($bookingData['user_id']);

        if (!$this->validateRatingRequirement($bookingUser, $position)) {
            throw new RatingRequirementNotMetException();
        }

        if (!$this->validateSpecialEndorsementRequirement($bookingUser, $position)) {
            throw new SpecialEndorsementNotAttainedException();
        }

        if (!$this->validateBookingTimes($from, $to, $position, $network_type)) {
            throw new OverlappingBookingException();
        }

        if (!$this->validateNetworkType($network_type)) {
            throw new Error("Invalid network type!");
        }

        return $bookingUser->bookings()->create([
            'user_id' => $bookingUser->id,
            'position_id' => $position->id,
            'network_type' => $network_type,
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
        $network_type = $existingBooking->network_type;

        if (!$this->validateBookingTimes($from, $to, $position, $existingBooking->getKey())) {
            throw new OverlappingBookingException();
        }

        if (!$this->validateNetworkType($network_type)) {
            throw new Error("Invalid network type!");
        }

        return $existingBooking->update([
            'from' => $from,
            'to' => $to,
            'network_type' => $network_type,
        ]);
    }
}
