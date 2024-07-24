<?php

use PHPUnit\Framework\TestCase;
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
class RssFormatTest extends TestCase
{
    private Config $config;

    /** @var array<string, mixed> $data */
    private array $data = [];

    /**
     * @return PHPUnit\Framework\MockObject\Stub&Config
     */
    private function createConfigStub(): Config
    {
        /** @var PHPUnit\Framework\MockObject\Stub&Config */
        $config = $this->createStub(Config::class);
        $config->method('getImageProxyStatus')->willReturn(false);
        $config->method('getSelfUrl')->willReturn('https://example.com/');
        $config->method('getTimezone')->willReturn('Europe/London');
        $config->method('getDateFormat')->willReturn('F j, Y');
        $config->method('getTimeFormat')->willReturn('H:i');

        return $config;
    }

    public function setUp(): void
    {
        $this->data = (array) json_decode((string) file_get_contents('tests/files/channel-cache-data.json'), true);
        $this->config = $this->createConfigStub();
    }

    /**
     * Test `build()`
     */
    public function testBuild(): void
    {
        $format = new RssFormat($this->data, false, false, $this->config);
        $format->build();

        $expected = 'tests/files/FeedFormats/expected-rss-feed.xml';
        $this->assertXmlStringEqualsXmlFile(
            $expected,
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
