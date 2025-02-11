<?php

declare(strict_types=1);

namespace Test\Config;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Config\Base;

#[CoversClass(Base::class)]
class BaseTest extends TestCase
{
    /**
     * Test `getEnv()`
     */
    public function testGetEnv(): void
    {
        putenv('BVRSS_TEST=hello');

        $class = new class () extends Base {
        };
        $this->assertEquals('hello', $class->getEnv('TEST'));
    }

    /**
     * Test `getEnv()` with no environment variable
     */
    public function testGetEnvEmptyValue(): void
    {
        putenv('BVRSS_TEST');

        $class = new class () extends Base {
        };
        $this->assertEquals('', $class->getEnv('TEST_1'));
    }
}
