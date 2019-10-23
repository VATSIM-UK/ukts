<?php

namespace Tests\Feature\Endorsement;

use App\Modules\Endorsement\Special\SpecialEndorsement;
use App\User;
use Tests\TestCase;

class SpecialEndorsementRequestTest extends TestCase
{
    protected $user;
    protected $endorsement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->make();

        $this->endorsement = factory(SpecialEndorsement::class)->create();
    }

    /** @test */
    public function testRequestCanBeMadeAgainstAnEndorsement()
    {

    }
}
