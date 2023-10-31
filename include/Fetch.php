<?php

namespace App;

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
     * @return string Response body from Curl
     */
    public function feed(string $feedId, string $feedType)
    {
        return $this->fetch(
            Url::getRssFeed($feedType, $feedId)
        );
    }

    /**
     * Fetch YouTube thumbnail
     *
     * @param string $url YouTube thumbnail URL
     * @return string Response body from Curl
     */
    public function thumbnail(string $url)
    {
        return $this->fetch($url);
    }

    /**
     * Fetch URL
     *
     * @param string $url URL
     * @return string Response body from Curl
     *
     * @throws Exception if a cURL error occurred.
     * @throws Exception if image fetch failed.
     */
    private function fetch(string $url): string
    {
        $curl = new Curl();
        $curl->setUserAgent($this->config->getUserAgent());
        $curl->get($url);

        if ($curl->getErrorCode() !== 0) {
            throw new Exception('Error: ' . $curl->getErrorCode() . ': ' . $curl->getErrorMessage());
        }

        if ($curl->getStatusCode() !== 200) {
            throw new Exception('Failed to fetch: ' . $url . ' (' . $curl->getStatusCode() . ')');
        }

        return $curl->getResponse();
    }
}
