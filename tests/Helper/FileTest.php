<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use MockFileSystem\MockFileSystem as mockfs;
use App\Helper\File;

#[CoversClass(File::class)]
class FileTest extends TestCase
{
    public function setup(): void
    {
        mockfs::create();
    }

    public function tearDown(): void
    {
        stream_context_set_default(
            [
                'mfs' => [
                    'fread_fail' => false,
                    'fwrite_fail' => false,
                    'fopen_fail' => false
                ]
            ]
        );
    }

    /**
     * Test `open()`
     */
    public function testOpen(): void
    {
        $file = mockfs::getUrl('/test.file');
        file_put_contents($file, uniqid());

        $handler = File::open($file, 'r');
        $contents = fread($handler, (int) filesize($file));

        $this->assertIsString($contents);
    }

    /**
     * Test `open()` not opened failure
     */
    public function testOpenFailure(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File not opened');

        $file = mockfs::getUrl('/test.file');
        file_put_contents($file, uniqid());

        $this->setStreamContext(['fopen_fail' => true]);

        File::open($file, 'r');
    }

    /**
     * Test `read()`
     */
    public function testRead(): void
    {
        $file = mockfs::getUrl('/test.file');
        file_put_contents($file, 'Hello World');

        self::assertEquals('Hello World', File::read($file));
    }

    /**
     * Test `read()` file not read exception.
     */
    public function testReadNotReadException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File not read');

        $file = mockfs::getUrl('/test.file');
        file_put_contents($file, uniqid());

        $this->setStreamContext(['fread_fail' => true]);

        File::read($file);
    }

    /**
     * Test `write()`
     */
    public function testWrite(): void
    {
        $data = 'Hello Word from PHP Unit';

        File::write(mockfs::getUrl('/test.file'), $data);

        self::assertEquals($data, File::read(mockfs::getUrl('/test.file')));
    }

    /**
     * Test `write()` file not written exception.
     */
    public function testWriteNotWrittenException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File not written');

        //$file = mockfs::getUrl('/test.file');
        //file_put_contents($file, uniqid());

        $this->setStreamContext(['fwrite_fail' => true]);

        File::write(mockfs::getUrl('/test.file'), 'hello');
    }

    /**
     * Test exists()
     */
    public function testExists(): void
    {
        $file = mockfs::getUrl('/test.file');
        file_put_contents($file, uniqid());

        self::assertEquals(true, File::exists($file));
    }

    /**
     * Test exists() when file does not exist.
     */
    public function testExistsFalse(): void
    {
        $file = mockfs::getUrl('/test.file');

        self::assertEquals(false, File::exists($file));
    }

    /**
     * Set stream context defaults for `MockFileSystem\MockFileSystem`
     *
     * @param array<string, boolean> $options
     */
    private function setStreamContext(array $options): void
    {
        stream_context_set_default([
            'mfs' => $options
        ]);
    }
}
