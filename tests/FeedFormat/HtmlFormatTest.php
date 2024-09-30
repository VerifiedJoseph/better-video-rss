<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use App\Config;
use App\FeedFormat\FeedFormat;
use App\FeedFormat\HtmlFormat;

#[CoversClass(FeedFormat::class)]
#[CoversClass(HtmlFormat::class)]
#[UsesClass(Config::class)]
#[UsesClass(App\FeedFormat\FeedFormat::class)]
#[UsesClass(App\Helper\Convert::class)]
#[UsesClass(App\Helper\Format::class)]
#[UsesClass(App\Helper\Json::class)]
#[UsesClass(App\Helper\File::class)]
#[UsesClass(App\Helper\Url::class)]
#[UsesClass(App\Template::class)]
class HtmlFormatTest extends AbstractTestCase
{
    private Config $config;

    /** @var array<string, mixed> $data */
    private array $data = [];

    public function setUp(): void
    {
        $this->data = (array) json_decode((string) file_get_contents('tests/files/channel-cache-data.json'), true);
        $this->config = self::createConfigStub([
            'getSelfUrl' => 'https://example.com/',
            'getTimezone' => 'Europe/London',
            'getDateFormat' => 'F j, Y',
            'getTimeFormat' => 'H:i',
            'getFeedFormats' => ['html','json']
        ]);
    }

    /**
     * Test `build()`
     */
    public function testBuild(): void
    {
        $format = new HtmlFormat($this->data, false, false, $this->config);
        $format->build();

        $expected = (string) file_get_contents('tests/files/FeedFormats/expected-html-feed.html');

        $this->assertStringEqualsStringIgnoringLineEndings(
            $expected,
            $format->get()
        );
    }

    /**
     * Test `build()` with video premieres ignored
     */
    public function testBuildWithIgnoredPremieres(): void
    {
        $format = new HtmlFormat($this->data, false, true, $this->config);
        $format->build();

        $expected = (string) file_get_contents('tests/files/FeedFormats/expected-html-with-ignored-premieres.html');

        $this->assertStringEqualsStringIgnoringLineEndings(
            $expected,
            $format->get()
        );
    }

    /**
     * Test `build()` with video iframes
     */
    public function testBuildWithIFrames(): void
    {
        $format = new HtmlFormat($this->data, true, false, $this->config);
        $format->build();

        $expected = (string) file_get_contents('tests/files/FeedFormats/expected-html-feed-with-iframes.html');

        $this->assertStringEqualsStringIgnoringLineEndings(
            $expected,
            $format->get()
        );
    }

    /**
     * Test `getContentType()`
     */
    public function testGetContentType(): void
    {
        $format = new HtmlFormat($this->data, false, false, $this->config);
        $format->build();

        $this->assertEquals('text/html; charset=UTF-8', $format->getContentType());
    }

    /**
     * Test `getLastModified()`
     */
    public function testGetLastModified(): void
    {
        $format = new HtmlFormat($this->data, false, false, $this->config);
        $format->build();

        $this->assertEquals('Wed, 18 Oct 2023 11:22:06 BST', $format->getLastModified());
    }
}
