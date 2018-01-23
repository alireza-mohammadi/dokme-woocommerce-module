<?php
if (!function_exists('dokme_array_get')) {

    function dokme_array_get(array $data, $key, $default = null)
    {
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }
        return $default;
    }

}

if (!function_exists('dokme_array_selected')) {

    function dokme_array_selected(array $dataset, $key, $default = null)
    {
        if (!is_array($dataset)) {
            return $default;
        }

        if (empty($key)) {
            return $default;
        }

        $lists = array();
        foreach ($dataset as $data) {
            $lists [] = $data[$key];
        }
        return $lists;
    }

}

