<?php

namespace App;

class Version
{
    private static string $version = '1.7.1';
    private static int $cacheFormatVersion = 1;

    /**
     * Returns script version
     */
    public static function getVersion(): string
    {
        return self::$version;
    }

    /**
     * Returns cache format version
     */
    public static function getCacheFormatVersion(): int
    {
        return self::$cacheFormatVersion;
    }
}
