<?php

namespace GQL;

use function GuzzleHttp\json_encode;

class Client
{
    private $_endpoint;
    private $_options = ["pretty" => true];
    public $headers = ["Accept" => "application/json"];
    public $auth = [];
    private $guzzle_client_config = [];

    const CONFIG_FIELDS = ['__args', '__alias', '__aliasFor', '__variables', '__directives', '__on', '__typeName'];

    public function __construct(string $endpoint, array $options = [], array $guzzle_client_config = [])
    {
        $this->_endpoint = $endpoint;
        $this->guzzle_client_config = array_merge($this->guzzle_client_config, $guzzle_client_config);
        $this->_options = array_merge($this->_options, $options);
    }

    public function query(array $query)
    {
        $q["query"] = $query;
        $gql = $this->objToQuery($q);
        return $this->request($gql);
    }

    public function subscription(array $query, array $multipart = [])
    {
        $q["subscription"] = $query;
        $gql = $this->objToQuery($q);
        return $this->request($gql, $multipart);
    }

    public function mutation(array $query, array $multipart = [])
    {
        $q["mutation"] = $query;
        $gql = $this->objToQuery($q);
        return $this->request($gql, $multipart);
    }

    private function getIndent(int $level)
    {
        return str_repeat(" ", $level * 4 + 1);
    }

    public function objToQuery(array $obj): string
    {
        $queryLines = [];
        $this->convertQuery($obj, 0, $queryLines);
        //print_r($queryLines);
        $output = "";
        foreach ($queryLines as $a) {
            $line = $a[0];
            $level = $a[1];

            if ($this->_options["pretty"]) {
                if ($output) {
                    $output .= "\n";
                }
                $output .= $this->getIndent($level) . $line;
            } else {
                if ($output) {
                    $output .= " ";
                }
                $output .= $line;
            }
        }
        return $output;
    }

    public function request(string $query, array $multipart = []): array
    {
        $http = new \GuzzleHttp\Client($this->guzzle_client_config);
        try {
            if ($multipart) {
                $m = $multipart;
                $m[] = [
                    "name" => "query",
                    "contents" => $query
                ];
                $resp = $http->request("POST", $this->_endpoint, [
                    "auth" => $this->auth,
                    "headers" => $this->headers,
                    "multipart" => $m
                ]);
            } else {
                $resp = $http->request("POST", $this->_endpoint, [
                    "auth" => $this->auth,
                    "headers" => $this->headers,
                    "json" => [
                        "query" => $query
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return ["error" => ["message" => $e->getMessage()]];
        }
        return json_decode($resp->getBody()->getContents(), true);
    }

    private function filterNonConfigFields(string $fieldName)
    {
        return !in_array($fieldName, self::CONFIG_FIELDS);
    }

    private function convertQuery(array $node, int $level, array &$output)
    {
        foreach ($node as $key => $value) {
            if (!$this->filterNonConfigFields($key)) {
                continue;
            }

            if (is_array($value)) {

                if (!$value) {
                    $output[] = [$key, $level];
                    return;
                }
                $fieldCount = count(array_filter(array_keys($value), function ($keyCount) {
                    return $this->filterNonConfigFields($keyCount);
                }));

                $subFields = $fieldCount > 0;
                $token = $key;
                $argsExist = $value["__args"];

                if ($argsExist) {
                    $argsStr = "(" . $this->buildArgs($value["__args"]) . ")";


                    //         $spacer = $argsExist ? ' ' : '';
                    $spacer = "";
                    $token = $token . " " . $spacer . ($argsStr ? $argsStr : '');
                }

                $output[] = [$token . ($subFields ? ' {' : ''), $level];

                $this->convertQuery($value, $level + 1, $output);

                if ($subFields) {
                    $output[] = ['}', $level];
                }
            } elseif ($value) {
                $output[] = [$key, $level];
            }
        }
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
