<?php

namespace GQL;

class Builder
{
    public $name;
    public $selectors = [];
    public $args;
    public $pretty = false;
    public function __construct(String $name, $args = null)
    {
        $this->name = $name;
        $this->args = $args;
    }

    public static function _(array $fields)
    {
        $selectors = [];

        foreach ($fields as $name => $field) {

            if ($name === "__args") {
                continue;
            }

            if (is_bool($field)) {
                $selectors[] = new Builder($name);
                continue;
            }

            if (is_string($field)) {
                $selectors[] = new Builder($field);

                continue;
            }

            $builder = new Builder($name);

            $builder->selectors = self::_($field ?? []);
            if ($field["__args"]) {
                $builder->args = $field["__args"];
            }
            $selectors[] = $builder;
        }
        return $selectors;
    }

    public static function Query(array $selectors)
    {
        $builder = new self("query");
        $builder->selectors = self::_($selectors);
        return $builder;
    }

    public function add($selector)
    {
        $this->selectors[] = $selector;
        return $this;
    }

    public static function Mutation(string $name, array $selector = [])
    {
        $builder = new self("mutation");
        $builder->selectors = self::_([$name => $selector]);
        return $builder;
    }

    public static function Subscription(string $name,  array $selector = [])
    {
        $builder = new self("mutation");
        $builder->selectors = self::_([$name => $selector]);
        return $builder;
    }

    private function toObject()
    {
        $obj = [];
        $obj[$this->name] = [];
        $obj[$this->name]["__args"] = $this->args;

        foreach ($this->selectors as $s) {

            $o = $s->toObject();
            foreach ($o as $k => $v) {
                $obj[$this->name][$k] = $v;
            }
        }
        return $obj;
    }

    public function __toString()
    {
        $obj = $this->toObject();

        return Utils::ObjToQuery($obj, $this->pretty);
    }
}
