<?php

use \Curl\Curl;
use Configuration as Config;
use Helper\Url;

class Api {
	/**
	 * Get channel details
	 *
	 * @param string $parameter Request parameter
	 * @param string $etag Request ETag
	 * @return object
	 */
	public function getChannel(string $parameter, string $etag) {
		$url = Url::getApi('channel', $parameter);
		return $this->fetch($url, $etag);
	}

	/**
	 * Get playlist details
	 *
	 * @param string $parameter Request parameter
	 * @param string $etag Request ETag
	 * @return object
	 */
	public function getPlaylist(string $parameter, string $etag) {
		$url = Url::getApi('playlist', $parameter);
		return $this->fetch($url, $etag);
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
		return $this->fetch($url, $etag);
	}

	/**
	 * Search for a channel
	 *
	 * @param string $parameter Request parameter
	 * @return object|array
	 */
	public function searchChannels(string $parameter) {
		$url = Url::getApi('searchChannels', $parameter);
		return $this->fetch($url);
	}

	/**
	 * Search for a playlist
	 *
	 * @param string $parameter Request parameter
	 * @return object|array
	 */
	public function searchPlaylists(string $parameter) {
		$url = Url::getApi('searchPlaylists', $parameter);
		return $this->fetch($url);
	}

	/**
	 * Fetch API request
	 *
	 * @param string $url Request URL
	 * @param string $etag Request ETag
	 * @return object|array
	 *
	 * @throws Exception If a curl error has occurred.
	 */
	private function fetch(string $url, string $etag = '') {
		$curl = new Curl();

		// Set if-Match header
		if (empty($etag) === false) {
			$curl->setHeader('If-None-Match', $etag);
		}

		$curl->get($url);

		if ($curl->getCurlErrorCode() !== 0) {
			throw new Exception('Error: ' . $curl->getCurlErrorCode() . ': ' . $curl->getErrorMessage());
		}

		if ($curl->getHttpStatusCode() === 304) {
			return array();

		} elseif ($curl->getHttpStatusCode() !== 200) {
			$this->handleError($curl->getResponse());
		}

		return $curl->getResponse();
	}

	/**
	 * Handle API errors
	 *
	 * @param object $response API response
	 * @throws Exception
	 */
	private function handleError($response) {
		$error = $response->error->errors[0];

		if (config::get('RAW_API_ERRORS') === true) {
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
