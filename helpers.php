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