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

## auth
```php
$client = new GQL\Client($server_address);

$client->auth=["username","password"];

$data = $client->query([
    "me" => [
        "first_name" => true,
        "last_name" => true
    ]
]);
```

## no ssl check
```php
$client = new GQL\Client($server_address,[],["verify"=>false]);


$data = $client->query([
    "me" => [
        "first_name" => true,
        "last_name" => true
    ]
]);
```

