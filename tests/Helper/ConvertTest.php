<?php

use PHPUnit\Framework\TestCase;
use App\Helper\Convert;

class ConvertTest extends TestCase
{
    /**
     * Test `videoDuration()`
     */
    public function testVideoDuration(): void
    {
        self::assertEquals('03:45', Convert::videoDuration('PT3M45S'));
        self::assertEquals('01:33:05', Convert::videoDuration('PT1H33M5S'));
		self::assertEquals('10:00:05', Convert::videoDuration('PT10H5S'));
        self::assertEquals('1:00:00:45', Convert::videoDuration('P1DT45S'));
    }

    /**
     * Test `fileSize()`
     */
    public function testFileSize(): void
    {
        self::assertEquals('1 GB', Convert::fileSize(1073741824));
        self::assertEquals('1 MB', Convert::fileSize(1048576));
        self::assertEquals('1 KB', Convert::fileSize(1024));
        self::assertEquals('100 bytes', Convert::fileSize(100));
        self::assertEquals('1 byte', Convert::fileSize(1));
        self::assertEquals('0 bytes', Convert::fileSize(0));
    }

    /**
     * Test `unixTime()`
     */
    public function testUnixTime(): void
    {
        self::assertEquals('1970-01-01 01:00:00', Convert::unixTime(0, 'Y-m-d H:i:s', 'Europe/London'));
        self::assertEquals('2023-10-17T23:46:52+01:00', Convert::unixTime(1697582812, 'c', 'Europe/London'));
    }

    /**
     * Test `urls()`
     */
    public function testUrls(): void
    {
        $plaintext = 'Visit us at https://example.com/phpuint/';
        $html = 'Visit us at <a href="https://example.com/phpuint/" target="_blank">https://example.com/phpuint/</a>';

        self::assertEquals($html, Convert::urls($plaintext));
    }

    /**
     * Test `newlines()`
     */
    public function testNewlines(): void
    {
        self::assertEquals('<br />', Convert::newlines("\r\n"));
        self::assertEquals('<br />', Convert::newlines("\r"));
        self::assertEquals('<br />', Convert::newlines("\n"));
    }
}
