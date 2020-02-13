<?php

namespace Tests\Feature\Availability;

use App\Modules\Availability\Availability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;
use Tests\Helpers\UserHelper;

class AvailabilityQueryTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests, UserHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->mockedUser());
        $this->mockUserFind();
    }

    /* @test */
    public function testItReturnsAvailabilityPaginated()
    {
        $availability = factory(Availability::class, 15)->create();

        $data = $this->graphQL('
        {
            availabilities (first: 10) {
                data {
                    id
                }
                paginatorInfo {
                    currentPage
                    lastPage
                }
            }
        }
        ')->assertJsonStructure([
            'data' => [
                'availabilities' => [
                    'data' => [
                        [
                            'id',
                        ],
                    ],
                    'paginatorInfo' => [
                        'currentPage',
                        'lastPage',
                    ],
                ],
            ],
        ]);
    }

    /* @test */
    public function testItReturnsAvailabilityForDate()
    {
        factory(Availability::class)->create([
            'from' => '2025-08-10 14:00:00',
            'to' => '2025-08-10 15:00:00',
        ]);
        factory(Availability::class)->create([
            'from' => '2025-08-09 14:00:00',
            'to' => '2025-08-09 15:00:00',
        ]);
        factory(Availability::class)->create([
            'from' => '2025-08-09 20:00:00',
            'to' => '2025-08-09 22:30:00',
        ]);

        $data = $this->graphQL('
        {
            availabilityByDate (from: "2025-08-09") {
                id
            }
        }
        ')->assertJson([
            'data' => [
                'availabilityByDate' => [
                    ['id' => 2],
                    ['id' => 3],
                ],
            ],
        ]);
    }

    /* @test */
    public function testItReturnsAvailabilityBetweenDates()
    {
        factory(Availability::class)->create([
            'from' => '2025-08-06 14:00:00',
            'to' => '2025-08-06 15:00:00',
        ]);
        factory(Availability::class)->create([
            'from' => '2025-08-09 20:00:00',
            'to' => '2025-08-09 22:30:00',
        ]);
        factory(Availability::class)->create([
            'from' => '2025-08-04 14:00:00',
            'to' => '2025-08-04 15:00:00',
        ]);
        factory(Availability::class)->create([
            'from' => '2025-08-10 14:00:00',
            'to' => '2025-08-10 15:00:00',
        ]);

        $data = $this->graphQL('
        {
            availabilityBetweenDates (from: {
                from: "2025-08-05"
                to: "2025-08-09"
            }) {
                id
            }
        }
        ')->assertJson([
            'data' => [
                'availabilityBetweenDates' => [
                    ['id' => 1],
                    ['id' => 2],
                ],
            ],
        ]);
    }

    /* @test */
    public function testItReturnsAvailabilityById()
    {
        $availability = factory(Availability::class, 3)->create();

        $this->graphQL("
        {
            availability(id: {$availability->get(1)->id}) {
                id
            }
        }
        ")->assertJson([
            'data' => [
                'availability' => [
                    'id' => $availability->get(1)->id,
                ],
            ],
        ]);
    }
}
