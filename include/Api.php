<?php

use \Curl\Curl;
use Configuration as Config;
use Helper\Url;

class Api {

	/** @var array $expectedStatusCodes Non-error HTTP status codes returned by the API */
	private array $expectedStatusCodes = array(200, 304);

	/**
	 * Get channel or playlist details
	 *
	 * @param string $type Feed type
	 * @param string $parameter Request parameter (channel or playlist id)
	 * @param string $etag Request ETag
	 * @return object
	 *
	 * @throws Exception if channel or playlist is not found.
	 */
	public function getDetails(string $type, string $parameter, string $etag) {
		$url = Url::getApi($type, $parameter);
		$response = $this->fetch($url, $etag);

		if ($response['statusCode'] === 200 && empty($response['data']->items)) {
			throw new Exception(ucfirst($type) . ' not found');
		}

		return $response['data'];
	}

	/**
	 * Get videos details
	 *
	 * @param string $parameter Request parameter
	 * @param string $etag Request ETag
	 * @return object
	 */
	public function getVideos(string $parameter, string $etag) {
		$url = Url::getApi('videos', $parameter);
		$response = $this->fetch($url);

		return $response['data'];
	}

	/**
	 * Search for a channel
	 *
	 * @param string $parameter Request parameter
	 * @return object
	 */
	public function searchChannels(string $parameter) {
		$url = Url::getApi('searchChannels', $parameter);
		$response = $this->fetch($url);

		return $response['data'];
	}

	/**
	 * Search for a playlist
	 *
	 * @param string $parameter Request parameter
	 * @return object
	 */
	public function searchPlaylists(string $parameter) {
		$url = Url::getApi('searchPlaylists', $parameter);
		$response = $this->fetch($url);

		return $response['data'];
	}

	/**
	 * Fetch API request
	 *
	 * @param string $url Request URL
	 * @param string $etag Request ETag
	 * @return array
	 *
	 * @throws Exception If a curl error has occurred.
	 */
	private function fetch(string $url, string $etag = '') {
		$curl = new Curl();

		// Set if-Match header
		if (empty($etag) === false) {
			$curl->setHeader('If-None-Match', $etag);
		}

		$curl->setUserAgent(Config::getUserAgent());
		$curl->get($url);

		if ($curl->getCurlErrorCode() !== 0) {
			throw new Exception('Error: ' . $curl->getCurlErrorCode() . ': ' . $curl->getErrorMessage());
		}

		$response = array();
		$response['data'] = $curl->getResponse();
		$response['statusCode'] = $curl->getHttpStatusCode();

		if (in_array($curl->getHttpStatusCode(), $this->expectedStatusCodes) === false) {
			$this->handleError($curl->getResponse());
		}

		return $response;
	}

	/**
	 * Handle API errors
	 *
	 * @param object $response API response
	 * @throws Exception
	 */
	private function handleError($response) {
		$error = $response->error->errors[0];

		if (Config::get('RAW_API_ERRORS') === true) {
			$raw = json_encode($response->error, JSON_PRETTY_PRINT);

			throw new Exception(
				"API Error \n"
				. "\n" . $raw
			);
		}

		throw new Exception(
			'API Error: ' . $error->message . ' (' . $error->reason . ')'
		);
	}
}
