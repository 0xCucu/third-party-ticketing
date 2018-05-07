<?php

if (!function_exists('array_urlencode')) {
    function array_urlencode($data)
    {
        $new_data = array();
        foreach ($data as $key => $val) {
            $new_data[urlencode($key)] = is_array($val) ? array_urlencode($val) : urlencode($val);
        }
        return $new_data;
    }

}

