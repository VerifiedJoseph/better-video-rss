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

    private string $errorResponseBody = '{
        "error": {
          "code": 400,
          "message": "API key not valid. Please pass a valid API key.",
          "errors": [
            {
              "message": "API key not valid. Please pass a valid API key.",
              "domain": "global",
              "reason": "badRequest"
            }
          ]
        }
      }';

    public static function setupBeforeClass(): void
    {
        /** @var PHPUnit\Framework\MockObject\Stub&Config */
        $config = self::createStub(Config::class);
        $config->method('getApiKey')->willReturn('qwerty');
        $config->method('getRawApiErrorStatus')->willReturn(false);
        self::$config = $config;
    }

    /**
     * Test `getDetails()`
     */
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

    /**
     * Test `getVideos()`
     */
    public function testGetVideos(): void
    {
        /** @var PHPUnit\Framework\MockObject\Stub&Request */
        $request = self::createStub(Request::class);
        $request->method('get')->willReturn(new Response('{"items": [{}]}', 200));

        $api = new Api(self::$config, $request);
        $body = $api->getVideos('CkZyZFa5qO0');

        $this->assertInstanceOf(stdClass::class, $body);
        $this->assertObjectHasProperty('items', $body);
    }

    /**
     * Test `searchChannels()`
     */
    public function testSearchChannels(): void
    {
        /** @var PHPUnit\Framework\MockObject\Stub&Request */
        $request = self::createStub(Request::class);
        $request->method('get')->willReturn(new Response('{"items": [{}]}', 200));

        $api = new Api(self::$config, $request);
        $body = $api->searchChannels('Tom Scott');

        $this->assertInstanceOf(stdClass::class, $body);
        $this->assertObjectHasProperty('items', $body);
    }

    /**
     * Test `searchPlaylists()`
     */
    public function testSearchPlaylists(): void
    {
        /** @var PHPUnit\Framework\MockObject\Stub&Request */
        $request = self::createStub(Request::class);
        $request->method('get')->willReturn(new Response('{"items": [{}]}', 200));

        $api = new Api(self::$config, $request);
        $body = $api->searchPlaylists('Things You Might Not Know');

        $this->assertInstanceOf(stdClass::class, $body);
        $this->assertObjectHasProperty('items', $body);
    }

    /**
     * Test `handleError()`
     */
    public function testHandleError(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error: API returned code 400 (badRequest: API key not valid.');

        /** @var PHPUnit\Framework\MockObject\Stub&Request */
        $request = self::createStub(Request::class);
        $request->method('get')->willReturn(new Response($this->errorResponseBody, 400));

        $api = new Api(self::$config, $request);
        $api->getDetails('channel', 'UCBa659QWEk1AI4Tg--mrJ2A', 'e-tag');
    }

    /**
     * Test `handleError()` with raw API errors enabled
     */
    public function testHandleErrorRawApiErrors(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('API error:');

        /** @var PHPUnit\Framework\MockObject\Stub&Config */
        $config = self::createStub(Config::class);
        $config->method('getApiKey')->willReturn('qwerty');
        $config->method('getRawApiErrorStatus')->willReturn(true);

        /** @var PHPUnit\Framework\MockObject\Stub&Request */
        $request = self::createStub(Request::class);
        $request->method('get')->willReturn(new Response($this->errorResponseBody, 400));

        $api = new Api($config, $request);
        $api->getDetails('channel', 'UCBa659QWEk1AI4Tg--mrJ2A', 'e-tag');
    }
}
