<?php

use PHPUnit\Framework\TestCase;
use App\Helper\File;

class FileTest extends TestCase
{
    private static string $tempFilePath;

    public static function setUpBeforeClass(): void
    {
        self::$tempFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'text.txt';

        file_put_contents(self::$tempFilePath, 'Hello World');
    }

    /**
     * Test `read()` with file that does not exist
     */
    public function testReadWithMissingFile(): void
    {
        self::assertEquals('', File::read('i-do-not-exist.txt'));
    }

    /**
     * Test `read()`
     */
    public function testRead(): void
    {
        self::assertEquals('Hello World', File::read(self::$tempFilePath));
    }

    /**
     * Test `write()`
     */
    public function testWrite(): void
    {
        $data = 'Hello Word from PHP Unit';

        File::write(self::$tempFilePath, $data);

        self::assertEquals($data, File::read(self::$tempFilePath));
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$tempFilePath);
    }
}
