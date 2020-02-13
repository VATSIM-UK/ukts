<?php

namespace Tests\Unit\Booking;

use App\Modules\Booking\Exceptions\OverlappingBookingException;
use App\Modules\Booking\Booking;
use App\Modules\Booking\Services\BookingsService;
use App\Modules\Position\Position;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceUpdateTest extends TestCase
{
    use RefreshDatabase;

    private $service;
    private $mockUserModel;
    private $position;
    private $existingBooking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(BookingsService::class);

        $this->position = factory(Position::class)->create();

        $this->existingBooking = factory(Booking::class)->create(['position_id' => $this->position->id]);

        $this->mockUserModel = User::initModelWithData([
            'id' => 1234567,
            'name_first' => 'First',
            'name_last' => 'Last',
            'atcRating' => (object) ['code' => 'S2', 'vatsim_id' => 3],
        ]);
    }

    /** @test */
    public function itUpdatesBookingGivenValidParameters()
    {
        $params = $this->generateUpdateMethodParameters();
        $serviceResult = $this->service->updateExistingBooking($params);

        $this->assertTrue($serviceResult);
        $this->assertDatabaseHas('bookings', [
            'id' => $params['id'],
            'position_id' => $params['position_id'],
            'from' => $params['from'],
            'to' => $params['to'],
        ]);
    }

    /** @test */
    public function itThrowsModelNotFoundExceptionWhenInvalidBookingIsGiven()
    {
        $invalidBookingId = -1;
        $this->expectException(ModelNotFoundException::class);

        $this->service->updateExistingBooking($this->generateUpdateMethodParameters(['id' => $invalidBookingId]));
    }

    /** @test */
    public function itChecksForOverlappingBookingsOnUpdate()
    {
        $this->expectException(OverlappingBookingException::class);

        // explicitly setting booking times to stop coincidental test pass
        $this->existingBooking->from = new Carbon('10th January 2019 13:30:00');
        $this->existingBooking->to = new Carbon('10th January 2019 15:30:00');
        $this->existingBooking->save();

        $otherBooking = factory(Booking::class)->create([
            'position_id' => $this->position->id,
            'from' => new Carbon('10th January 2019 16:30:00'),
            'to' => new Carbon('10th January 2019 18:30:00'),
        ]);

        $this->service->updateExistingBooking($this->generateUpdateMethodParameters([
            'id' => $this->existingBooking->id,
            'from' => new Carbon('10th January 2019 16:45:00'),
            'to' => new Carbon('10th January 2019 18:45:00'),
        ]));
    }

    private function generateUpdateMethodParameters(array $overrides = []): array
    {
        return array_merge([
            'id' => $this->existingBooking->id,
            'position_id' => $this->position->id,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:00:00'),
        ], $overrides);
    }
}
