<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Version;

#[CoversClass(Version::class)]
class VersionTest extends TestCase
{
    private static string $version;
    private static int $cacheFormatVersion;

    public static function setUpBeforeClass(): void
    {
        $reflection = new ReflectionClass('App\Version');
        self::$version = $reflection->getStaticPropertyValue('version');
        self::$cacheFormatVersion = $reflection->getStaticPropertyValue('cacheFormatVersion');
    }

    /**
     * Test `getVersion()`
     */
    public function testGetVersion(): void
    {
        $this->assertEquals(self::$version, Version::getVersion());
    }

    /**
     * Test `getCacheFormatVersion()`
     */
    public function testGetCacheFormatVersion(): void
    {
        $this->assertEquals(self::$cacheFormatVersion, Version::getCacheFormatVersion());
    }
}
