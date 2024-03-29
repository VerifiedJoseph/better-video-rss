<?php

namespace App;

use App\Config;
use App\Helper\Url;
use App\Helper\Json;
use Exception;

class Api
{
    /** @var Config Config class instance */
    private Config $config;

    /** @var array<int, int> $expectedStatusCodes Non-error HTTP status codes returned by the API */
    private array $expectedStatusCodes = [200, 304];

    /**
     * @param Config $config Config class instance
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get channel or playlist details
     *
     * @param string $type Feed type
     * @param string $parameter Request parameter (channel or playlist id)
     * @param string $eTag Request ETag
     * @return mixed
     *
     * @throws Exception if channel or playlist is not found.
     */
    public function getDetails(string $type, string $parameter, string $eTag)
    {
        $url = Url::getApi($type, $parameter, $this->config->getApiKey());
        $response = $this->fetch($url, $eTag);

        if ($response['statusCode'] === 200 && empty($response['data']->items)) {
            throw new Exception(ucfirst($type) . ' not found');
        }

        return $response['data'];
    }

    /**
     * Get videos details
     *
     * @param string $parameter Request parameter
     * @return object|string
     */
    public function getVideos(string $parameter)
    {
        $url = Url::getApi('videos', $parameter, $this->config->getApiKey());
        $response = $this->fetch($url);

        return $response['data'];
    }

    /**
     * Search for a channel
     *
     * @param string $parameter Request parameter
     * @return object
     */
    public function searchChannels(string $parameter): object
    {
        $url = Url::getApi('searchChannels', $parameter, $this->config->getApiKey());
        $response = $this->fetch($url);

        return $response['data'];
    }

    /**
     * Search for a playlist
     *
     * @param string $parameter Request parameter
     * @return object
     */
    public function searchPlaylists(string $parameter): object
    {
        $url = Url::getApi('searchPlaylists', $parameter, $this->config->getApiKey());
        $response = $this->fetch($url);

        return $response['data'];
    }

    /**
     * Make API request
     *
     * @param string $url Request URL
     * @param string $eTag Request ETag
     * @return array<string, mixed>
     *
     * @throws Exception If a cURL error occurred.
     */
    private function fetch(string $url, string $eTag = ''): array
    {
        $curl = new Curl();

        // Set if-Match header
        if (empty($eTag) === false) {
            $curl->setHeader('If-None-Match', $eTag);
        }

        $curl->setUserAgent($this->config->getUserAgent());
        $curl->get($url);

        if ($curl->getErrorCode() !== 0) {
            throw new Exception('Error: ' . $curl->getErrorCode() . ': ' . $curl->getErrorMessage());
        }

        $response = array();
        $response['data'] = '';

        if ($curl->getStatusCode() !== 304) {
            $response['data'] = Json::decode($curl->getResponse());
        }

        $response['statusCode'] = $curl->getStatusCode();

        if (in_array($curl->getStatusCode(), $this->expectedStatusCodes) === false) {
            $this->handleError($curl->getResponse());
        }

        return $response;
    }

    /**
     * Handle API errors
     *
     * @param string $response API response
     * @throws Exception
     */
    private function handleError(string $response): void
    {
        $data = Json::decode($response);
        $error = $data->error->errors[0];
        $code = $data->error->code;

        if ($this->config->getRawApiErrorStatus() === true) {
            throw new Exception(
                "API error: \n"
                . "\n" . $response
            );
        }

        throw new Exception(
            sprintf('Error: API returned code %s (%s: %s)', $code, $error->reason, $error->message)
        );
    }
}
