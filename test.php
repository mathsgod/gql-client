<?
use function GuzzleHttp\json_encode;

require_once __DIR__ . "/vendor/autoload.php";

$client = new GQL\Client();

/*echo $client->objToQuery([
    "me" => [
        "first_name" => true,
        "last_name" => true
    ]
]);*/

echo $client->objToQuery([
    "searchProduct" => [
        "__args" => [
            "first" => [
                ["lastName" => "Test1"],
                ["lastName" => "Test2"]
            ],
            "word" => (string)"a\"b\c",
            "offset" => (int)10
        ],
        "totalCount" => true,
        "edges" => [
            "node" => [
                "code" => true,
                "description" => true,
                "ProductColor" => [
                    "__args" => [
                        "search" => [
                            "aa"=>1,
                            "available"=>true
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
