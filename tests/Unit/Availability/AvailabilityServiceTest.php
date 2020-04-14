<?php

namespace Tests\Unit\Availability;

use App\Exceptions\OverlappingAvailabilityException;
use App\Modules\Availability\Availability;
use App\Modules\Availability\AvailabilityMinimumTimeException;
use App\Modules\Availability\AvailabilityService;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase, UserHelper;

    protected $service;
    private $mockUserModel;

    protected function setUp(): void
    {
        parent::setUp();
        DB::enableQueryLog();

        $this->mockUserModel = new User([
            'id' => 1234567,
            'name_first' => 'First',
            'name_last' => 'Last',
            'atcRating' => (object) ['code' => 'S2', 'vatsim_id' => 3],
        ]);

        $this->mockUserFind();

        $this->service = $this->app->make(AvailabilityService::class);
    }

    /** @test */
    public function itDetectsWhenAvailabilityOverlapsOnStartTime()
    {
        $from = new Carbon();
        $from->addHours(2);
        $to = new Carbon();
        $to->addHours(3);

        // create existing availability
        factory(Availability::class)->create(['from' => $from, 'to' => $to, 'user_id' => $this->mockUserModel->id]);

        $overlappingTo = $to->copy()->addMinutes(20);

        $this->assertFalse($this->service->validateAvailabilityTimes($from, $overlappingTo, $this->mockUserModel));
    }

    /** @test */
    public function itDetectsWhenAvailabilityOverlapsOnEndTime()
    {
        $from = new Carbon();
        $from->addHours(2);
        $to = new Carbon();
        $to->addHours(1);

        // create existing availability
        factory(Availability::class)->create([
            'from' => $from,
            'to' => $to,
            'user_id' => $this->mockUserModel->id,
        ]);

        $overlappingFrom = $to->copy()->addMinutes(20);

        $this->assertFalse($this->service->validateAvailabilityTimes($overlappingFrom, $to, $this->mockUserModel));
    }

    /** @test */
    public function itDetectsWhenAvailabilityOverlapsCompletely()
    {
        $from = new Carbon();
        $from->addHours(2);
        $to = new Carbon();
        $to->addHours(5);

        // create existing availability
        factory(Availability::class)->create([
            'from' => $from,
            'to' => $to,
            'user_id' => $this->mockUserModel->id,
        ]);

        $this->assertFalse($this->service->validateAvailabilityTimes($from, $to, $this->mockUserModel));
        $this->assertFalse($this->service->validateAvailabilityTimes(
            $from->subHour(),
            $to->addHour(),
            $this->mockUserModel
        ));
    }

    /** @test */
    public function itAllowsAvailabilityWhenNoOverlapDetected()
    {
        $from = new Carbon();
        $from->addHours(2);
        $to = new Carbon();
        $to->addHours(3);

        factory(Availability::class)->create([
            'from' => $from,
            'to' => $to,
            'user_id' => $this->mockUserModel->id,
        ]);

        $this->assertTrue($this->service->validateAvailabilityTimes(
            $from->addHour(10),
            $to->addHour(11),
            $this->mockUserModel
        ));
    }

    /** @test */
    public function itAllowsAvailabilityWhenUpdatedToTimeOverlappingOriginal()
    {
        $from = new Carbon();
        $from->addHours(2);
        $to = $from->copy()->addHours(3);

        $availability = factory(Availability::class)->create([
            'from' => $from,
            'to' => $to,
            'user_id' => $this->mockUserModel->id,
        ]);

        $this->assertTrue($this->service->validateAvailabilityTimes(
            $from,
            $to->subMinutes(1),
            $this->mockUserModel,
            $availability->id
        ));
    }

    /** @test */
    public function itDoesntAllowAvailabilityWhichWhenUpdatedOverlapWithAnotherAvailability()
    {
        $from = new Carbon();
        $from->addHours(2);
        $to = new Carbon();
        $to->addHours(1);

        $from1 = $from->copy()->addHour();
        $to1 = $to->copy()->addHours(2);

        $availability = factory(Availability::class)->create([
            'from' => $from,
            'to' => $to,
            'user_id' => $this->mockUserModel->id,
        ]);

        $availability2 = factory(Availability::class)->create([
            'from' => $from1,
            'to' => $to1,
            'user_id' => $this->mockUserModel->id,
        ]);

        $this->assertFalse($this->service->validateAvailabilityTimes(
            $from->addMinutes(30),
            $to,
            $this->mockUserModel,
            $availability->id
        ));
    }

    /** @test */
    public function itCreatesTheAvailabilityGivenAllValidParameters()
    {
        $from = new Carbon();
        $from->addHours(2);
        $to = $from->copy()->addHours(1);

        $this->service->createAvailability([
            'user_id' => $this->mockUserModel->id,
            'from' => $from,
            'to' => $to,
        ]);

        $this->assertDatabaseHas('availability', [
            'user_id' => $this->mockUserModel->id,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /** @test */
    public function itThrowsAnExceptionWhenOverlapDetected()
    {
        $this->expectException(OverlappingAvailabilityException::class);

        $from = new Carbon();
        $from->addHours(2);
        $to = $from->copy()->addHours(1)->addMinutes(30);

        factory(Availability::class)->create([
            'user_id' => $this->mockUserModel->id,
            'from' => $from,
            'to' => $to,
        ]);

        $this->service->createAvailability([
            'user_id' => $this->mockUserModel->id,
            'from' => $from,
            'to' => $to->subMinutes(30),
        ]);

        $this->assertDatabaseMissing('availability', [
            'from' => $from,
            'to' => $to,
        ]);
    }

    /** @test */
    public function itThrowsModelNotFoundExceptionWhenTheUserIsntFound()
    {
        $this->expectException(ModelNotFoundException::class);

        $from = new Carbon();
        $from->addHours(2);
        $to = $from->addHours(1)->addMinutes(30);

        $this->service->createAvailability([
            'user_id' => $this->invalidUserId,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /** @test */
    public function itThrowsAnExceptionWhenTheLengthOfTimeIsUnderTheMinimumRequired()
    {
        $this->expectException(AvailabilityMinimumTimeException::class);

        $from = new Carbon();
        $from = $from->addHours(5);
        $to = $from->copy()->addMinutes(20);

        $this->service->createAvailability([
            'user_id' => $this->mockUserModel->id,
            'from' => $from,
            'to' => $to,
        ]);

        $this->assertDatabaseMissing('availability', [
            'from' => $from,
            'to' => $to,
        ]);
    }

    /** @test */
    public function itRemovesTheCorrectAvailability()
    {
        $from = new Carbon();
        $from->addHours(2);
        $to = $from->copy()->addHours(1);

        factory(Availability::class)->create([
            'user_id' => $this->mockUserModel->id,
            'from' => $from,
            'to' => $to,
            'id' => 1,
        ]);

        factory(Availability::class)->create([
            'user_id' => $this->mockUserModel->id,
            'from' => $from->clone()->addDay(),
            'to' => $to->clone()->addDay(),
            'id' => 2,
        ]);

        factory(Availability::class)->create([
            'user_id' => $this->mockUserModel->id,
            'from' => $from->clone()->addDays(2),
            'to' => $to->clone()->addDays(2),
            'id' => 3,
        ]);

        $this->service->removeAvailabilityRange([
            'user_id' => $this->mockUserModel->id,
            'from' => $from,
            'to' => $to->clone()->addDays(2)->subHour(),
        ]);

        $this->assertDatabaseMissing('availability', [
            'id' => 1,
        ]);

        $this->assertDatabaseMissing('availability', [
            'id' => 2,
        ]);

        $this->assertDatabaseHas('availability', [
            'id' => 3,
        ]);
    }
}
