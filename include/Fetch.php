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
	 * @param array $data Cache data
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
		$this->fetchType = 'feed';

		$url = Config::getEndpoint('feed') . '?' . $this->feedType . '_id=' . $this->feedId;

		$curl = new Curl();
		$curl->get($url);

		$statusCode = $curl->getHttpStatusCode();
		$errorCode = $curl->getCurlErrorCode();
		$this->response = $curl->response;

		if ($errorCode !== 0) {
			throw new Exception('Error: ' . $curl->errorCode . ': ' . $curl->errorMessage);
		}

		if ($statusCode !== 200) {
			throw new Exception('Failed to fetch: ' . $url);
		}
	}

	/**
	 * Fetch data from API
	 *
	 * @param string $part Name part
	 * @param string $parameter Request parameter
	 * @param string $etag Request etag
	 * @throws Exception If a curl error has occurred.
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
		}

		if ($part === 'videos') {
			$this->response = $api->getVideos($parameter, $etag);
		}
	}
}
