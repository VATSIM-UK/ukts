<?php


namespace VATSIMUK\Auth\Remote\Tests;

use Orchestra\Testbench\TestCase;
use VATSIMUK\Auth\Remote\Models\RemoteUser;
use VATSIMUK\Auth\Remote\RemoteEloquent\RemoteBuilder;

class RemoteUserTest extends TestCase
{
    public function testItCanBeConstructed()
    {
        $user = new RemoteUser(['id' => 1300001]);

        $this->assertEquals($user->id, 1300001);
    }

    public function testItCanBeInitialisedWithData()
    {
        $data = [
            'id' => 13000001,
            'name_first' => 'Joe',
            'name_last' => 'Bloggs',
            'email' => 'joe.bloggs@example.org',
        ];

        $user = RemoteUser::initModelWithData($data);

        foreach ($data as $key => $data) {
            $this->assertEquals($user->{$key}, $data);
        }
    }

    public function testItReturnsCustomBuilderClass(){
        $builder = RemoteUser::where('id', 1300002);
        $this->assertInstanceOf(RemoteBuilder::class, $builder);
    }

    public function testItGeneratesParamsCorrectly()
    {
        $this->assertEquals(['id', 'name_first', 'name_last'], RemoteUser::generateParams(null));

        $this->assertEquals(['id', 'name_first', 'name_last'], RemoteUser::generateParams(["*"]));

        $this->assertEquals(['id', 'name_first', 'name_last'], RemoteUser::generateParams([""]));

        $this->assertEquals(['id', 'email'], RemoteUser::generateParams(["", "email"]));

        $this->assertEquals(['id'], RemoteUser::generateParams(["id"]));
    }
}
