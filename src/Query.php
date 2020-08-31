<?php

namespace GQL;

class Query
{
    private $field;
    private $child = [];
    private $args = [];

    public function __construct(string $field = null, array $args = [])
    {
        $this->field = $field;
        $this->args = $args;
    }

    public function addField(string $field, array $args = [])
    {
        $q = new Query($field, $args);
        $this->child[] = $q;
        return $q;
    }

    public function addFields(array $fields)
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }
    }

    public function __toString()
    {
        $query = $this->field;

        if ($this->args) {
            $argsStr = "(" . $this->buildArgs($this->args) . ")";
            $query .= $argsStr;
        }

        if ($this->child) {
            $query .= " {";
            foreach ($this->child as $c) {
                $query .= " " . $c;
            }
            $query .= " }";
        }
        return $query;
    }

    private function buildArgs(array $argsObj)
    {
        $args = [];
        foreach ($argsObj as $name => $value) {
            $args[] = $name . ": " . $this->stringify($value);
        }
        return implode(", ", $args);
    }

    private function stringify($obj_from_json)
    {
        if (!is_array($obj_from_json)) {
            if (!is_object($obj_from_json) || $obj_from_json === null) {
                return json_encode($obj_from_json);
            }
        }
        $keys = array_keys($obj_from_json);

        if ($keys[0] === 0) {
            $props = array_map(function ($key) use ($obj_from_json) {
                return  $this->stringify($obj_from_json[$key]);
            }, $keys);

            $props = implode(", ", $props);
            return "[" . $props . "]";
        } else {
            $props = array_map(function ($key) use ($obj_from_json) {
                return $key . ": " . $this->stringify($obj_from_json[$key]);
            }, $keys);
            $props = implode(", ", $props);
            return "{" . $props . "}";
        }
    }
}
