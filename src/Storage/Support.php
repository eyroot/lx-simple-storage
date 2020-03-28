<?php

namespace Lx\Storage;

class Support
{
    public static function arraySort($array, $sortKey, $sortType = SORT_NUMERIC, $direction = 'asc') {
        $t = array();
        foreach($array as $k => $v) {
            $t[$k] = $v[$sortKey];
        }
        asort($t, $sortType);

        $res = array();
        foreach($t as $k => $v) {
            $res[] = $array[$k];
        }

        if($direction == 'desc') {
            $res = array_reverse($res);
        }

        return $res;
    }
}
