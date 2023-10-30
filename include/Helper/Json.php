<?php

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
     * @param array<mixed> $data
     * @return string
     *
     * @throws Exception if array could not be encoded
     */
    public static function encode(array $data, $flags = 0): string
    {
        $flags = $flags | JSON_THROW_ON_ERROR;

        try {
            return json_encode($data, $flags);
        } catch (JsonException $err) {
            throw new Exception('JSON Error: ' . $err->getMessage());
        }
    }

    /**
     * Decode JSON
     *
     * @param string $json
     * @return stdClass|array
     *
     * @throws Exception if JSON could not be decoded
     */
    public static function decode(string $json, bool $associative = false): stdClass|array
    {
        try {
            return json_decode($json, $associative, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $err) {
            throw new Exception('JSON Error: ' . $err->getMessage());
        }
    }
}
