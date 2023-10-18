<?php

use PHPUnit\Framework\TestCase;
use App\Config;
use App\FeedFormat\Json;

class JsonTest extends TestCase
{
    private Config $config;

    private array $data = [];

    /**
     * @return Config&PHPUnit\Framework\MockObject\Stub
     */
    private function createConfigStub(): Config
    {
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
        $this->data = (array) json_decode(file_get_contents('tests/files/channel-cache-data.json'), true);
        $this->config = $this->createConfigStub();
    }

    /**
     * Test `build()`
     */
    public function testBuild(): void
    {
        $output = file_get_contents('tests/files/FeedFormats/channel.json');

        $format = new Json($this->data, false, $this->config);
        $format->build();

        $this->assertEquals(
           json_decode($output),
           json_decode($format->get())
        );
    }

    /**
     * Test `getContentType()`
     */
    public function testGetContentType(): void
    {
        $format = new Json($this->data, false, $this->config);
        $format->build();

        $this->assertEquals('application/json', $format->getContentType());
    }

    /**
     * Test `getLastModified()`
     */
    public function testGetLastModified(): void
    {
        $format = new Json($this->data, false, $this->config);
        $format->build();

        $this->assertEquals('Wed, 18 Oct 2023 11:22:06 BST', $format->getLastModified());
    }
}
