<?php

use PHPUnit\Framework\TestCase;
use App\Helper\Json;

class JsonTest extends TestCase
{
    /**
     * Test `encode()` with valid JSON
     */
    public function testEncodeValid(): void
    {
        self::assertEquals('{"foo":"bar"}', Json::encode(['foo' => 'bar']));
    }

    /**
     * Test `decode()` with valid JSON
     */
    public function testDecodeValid(): void
    {
        $expected = new stdClass();
        $expected->foo = 'bar';
        self::assertEquals($expected, Json::decode('{"foo": "bar"}'));
    }

    /**
     * Test `decode()` with invalid JSON
     */
    public function testDecodeInvalid(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('JSON Error: Syntax error');
        Json::decode('foo');
    }

    /**
     * Test `decodeToArray()` with valid JSON
     */
    public function testDecodeToArrayValid(): void
    {
        self::assertEquals(['foo' => 'bar'], Json::decodeToArray('{"foo": "bar"}'));
    }

    /**
     * Test `decodeToArray()` with invalid JSON
     */
    public function testDecodeToArrayInvalid(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('JSON Error: Syntax error');
        Json::decodeToArray('foo');
    }
}
