<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
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
    private static Data $data;

    /** @var array<string, mixed> $channelCacheData */
    private static array $channelCacheData;

    private static stdClass $apiResponses;
    private static string $cacheFilepath = '';
    private static string $feedId = 'UCMufUaGlcuAvsSdzQV08BEA';
    private static string $feedType = 'channel';

    private static function createCacheFile(): void
    {
        self::$channelCacheData = (array) json_decode(
            (string)
            file_get_contents('tests/files/channel-cache-data.json'),
            associative: true
        );

        self::$apiResponses = json_decode(
            (string) file_get_contents('tests/files/data-class-api-response-samples.json'),
        );

        self::$cacheFilepath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . hash('sha256', self::$feedId) . '.cache';

        copy('tests/files/channel-cache-data.json', self::$cacheFilepath);
    }

    public static function setUpBeforeClass(): void
    {
        self::createCacheFile();
        $config = self::createConfigStub([
            'getCacheDisableStatus' => false,
            'getCacheDirectory' => sys_get_temp_dir(),
            'getCacheFormatVersion' => 1
        ]);

        self::$data = new Data(
            self::$feedId,
            self::$feedType,
            $config
        );
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$cacheFilepath);
    }

    /**
     * Test `getData()`
     */
    public function testGetData(): void
    {
        $this->assertEquals(self::$channelCacheData, self::$data->getData());
    }

    /**
     * Test `getPartEtag()`
     */
    public function testGetPartEtag(): void
    {
        $expected = 'Q4tjoKjLcrpOC7-WzcBdtCxY0kg';

        $this->assertEquals($expected, self::$data->getPartEtag('details'));
    }

    /**
     * Test `getExpiredParts()`
     */
    public function testGetExpiredParts(): void
    {
        $expected = ['details', 'feed', 'videos'];

        $this->assertEquals($expected, self::$data->getExpiredParts());
    }

    /**
     * Test `getExpiredVideos()`
     */
    public function testGetExpiredVideos(): void
    {
        $expected = 'Owd0fCoJhiv,jVhUHba1WyK,e3bDAwuzUnd,MVsly5H30BO';

        $this->assertEquals($expected, self::$data->getExpiredVideos());
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
        self::$data->updateVideos(self::$apiResponses->videos);
        $data = self::$data->getData();

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

    public function testSave(): void
    {
        $oldHash = hash_file('sha256', self::$cacheFilepath);

        self::$data->save();

        $this->assertNotEquals($oldHash, hash_file('sha256', self::$cacheFilepath));
    }
}
