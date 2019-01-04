# gql-client

## Example
```php
$client = new GQL\Client($server_address);

$data = $client->query([
    "me" => [
        "first_name" => true,
        "last_name" => true
    ]
]);
```