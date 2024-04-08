<?php

namespace App;

use Exception;

class Request
{
    /** @var string $useragent HTTP request user-agent */
    private string $useragent;

    /**
     * @param string $useragent HTTP request user-agent
     */
    public function __construct(string $useragent)
    {
        $this->useragent = $useragent;
    }

    /**
     * Make GET request
     * @param string $url Request URL
     * @param array<string, mixed> $headers HTTP headers
     * @return array<string, mixed> Response
     * @throws Exception if an error occurs
     */
    public function get(string $url, array $headers = []): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => $this->useragent
        ]);

        if ($headers !== []) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->formatHeaders($headers));
        }

        $body = curl_exec($ch);
        $errorCode = curl_errno($ch);
        $errorMessage = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($errorCode !== 0) {
            throw new Exception(sprintf('Error: %s (%d)', $errorMessage, $errorCode));
        }

        return [
            'body' => $body,
            'statusCode' => $statusCode
        ];
    }

    /**
     * Convert headers into format required by cURL
     * @param array<string, mixed> $headers 
     * @return array<int, string>
     */
    private function formatHeaders(array $headers): array
    {
        $formattedHeaders = [];

        foreach ($headers as $key => $value) {
            $formattedHeaders[] = sprintf('%s:%s', $key, $value);
        }

        return $formattedHeaders;
    }
}
