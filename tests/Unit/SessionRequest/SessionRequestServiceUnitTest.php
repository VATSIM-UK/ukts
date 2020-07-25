<?php

namespace Tests\Unit\SessionRequest;

use App\Modules\Position\Position;
use App\Modules\SessionRequest\Exceptions\SessionRequestAlreadyAcceptedException;
use App\Modules\SessionRequest\Exceptions\SessionRequestAlreadyExistsException;
use App\Modules\SessionRequest\SessionRequest;
use App\Modules\SessionRequest\SessionRequestService;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class SessionRequestServiceUnitTest extends TestCase
{
    use RefreshDatabase, UserHelper;

    private $service;
    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockUserFind();

        $this->service = $this->app->make(SessionRequestService::class);

        $this->user = $this->mockedUser();
    }

    /** @test */
    public function itCanCreateASessionRequest()
    {
        $this->user = factory(User::class)->make();
        $position = factory(Position::class)->create();

        $this->service->createSessionRequest($this->user, $position);

        $this->assertDatabaseHas('session_requests', [
            'user_id' => $this->user->id,
            'position_id' => $position->id,
        ]);
    }

    /** @test */
    public function itDoesntAllowDuplicateSessionRequestsForTheSamePosition()
    {
        $this->expectException(SessionRequestAlreadyExistsException::class);
        $position = factory(Position::class)->create();
        factory(SessionRequest::class)->create([
            'position_id' => $position->id, 'user_id' => $this->user->id,
        ]);

        $this->service->createSessionRequest($this->user, $position);

        $this->assertDatabaseHas('session_requests', [
            'position_id' => $position->id,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function itCanBeRevokedByTheCreator()
    {
        $sessionRequest = factory(SessionRequest::class)->create([
            'taken_on' => now(), 'taken_by' => 1234567, 'booking_id' => null,
        ]);

        $this->service->revokeSessionRequest($sessionRequest);

        $this->assertSoftDeleted('session_requests', ['id' => $sessionRequest->id]);
    }

    /** @test */
    public function itCanBeAcceptedByAUserWhenSessionIsSameAsBookingTime()
    {
        $sessionRequest = factory(SessionRequest::class)->create();

        $from = Carbon::now()->subHours(1);
        $to = Carbon::now();

        $this->service->acceptSessionRequest($sessionRequest, $this->mockedUser(), $from, $to);

        $this->assertDatabaseHas('session_requests', [
            'id' => $sessionRequest->id,
            'start_date' => $from,
            'end_date' => $to,
            'taken_on' => Carbon::now(),
            'taken_by' => $this->user->id,
            'booking_id' => $sessionRequest->booking->id,
        ]);

        $this->assertDatabaseHas('bookings', [
            'from' => $from,
            'to' => $to,
            'position_id' => $sessionRequest->id,
        ]);
    }

    /** @test */
    public function itCanBeAcceptedByAUserWhenSessionTimeDiffersFromBookingTime()
    {
        $sessionRequest = factory(SessionRequest::class)->create();

        $sessionFrom = Carbon::now()->subHours(2);
        $sessionTo = Carbon::now();
        $bookingFrom = Carbon::now()->subHours(1);
        $bookingTo = Carbon::now();

        $this->service->acceptSessionRequest($sessionRequest, $this->mockedUser(), $sessionFrom, $sessionTo,
            $bookingFrom, $bookingTo);

        $this->assertDatabaseHas('session_requests', [
            'id' => $sessionRequest->id,
            'start_date' => $sessionFrom,
            'end_date' => $sessionTo,
            'taken_on' => Carbon::now(),
            'taken_by' => $this->user->id,
            'booking_id' => $sessionRequest->booking->id,
        ]);

        $this->assertDatabaseHas('bookings', [
            'from' => $bookingFrom,
            'to' => $bookingTo,
            'position_id' => $sessionRequest->id,
        ]);
    }

    /** @test */
    public function itThrowsExceptionWhenSessionRequestAlreadyTaken()
    {
        $this->expectException(SessionRequestAlreadyAcceptedException::class);

        $sessionRequest = factory(SessionRequest::class)->create([
            'taken_on' => Carbon::now(), 'taken_by' => $this->mockUserId,
        ]);

        $this->service->acceptSessionRequest($sessionRequest, $this->mockedUser(), Carbon::now()->subHours(2),
            Carbon::now());
    }
}
