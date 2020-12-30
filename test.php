<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

use GQL\Builder;

require_once("vendor/autoload.php");

echo Builder::Query([
    "a" => [
        "__args" => [
            "b" => 1
        ],
        "test"
    ],
]);
