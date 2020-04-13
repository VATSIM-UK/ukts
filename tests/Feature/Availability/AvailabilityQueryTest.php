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
