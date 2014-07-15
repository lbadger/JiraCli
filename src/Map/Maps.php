<?php
namespace WCurtis\Map;

use WCurtis\Util;

class Maps {
    protected $maps;

    public function __construct($maps = []) {
        $this->maps = empty($maps)
            ? require(__DIR__ . '/MapDefs.php')
            : $maps;
    }

    public function MapArray($type, $things) {
        return array_map(function($t) use ($type) {
            return $this->MapThing($type, $t);
        }, $things);
    }

    public function MapThing($type, $thing) {
        $newThing = [];
        $map = Util::GetFromArray($this->maps, $type, true);

        foreach($map as $key => $def) {

            list($key, $value) = $this->ResolveDef($key, $def, $thing);

            $newThing[$key] = $value;
        }

        return $newThing;
    }

    protected function ResolveDef($key, $def, $item) {
        if(is_string($def)) {
            if(is_numeric($key)) return [$def, Util::GetFromArray($item, $def)];

            return [$key, Util::GetFromArray($item, $def)];
        }

        if(is_array($def)) {
            $args = [];
            $fields = [];

            if(isset($def['fields'])) $fields = $def['fields'];
            else if(isset($def['field'])) $fields = [$def['field']];
            else if(!empty($def['fullItem'])) $args = [$item];

            if(!empty($fields)) {
                $args = array_map(function($f) use ($item) {
                    return Util::GetFromArray($item, $f);
                }, $fields);
            }

            if(is_array($def['callable'])) {
                $value = forward_static_call_array($def['callable'], $args);
            } else $value = call_user_func_array($def['callable'], $args);

            return [$key, $value];
        }

        throw new \Exception("Invalid map def: " . print_r($def, true));
    }
} 