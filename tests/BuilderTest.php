<?php

declare(strict_types=1);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

use GQL\Builder;
use PHPUnit\Framework\TestCase;

final class BuilderTest extends TestCase
{
    public function test_query2()
    {
        $query = (string)Builder::Query([
            "a" => [
                "__args" => [
                    "b" => 1
                ],
                "test" => true
            ],
        ]);
        $this->assertEquals("query { a (b: 1) { test } }", $query);
    }

    public function test_query()
    {
        $mutation = (string)Builder::Mutation("update", ["user" => 1]);


        $query = (string)Builder::Query([
            "a" => [
                "__args" => [
                    "b" => 1
                ],
                "test"
            ],
        ]);

        $this->assertEquals("mutation { update (user: 1) }", $mutation);
        $this->assertEquals("query { a (b: 1) { test } }", $query);
    }


    public function test_emoji()
    {

        $query = (string)Builder::Query([
            "a" => [
                "__args" => [
                    "b" => "🙏🏼"
                ],
                "test"
            ],
        ]);

        $this->assertEquals("query { a (b: \"🙏🏼\") { test } }", $query);
    }
}
