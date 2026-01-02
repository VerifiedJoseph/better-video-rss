<?php

declare(strict_types=1);

namespace App;

class Version
{
    /** @var non-empty-string $version */
    private static string $version = '1.9.3';

    /** @var int $cacheFormatVersion */
    private static int $cacheFormatVersion = 1;

    /**
     * Returns script version
     * @return non-empty-string
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
