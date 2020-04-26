<?php

namespace App\Modules\Availability\Services;

use Exception;
use App\Modules\Availability\Availability;
use App\Modules\Availability\Exceptions\OverlappingAvailabilityException;
use App\Modules\Availability\Exceptions\AvailabilityMinimumTimeException;
use App\Modules\Availability\Exceptions\AvailabilityInPastException;
use App\Exceptions\PrivilegeException;
use App\User;
use Carbon\Carbon;

class AvailabilityService
{
    /** @var mixed App\User */
    protected $user;

    /** HIS MIN TIME BLOCK TIME AVAILABILITY SHOULD BE */
    const MIN_BLOCK_TIME = 30;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Validate that two given times are valid within the context of other availability.
     *
     * @param Carbon $from
     * @param Carbon $to
     * @param User $user - User
     * @param int $doNotCheck - Pass in an int to exclude from the search
     * @return bool
     * @throws AvailabilityMinimumTimeException
     * @throws AvailabilityInPastException
     * @throws \Throwable
     */
    public function validateAvailabilityTimes(Carbon $from, Carbon $to, User $user, int $doNotCheck =  null): bool
    {
        if ($from->isAfter($to)) {
            return false;
        }

        throw_if($from->isBefore(new Carbon()), AvailabilityInPastException::class);

        if ($from->diffInMinutes($to) < self::MIN_BLOCK_TIME) {
            throw new AvailabilityMinimumTimeException(self::MIN_BLOCK_TIME);
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
        } catch (Exception $e) {
            if (get_class($e) == 'AvailabilityInPastException') {
                throw new AvailabilityInPastException();
            } else {
                throw new AvailabilityMinimumTimeException(self::MIN_BLOCK_TIME);
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
            $passesTimeChecks = $this->validateAvailabilityTimes(
                $from,
                $to,
                $availabilityUser,
                $existingAvailability->getKey()
            );
        } catch (AvailabilityInPastException $e) {
            throw new AvailabilityInPastException();
        } catch (AvailabilityMinimumTimeException $e) {
            throw new AvailabilityMinimumTimeException(self::MIN_BLOCK_TIME);
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
