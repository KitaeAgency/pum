<?php

namespace Pum\Extension\Util;

class Namer
{
    const UNSAFE_CHARACTER = '[^a-zA-Z0-9]';

    public static function toCamelCase($text)
    {
        $text = strtolower(self::removeAccents($text));

        return lcfirst(implode('', array_map('ucfirst', preg_split('/'.self::UNSAFE_CHARACTER.'+/', $text))));
    }

    public static function removeUnsafe($text)
    {
        return preg_replace('/'.self::UNSAFE_CHARACTER.'+/', '', $text);
    }

    public static function toLowercase($text)
    {
        $text = strtolower(self::removeAccents($text));

        return preg_replace('/'.self::UNSAFE_CHARACTER.'+/', '_', $text);
    }

    public static function removeAccents($text)
    {
        return iconv('UTF8', 'ASCII//TRANSLIT', $text);
    }
}
