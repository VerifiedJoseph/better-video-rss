<?php

use \Curl\Curl;
use Configuration as Config;
use Helper\Url;

class Fetch {
	/**
	 * Fetch YouTube RSS feed
	 *
	 * @param string $feedId Feed id (channel or playlist ID)
	 * @param string $feedType Feed type (channel or playlist)
	 *
	 * @return object Response from Curl
	 *
	 * @throws Exception if a cURL error occurred.
	 * @throws Exception if RSS feed fetch failed.
	 */
	public function feed(string $feedId, string $feedType) {
		$url = Url::getRssFeed($feedType, $feedId);

		$curl = new Curl();
		$curl->setUserAgent(Config::getUserAgent());
		$curl->get($url);

		if ($curl->getCurlErrorCode() !== 0) {
			throw new Exception('Error: ' . $curl->getCurlErrorCode() . ': ' . $curl->errorMessage);
		}

		if ($curl->getHttpStatusCode() !== 200) {
			throw new Exception('Failed to fetch: ' . $url . ' (' . $curl->getHttpStatusCode() . ')');
		}

		return $curl->getResponse();
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
	public function thumbnail(string $url) {
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
