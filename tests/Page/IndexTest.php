<?php

use PHPUnit\Framework\TestCase;
use App\Config;
use App\Api;
use App\Page\Index;

class IndexTest extends TestCase
{
    private static Config $config;
    private static Api $api;

    public static function setUpBeforeClass(): void
    {
        self::$config = new Config();
        self::$api = new Api(self::$config);
    }

    /**
     * Test class with empty query
     */
    public function testClassWithEmptyQuery(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Query parameter not given.');

        $inputs = [
            'query' => ''
        ];

        new Index($inputs, self::$config, self::$api);
    }

    /**
     * Test class with empty type
     */
    public function testClassWithEmptyType(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Type parameter not given.');

        $inputs = [
            'query' => 'Hello World',
            'type' => ''
        ];

        new Index($inputs, self::$config, self::$api);
    }

    /**
     * Test class with unsupported type
     */
    public function testClassWithUnsupportedType(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown type parameter given.');

        $inputs = [
            'query' => 'Hello World',
            'type' => 'fake-type-here'
        ];

        new Index($inputs, self::$config, self::$api);
    }
}
