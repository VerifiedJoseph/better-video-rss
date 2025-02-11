<?php

declare(strict_types=1);

namespace App\Helper;

class Log
{
    /**
     * Write error message to system log file
     *
     * @param string $message Error message
     */
    public static function error(string $message): void
    {
        error_log('[BetterVideoRss] ' . $message, 0);
    }
}
