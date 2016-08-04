<?php
/**
 * Created by PhpStorm.
 * User: Ruben Hazenbosch
 * Date: 01-Aug-16
 * Time: 17:38
 */

namespace app\core;


class Library
{
    public static function path($sPath, $sDelimiter = '/', $sReplacementDelimiter = DIRECTORY_SEPARATOR)
    {
        return str_replace($sDelimiter, $sReplacementDelimiter, $sPath);
    }

    public static function microtimeFormat( $data )
        {
            $duration   = microtime(true) - $data;
            $hours      = (int)($duration/60/60);
            $minutes    = (int)($duration/60)-$hours*60;
            $seconds    = $duration-$hours*60*60-$minutes*60;

            return( number_format((float)$seconds, 3, '.', '') );
        }
}