<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use MockFileSystem\MockFileSystem as mockfs;
use App\Cache;
use App\Config;

#[CoversClass(Cache::class)]
#[UsesClass(Config::class)]
#[UsesClass(App\Helper\File::class)]
#[UsesClass(App\Helper\Json::class)]
class CacheTest extends AbstractTestCase
{
    private static Config $config;

    private static string $channelId = 'UCBa659QWEk1AI4Tg--mrJ2A';
    private static string $cacheFilepath = '';

    /** @var array<string, mixed> $data Feed date */
    private static array $data = [];

    public static function setUpBeforeClass(): void
    {
        mockfs::create();
        self::$cacheFilepath = mockfs::getUrl('/' . hash('sha256', self::$channelId) . '.cache');

        self::$data = (array) json_decode(
            (string)
            file_get_contents('tests/files/channel-cache-data.json'),
            associative: true
        );

        self::$config = self::createConfigStub([
            'getCacheDisableStatus' => false,
            'getCacheDirectory' => mockfs::getUrl('/'),
            'getCacheFormatVersion' => 1
        ]);
    }

    public function setUp(): void
    {
        // Reset cache file data
        copy('tests/files/channel-cache-data.json', self::$cacheFilepath);
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
        $config = self::createConfigStub([
            'getCacheDisableStatus' => false,
            'getCacheDirectory' => mockfs::getUrl('/'),
            'getCacheFormatVersion' => 2
        ]);

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
