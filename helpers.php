<?php

if (!function_exists('config')) {
    function config($key, $default = null) {
        $keys = explode('.', $key);
        $filename = array_shift($keys);

        $config = include __DIR__ . '/src/config/' . $filename . '.php';
        $value = $config;

        foreach ($keys as $segment) {
            $value = $value[$segment] ?? $default;
        }

        return $value;
    }
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        return $default;
    }
}

if (!function_exists('dump')) {
    function dump(...$data) {
        echo '<pre>';

        foreach ($data as $datum) {
            print_r($datum);
        }

        echo '</pre>';
    }
}

if (!function_exists('dd')) {
    function dd(...$data) {
        echo '<pre>';

        foreach ($data as $datum) {
            print_r($datum);
        }

        echo '</pre>';

        exit();
    }
}