<?php
namespace Docsy\Enums;
enum ParamLocation: string {
    case Query = 'query';
    case Path = 'path';
    case Header = 'header';
    case Cookie = 'cookie';
    case Body = 'body';

    public static function get(string | ParamLocation $location): self
    {
        if ($location instanceof ParamLocation) return $location;

        $locations = array_filter(self::cases(), fn($location_obj) => strtolower($location) === $location_obj->value);
        return array_shift($locations);
    }
}