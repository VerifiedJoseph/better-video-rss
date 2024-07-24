<?php

use PHPUnit\Framework\TestCase;
use App\Helper\Format;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Format::class)]
class FormatTest extends TestCase
{
    /**
     * Test `minify()`
     */
    public function testMinify(): void
    {
        $input = (string) file_get_contents('tests/files/test.html');

        $this->assertStringEqualsFile('tests/files/test-minified.html', Format::minify($input));
    }
}
