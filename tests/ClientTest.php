<?php

declare(strict_types=1);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{

    public function test_objToQuery()
    {
        $client = new GQL\Client("endpoint", ["pretty" => false]);
        $query = $client->objToQuery([
            "searchProduct" => [
                "__args" => [
                    "first" => [
                        ["lastName" => "Test1"],
                        ["lastName" => "Test2"]
                    ],
                    "word" => (string) "a\"b\c",
                    "offset" => (int) 10
                ],
                "totalCount" => true,
                "edges" => [
                    "node" => [
                        "code" => true,
                        "description" => true,
                        "ProductColor" => [
                            "__args" => [
                                "search" => [
                                    "aa" => 1,
                                    "available" => true
                                ]
                            ],
                            "code" => true,
                            "color" => true,
                            "price" => true,
                            "image" => true,
                            "thumb" => true,
                        ]
                    ]
                ]
            ]
        ]);
        $expected = <<<GQL
searchProduct (first: [{lastName: "Test1"}, {lastName: "Test2"}], word: "a\"b\\\\c", offset: 10) { totalCount edges { node { code description ProductColor (search: {aa: 1, available: true}) { code color price image thumb } } } }
GQL;

        $this->assertEquals($expected, $query);
    }
}
