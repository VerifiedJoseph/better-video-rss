<?php

namespace App\Helper;

use Exception;

class File
{
    /**
     * Read a file
     *
     * @param string $path File path
     * @return string $contents File contents
     *
     * @throws Exception if file was not opened.
     * @throws Exception if file was not read.
     */
    public static function read(string $path): string
    {
        // Return empty string if file does not exist
        if (file_exists($path) === false) {
            return '';
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new Exception('File not opened: ' . $path);
        }

        $contents = fread($handle, (int) filesize($path));

        if ($contents === false) {
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
     * @throws Exception if file was not opened.
     * @throws Exception if data was not written to file.
     */
    public static function write(string $path, string $data): void
    {
        $handle = fopen($path, 'w');

        if ($handle === false) {
            throw new Exception('File not opened: ' . $path);
        }

        $status = fwrite($handle, $data);

        if ($status === false) {
            throw new Exception('File not written: ' . $path);
        }

        fclose($handle);
    }
}
