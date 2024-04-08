<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Request;

#[CoversClass(Request::class)]
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
        $body = json_decode($response['body'], true);

        $this->assertArrayHasKey('body', $response);
        $this->assertArrayHasKey('statusCode', $response);
        $this->assertEquals(200, $response['statusCode']);

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
