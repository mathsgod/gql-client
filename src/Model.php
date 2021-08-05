<?php

namespace GQL;

use Exception;
use ReflectionClass;
use ReflectionProperty;

class Model
{

    static \GQL\Client $client;

    static function SetClient(Client $client)
    {
        self::$client = $client;
    }

    static function Query()
    {
        $query = new QueryBuilder();
        $query->api = self::$client;
        $query->name = static::class;
        $query->class = static::class;

        $ref = new ReflectionClass(static::class);
        $props = $ref->getProperties(ReflectionProperty::IS_PUBLIC);


        $select = [];
        foreach ($props as $prop) {
            if ($prop->isStatic()) continue;
            if ($prop->getAttributes(ID::class) || $prop->getAttributes(Field::class) || $prop->getAttributes(QueryField::class)) {
                $select[] = $prop->getName();
            }
        }

        $query->select($select);
        return $query;
    }


    static function Load(int $id): static
    {
        $ref = new ReflectionClass(static::class);
        $props = $ref->getProperties(ReflectionProperty::IS_PUBLIC);

        $select = [];
        $key = self::__key();
        foreach ($props as $prop) {
            if ($prop->isStatic()) continue;
            if ($prop->getAttributes(ID::class) || $prop->getAttributes(Field::class) || $prop->getAttributes(QueryField::class)) {
                $select[] = $prop->getName();
            }
        }

        $query = [
            static::class => [
                "get" => [
                    "__args" => [
                        $key => $id
                    ],
                    ...$select
                ]
            ]
        ];

        $resp = static::$client->query($query);
        $data = $resp["data"][static::class]["get"];

        $class = static::class;
        $obj = new $class;
        foreach ($data as $k => $v) {
            $obj->$k = $v;
        }
        return $obj;
    }

    static function __key()
    {
        $ref = new ReflectionClass(static::class);
        $props = $ref->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($props as $prop) {
            if ($prop->isStatic()) continue;

            if ($prop->getAttributes(ID::class)) {
                return  $prop->getName();
            }
        }
    }

    public function delete()
    {
        $key = self::__key();
        if (!$key) {
            throw new Exception("key is not defined");
        }

        $resp = self::$client->mutation(
            static::class,
            [
                "__args" => [
                    "filter" => [
                        $key => $this->$key
                    ]
                ],
                "delete"
            ]
        );
        return $resp["data"][static::class]["delete"];
    }

    public function save()
    {
        $key = self::__key();
        if (!$key) {
            throw new Exception("key is not defined");
        }

        if ($this->$key) {

            //mutation field 
            $ref = new ReflectionClass(static::class);
            $props = $ref->getProperties(ReflectionProperty::IS_PUBLIC);

            $data = [];
            $key = self::__key();
            foreach ($props as $prop) {
                if ($prop->isStatic()) continue;
                if ($prop->getAttributes(Field::class) || $prop->getAttributes(MutationField::class)) {
                    $name = $prop->getName();
                    $data[$name] = $this->$name;
                }
            }


            self::$client->mutation(
                static::class,
                [
                    "__args" => [
                        "filter" => [
                            $key => $this->$key
                        ]
                    ],
                    "update" => [
                        "__args" => $data
                    ]
                ]
            );
        } else {
            //subscription field
            $ref = new ReflectionClass(static::class);
            $props = $ref->getProperties(ReflectionProperty::IS_PUBLIC);

            $data = [];
            $key = self::__key();
            foreach ($props as $prop) {
                if ($prop->isStatic()) continue;
                if ($prop->getAttributes(Field::class) || $prop->getAttributes(SubscriptionField::class)) {
                    $name = $prop->getName();
                    $data[$name] = $this->$name;
                }
            }


            $resp = self::$client->subscription(
                static::class,
                [
                    "insert" => [
                        "__args" => $data
                    ]
                ]
            );

            $this->$key = $resp["data"][static::class]["insert"];
        }
    }
}
