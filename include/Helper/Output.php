<?php

namespace App\Helper;

use App\Helper\Log;

class Output
{
    /**
     * Output error message
     *
     * @param string $message Error message
     * @param int $code HTTP response code
     */
    public static function error(string $message, int $code = 400): void
    {
        Log::error($message);

        http_response_code($code);
        header('Content-Type: text/plain');
        echo $message;
    }

    /**
     * Output feed
     *
     * @param string $data Feed data
     * @param string $contentType Content-type header value
     * @param string $lastModified Last-modified header value
     */
    public static function feed(string $data, string $contentType, string $lastModified): void
    {
        header('content-type: ' . $contentType);
        header('last-modified:' . $lastModified);
        echo $data;
    }

    /**
     * Output image
     *
     * @param string $data Image data
     * @param string $contentType Content-type header value
     */
    public static function image(string $data, string $contentType = 'image/jpeg'): void
    {
        header('content-type: ' . $contentType);
        echo $data;
    }
}
