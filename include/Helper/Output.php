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
    public static function error(string $message, int $code = 500): void
    {
        Log::error($message);

        http_response_code($code);
        header('Content-Type: text/plain');
        echo $message;
    }

    /**
     * Output html
     *
     * @param string $html Page HTML
     * @param string $csp Content-Security-Policy header value
     * @param bool $cspDisabled Content-Security-Policy disabled status
     */
    public static function html(string $html, string $csp, bool $cspDisabled): void
    {
        if ($cspDisabled === false) {
            header('Content-Security-Policy:' . $csp);
        }

        echo $html;
    }

    /**
     * Output feed
     *
     * @param string $data Feed data
     * @param string $contentType Content-type header value
     * @param string $lastModified Last-modified header value
     * @param string $csp Content-Security-Policy header value
     * @param bool $cspDisabled Content security policy disabled status
     */
    public static function feed(string $data, string $contentType, string $lastModified, string $csp, bool $cspDisabled): void
    {
        if ($cspDisabled === false) {
            header('Content-Security-Policy:' . $csp);
        }

        header('content-type: ' . $contentType);
        header('last-modified:' . $lastModified);
        echo $data;
    }

    /**
     * Output image
     *
     * @param string $data Image data
     * @param string $contentType Content-type header value
     * @param string $csp Content-Security-Policy header value
     * @param bool $cspDisabled Content security policy disabled status
     */
    public static function image(string $data, string $contentType, string $csp, bool $cspDisabled): void
    {
        if ($cspDisabled === false) {
            header('Content-Security-Policy:' . $csp);
        }

        header('content-type: ' . $contentType);
        echo $data;
    }
}
