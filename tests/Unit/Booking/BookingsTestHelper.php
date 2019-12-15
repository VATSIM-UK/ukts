<?php

namespace Tests\Unit\Booking;

use App\User;

trait BookingsTestHelper
{
    protected $mockUserId = 1234567;

    protected function mockUserFind(array $atcRatingOverrides = ['code' => 'S2', 'vatsim_id' => 3])
    {
        $ratingObject = (object) $atcRatingOverrides;
        $this->mock(User::class, function ($mock) use ($ratingObject) {
            $mock->shouldReceive('findOrFail')
                ->andReturn(
                    User::initModelWithData([
                        'id' => $this->mockUserId,
                        'name_fist' => 'First',
                        'name_last' => 'Last',
                        'atcRating' => $ratingObject,
                    ])
                );
        });
    }
}
