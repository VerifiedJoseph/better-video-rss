<?php

use \Curl\Curl;

class Api {

	/** @var string $endpoint YouTube API Endpoint */
	private $endpoint = 'https://www.googleapis.com/youtube/v3/';

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

	public function searchChannels(string $parameter) {
		
	}

	public function searchPlaylists(string $parameter) {
		
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
					. $parameter . '&fields=etag,items(id,snippet(title,description,channelTitle,tags,publishedAt,thumbnails(standard(url),maxres(url))),contentDetails(duration))';
				break;
		}

		return $this->endpoint . $parameters . '&prettyPrint=false&key=' . Config::get('YOUTUBE_API_KEY');
	}
	
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
				. 'Fetch: ' . $this->fetchType
				. "\n" . $raw
			);
		}

		throw new Exception(
			'API Error'
			. "\n Fetch:   " . $this->fetchType
			. "\n Message: " . $error->message
			. "\n Domain:  " . $error->domain
			. "\n Reason:  " . $error->reason
		);
	}
}
