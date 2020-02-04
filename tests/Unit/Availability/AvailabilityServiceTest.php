<?php

namespace Tests\Unit\Availability;

use App\Exceptions\OverlappingAvailabilityException;
use App\Modules\Availability\Availability;
use App\Modules\Availability\AvailabilityService;
use App\Modules\Position\Position;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Unit\Availability\AvailabilityTestHelper;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase, AvailabilityTestHelper;

    protected $position;
    protected $service;
    private $mockUserModel;

    protected function setUp(): void
    {
        parent::setUp();
        DB::enableQueryLog();

        $this->position = factory(Position::class)->create();

        $this->mockUserModel = User::initModelWithData([
            'id' => 1234567,
            'name_fist' => 'First',
            'name_last' => 'Last',
            'atcRating' => (object) ['code' => 'S2', 'vatsim_id' => 3],
        ]);

        $this->mockUserFind();

        $this->service = $this->app->make(AvailabilityService::class);

    }

    /** @test */
    public function itDetectsWhenAvailabilityOverlapsOnStartTime()
    {
        $from = new Carbon('10th January 2019 15:00:00');
        $to = new Carbon('10th January 2019 16:30:00');

        // create existing availability
        factory(Availability::class)->create(['from' => $from, 'to' => $to, 'user_id' => $this->mockUserModel->id]);

        $overlappingFrom = new Carbon('10th January 2019 14:45:00');
        $overlappingTo = new Carbon('10th January 2019 16:00:00');

        $this->assertFalse($this->service->validateAvailabilityTimes($overlappingFrom, $overlappingTo, $this->mockUserModel));
    }

    /** @test */
    public function itDetectsWhenAvailabilityOverlapsOnEndTime()
    {

        // create existing availability
        factory(Availability::class)->create([
            'from' => new Carbon('10th January 2019 15:00:00'),
            'to' => new Carbon('10th January 2019 16:30:00'),
            'user_id' => $this->mockUserModel->id
        ]);

        $overlappingFrom = new Carbon('10th January 2019 16:00:00');
        $overlappingTo = new Carbon('10th January 2019 17:00:00');

        $this->assertFalse($this->service->validateAvailabilityTimes($overlappingFrom, $overlappingTo, $this->mockUserModel));
    }

    /** @test */
    public function itDetectsWhenAvailabilityOverlapsCompletely()
    {
        $from = new Carbon('10th January 2019 15:00:00');
        $to = new Carbon('10th January 2019 16:30:00');

        // create existing availability
        factory(Availability::class)->create([
            'from' => $from,
            'to' => $to,
            'user_id' => $this->mockUserModel->id
        ]);

        $this->assertFalse($this->service->validateAvailabilityTimes($from, $to, $this->mockUserModel));
        $this->assertFalse($this->service->validateAvailabilityTimes($from->subHour(), $to->addHour(),  $this->mockUserModel));
    }

    /** @test */
    public function itAllowsAvailabilityWhenNoOverlapDetected()
    {
        factory(Availability::class)->create([
            'from' => new Carbon('10th January 2019 15:00:00'),
            'to' => new Carbon('10th January 2019 16:30:00'),
            'user_id' => $this->mockUserModel->id
        ]);

        $this->assertTrue($this->service->validateAvailabilityTimes(
            new Carbon('10th January 2019 13:00:00'),
            new Carbon('10th January 2019 14:00:00'),
            $this->mockUserModel
        ));
    }

    /** @test */
    public function itAllowsAvailabilityWhenUpdatedToTimeOverlappingOriginal()
    {
        $availability = factory(Availability::class)->create([
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 15:00:00'),
            'user_id' => $this->mockUserModel->id
        ]);

        $this->assertTrue($this->service->validateAvailabilityTimes(
            new Carbon('10th January 2019 13:00:00'),
            new Carbon('10th January 2019 14:00:00'),
            $this->mockUserModel,
            $availability->id
        ));
    }

    /** @test */
    public function itDoesntAllowAvailabilityWhichWhenUpdatedOverlapWithAnotherAvailability()
    {
        $availability = factory(Availability::class)->create([
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 15:00:00'),
            'user_id' => $this->mockUserModel->id
        ]);

        $availability2 = factory(Availability::class)->create([
            'from' => new Carbon('10th January 2019 16:00:00'),
            'to' => new Carbon('10th January 2019 17:00:00'),
            'user_id' => $this->mockUserModel->id
        ]);

        $this->assertFalse($this->service->validateAvailabilityTimes(
            new Carbon('10th January 2019 15:30:00'),
            new Carbon('10th January 2019 17:00:00'),
            $this->mockUserModel,
            $availability->id
        ));
    }

    /** @test */
    public function itCreatesTheAvailabilityGivenAllValidParameters()
    {

        $this->service->createAvailability([
            'user_id' => $this->mockUserModel->id,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:00:00'),
        ]);

        $this->assertDatabaseHas('availability', [
            'user_id' => $this->mockUserModel->id,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:00:00'),
        ]);
    }

    /** @test */
    public function itThrowsAnExceptionWhenOverlapDetected()
    {
        $this->expectException(OverlappingAvailabilityException::class);

        factory(Availability::class)->create([
            'user_id' => $this->mockUserModel->id,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:30:00'),
        ]);

        $this->service->createAvailability([
            'user_id' => $this->mockUserModel->id,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:00:00'),
        ]);

        $this->assertDatabaseMissing('availability', [
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:30:00'),
        ]);
    }

    /** @test */
    public function itThrowsModelNotFoundExceptionWhenTheUserIsntFound()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->createAvailability([
            'user_id' => $this->invalidUserId,
            'from' => new Carbon('10th January 2019 13:00:00'),
            'to' => new Carbon('10th January 2019 14:00:00'),
        ]);
    }
}
