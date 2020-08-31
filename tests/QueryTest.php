<?php

declare(strict_types=1);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

use GQL\Query;
use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    public function test_query()
    {

        $q = new Query();
        $q->addField("hero")->addField('name');
        $this->assertEquals(" { hero { name } }", (string)$q);
    }

    public function test_addFields()
    {
        $q = new Query();
        $q->addField("hero")->addFields(['name', 'ages']);
        $this->assertEquals(" { hero { name ages } }", (string)$q);
    }

    public function test_addArgs()
    {
        $q = new Query();
        $q->addField("hero", ["id" => "1000"]);
        $this->assertEquals(" { hero(id: \"1000\") }", (string)$q);

        $q = new Query();
        $field = $q->addField("hero", ["id" => "1000"]);
        $field->addField("height", ["unit" => "FOOT"]);

        $this->assertEquals(" { hero(id: \"1000\") { height(unit: \"FOOT\") } }", (string)$q);

    }
}
