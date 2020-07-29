<?php

use \Curl\Curl;
use Configuration as Config;

class Api {

	/** @var string $endpoint YouTube API Endpoint */
	private string $endpoint = 'https://www.googleapis.com/youtube/v3/';

	/**
	 * Get channel details
	 *
	 * @param string $parameter Request parameter
	 * @param string $etag Request etag
	 * @return object
	 */
	public function getChannel(string $parameter, string $etag) {
		$url = $this->buildUrl('channel', $parameter);
		return $this->fetch($url, $etag);
	}

	/**
	 * Get playlist details
	 *
	 * @param string $parameter Request parameter
	 * @param string $etag Request etag
	 * @return object
	 */
	public function getPlaylist(string $parameter, string $etag) {
		$url = $this->buildUrl('playlist', $parameter);
		return $this->fetch($url, $etag);
	}

	/**
	 * Get videos details
	 *
	 * @param string $parameter Request parameter
	 * @param string $etag Request etag
	 * @return object
	 */
	public function getVideos(string $parameter, string $etag) {
		$url = $this->buildUrl('videos', $parameter);
		return $this->fetch($url, $etag);
	}

	/**
	 * Search for a channel
	 *
	 * @param string $parameter Request parameter
	 * @return object|array
	 */
	public function searchChannels(string $parameter) {
		$url = $this->buildUrl('searchChannels', $parameter);
		return $this->fetch($url);
	}

	/**
	 * Search for a playlist
	 *
	 * @param string $parameter Request parameter
	 * @return object|array
	 */
	public function searchPlaylists(string $parameter) {
		$url = $this->buildUrl('searchPlaylists', $parameter);
		return $this->fetch($url);
	}

	/**
	 * Build URL
	 *
	 * @param string $type Type
	 * @param string $parameter parameter
	 * @return string Returns API URL
	 */
	private function buildUrl(string $type, string $parameter = '') {
		switch ($type) {
			case 'channel':
				$parameters = 'channels?part=snippet,contentDetails&id='
					. $parameter . '&fields=etag,items(snippet(title,description,publishedAt,thumbnails(default(url))),contentDetails(relatedPlaylists(uploads)))';
				break;
			case 'playlist':
				$parameters = 'playlists?part=snippet,contentDetails&id='
					. $parameter . '&fields=etag,items(id,snippet(title,description,publishedAt,thumbnails(default(url))))';
				break;
			case 'videos':
				$parameters = 'videos?part=id,snippet,contentDetails&id='
					. $parameter . '&fields=etag,items(id,snippet(description,tags,thumbnails(standard(url),maxres(url))),contentDetails(duration))';
				break;
			case 'searchChannels':
				$parameters = 'search?part=snippet&fields=items(snippet(channelId))&q='
					. urlencode($parameter) . '&type=channel&maxResults=1';
				break;
			case 'searchPlaylists':
				$parameters = 'search?part=snippet&fields=items(id(playlistId))&q='
					. urlencode($parameter) . '&type=playlist&maxResults=1';
				break;
		}

		return $this->endpoint . $parameters . '&prettyPrint=false&key=' . Config::get('YOUTUBE_API_KEY');
	}

	/**
	 * Fetch API request
	 *
	 * @param string $url
	 * @param string $etag Request etag
	 * @return object|array
	 */
	private function fetch(string $url, string $etag = '') {
		$curl = new Curl();

		// Set if-Match header
		if (empty($etag) === false) {
			$curl->setHeader('If-None-Match', $etag);
		}

		$curl->get($url);

		if ($curl->getCurlErrorCode() !== 0) {
			throw new Exception('Error: ' . $curl->errorCode . ': ' . $curl->errorMessage);
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
			'API Error :' . $error->message . ' (' . $error->reason . ')'
		);
	}
}
