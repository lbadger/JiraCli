<?php
namespace WCurtis;

class Util {
    public static function EndsWith($haystack, $needle) {
        $length = strlen($needle);
        return $length === 0
            ? true
            : (substr($haystack, -$length) === $needle);
    }

    public static function GetFromArray($array, $path, $require=false, $default = null)
    {
        if(is_string($path)) $path = explode('.', $path);
        $current = $array;

        foreach($path as $el) {
            if(isset($current[$el])) {
                $current = $current[$el];
            } else {
                if($require) {
                    throw new \OutOfBoundsException("Cannot find "
                        . print_r($path, true) . " in array "
                        . print_r($current, true)
                    );
                }
                return $default;
            }
        }

        return $current;
    }

    public static function joinPaths() {
        $ds = DIRECTORY_SEPARATOR;
        $args = func_get_args();
        $paths = array();

        foreach($args as $arg) {
            $paths = array_merge($paths, (array)$arg);
        }
        $absolute = $paths[0][0] == $ds;

        $saniPaths = array_map(function($p) use ($ds) {
                return trim($p, $ds);
            }, $paths);
        $saniPaths = array_filter($saniPaths);

        $joined = implode($ds, $saniPaths);
        if($absolute) $joined = $ds . $joined;

        return $joined;
    }

    /**
     * @param array $array
     * @param callable $callable
     *  Each element of $array passed to it. Should return a two-element array: [$key, $value]
     *
     * @return array
     */
    public static function toDict($array, $callable) {
        $newArray = [];

        foreach($array as $el) {
            list($key, $value) = $callable($el);
            $newArray[$key] = $value;
        }

        return $newArray;
    }
}
