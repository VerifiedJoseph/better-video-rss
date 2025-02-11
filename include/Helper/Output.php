<?php

declare(strict_types=1);

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
     * @param string $type Content-type header value
     * @param string $modified Last-modified header value
     * @param string $csp Content-Security-Policy header value
     * @param bool $cspDisabled Content security policy disabled status
     */
    public static function feed(string $data, string $type, string $modified, string $csp, bool $cspDisabled): void
    {
        if ($cspDisabled === false) {
            header('Content-Security-Policy:' . $csp);
        }

        header('content-type: ' . $type);
        header('last-modified:' . $modified);
        echo $data;
    }
}
