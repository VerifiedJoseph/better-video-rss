<?php

namespace App;

use App\Config;
use App\Http\Request;
use App\Http\Response;
use App\Helper\Url;
use App\Helper\Json;
use Exception;

class Api
{
    /** @var Config Config class instance */
    private Config $config;

    /** @var Request Request class instance */
    private Request $request;

    /** @var array<int, int> $expectedStatusCodes Non-error HTTP status codes returned by the API */
    private array $expectedStatusCodes = [200, 304];

    /**
     * @param Config $config Config class instance
     * @param Request $request Request class instance
     */
    public function __construct(Config $config, Request $request)
    {
        $this->config = $config;
        $this->request = $request;
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
        $url = Url::getApi(
            $type,
            $parameter,
            $this->config->getApiKey()
        );

        $response = $this->fetch($url, $eTag);

        if ($response->getStatusCode() === 304) {
            return $response->getBody();
        }

        $body = Json::decode($response->getBody());

        if ($response->getStatusCode() === 200 && empty($body->items)) {
            throw new Exception(ucfirst($type) . ' not found');
        }

        return $body;
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

        return Json::decode($response->getBody());
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

        return Json::decode($response->getBody());
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

        return Json::decode($response->getBody());
    }

    /**
     * Make API request
     *
     * @param string $url Request URL
     * @param string $eTag Request ETag
     * @return Response
     *
     * @throws Exception If a cURL error occurred.
     */
    private function fetch(string $url, string $eTag = ''): Response
    {
        $headers = [];
        if (empty($eTag) === false) {
            $headers['If-None-Match'] = $eTag;
        }

        $response = $this->request->get($url, $headers);

        if (in_array($response->getStatusCode(), $this->expectedStatusCodes) === false) {
            $this->handleError($response->getBody());
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
            sprintf('Error: API returned code %s (%s: %s)', $code, $error->reason, strip_tags($error->message))
        );
    }
}
