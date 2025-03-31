<?php

declare(strict_types=1);

namespace App;

class Version
{
    private static string $version = '1.9.0';
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
