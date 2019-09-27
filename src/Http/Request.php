<?php


namespace App\Http;


class Request
{
    private static $_request;

    public static function get($key)
    {
        return self::$_request[$key] ?? NULL;
    }

    public static function set($key, $value)
    {
        self::$_request[$key] = $value;
    }

    public static function setRequest($request)
    {
        self::$_request = $request;
    }
}