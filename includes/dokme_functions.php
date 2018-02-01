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

    function dokme_array_selected($dataset, $key, $default = null)
    {
        if (empty($dataset)) {
            return $default;
        }

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
if (!function_exists('dokme_is_exist')) {

    function dokme_is_exist($selected, $ids)
    {
        if (empty($selected) || empty($ids)) {
            return false;
        }

        foreach ($ids as $id) {
            if (array_search($id, $selected) !== false) {
                return true;
            }
        }
        return false;
    }

}