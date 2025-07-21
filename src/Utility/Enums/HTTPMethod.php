<?php

namespace Docsy\Utility\Enums;

enum HTTPMethod: string
{
    case HEAD = 'HEAD';
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case PATCH = 'PATCH';
    case OPTIONS = 'OPTIONS';

    public static function isValid($method): bool
    {
        return in_array((is_string($method) ? strtoupper($method) : $method->value), array_column(self::cases(),'value'));
    }
    public static function get($method): HTTPMethod
    {
        $methods = array_filter(self::cases(), fn($HTTPMethod) => (is_string($method) ? strtoupper($method) : $method->name) === $HTTPMethod->name);
        return array_shift($methods);
    }
}
