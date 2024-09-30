<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use App\Config;
use App\FeedFormat\JsonFormat;

#[CoversClass(JsonFormat::class)]
#[UsesClass(Config::class)]
#[UsesClass(App\FeedFormat\FeedFormat::class)]
#[UsesClass(App\Helper\Convert::class)]
#[UsesClass(App\Helper\Format::class)]
#[UsesClass(App\Helper\Json::class)]
#[UsesClass(App\Helper\Url::class)]
class JsonFormatTest extends AbstractTestCase
{
    private Config $config;

    /** @var array<string, mixed> $data */
    private array $data = [];

    public function setUp(): void
    {
        $this->data = (array) json_decode((string) file_get_contents('tests/files/channel-cache-data.json'), true);
        $this->config = self::createConfigStub([
            'getImageProxyStatus' => false,
            'getSelfUrl' => 'https://example.com/',
            'getTimezone' => 'Europe/London',
            'getDateFormat' => 'F j, Y',
            'getTimeFormat' => 'H:i'
        ]);
    }

    /**
     * Test `build()`
     */
    public function testBuild(): void
    {
        $format = new JsonFormat($this->data, false, false, $this->config);
        $format->build();

        $this->assertJsonStringEqualsJsonFile(
            'tests/files/FeedFormats/expected-json-feed.json',
            $format->get()
        );
    }

    /**
     * Test `build()` with video premieres ignored
     */
    public function testBuildWithIgnoredPremieres(): void
    {
        $format = new JsonFormat($this->data, false, true, $this->config);
        $format->build();

        $this->assertJsonStringEqualsJsonFile(
            'tests/files/FeedFormats/expected-json-feed-with-ignored-premieres.json',
            $format->get()
        );
    }

    /**
     * Test `build()` with video iframes
     */
    public function testBuildWithIFrames(): void
    {
        $format = new JsonFormat($this->data, true, false, $this->config);
        $format->build();

        $this->assertJsonStringEqualsJsonFile(
            'tests/files/FeedFormats/expected-json-feed-with-iframes.json',
            $format->get()
        );
    }

    /**
     * Test `getContentType()`
     */
    public function testGetContentType(): void
    {
        $format = new JsonFormat($this->data, false, false, $this->config);
        $format->build();

        $this->assertEquals('application/json', $format->getContentType());
    }

    /**
     * Test `getLastModified()`
     */
    public function testGetLastModified(): void
    {
        $format = new JsonFormat($this->data, false, false, $this->config);
        $format->build();

        $this->assertEquals('Wed, 18 Oct 2023 11:22:06 BST', $format->getLastModified());
    }
}
