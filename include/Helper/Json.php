<?php

declare(strict_types=1);

namespace App\Helper;

use stdClass;
use JsonException;
use Exception;

/**
 * Class for encoding and decoding JSON
 */
final class Json
{
    /**
     * Encode JSON
     *
     * @param mixed $data
     * @param int $flags Bitmask of JSON options
     * @return string
     *
     * @throws Exception if array could not be encoded
     */
    public static function encode(mixed $data, int $flags = 0): string
    {
        $flags = $flags | JSON_THROW_ON_ERROR;

        try {
            return json_encode($data, $flags | JSON_THROW_ON_ERROR);
        } catch (JsonException $err) {
            throw new Exception('JSON Error: ' . $err->getMessage());
        }
    }

    /**
     * Decode JSON
     *
     * @param string $json
     * @return stdClass
     *
     * @throws Exception if JSON could not be decoded
     */
    public static function decode(string $json): stdClass
    {
        try {
            return json_decode($json, false, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $err) {
            throw new Exception('JSON Error: ' . $err->getMessage());
        }
    }

    /**
     * Decode JSON to an associative array
     *
     * @param string $json
     * @return array<mixed>
     *
     * @throws Exception if JSON could not be decoded
     */
    public static function decodeToArray(string $json): array
    {
        try {
            return json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $err) {
            throw new Exception('JSON Error: ' . $err->getMessage());
        }
    }
}
