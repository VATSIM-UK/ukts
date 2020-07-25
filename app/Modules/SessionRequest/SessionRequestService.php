<?php

namespace App\Modules\SessionRequest;

use App\Modules\Bookings\Booking;
use App\Modules\Bookings\Services\BookingsService;
use App\Modules\Position\Position;
use App\Modules\SessionRequest\Exceptions\SessionRequestAlreadyAcceptedException;
use App\Modules\SessionRequest\Exceptions\SessionRequestAlreadyExistsException;
use App\User;
use Carbon\Carbon;

class SessionRequestService
{
    private $bookingsService;

    public function __construct(BookingsService $bookingsService)
    {
        $this->bookingsService = $bookingsService;
    }

    public function createSessionRequest(User $user, Position $position)
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

    public function revokeSessionRequest(SessionRequest $sessionRequest)
    {
        return $sessionRequest->delete();
    }

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
        $sessionRequest->taken_on = Carbon::now();
        $sessionRequest->taken_by = $acceptingUser->id;

        $bookingStart = $bookingDateFrom ?: $sessionDateFrom;
        $bookingEnd = $bookingDateTo ?: $sessionDateTo;

        $booking = $this->bookingsService->createBooking(
            [
                'from' => $bookingStart,
                'to' => $bookingEnd,
                'network_type' => $networkType,
                'user_id' => $acceptingUser->id,
                'position_id' => $sessionRequest->position_id
            ], true);

        $sessionRequest->booking()->associate($booking);
        $sessionRequest->save();

        return $sessionRequest;
    }
}
