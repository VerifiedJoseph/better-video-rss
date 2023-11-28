<?php

use PHPUnit\Framework\TestCase;
use App\Config;
use App\FeedFormat\JsonFormat;

class JsonFormatTest extends TestCase
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
