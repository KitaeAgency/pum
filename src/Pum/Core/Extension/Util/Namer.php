<?php

namespace Pum\Core\Extension\Util;

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

    public static function toSlug($text)
    {
        return str_replace('_', '-', self::toLowercase($text));
    }

    public static function removeAccents($text)
    {
        $text = htmlentities($text, ENT_NOQUOTES, 'utf-8');

        $search     = '_';
        $replace    = '-';

        $trans = array(
                        '&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);' => '\1',
                        '&([A-za-z]{2})(?:lig);' => '\1',
                        '&[^;]+;'              => '',
                        '&\#\d+?;'              => '',
                        '&\S+?;'                => '',
                        '\s+'                   => $replace,
                        '[^a-z0-9\-\._]'        => '',
                        $replace.'+'            => $replace,
                        $replace.'$'            => $replace,
                        '^'.$replace            => $replace,
                        '\.+$'                  => ''
                    );

        $text = strip_tags($text);

        foreach ($trans as $key => $val)
        {
            $text = preg_replace("#".$key."#i", $val, $text);
        }

        $text = strtolower($text);
        
        return trim(stripslashes($text));

        //return iconv('UTF8', 'ASCII//TRANSLIT', $text);
    }
}
