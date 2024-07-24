<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use App\Config;
use App\FeedFormat\RssFormat;

#[CoversClass(RssFormat::class)]
#[UsesClass(Config::class)]
#[UsesClass(App\FeedFormat\FeedFormat::class)]
#[UsesClass(App\Helper\Convert::class)]
#[UsesClass(App\Helper\Format::class)]
#[UsesClass(App\Helper\Url::class)]
#[UsesClass(App\Helper\File::class)]
#[UsesClass(App\Template::class)]
class RssFormatTest extends AbstractTestCase
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
        $format = new RssFormat($this->data, false, false, $this->config);
        $format->build();

        $this->assertXmlStringEqualsXmlFile(
            'tests/files/FeedFormats/expected-rss-feed.xml',
            $format->get()
        );
    }

    /**
     * Test `build()` with video premieres ignored
     */
    public function testBuildWithIgnoredPremieres(): void
    {
        $format = new RssFormat($this->data, false, true, $this->config);
        $format->build();

        file_put_contents('tests/files/FeedFormats/expected-xml-feed-with-ignored-premieres.xml', $format->get());

        $this->assertXmlStringEqualsXmlFile(
            'tests/files/FeedFormats/expected-xml-feed-with-ignored-premieres.xml',
            $format->get()
        );
    }

    /**
     * Test `build()` with image proxy enabled
     */
    public function testBuildWithImageProxy(): void
    {
        $config = $this->createConfigStub([
            'getImageProxyStatus' => true,
            'getSelfUrl' => 'https://example.com/',
            'getTimezone' => 'Europe/London',
            'getDateFormat' => 'F j, Y',
            'getTimeFormat' => 'H:i'
        ]);

        $format = new RssFormat($this->data, false, false, $config);
        $format->build();

        $this->assertXmlStringEqualsXmlFile(
            'tests/files/FeedFormats/expected-rss-feed-with-image-proxy.xml',
            $format->get()
        );
    }

    /**
     * Test `getContentType()`
     */
    public function testGetContentType(): void
    {
        $format = new RssFormat($this->data, false, false, $this->config);
        $format->build();

        $this->assertEquals('text/xml; charset=UTF-8', $format->getContentType());
    }

    /**
     * Test `getLastModified()`
     */
    public function testGetLastModified(): void
    {
        $format = new RssFormat($this->data, false, false, $this->config);
        $format->build();

        $this->assertEquals('Wed, 18 Oct 2023 11:22:06 BST', $format->getLastModified());
    }
}
