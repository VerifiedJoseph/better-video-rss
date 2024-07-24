<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Exception\ConfigException;

#[CoversClass(ConfigException::class)]
class ConfigExceptionTest extends TestCase
{
    public function testConfigException(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Config Error: testing');

        throw new ConfigException('testing');
    }
}
