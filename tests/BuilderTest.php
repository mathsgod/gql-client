<?php

declare(strict_types=1);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

use GQL\Builder;
use PHPUnit\Framework\TestCase;

final class BuilderTest extends TestCase
{

    public function test_query()
    {
        echo Builder::Query([Builder::_("me")]);
    }
}
