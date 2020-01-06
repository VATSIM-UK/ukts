<?php

namespace App\Modules\Bookings;

use App\Modules\Position\Position;
use App\User;
use Carbon\Carbon;

interface BookingsServiceInterface
{
    /**
     * Check if the the user is able to book the given position only on the rating requirement.
     *
     * @param  User  $user
     * @param  Position  $position
     * @return bool
     */
    public function validateRatingRequirement(User $user, Position $position): bool;

    /**
     * Validate that two given times are valid within the context of other bookings.
     *
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @param  Position  $position
     * @param  int|null  $excluded  - Booking ID to be excluded from the check (typically used during an update).
     * @return bool
     */
    public function validateBookingTimes(Carbon $from, Carbon $to, Position $position, int $excluded = null): bool;

    /**
     * Validates whether the booking is allowed under any conditions of any relevant SpecialEndorsements.
     *
     * @param  User  $user
     * @param  Position  $position
     * @return bool
     */
    public function validateSpecialEndorsementRequirement(User $user, Position $position): bool;

    public function createBooking(array $bookingData): Booking;

    public function updateExistingBooking(array $newData): bool;
}
