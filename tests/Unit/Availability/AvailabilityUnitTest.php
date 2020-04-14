<?php

namespace Tests\Unit\Availability;

use App\Modules\Availability\Availability;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class AvailabilityUnitTest extends TestCase
{
    use RefreshDatabase, UserHelper;

    protected $availability;

    protected function setUp(): void
    {
        parent::setUp();

        $this->availability = factory(Availability::class)->create(['user_id' => 1234567]);
    }

    /** @test */
    public function itReturnsTheUserRelation()
    {
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('find')
                ->andReturn($this->mockedUser());
        })->makePartial();

        $this->assertInstanceOf(User::class, $this->availability->user);
    }
}
