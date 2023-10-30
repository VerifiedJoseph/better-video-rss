<?php

namespace App;

use CurlHandle;

class Curl
{
    /** @var CurlHandle Curl class instance */
    private CurlHandle $curl;

    /** @var int $errorCode error code */
    private int $errorCode = 0;

    /** @var ?string $errorMessage error message */
    private ?string $errorMessage = null;

    /** @var string $response HTTP response */
    private string $response = '';

    /** @var string $useragent HTTP user agent */
    private string $useragent = '';

    /** @var array<int, string> $headers HTTP request headers */
    private array $headers = [];

    /** @var ?int $statusCode HTTP  status code */
    private ?int $statusCode = null;

    public function __construct()
    {
        $this->curl = curl_init();
    }

    /**
     * Make a GET request
     *
     * @param string $url Request URL
     */
    public function get(string $url): void
    {
        curl_setopt_array($this->curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => $this->useragent,
            CURLOPT_HTTPHEADER => $this->headers
        ]);

        $response = curl_exec($this->curl);

        if (is_bool($response) === false) {
            $this->response = $response;
        }

        $this->statusCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $this->errorCode = curl_errno($this->curl);
        $this->errorMessage = curl_error($this->curl);
    }

    /**
     * Set HTTP user agent
     *
     * @param string $useragent HTTP user agent
     */
    public function setUserAgent(string $useragent): void
    {
        $this->useragent = $useragent;
    }

    /**
     * Set a HTTP header
     *
     * @param string $key Header key
     * @param string $value Header value
     */
    public function setHeader(string $key, string $value): void
    {
        $this->headers[] = sprintf('%s:%s', $key, $value);
    }

    /**
     * Get CURL error code
     *
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * Get CURL error message
     *
     * @return null|string
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Get HTTP status code
     *
     * @return null|int
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * Get HTTP response body
     *
     * @return string
     */
    public function getResponse(): string
    {
        return $this->response;
    }
}
