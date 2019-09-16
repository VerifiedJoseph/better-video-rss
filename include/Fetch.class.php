<?php

use \Curl\Curl;

class Fetch {

	/** @var string $apiEndpoint YouTube API Endpoint */
	private $apiEndpoint = 'https://www.googleapis.com/youtube/v3/';

	/** @var string $feedEndpoint YouTube RSS Feed Endpoint */
	private $feedEndpoint = 'https://www.youtube.com/feeds/videos.xml';

	/** @var string $feedId YouTube channel or playlist ID */
	private $feedId = '';

	/** @var string $feedType Feed type (channel or playlist) */
	private $feedType = 'channel';
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

		$url = $this->feedEndpoint . '?' . $this->feedType . '_id=' . $this->feedId;

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

		$this->fetchType = $part;
		
		$curl = new Curl();

		// Set if-Match header
		if (!empty($etag)) {
			$curl->setHeader('If-None-Match', $etag);
		}

		$curl->get(
			$this->buildApiUrl($parameter)
		);

		$statusCode = $curl->getHttpStatusCode();
		$errorCode = $curl->getCurlErrorCode();
		$this->response = $curl->response;

		if ($errorCode !== 0) {
			throw new Exception('Error: ' . $curl->errorCode . ': ' . $curl->errorMessage);
		}

		if ($statusCode === 304) {
			$this->response = array();
		} else if ($statusCode !== 200) {
			$this->handleApiError(
				$this->response
			);
		}
	}

	/**
	 * Build API URL for a fetch type
	 *
	 * @return string Returns API URL
	 */
	private function buildApiUrl(string $parameter = '') {

		$parameters = '';
		
		if ($this->fetchType === 'details') {

			if ($this->feedType === 'channel') {
				$parameters = 'channels?part=snippet,contentDetails&id='
					. $this->feedId . '&fields=etag,items(snippet(title,description,publishedAt,thumbnails(default(url))),contentDetails(relatedPlaylists(uploads)))';
			}

			if ($this->feedType === 'playlist') {
				$parameters = 'playlists?part=snippet,contentDetails&id='
					. $this->feedId . '&fields=etag,items(id,snippet(title,description,publishedAt,thumbnails(default(url))))';
			}
		}

		if ($this->fetchType === 'playlist') {
			$parameters = 'playlistItems?part=contentDetails&maxResults=' . Config::get('RESULTS_LIMIT') . '&playlistId='
				. $parameter . '&fields=etag,items(contentDetails(videoId))';
		}

		if ($this->fetchType === 'videos') {
			$ids = $parameter;

			$parameters = 'videos?part=id,snippet,contentDetails&id='
				. $ids . '&fields=etag,items(id,snippet(title,description,channelTitle,tags,publishedAt,thumbnails(standard(url),maxres(url))),contentDetails(duration))';
		}

		return $this->apiEndpoint . $parameters . '&prettyPrint=false&key=' . Config::get('YOUTUBE_API_KEY');
	}

	/**
	 * Handle API errors
	 *
	 * @param object $response API response
	 * @throws Exception
	 */
	private function handleApiError($response) {
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
