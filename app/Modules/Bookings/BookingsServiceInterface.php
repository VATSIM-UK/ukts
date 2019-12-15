<?php

namespace App\Modules\Bookings;

use App\Position;
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

    public function validateBookingTimes(Carbon $from, Carbon $to, Position $position, int $excluded = null): bool;

    public function createBooking(array $bookingData): Booking;

    public function updateExistingBooking(array $newData): bool;
}
