<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use App\Api;
use App\Config;
use App\Http\Request;
use App\Http\Response;

#[CoversClass(Api::class)]
class ApiTest extends TestCase
{
    private static Config $config;

    public static function setupBeforeClass(): void
    {
        /** @var PHPUnit\Framework\MockObject\Stub&Config */
        $config = self::createStub(Config::class);
        $config->method('getApiKey')->willReturn('qwerty');
        $config->method('getRawApiErrorStatus')->willReturn(false);
        self::$config = $config;
    }

    public function testGetDetails(): void
    {
        /** @var PHPUnit\Framework\MockObject\Stub&Request */
        $request = self::createStub(Request::class);
        $request->method('get')->willReturn(new Response('{"items": [{}]}', 200));

        $api = new Api(self::$config, $request);
        $body = $api->getDetails('channel', 'UCBa659QWEk1AI4Tg--mrJ2A', 'e-tag');

        $this->assertInstanceOf(stdClass::class, $body);
        $this->assertObjectHasProperty('items', $body);
    }

    /**
     * Test `getDetails()` with API request that returns 304
     */
    public function testGetDetailsHttp304(): void
    {
        /** @var PHPUnit\Framework\MockObject\Stub&Request */
        $request = self::createStub(Request::class);
        $request->method('get')->willReturn(new Response('', 304));

        $api = new Api(self::$config, $request);
        $body = $api->getDetails('channel', 'UCBa659QWEk1AI4Tg--mrJ2A', 'e-tag');

        $this->assertIsString($body);
        $this->assertEmpty($body);
    }

    /**
     * Test `getDetails()` with API request that returns no items
     */
    public function testGetDetailsNotItems(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Channel not found');

        /** @var PHPUnit\Framework\MockObject\Stub&Request */
        $request = self::createStub(Request::class);
        $request->method('get')->willReturn(new Response('{"items": []}', 200));

        $api = new Api(self::$config, $request);
        $body = $api->getDetails('channel', 'UCBa659QWEk1AI4Tg--mrJ2A', 'e-tag');

        $this->assertInstanceOf(stdClass::class, $body);
    }
}
