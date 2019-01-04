<?
namespace GQL;

class Client
{
    private $_endpoint;
    private $_options = ["pretty" => true];
    const CONFIG_FIELDS = ['__args', '__alias', '__aliasFor', '__variables', '__directives', '__on', '__typeName'];

    public function __construct($endpoint)
    {
        $this->_endpoint = $endpoint;
    }

    public function query($query)
    {
        $q["query"] = $query;
        $gql = $this->objToQuery($q);
        return $this->request($gql);
    }


    public function subscription($query)
    {
        $q["subscription"] = $query;
        $gql = $this->objToQuery($q);
        return $this->request($gql);

    }

    public function mutation($query)
    {
        $q["mutation"] = $query;
        $gql = $this->objToQuery($q);
        return $this->request($gql);
    }

    private function getIndent($level)
    {
        return str_repeat(" ", $level * 4 + 1);
    }

    private function objToQuery($obj)
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

    public function request($query)
    {
        $http = new \GuzzleHttp\Client();
        try {
            $resp = $http->request("GET", $this->_endpoint, [
                "headers" => [
                    "Accept" => "application/json"
                ],
                "json" => [
                    "query" => $query
                ]
            ]);
        } catch (\Exception $e) {
            return ["error" => ["message" => $e->getMessage()]];
        }
        return json_decode($resp->getBody()->getContents(), true);
    }

    private function filterNonConfigFields($fieldName)
    {
        return !in_array($fieldName, self::CONFIG_FIELDS);
    }

    private function convertQuery($node, $level, &$output, $options)
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

    private function buildArgs($argsObj)
    {
        $args = [];
        foreach ($argsObj as $name => $value) {
            $args[] = $name . ": " . $this->stringify($value);
        }
        return implode(", ", $args);
    }

    private function stringify($obj_from_json)
    {

        if (!is_object($obj_from_json) || $obj_from_json === null) {
            return json_encode($obj_from_json);
        }

        $keys = array_keys($obj_from_json);
        $props = array_map(function ($key) use ($obj_from_json) {
            return $key . ": " . $this->stringify($obj_from_json[$key]);
        }, $keys);
        $props = implode(", ", $props);
        return "{" . $props . "}";
    }

}