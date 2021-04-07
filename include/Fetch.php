<?php

use \Curl\Curl;
use Configuration as Config;
use Helper\Url;

class Fetch {

	/** @var string $feedId YouTube channel or playlist ID */
	private string $feedId = '';

	/** @var string $feedType Feed type (channel or playlist) */
	private string $feedType = 'channel';

	/**
	 * Constructor
	 *
	 * @param string $feedId Feed id (channel or playlist ID)
	 * @param string $feedType Feed type (channel or playlist)
	 */
	public function __construct(string $feedId, string $feedType) {
		$this->feedId = $feedId;
		$this->feedType = $feedType;
	}

	/**
	 * Returns response from cURL
	 *
	 * @return array|object
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * Fetch YouTube RSS feed
	 *
	 * @param string $id YouTube channel or playlist ID
	 * @throws Exception If a curl error has occurred.
	 */
	public function feed() {
		$url = Url::getRssFeed($this->feedType, $this->feedId);

		$curl = new Curl();
		$curl->get($url);
		$this->response = $curl->response;

		if ($curl->getCurlErrorCode() !== 0) {
			throw new Exception('Error: ' . $curl->getCurlErrorCode() . ': ' . $curl->errorMessage);
		}

		if ($curl->getHttpStatusCode() !== 200) {
			throw new Exception('Failed to fetch: ' . $url);
		}
	}
}
