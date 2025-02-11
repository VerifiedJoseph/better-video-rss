<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase as TestCase;
use App\Config;

abstract class AbstractTestCase extends TestCase
{
    /**
     * Create config class stub
     *
     * Array example: `['getSelfUrl' => 'https://example.com/', 'getTimezone' => 'UTC']`
     *
     * @param array<string, mixed> $methods
     * @return PHPUnit\Framework\MockObject\Stub&Config
     */
    protected static function createConfigStub(array $methods): Config
    {
        /** @var PHPUnit\Framework\MockObject\Stub&Config */
        $config = self::createStub(Config::class);

        foreach ($methods as $method => $value) {
            $config->method($method)->willReturn($value);
        }

        return $config;
    }
}
