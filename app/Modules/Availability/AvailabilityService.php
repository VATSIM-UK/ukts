<?php

namespace App\Modules\Availability;

use App\Constants\ControllerRating;
use App\Exceptions\OverlappingAvailabilityException;
use App\Exceptions\PrivilegeException;
use App\Modules\Availability\AvailabilityInPastException;
use App\Modules\Position\Position;
use App\User;
use Carbon\Carbon;

class AvailabilityService
{
    /** @var mixed App\User */
    protected $user;

    /** @var int THIS MINIMUM TIME AVAILABILITY SHOULD BE */
    public $TIME_IN_MINS = 30;

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
     * Validate that two given times are valid within the context of other availability.
     *
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @param int $doNotCheck - Pass in an int to exclude from the search
     * @param  User  $user - User
     * @param  int|null  $excluded  - Availability ID to be excluded from the check.
     * @throws AvailabilityInPastException - Cannot add availability that occurs in the past
     * @return bool
     */
    public function validateAvailabilityTimes(Carbon $from, Carbon $to, User $user, int $doNotCheck =  NULL): bool
    {
        if ($from->isAfter($to)) {
            return false;
        }

        if ($from->isBefore(new Carbon())) {
            throw new AvailabilityInPastException();
        }

        if ($from->diffInMinutes($to) < $this->TIME_IN_MINS) {
            throw new AvailabilityMinimumTimeException();
        }

        $avail = Availability::where([['user_id', $user->id], ['id', '!=', $doNotCheck]]);

        // Find between the times being booked for
        $avail->where(function ($query) use (&$from, &$to) {
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

        return $avail->doesntExist();
    }

    public function createAvailability(array $availabilityData): Availability
    {
        ['from' => $from, 'to' => $to, 'user_id' => $userId] = $availabilityData;

        $availabilityUser = $this->user::findOrFail($userId);

        try {
            $passesTimeChecks = $this->validateAvailabilityTimes($from, $to, $availabilityUser);
        } catch (Exception $e)  {
            if (get_class($e) == 'AvailabilityInPastException') {
                throw new AvailabilityInPastException();
            } else {
                throw new AvailabilityMinimumTimeException();
            }
        }

        if (!$passesTimeChecks) {
            throw new OverlappingAvailabilityException();

        }
        return $availabilityUser->availability()->create([
            'user_id' => $availabilityUser->id,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function updateExistingAvailability(array $newData, bool $adminOverride = false): bool
    {
        ['from' => $from, 'to' => $to, 'user_id' => $userId] = $newData;

        /** @var Availability $existingAvailability */
        $existingAvailability = Availability::findOrFail($newData['id']);

        $availabilityUser = $this->user::findOrFail($userId);

        if (!$adminOverride && ($userId != $existingAvailability['user_id'])) {
            throw new PrivilegeException('update this availability');
        }

        try {
            $passesTimeChecks = $this->validateAvailabilityTimes($from, $to, $availabilityUser, $existingAvailability->getKey());
        } catch (Exception $e)  {
            if (get_class($e) == 'AvailabilityInPastException') {
                throw new AvailabilityInPastException();
            } else {
                throw new AvailabilityMinimumTimeException($this->TIME_IN_MINS);
            }
        }

        if (!$passesTimeChecks) {
            throw new OverlappingAvailabilityException();
        }

        return $existingAvailability->update([
            'user_id' => $availabilityUser->id,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function removeAvailabilityRange(array $newData, bool $adminOverride = false): int
    {
        ['from' => $from, 'to' => $to, 'user_id' => $userId] = $newData;

        if ($from < new Carbon()) {
            throw new AvailabilityInPastException();
        }

        $availabilityUser = $this->user::findOrFail($userId);

        $avail = Availability::where(function ($query) use (&$from, &$to, &$adminOverride, &$availabilityUser) {
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

        if (!$adminOverride) {
            $avail->where(['user_id' => $availabilityUser->id]);
        }

        return $avail->delete();
    }
}