<?php

namespace m;

class Util
{
    public static function removeUtf8Bom($text)
    {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }

    public static function prePrint($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

    public static function hasFirstOccurrence($search, $str)
    {
        $pos = strpos($str, $search);

        if($pos !== 0)
        {
            return false;
        }

        return true;
    }

    public static function isLocalhost()
    {
        return strpos($_SERVER['HTTP_HOST'], 'localhost') > -1;
    }
}
