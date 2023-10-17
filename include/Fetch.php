<?php

namespace App;

use Curl\Curl;
use App\Config;
use App\Helper\Url;
use Exception;

class Fetch
{
    /** @var Config Config class instance */
    private Config $config;

    /**
     * @param Config $config Config class instance
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Fetch YouTube RSS feed
     *
     * @param string $feedId Feed id (channel or playlist ID)
     * @param string $feedType Feed type (channel or playlist)
     *
     * @return string Response from Curl
     *
     * @throws Exception if a cURL error occurred.
     * @throws Exception if RSS feed fetch failed.
     */
    public function feed(string $feedId, string $feedType)
    {
        $url = Url::getRssFeed($feedType, $feedId);

        $curl = new Curl();
        $curl->setUserAgent($this->config->getUserAgent());
        $curl->get($url);

        if ($curl->getCurlErrorCode() !== 0) {
            throw new Exception('Error: ' . $curl->getCurlErrorCode() . ': ' . $curl->errorMessage);
        }

        if ($curl->getHttpStatusCode() !== 200) {
            throw new Exception('Failed to fetch: ' . $url . ' (' . $curl->getHttpStatusCode() . ')');
        }

        return $curl->getRawResponse();
    }

    /**
     * Fetch YouTube thumbnail
     *
     * @param string $url YouTube thumbnail URL
     *
     * @return string Response from Curl
     *
     * @throws Exception if a cURL error occurred.
     * @throws Exception if image fetch failed.
     */
    public function thumbnail(string $url)
    {
        $curl = new Curl();
        $curl->get($url);

        if ($curl->getCurlErrorCode() !== 0) {
            throw new Exception('Error: ' . $curl->getCurlErrorCode() . ': ' . $curl->errorMessage);
        }

        if ($curl->getHttpStatusCode() !== 200) {
            throw new Exception('Failed to fetch: ' . $url . ' (' . $curl->getHttpStatusCode() . ')');
        }

        return $curl->getResponse();
    }
}
