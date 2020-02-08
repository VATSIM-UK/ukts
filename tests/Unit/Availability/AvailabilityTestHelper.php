<?php

namespace Tests\Unit\Availability;

use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait AvailabilityTestHelper
{
    protected $mockUserId = 1234567;
    protected $invalidUserId = 12345678;

    protected function mockUserFind()
    {
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('findOrFail')->with($this->mockUserId)
                ->andReturn($this->mockedUser($this->mockUserId, ['code' => 'S2', 'vatsim_id' => 3]));

            $mock->shouldReceive('findOrFail')->with($this->invalidUserId)
                ->andThrow(ModelNotFoundException::class);
        })->makePartial()->shouldReceive('id')->andReturn($this->mockUserId);

    }

    protected function mockedUser($id = null, $ratingObject = ['code' => 'S2', 'vatsim_id' => 3])
    {
        return new User([
            'id' => $id ?: $this->mockUserId,
            'name_fist' => 'First',
            'name_last' => 'Last',
            'atcRating' => $ratingObject,
        ]);
    }
}
