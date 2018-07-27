<?php

if (!function_exists('array_urlencode')) {
    function array_urlencode($data, $expect = '')
    {
        $new_data = array();
        foreach ($data as $key => $val) {
            if ($expect && $key == $expect) {
                $new_data[urlencode($key)] = $val;
                continue;
            }
            $new_data[urlencode($key)] = is_array($val) ? array_urlencode($val) : urlencode($val);
        }
        return $new_data;
    }

}

