<?
require_once __DIR__ . "/../vendor/autoload.php";

$client = new GQL\Client();

echo $client->objToQuery([
    "me" => [
        "first_name" => true,
        "last_name" => true
    ]
]);


