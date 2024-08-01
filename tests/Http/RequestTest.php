<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use App\Http\Request;
use App\Http\Response;

#[CoversClass(Request::class)]
#[UsesClass(Response::class)]
class RequestTest extends TestCase
{
    private string $useragent = 'phpunit-test';

    /**
     * Test `get()`
     */
    public function testGet(): void
    {
        $request = new Request($this->useragent);
        $response = $request->get('https://httpbin.org/headers', ['testing' => 'Hello World']);
        $body = json_decode($response->getBody(), true);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArrayHasKey('Testing', $body['headers']);
        $this->assertEquals('Hello World', $body['headers']['Testing']);
        $this->assertEquals($this->useragent, $body['headers']['User-Agent']);
    }

    /**
     * Test `get()` throwing an exception when an cURL error occurs
     */
    public function testCurlError(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not resolve host: example.invalid');

        $request = new Request($this->useragent);
        $request->get('https://example.invalid');
    }
}
