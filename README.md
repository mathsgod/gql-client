# gql-client

## Client example

### Query

```php
$client = new GQL\Client($server_address);

$data = $client->query([
    "me" => [
        "first_name", 
        "last_name"
    ]
]);
```

#### With arguments
```php
$client = new GQL\Client($server_address);

$data = $client->query([
    "getUser" => [
        "__args"=>[
            "id"=>1
        ],
        "first_name", 
        "last_name",
        "findInvoice"=>[
            "__args"=>[
                "status"=>"pending"
            ],
            "invoice_no"
        ]
    ]
]);
```

### Mutation and Subscription
```php
$data = $client->mutation("updateUser",[
    "__args"=>["user_id"=>1,"first_name"=>"Raymond"]
]);


$data=$client->subscription("createUser",[
    "__args"=>["first_name"=>"raymond"]
]);

```

### auth
```php
$client = new GQL\Client($server_address);

$client->auth=["username","password"];

$data = $client->query([
    "me" => [
        "first_name",
        "last_name"
    ]
]);
```

### no ssl check
```php
$client = new GQL\Client($server_address,["verify"=>false]);


$data = $client->query([
    "me" => [
        "first_name",
        "last_name"
    ]
]);
```

## Builder
### Query
```php

echo Builder::Query([
     "me" => [
        "first_name",
        "last_name"
    ]
]);

// query{ me {first_name last_name} }
```

### Mutation
```php

echo Builder:Mutation("updateUser",[
    "__args"=>[
        "user_id"=>1,
        "first_name"=>"Raymond"
    ]
]);
// mutation{ updateUser(user_id:1, first_name:"Raymond") }
```

### Subscription
```php

echo Builder:Mutation("createUser",["__args"=>["first_name"=>"Raymond"]]);
// subscription{ createUser(first_name:"Raymond") }
```




