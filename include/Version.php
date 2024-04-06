<?php

namespace App;

class Version
{
    private static string $version = '1.7.1';
    private static int $acheFormatVersion = 1;

    /**
     * Returns script version
     * @return string
     */
    public static function getVersion(): string
    {
        return self::$version;
    }

    /**
     * Returns cache format version
     * @return string
     */
    public static function getCacheFormatVersion(): string
    {
        return self::$acheFormatVersion;
    }
}
