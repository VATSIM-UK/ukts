<?php


namespace VATSIMUK\Auth\Remote\Tests;

use Orchestra\Testbench\TestCase;
use VATSIMUK\Auth\Remote\GraphQL\Builder;

class BuilderClassTest extends TestCase
{
    public function testItConstructs()
    {
        $this->expectNotToPerformAssertions();
        new Builder("user", ['id', 'name_first']);
    }

    public function testItCanComposeAGraphQLQuery()
    {
        $builder = new Builder("user", ['id', 'name_first', 'rating' => [ 'id', 'name']]);

        $this->assertEquals(trim(preg_replace('/    /', '',
        "query {
                    user {
                        id
                        name_first
                        rating {
                            id
                            name
                        }
                    }
                }")), $builder->getGraphQLQuery());
    }

    public function testItReturnsTheMethod()
    {
        $builder = new Builder("user", ['id', 'name_first']);
        $this->assertEquals("user", $builder->getMethod());
    }

    public function testItReturnsTheColumns()
    {
        $builder = new Builder("user", ['id', 'name_first', 'rating' => [ 'id', 'name']]);

        $this->assertEquals(preg_replace('/    /', '',
            "id
                    name_first
                    rating {
                        id
                        name
                    }
                    "), $builder->getColumns());
    }
}
