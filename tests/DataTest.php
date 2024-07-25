<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\Depends;
use MockFileSystem\MockFileSystem as mockfs;
use App\Data;
use App\Config;

#[CoversClass(Data::class)]
#[UsesClass(Config::class)]
#[UsesClass(App\Cache::class)]
#[UsesClass(App\Helper\Url::class)]
#[UsesClass(App\Helper\File::class)]
#[UsesClass(App\Helper\Json::class)]
#[UsesClass(App\Helper\Convert::class)]
class DataTest extends AbstractTestCase
{
    private static Config $config;
    private static Data $data;

    /** @var array<string, mixed> $channelCacheData */
    private static array $channelCacheData;

    private static stdClass $apiResponses;
    private static string $cacheFilepath = '';
    private static string $feedId = 'UCMufUaGlcuAvsSdzQV08BEA';
    private static string $feedType = 'channel';

    public static function setUpBeforeClass(): void
    {
        mockfs::create();
        self::$cacheFilepath = mockfs::getUrl('/' . hash('sha256', self::$feedId) . '.cache');

        self::$channelCacheData = (array) json_decode(
            (string)
            file_get_contents('tests/files/channel-cache-data.json'),
            associative: true
        );

        self::$apiResponses = json_decode(
            (string) file_get_contents('tests/files/data-class-api-response-samples.json'),
        );

        self::$config = self::createConfigStub([
            'getCacheDisableStatus' => false,
            'getCacheDirectory' => mockfs::getUrl('/'),
            'getCacheFormatVersion' => 1
        ]);

        self::$data = new Data(
            self::$feedId,
            self::$feedType,
            self::$config
        );
    }

    public function setUp(): void
    {
        // Reset cache file data
        copy('tests/files/channel-cache-data.json', self::$cacheFilepath);
    }

    /**
     * Test `getData()`
     */
    public function testGetData(): void
    {
        $data = new Data(
            self::$feedId,
            self::$feedType,
            self::$config
        );

        $this->assertEquals(self::$channelCacheData, $data->getData());
    }

    /**
     * Test `getPartEtag()`
     */
    public function testGetPartEtag(): void
    {
        $data = new Data(
            self::$feedId,
            self::$feedType,
            self::$config
        );

        $this->assertEquals(
            self::$channelCacheData['details']['etag'],
            $data->getPartEtag('details')
        );
    }

    /**
     * Test `getPartEtag()` with caching disabled
     */
    public function testGetPartEtagWithCachingDisabled(): void
    {
        $config = self::createConfigStub([
            'getCacheDisableStatus' => true,
            'getCacheDirectory' => mockfs::getUrl('/'),
            'getCacheFormatVersion' => 1
        ]);

        $data = new Data(
            self::$feedId,
            self::$feedType,
            $config
        );

        $this->assertEquals(
            '',
            $data->getPartEtag('details')
        );
    }

    /**
     * Test `getExpiredParts()`
     */
    public function testGetExpiredParts(): void
    {
        $data = new Data(
            self::$feedId,
            self::$feedType,
            self::$config
        );

        $this->assertEquals(
            ['details', 'feed', 'videos'],
            $data->getExpiredParts()
        );
    }

    /**
     * Test `getExpiredParts()` with caching is disabled
     */
    public function testGetExpiredPartsWithCachingDisabled(): void
    {
        $config = self::createConfigStub([
            'getCacheDisableStatus' => true,
            'getCacheDirectory' => mockfs::getUrl('/'),
            'getCacheFormatVersion' => 1
        ]);

        $data = new Data(
            self::$feedId,
            self::$feedType,
            $config
        );

        $this->assertEquals(
            ['details', 'feed', 'videos'],
            $data->getExpiredParts()
        );
    }

    /**
     * Test `getExpiredVideos()`
     */
    public function testGetExpiredVideos(): void
    {
        $data = new Data(
            self::$feedId,
            self::$feedType,
            self::$config
        );

        $expected = 'Owd0fCoJhiv,jVhUHba1WyK,e3bDAwuzUnd,MVsly5H30BO';

        $this->assertEquals($expected, $data->getExpiredVideos());
    }

    /**
     * Test `getExpiredVideos()` with caching disabled
     */
    public function testGetExpiredVideosWithCachingDisabled(): void
    {
        $config = self::createConfigStub([
            'getCacheDisableStatus' => true,
            'getCacheDirectory' => mockfs::getUrl('/'),
            'getCacheFormatVersion' => 1
        ]);

        $data = new Data(
            self::$feedId,
            self::$feedType,
            $config
        );

        $this->assertEquals('', $data->getExpiredVideos());
    }

    /**
     * Test `updateDetails()`
     */
    public function testUpdateDetails(): void
    {
        self::$data->updateDetails(self::$apiResponses->details);
        $data = self::$data->getData();

        $etag = self::$apiResponses->details->etag;
        $title = self::$apiResponses->details->items[0]->snippet->title;
        $description = self::$apiResponses->details->items[0]->snippet->description;
        $thumbnail = self::$apiResponses->details->items[0]->snippet->thumbnails->default->url;

        $this->assertEquals($etag, $data['details']['etag']);
        $this->assertEquals($title, $data['details']['title']);
        $this->assertEquals($description, $data['details']['description']);
        $this->assertEquals($thumbnail, $data['details']['thumbnail']);
        $this->assertGreaterThan(self::$channelCacheData['details']['expires'], $data['details']['expires']);
        $this->assertGreaterThan(self::$channelCacheData['details']['fetched'], $data['details']['fetched']);
    }

    /**
     * Test `updateVideos()`
     */
    public function testUpdateVideos(): void
    {
        $data = new Data(
            self::$feedId,
            self::$feedType,
            self::$config
        );

        $data->updateVideos(self::$apiResponses->videos);
        $data = $data->getData();

        $duration = '03:45';
        $tags = self::$apiResponses->videos->items[0]->snippet->tags;
        $thumbnail = 'https://i.ytimg.com/vi/CkZyZFa5qO0/sddefault.jpg';

        $this->assertEquals($duration, $data['videos'][0]['duration']);
        $this->assertEquals($tags, $data['videos'][0]['tags']);
        $this->assertEquals($thumbnail, $data['videos'][0]['thumbnail']);
        $this->assertGreaterThan(self::$channelCacheData['videos'][0]['expires'], $data['videos'][0]['expires']);
        $this->assertGreaterThan(self::$channelCacheData['videos'][0]['fetched'], $data['videos'][0]['fetched']);
    }

    /**
     * Test `updateFeed()`
     */
    public function testUpdateFeed(): void
    {
        $rssFeedResponse = (string) file_get_contents('tests/files/data-class-rss-feed-response-sample.xml');

        self::$data->updateFeed($rssFeedResponse);
        $data = self::$data->getData();

        /** @var \SimpleXMLElement $xml */
        $xml = simplexml_load_string($rssFeedResponse);
        $namespaces = $xml->getNamespaces(true);

        $mediaNodes = $xml->entry[0]->children($namespaces['media']);

        $title = (string) $xml->entry[0]->title;
        $description = (string) $mediaNodes->group->description;
        $published = strtotime((string) $xml->entry[0]->published);
        $author = (string) $xml->entry[0]->author->name;

        $this->assertEquals($title, $data['videos'][0]['title']);
        $this->assertEquals($description, $data['videos'][0]['description']);
        $this->assertEquals($published, $data['videos'][0]['published']);
        $this->assertEquals($author, $data['videos'][0]['author']);
        $this->assertGreaterThan(self::$channelCacheData['feed']['expires'], $data['feed']['expires']);
        $this->assertGreaterThan(self::$channelCacheData['feed']['fetched'], $data['feed']['fetched']);
    }

    #[Depends('testUpdateVideos')]
    public function testSave(): void
    {
        $oldHash = hash_file('sha256', self::$cacheFilepath);

        self::$data->updateVideos(self::$apiResponses->videos);
        self::$data->save();

        $this->assertNotEquals($oldHash, hash_file('sha256', self::$cacheFilepath));
    }
}
