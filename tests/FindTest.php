<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use App\Find;
use App\Api;

#[CoversClass(Find::class)]
#[UsesClass(Api::class)]
class FindTest extends TestCase
{
    /**
     * Test `lookup()` with channel
     */
    public function testLookupWithChannel(): void
    {
        $response = (object) [
            'items' => [
                (object) [
                    'id' => (object) ['channelId' => 'UCMufUaGlcuAvsSdzQV08BEA'],
                    'snippet' => (object) ['title' => 'Example channel']
                ]
            ]
        ];

        /** @var PHPUnit\Framework\MockObject\Stub&Api */
        $api = self::createStub(Api::class);
        $api->method('searchChannels')->willReturn($response);

        $find = new Find('channel', $api);
        $find->lookup('test');

        $this->assertEquals('UCMufUaGlcuAvsSdzQV08BEA', $find->getId());
        $this->assertEquals('Example channel', $find->getTitle());
    }

    /**
     * Test `lookup()` with playlist
     */
    public function testLookupWithPlaylist(): void
    {
        $response = (object) [
            'items' => [
                (object) [
                    'id' => (object) ['playlistId' => 'PLuQSJ2zY5-wSWuKSVrokXq7mDCRqGPsFW'],
                    'snippet' => (object) ['title' => 'Example playlist']
                ]
            ]
        ];

        /** @var PHPUnit\Framework\MockObject\Stub&Api */
        $api = self::createStub(Api::class);
        $api->method('searchPlaylists')->willReturn($response);

        $find = new Find('playlist', $api);
        $find->lookup('test');

        $this->assertEquals('PLuQSJ2zY5-wSWuKSVrokXq7mDCRqGPsFW', $find->getId());
        $this->assertEquals('Example playlist', $find->getTitle());
    }

    /**
     * Test exception
     */
    public function testException(): void
    {
        $response = (object) [
            'items' => []
        ];

        /** @var PHPUnit\Framework\MockObject\Stub&Api */
        $api = self::createStub(Api::class);
        $api->method('searchChannels')->willReturn($response);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Channel not found');

        $find = new Find('channel', $api);
        $find->lookup('Hello World');
    }
}
