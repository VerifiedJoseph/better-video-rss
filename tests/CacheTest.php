<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use MockFileSystem\MockFileSystem as mockfs;
use App\Cache;
use App\Config;

#[CoversClass(Cache::class)]
#[UsesClass(Config::class)]
class CacheTest extends TestCase
{
    private static Config $config;

    private static string $channelId = 'UCBa659QWEk1AI4Tg--mrJ2A';
    private static string $cacheFilepath = '';

    /** @var array<string, mixed> $data Feed date */
    private static array $data = [];

    public static function setUpBeforeClass(): void
    {
        self::$data = (array) json_decode(
            (string)
            file_get_contents('tests/files/channel-cache-data.json'),
            associative: true
        );

        mockfs::create();
        self::$cacheFilepath = mockfs::getUrl('/' . hash('sha256', self::$channelId) . '.cache');

        copy('tests/files/channel-cache-data.json', self::$cacheFilepath);

        /** @var PHPUnit\Framework\MockObject\Stub&Config */
        $config = self::createStub(Config::class);
        $config->method('getCacheDisableStatus')->willReturn(false);
        $config->method('getCacheDirectory')->willReturn(mockfs::getUrl('/'));
        $config->method('getCacheFormatVersion')->willReturn(1);
        self::$config = $config;
    }

    /**
     * Test `load()` with feed ID in cache
     */
    public function testLoad(): void
    {
        $cache = new Cache(self::$channelId, self::$config);
        $cache->load();

        $this->assertJsonStringEqualsJsonFile(
            'tests/files/channel-cache-data.json',
            (string) json_encode($cache->getData())
        );
    }

    /**
     * Test `load()` with feed ID not in cache
     */
    public function testLoadWithFeedIdNotInCache(): void
    {
        $cache = new Cache('UC4QobU6STFB0P71PMvOGN5A', self::$config);
        $cache->load();

        $this->assertEquals([], $cache->getData());
    }

    /**
     * Test `load()` with cache version that does not match current cache version
     */
    public function testLoadWithNoVersionMatch(): void
    {
        /** @var PHPUnit\Framework\MockObject\Stub&Config */
        $config = self::createStub(Config::class);
        $config->method('getCacheDisableStatus')->willReturn(false);
        $config->method('getCacheDirectory')->willReturn(mockfs::getUrl('/'));
        $config->method('getCacheFormatVersion')->willReturn(2);

        $cache = new Cache(self::$channelId, $config);
        $cache->load();

        $this->assertEquals([], $cache->getData());
    }

    /**
     * Test `save()`
     */
    public function testSave(): void
    {
        $data = self::$data;
        $data['details']['title'] = 'Hello World';

        $cache = new Cache(self::$channelId, self::$config);
        $cache->load();
        $cache->save($data);

        $this->assertEquals($data, $cache->getData());
        $this->assertJsonStringEqualsJsonFile(
            self::$cacheFilepath,
            (string) json_encode($data)
        );
    }
}
