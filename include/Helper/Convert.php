<?php

namespace App\Helper;

use DateTime;
use DateTimeZone;

class Convert
{
    /** @var int $byteCountGB Number of bytes in a GB */
    private static int $byteCountGB = 1073741824;

    /** @var int $byteCountMB Number of bytes in a MB */
    private static int $byteCountMB = 1048576;

    /** @var int $byteCountKB Number of bytes in a KB */
    private static int $byteCountKB = 1024;

    /** @var int $numDecimalPlaces Number of decimal places to round to. */
    private static int $numDecimalPlaces = 2;

    /** @var int $minByteCount Minimum byte count */
    private static int $minByteCount = 1;

    /** @var string $urlRegex URL regex */
    private static string $urlRegex = <<<REGEX
     /(https?:\/\/(?:www\.)?
     (?:[a-zA-Z0-9-.]{2,256}\.[a-z]{2,20})
     (\:[0-9]{2,4})?(?:\/[a-zA-Z0-9@:%_\+.,~#"!?&\/\/=\-*]+|\/)?)
     /ix
    REGEX;

    /** @var string $iso8601Regex ISO 8601 regex */
    private static string $iso8601Regex =
    '/^(-|)?P([0-9]+Y|)?([0-9]+M|)?([0-9]+D|)?T?([0-9]+H|)?([0-9]+M|)?([0-9]+S|)?$/';

    /** @var string $iso8601PartRegex ISO 8601 part regex */
    private static string $iso8601PartRegex = '/((?!([0-9]|-)).)*/';

    /**
     * Convert ISO 8601 video duration to hours, minutes and seconds
     *
     * @param string $duration ISO 8601 duration
     * @param boolean $allowNegative Allow a negative duration
     * @return boolean|string
     */
    public static function videoDuration($duration, $allowNegative = true)
    {
        $matches = array();

        if (preg_match(self::$iso8601Regex, $duration, $matches)) {
            foreach ($matches as &$match) {
                $match = preg_replace(self::$iso8601PartRegex, '', $match);
            }

            // Fetch min/plus symbol
            $result['symbol'] = ($matches[1] == '-') ? $matches[1] : '+';

            // Fetch duration parts
            $m = ($allowNegative) ? $matches[1] : '';
            $result['year'] = intval($m . $matches[2]);
            $result['month'] = intval($m . $matches[3]);
            $result['day'] = intval($m . $matches[4]);
            $result['hour'] = intval($m . $matches[5]);
            $result['minute'] = intval($m . $matches[6]);
            $result['second'] = intval($m . $matches[7]);

            if ($result['hour'] < 10) {
                $result['hour'] = 0 . $result['hour'];
            }

            if ($result['minute'] < 10) {
                $result['minute'] = 0 . $result['minute'];
            }

            if ($result['second'] < 10) {
                $result['second'] = 0 . $result['second'];
            }

            if ($result['day'] > 0) {
                $result = $result['day'] . ':' . $result['hour'] . ':' . $result['minute'] . ':' . $result['second'];
            } elseif ($result['hour'] > 0) {
                $result = $result['hour'] . ':' . $result['minute'] . ':' . $result['second'];
            } else {
                $result = $result['minute'] . ':' . $result['second'];
            }

            return $result;
        }

        return false;
    }

    /**
     * Convert file size from bytes into a readable format (GB, MB, KB)
     *
     * @param int $bytes File size in bytes
     * @return string $string Formatted file size
     */
    public static function fileSize(int $bytes = 0)
    {
        if ($bytes >= self::$byteCountGB) { // 1GB or greater
            $string = round($bytes / self::$byteCountGB, self::$numDecimalPlaces) . ' GB';
        } elseif ($bytes >= self::$byteCountMB) { // 1MB or greater
            $string = round($bytes / self::$byteCountMB, self::$numDecimalPlaces) . ' MB';
        } elseif ($bytes >= self::$byteCountKB) { // 1KB or greater
            $string = round($bytes / self::$byteCountKB, self::$numDecimalPlaces) . ' KB';
        } elseif ($bytes > self::$minByteCount) { // Greater than 1 byte
            $string = $bytes . ' bytes';
        } elseif ($bytes === self::$minByteCount) { // 1 byte
            $string = $bytes . ' byte';
        } else { // 0 bytes
            $string = '0 bytes';
        }

        return $string;
    }

    /**
     * Convert Unix timestamp into a readable format
     *
     * @param int $timestamp Unix timestamp
     * @param string $format DateTime format
     * @param string $timezone DateTime timezone
     * @return string
     */
    public static function unixTime(int $timestamp, string $format, string $timezone): string
    {
        $dt = new DateTime();
        $dt->setTimestamp($timestamp);
        $dt->setTimezone(new DateTimeZone($timezone));

        return $dt->format($format);
    }

    /**
     * Convert URLs to HTML links
     *
     * @param string $string URL
     * @return string
     */
    public static function urls(string $string): string
    {
        return (string) preg_replace(
            self::$urlRegex,
            '<a href="$1" target="_blank">$1</a>',
            $string
        );
    }

    /**
     * Convert newlines (\r, \r\n & \n) to HTML br tag
     *
     * @param string $string
     * @return string
     */
    public static function newlines(string $string): string
    {
        return str_replace(array("\r\n", "\r", "\n"), '<br />', $string);
    }
}
