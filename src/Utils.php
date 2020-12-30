<?php

namespace GQL;

class Utils
{
    const CONFIG_FIELDS = ['__args', '__alias', '__aliasFor', '__variables', '__directives', '__on', '__typeName'];

    public static function ObjToQuery(array $obj, bool $pretty = false): string
    {
        $self = new self();

        $queryLines = [];
        $self->convertQuery($obj, 0, $queryLines);
        //print_r($queryLines);
        $output = "";
        foreach ($queryLines as $a) {
            $line = $a[0];
            $level = $a[1];

            if ($pretty) {
                if ($output) {
                    $output .= "\n";
                }
                $output .= $self->getIndent($level) . $line;
            } else {
                if ($output) {
                    $output .= " ";
                }
                $output .= $line;
            }
        }
        return $output;
    }

    private function getIndent(int $level)
    {
        return str_repeat(" ", $level * 4 + 1);
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
