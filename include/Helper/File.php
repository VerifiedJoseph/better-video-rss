<?php

declare(strict_types=1);

namespace App\Helper;

use Exception;

class File
{
    /**
     * Open a file handler
     *
     * @param string $path File path
     * @param string $mode Mode
     *
     * @throws Exception if file is not opened.
     */
    public static function open(string $path, string $mode): mixed
    {
        $handle = @fopen($path, $mode);

        if ($handle === false) {
            throw new Exception('File not opened: ' . $path);
        }

        return $handle;
    }

    /**
     * Read a file
     *
     * @param string $path File path
     * @return string $contents File contents
     *
     * @throws Exception if file is not read.
     * @throws Exception if file is empty.
     */
    public static function read(string $path): string
    {
        $handle = File::open($path, 'r');
        $size = (int) filesize($path);

        if ($size === 0) {
            throw new Exception('File is empty: ' . $path);
        }

        $contents = @fread($handle, $size);

        if ($contents === false || $contents === '') {
            throw new Exception('File not read: ' . $path);
        }

        fclose($handle);

        return $contents;
    }

    /**
     * Write a file
     *
     * @param string $path File path
     * @param string $data Data to write
     *
     * @throws Exception if data is not written to file.
     */
    public static function write(string $path, string $data): void
    {
        $handle = File::open($path, 'w');
        $status = fwrite($handle, $data);

        if ($status === false || $status === 0) {
            throw new Exception('File not written: ' . $path);
        }

        fclose($handle);
    }

    /**
     * Checks whether a file exists
     *
     * @param string $path File path
     * @return bool
     */
    public static function exists(string $path)
    {
        clearstatcache();
        return file_exists($path);
    }
}
