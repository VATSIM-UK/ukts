<?php

namespace App\Modules\Bookings\Services;

use App\Constants\ControllerRating;
use App\Modules\Bookings\Booking;
use App\Modules\Bookings\Exceptions\OverlappingBookingException;
use App\Modules\Bookings\Exceptions\RatingRequirementNotMetException;
use App\Modules\Bookings\Exceptions\SpecialEndorsementNotAttainedException;
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
        return in_array($type, [Booking::NETWORK_TYPE_LIVE, Booking::NETWORK_TYPE_SWEATBOX]);
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
    public function validateBookingTimes(
        Carbon $from,
        Carbon $to,
        Position $position,
        int $network_type,
        int $excluded = null
    ): bool {
        $bookings = Booking::where([
            ['position_id', $position->getKey()],
            ['network_type', $network_type],
            ['id', '!=', $excluded],
        ]);

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

    /**
     * Validate if the user is allowed to book on the position on the premise of them having a solo endorsement.
     * Should be used in conjunction with a rating check.
     *
     * @param  User  $user
     * @param  Position  $position
     * @return bool
     */
    public function validateSoloEndorsementEligibility(User $user, Position $position): bool
    {
        $soloEndorsements = $position->load('soloEndorsements')->soloEndorsements()->where(function ($query) {
            return $query->active();
        })->get();

        // exit if no active endorsements on position to save later queries.
        if ($soloEndorsements->isEmpty()) {
            return false;
        }

        $userAssignedSoloEndorsements = $soloEndorsements->where('user_id', '==', $user->id);

        return ! $userAssignedSoloEndorsements->isEmpty();
    }

    public function createBooking(array $bookingData): Booking
    {
        ['from' => $from, 'to' => $to, 'network_type' => $network_type] = $bookingData;
        $position = Position::findOrFail($bookingData['position_id']);

        $bookingUser = $this->user::findOrFail($bookingData['user_id']);

        if (! $this->validateRatingRequirement($bookingUser, $position)) {
            if (! $this->validateSoloEndorsementEligibility($bookingUser, $position)) {
                throw new RatingRequirementNotMetException();
            }
        }

        if (! $this->validateSpecialEndorsementRequirement($bookingUser, $position)) {
            throw new SpecialEndorsementNotAttainedException();
        }

        if (! $this->validateNetworkType($network_type)) {
            throw new \InvalidArgumentException('Invalid network type!');
        }

        if (! $this->validateBookingTimes($from, $to, $position, $network_type)) {
            throw new OverlappingBookingException();
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
        ['from' => $from, 'to' => $to, 'network_type' => $network_type] = $newData;
        /** @var Booking $existingBooking */
        $existingBooking = Booking::findOrFail($newData['id']);

        $position = $existingBooking->position;

        if (! $this->validateBookingTimes($from, $to, $position, $network_type, $existingBooking->getKey())) {
            throw new OverlappingBookingException();
        }

        if (! $this->validateNetworkType($network_type)) {
            throw new \InvalidArgumentException('Invalid network type!');
        }

        return $existingBooking->update([
            'from' => $from,
            'to' => $to,
            'network_type' => $network_type,
        ]);
    }
}
