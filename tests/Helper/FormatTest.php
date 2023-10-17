<?php

use PHPUnit\Framework\TestCase;
use App\Helper\Format;

class FormatTest extends TestCase
{
    /**
     * Test `minify()`
     */
    public function testMinify(): void
    {
        $input = file_get_contents('tests/files/test.html');
        $output = trim(file_get_contents('tests/files/test-minified.html'));

        $this->assertEquals($output, Format::minify($input));
    }
}
