<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

use GQL\Builder;

require_once("vendor/autoload.php");

echo Builder::Mutation("update", ["user" => 1]);
