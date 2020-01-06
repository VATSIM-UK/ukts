<?php

namespace Tests\Unit\Booking;

use App\Exceptions\OverlappingBookingException;
use App\Modules\Bookings\Booking;
use App\Modules\Bookings\BookingsServiceInterface;
use App\Modules\Bookings\RatingRequirementNotMetException;
use App\Modules\Endorsement\Special\Assignment;
use App\Modules\Endorsement\Special\SpecialEndorsement;
use App\Modules\Position\Position;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingsServiceTest extends TestCase
{
    use RefreshDatabase, BookingsTestHelper;

    protected $position;
    protected $service;
    private $mockUserModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->position = factory(Position::class)->create();

        $this->service = $this->app->make(BookingsServiceInterface::class);

        $this->mockUserModel = User::initModelWithData([
            'id' => 1234567,
            'name_fist' => 'First',
            'name_last' => 'Last',
            'atcRating' => (object) ['code' => 'S2', 'vatsim_id' => 3],
        ]);
    }

    /** @test */
    public function itDetectsWhenAUserDoesntMeetRatingRequirements()
    {
        $specificPosition = factory(Position::class)->create(['callsign' => 'EGGD_APP']);

        $this->assertFalse($this->service->validateRatingRequirement($this->mockUserModel, $specificPosition));
    }

    /** @test */
    public function itDetectsWhenAUserMeetsRatingRequirements()
    {
        $specificPosition = factory(Position::class)->create(['callsign' => 'EGGD_TWR']);

        $this->assertTrue($this->service->validateRatingRequirement($this->mockUserModel, $specificPosition));
    }

    /** @test */
    public function itDetectsWhenBookingOverlapsOnStartTime()
    {
        $from = new Carbon('10th January 2019 15:00:00');
        $to = new Carbon('10th January 2019 16:30:00');

        // create existing booking
        factory(Booking::class)->create(['from' => $from, 'to' => $to, 'position_id' => $this->position->id]);

        $overlappingFrom = new Carbon('10th January 2019 14:45:00');
        $overlappingTo = new Carbon('10th January 2019 16:00:00');

        $this->assertFalse($this->service->validateBookingTimes($overlappingFrom, $overlappingTo, $this->position));
    }

    /** @test */
    public function itDetectsWhenBookingOverlapsOnEndTime()
    {

        // create existing booking
        factory(Booking::class)->create([
            'from' => new Carbon('10th January 2019 15:00:00'),
            'to' => new Carbon('10th January 2019 16:30:00'),
            'position_id' => $this->position->id,
        ]);

        $overlappingFrom = new Carbon('10th January 2019 16:00:00');
        $overlappingTo = new Carbon('10th January 2019 17:00:00');

        $this->assertFalse($this->service->validateBookingTimes($overlappingFrom, $overlappingTo, $this->position));
    }

    /** @test */
    public function itDetectsWhenBookingOverlapsCompletely()
    {
        $from = new Carbon('10th January 2019 15:00:00');
        $to = new Carbon('10th January 2019 16:30:00');

        // create existing booking
        factory(Booking::class)->create([
            'from' => $from,
            'to' => $to,
            'position_id' => $this->position->id,
        ]);

        $this->assertFalse($this->service->validateBookingTimes($from, $to, $this->position));
        $this->assertFalse($this->service->validateBookingTimes($from->subHour(), $to->addHour(), $this->position));
    }

    /** @test */
    public function itAllowsBookingWhenNoOverlapDetected()
    {
        factory(Booking::class)->create([
            'from' => new Carbon('10th January 2019 15:00:00'),
            'to' => new Carbon('10th January 2019 16:30:00'),
            'position_id' => $this->position->id,
        ]);

        $this->assertTrue($this->service->validateBookingTimes(
            new Carbon('10th January 2019 13:00:00'),
            new Carbon('10th January 2019 14:00:00'),
            $this->position
        ));
    }

    /** @test */
    public function itAllowsBookingsWhichAreBackToBackOnSamePosition()
    {
        factory(Booking::class)->create([
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 15:00:00'),
            'position_id' => $this->position->id,
        ]);

        $this->assertTrue($this->service->validateBookingTimes(
            new Carbon('10th January 2019 15:00:00'),
            new Carbon('10th January 2019 16:00:00'),
            $this->position
        ));
    }

    /** @test */
    public function itAllowsBookingsWhenUpdatedToTimeOverlappingOriginal()
    {
        $booking = factory(Booking::class)->create([
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 15:00:00'),
            'position_id' => $this->position->id,
        ]);

        $this->assertTrue($this->service->validateBookingTimes(
            new Carbon('10th January 2019 13:00:00'),
            new Carbon('10th January 2019 14:00:00'),
            $this->position,
            $booking->id
        ));
    }

    /** @test */
    public function itDoesntAllowBookingsWhichWhenUpdatedOverlapWithAnotherBooking()
    {
        $booking = factory(Booking::class)->create([
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 15:00:00'),
            'position_id' => $this->position->id,
        ]);

        $booking2 = factory(Booking::class)->create([
            'from' => new Carbon('10th January 2019 16:00:00'),
            'to' => new Carbon('10th January 2019 17:00:00'),
            'position_id' => $this->position->id,
        ]);

        $this->assertFalse($this->service->validateBookingTimes(
            new Carbon('10th January 2019 15:30:00'),
            new Carbon('10th January 2019 17:00:00'),
            $this->position,
            $booking->id
        ));
    }

    /** @test */
    public function itShouldAllowBookingWhenMandatedEndorsementOnPositionIsAttained()
    {
        $specialEndorsement = factory(SpecialEndorsement::class)->create();

        $this->mandateEndorsementForPositionHelper($specialEndorsement->id, $this->position->id);

        $this->grantEndorsementHelper($this->mockUserId, $specialEndorsement->id);

        $this->assertTrue($this->service->validateSpecialEndorsementRequirement($this->mockUserModel, $this->position));
    }

    /** @test */
    public function itShouldNotAllowBookingWhenMandatedEndorsementOnPositionIsNotAttained()
    {
        $specialEndorsement = factory(SpecialEndorsement::class)->create();

        $this->mandateEndorsementForPositionHelper($specialEndorsement->id, $this->position->id);

        $this->assertFalse($this->service->validateSpecialEndorsementRequirement($this->mockUserModel,
            $this->position));
    }

    /** @test */
    public function itShouldNotAllowBookingWhenOnlyOneOfTwoMandatedEndorsementsAreAttained()
    {
        $specialEndorsement = factory(SpecialEndorsement::class)->create();
        $specialEndorsement2 = factory(SpecialEndorsement::class)->create();

        $this->mandateEndorsementForPositionHelper($specialEndorsement->id, $this->position->id);
        $this->mandateEndorsementForPositionHelper($specialEndorsement2->id, $this->position->id);

        $this->grantEndorsementHelper($this->mockUserId, $specialEndorsement->id);

        $this->assertFalse($this->service->validateSpecialEndorsementRequirement($this->mockUserModel,
            $this->position));
    }

    /** @test */
    public function itShouldAllowBookingWhenMultipleEndorsementsMandatedHaveAllBeenAttained()
    {
        $specialEndorsement = factory(SpecialEndorsement::class)->create();
        $specialEndorsement2 = factory(SpecialEndorsement::class)->create();

        $this->mandateEndorsementForPositionHelper($specialEndorsement->id, $this->position->id);
        $this->mandateEndorsementForPositionHelper($specialEndorsement2->id, $this->position->id);

        $this->grantEndorsementHelper($this->mockUserId, $specialEndorsement->id);
        $this->grantEndorsementHelper($this->mockUserId, $specialEndorsement2->id);

        $this->assertTrue($this->service->validateSpecialEndorsementRequirement($this->mockUserModel,
            $this->position));
    }

    /** @test */
    public function itCreatesTheBookingGivenAllValidParameters()
    {
        $this->position->callsign = 'EGGD_TWR';
        $this->position->save();

        $this->mockUserFind();

        // new service required to inject user dependency
        $newService = $this->app->make(BookingsServiceInterface::class);
        $newService->createBooking([
            'position_id' => $this->position->id,
            'user_id' => 1234567,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:00:00'),
        ]);

        $this->assertDatabaseHas('bookings', [
            'position_id' => $this->position->id,
            'user_id' => 1234567,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:00:00'),
        ]);
    }

    /** @test */
    public function itThrowsAnExceptionWhenOverlapDetected()
    {
        $this->expectException(OverlappingBookingException::class);
        $this->position->callsign = 'EGGD_TWR';
        $this->position->save();

        factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:30:00'),
        ]);

        $this->mockUserFind();

        // new service required to inject user dependency
        $newService = $this->app->make(BookingsServiceInterface::class);
        $newService->createBooking([
            'position_id' => $this->position->id,
            'user_id' => 1234567,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:00:00'),
        ]);

        $this->assertDatabaseMissing('bookings', [
            'position_id' => $this->position->id,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:30:00'),
        ]);
    }

    /** @test */
    public function itThrowsAnExceptionWhenRatingRequirementNotMet()
    {
        $this->expectException(RatingRequirementNotMetException::class);
        $this->position->callsign = 'EGGD_TWR';
        $this->position->save();

        $this->mockUserFind(['code' => 'OBS', 'vatsim_id' => 1]);

        // new service required to inject user dependency
        $newService = $this->app->make(BookingsServiceInterface::class);
        $newService->createBooking([
            'position_id' => $this->position->id,
            'user_id' => 1234567,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:00:00'),
        ]);

        $this->assertDatabaseMissing('bookings', [
            'position_id' => $this->position->id,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:30:00'),
        ]);
    }

    /** @test */
    public function itThrowsModelNotFoundExceptionWhenTheUserIsntFound()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('findOrFail')->andThrow(ModelNotFoundException::class);
        });

        $newService = $this->app->make(BookingsServiceInterface::class);
        $newService->createBooking([
            'position_id' => $this->position->id,
            'user_id' => 1234587,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:00:00'),
        ]);
    }

    /** @test */
    public function itThrowsModelNotFoundExceptionWhenThePositionIsntFound()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->mockUserFind();

        $newService = $this->app->make(BookingsServiceInterface::class);
        $newService->createBooking([
            'position_id' => 9999,
            'user_id' => 1234567,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:00:00'),
        ]);
    }

    private function grantEndorsementHelper($user_id, $endorsement_id)
    {
        Assignment::create([
            'endorsement_id' => $endorsement_id,
            'granted_by' => $user_id,
            'user_id' => $user_id,
        ]);
    }

    private function mandateEndorsementForPositionHelper($endorsement_id, $position_id)
    {
        DB::table('special_endorsement_positions')->insert([
            'endorsement_id' => $endorsement_id,
            'position_id' => $position_id,
        ]);
    }
}
