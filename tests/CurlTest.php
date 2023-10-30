<?php

use PHPUnit\Framework\TestCase;
use App\Curl;

class CurlTest extends TestCase
{
    /** @var string $httpBinUri httpbin URI */
    private string $httpBinUri = 'https://httpbingo.org';

    /** @var string $useragent HTTP useragent */
    private string $useragent = 'PHPUnit';

    /** @var array<string, string> $header */
    private array $header = [
        'key' => 'X-Test',
        'value' => 'Hello World'
    ];

    /**
     * Test `get()` method
     */
    public function testGet(): void
    {
        $curl = new Curl();
        $curl->setUserAgent($this->useragent);
        $curl->setHeader(
            $this->header['key'],
            $this->header['value']
        );
        $curl->get($this->getHttpBinUri() . '/get');

        /** @var stdClass $response */
        $response = json_decode($curl->getResponse());

        $this->assertEquals(200, $curl->getStatusCode());

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('headers', $response);
        $this->assertObjectHasProperty($this->header['key'], $response->headers);
        $this->assertObjectHasProperty('User-Agent', $response->headers);

        $headers = get_object_vars($response->headers);
        $this->assertEquals($this->header['value'], $headers[$this->header['key']][0]);
        $this->assertEquals($this->useragent, $headers['User-Agent'][0]);
    }

    /**
     * Test `get()` method with an invalid URL
     */
    public function testGetWithInvalidUrl(): void
    {
        $curl = new Curl();
        $curl->get('https://example.invalid');

        $this->assertEquals(6, $curl->getErrorCode());
        $this->assertEquals('Could not resolve host: example.invalid', $curl->getErrorMessage());
    }

    /**
     * Returns httpbin server URI
     *
     * Return value of class property `$getHttpBinUri` or environment variable `HTTPBIN_URI` if set.
     */
    private function getHttpBinUri(): string
    {
        if (getenv('HTTPBIN_URI') !== false) {
            return getenv('HTTPBIN_URI');
        }

        return $this->httpBinUri;
    }
}
