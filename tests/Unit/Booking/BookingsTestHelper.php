<?php

namespace Tests\Unit\Booking;

use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait BookingsTestHelper
{
    protected $mockUserId = 1234567;
    protected $invalidUserId = 12345678;

    protected function mockUserFind(array $atcRatingOverrides = ['code' => 'S2', 'vatsim_id' => 3])
    {
        $ratingObject = (object) $atcRatingOverrides;
        $this->mock(User::class, function ($mock) use ($ratingObject) {
            $mock->shouldReceive('findOrFail')->with($this->mockUserId)
                ->andReturn($this->mockedUser($this->mockUserId, $ratingObject));

            $mock->shouldReceive('findOrFail')->with($this->invalidUserId)
                ->andThrow(ModelNotFoundException::class);
        })->makePartial();
    }

    protected function mockedUser($id = null, $ratingObject = ['code' => 'S2', 'vatsim_id' => 3])
    {
        return User::initModelWithData([
            'id' => $id ?: $this->mockUserId,
            'name_first' => 'First',
            'name_last' => 'Last',
            'atcRating' => $ratingObject,
        ]);
    }
}
