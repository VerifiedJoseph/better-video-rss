<?php

use \Curl\Curl;
use Configuration as Config;

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
	public function __construct(string $feedId, $feedType) {
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
		$url = Config::getEndpoint('feed') . '?' . $this->feedType . '_id=' . $this->feedId;

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

	/**
	 * Fetch data from API
	 *
	 * @param string $part Name part
	 * @param string $parameter Request parameter
	 * @param string $etag Request etag
	 * @throws Exception If channel or playlist was not found.
	 */
	public function api(string $part, string $parameter, string $etag) {
		$api = new Api();

		if ($part === 'details') {
			if ($this->feedType === 'channel') {
				$this->response = $api->getChannel($this->feedId, $etag);
			}

			if ($this->feedType === 'playlist') {
				$this->response = $api->getPlaylist($this->feedId, $etag);
			}

			if (empty($this->response->items)) {
				throw new Exception($this->feedType . ' not found.');
			}
		}

		if ($part === 'videos') {
			$this->response = $api->getVideos($parameter, $etag);
		}
	}
}
