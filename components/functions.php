<?php

if (!function_exists('config')) {

    /**
     * @param string $key
     * @return array|mixed
     */
    function config($key = '') {
        $configArray = [];
        if ($key === '') {
            return $configArray;
        }
        $dir_files = scandir(__DIR__ . '/../config');
        foreach ($dir_files as $file) {
            if (substr($file, -4) == '.php' && substr($file, 0, -4) == $key) {
                $configArray = require_once __DIR__ . '/../config/' .$file;
            }
        }
        return $configArray;
    }
}