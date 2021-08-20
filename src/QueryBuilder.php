<?php

namespace GQL;

use IteratorAggregate;
use Countable;
use ArrayObject;

class QueryBuilder implements IteratorAggregate, Countable
{
    public function select(array $select)
    {
        $this->select = $select;
        return $this;
    }

    public function count()
    {
        $name = $this->name;
        $resp = $this->api->query([$name => ["count"]]);
        return $resp["data"][$name]["count"];
    }

    public function filter(array $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function order($order)
    {
        $this->order = $order;
        return $this;
    }

    public function getIterator()
    {
        $name = $this->name;

        $query = $this->select ?? [];
        $args = [];
        if ($this->limit) {
            $args["limit"] = $this->limit;
        }

        if ($this->order) {
            $args["order"] = $this->order;
        }


        if ($this->offset) {
            $args["offset"] = $this->offset;
        }

        if ($args) {
            $query["__args"] = $args;
        }

        $resp = $this->api->query([
            $name => [
                "list" => $query
            ]
        ]);

        $ao = new ArrayObject;

        $class = $this->class;

        foreach ($resp["data"][$name]["list"] as $ds) {

            $s = new $class;
            foreach ($ds as $d => $v) {
                $s->$d = $v;
            }

            $ao->append($s);
        }

        return $ao;
    }
}
