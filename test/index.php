<?
require_once __DIR__ . "/../vendor/autoload.php";

$client = new GQL\Client("https://127.0.0.1/api");

echo $client->objToQuery([
    "me" => [
        "first_name" => true,
        "last_name" => true
    ]
]);
