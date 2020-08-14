<?php

namespace App\Modules\SessionRequest;

use App\Modules\Bookings\Booking;
use App\Modules\Bookings\Services\BookingsService;
use App\Modules\Position\Position;
use App\Modules\SessionRequest\Exceptions\SessionRequestAlreadyAcceptedException;
use App\Modules\SessionRequest\Exceptions\SessionRequestAlreadyExistsException;
use App\Modules\SessionRequest\Exceptions\SessionRequestNotAcceptedException;
use App\User;
use Carbon\Carbon;

class SessionRequestService
{
    private BookingsService $bookingsService;

    public function __construct(BookingsService $bookingsService)
    {
        $this->bookingsService = $bookingsService;
    }

    /**
     * Creates a session request for a given user on a particular position.
     *
     * @param User $user
     * @param Position $position
     * @return SessionRequest
     */
    public function createSessionRequest(User $user, Position $position): SessionRequest
    {
        $existingSessionRequests = SessionRequest::where('user_id', $user->id)->where(
            'position_id',
            $position->id
        )->get();

        throw_if(! $existingSessionRequests->isEmpty(), SessionRequestAlreadyExistsException::class);

        return SessionRequest::create([
            'user_id' => $user->id,
            'position_id' => $position->id,
        ]);
    }

    /**
     * Revokes a session request for a given session which hasn't been accepted.
     *
     * @param SessionRequest $sessionRequest
     * @return bool
     */
    public function revokeSessionRequest(SessionRequest $sessionRequest): bool
    {
        throw_if($sessionRequest->isTaken(), SessionRequestAlreadyAcceptedException::class);

        return $sessionRequest->delete();
    }

    /**
     * Accept a session request with the given times.
     *
     * @param SessionRequest $sessionRequest
     * @param User $acceptingUser
     * @param Carbon $sessionDateFrom
     * @param Carbon $sessionDateTo
     * @param Carbon $bookingDateFrom
     * @param Carbon $bookingDateTo
     * @param int $networkType
     * @return SessionRequest
     */
    public function acceptSessionRequest(
        SessionRequest $sessionRequest,
        User $acceptingUser,
        Carbon $sessionDateFrom,
        Carbon $sessionDateTo,
        Carbon $bookingDateFrom = null,
        Carbon $bookingDateTo = null,
        int $networkType = Booking::NETWORK_TYPE_LIVE
    ): SessionRequest {
        throw_if($sessionRequest->isTaken(), SessionRequestAlreadyAcceptedException::class);

        $sessionRequest->start_date = $sessionDateFrom;
        $sessionRequest->end_date = $sessionDateTo;
        $sessionRequest->accepted_at = Carbon::now();
        $sessionRequest->accepted_by = $acceptingUser->id;

        $bookingStart = $bookingDateFrom ?: $sessionDateFrom;
        $bookingEnd = $bookingDateTo ?: $sessionDateTo;

        $booking = $this->bookingsService->createBooking(
            [
                'from' => $bookingStart,
                'to' => $bookingEnd,
                'network_type' => $networkType,
                'user_id' => $acceptingUser->id,
                'position_id' => $sessionRequest->position_id,
            ],
            true
        );

        $sessionRequest->booking()->associate($booking);
        $sessionRequest->save();

        return $sessionRequest;
    }

    /**
     * Cancel a session request which has already been accepted.
     *
     * @param SessionRequest $sessionRequest
     * @param string $reason
     * @return SessionRequest
     */
    public function cancelSession(SessionRequest $sessionRequest, string $reason): SessionRequest
    {
        throw_if(! $sessionRequest->isTaken(), SessionRequestNotAcceptedException::class);

        $sessionRequest->update(['cancelled_at' => Carbon::now(), 'cancelled_reason' => $reason]);

        $sessionRequest->booking->delete();

        return $sessionRequest;
    }
}
