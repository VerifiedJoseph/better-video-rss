<?php

use \Curl\Curl;

class Fetch {

	/** @var string $endpoint YouTube.com Endpoint */
	private $endpoint = 'https://www.youtube.com';

	/** @var string $apiEndpoint YouTube API Endpoint */
	private $apiEndpoint = 'https://www.googleapis.com/youtube/v3/';

	/** @var string $feedEndpoint YouTube RSS Feed Endpoint */
	private $feedEndpoint = 'https://www.youtube.com/feeds/videos.xml';

	/** @var array $data */
	private $data = array();

	/** @var string $fetchType Data fetch type */
	private $fetchType = '';

	/**
	 * Constructor
	 *
	 * @param array $data Cache data
	 */
	public function __construct(array $data) {
		$this->data = $data;
	}

	/**
	 * Return data
	 *
	 * @param string $part Name part
	 * @return array Returns fetch data
	 */
	public function getData(string $part) {

		if (isset($part)) {
			return $this->data[$part];
		}

		return $this->data;
	}

	/**
	 * Fetch and headle response for a part
	 *
	 * @param string $part Name part
	 */
	}

	/**
	 * Fetch YouTube RSS feed
	 *
	 * @param string $id YouTube channel or playlist ID
	 * @return object
	 * @throws Exception If a curl error has occurred.
	 */
	private function fetchFeed(string $id) {

		$url = $this->feedEndpoint . '?' . $this->data['details']['type'] . '_id=' . $id;

		$curl = new Curl();
		$curl->get($url);

		$statusCode = $curl->getHttpStatusCode();
		$errorCode = $curl->getCurlErrorCode();

		if ($errorCode !== 0) {
			throw new Exception('Error: ' . $curl->errorCode . ': ' . $curl->errorMessage);
		}

		if ($statusCode !== 200) {
			throw new Exception('Failed to fetch: ' . $url);
		}

		return $curl->response;
	}

	/**
	 * Fetch data from API
	 *
	 * @param string $etag HTTP etag
	 * @return array|object
	 * @throws Exception If a curl error has occurred.
	 */
	private function fetch(string $etag = '', string $parameter = '') {

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

		if ($errorCode !== 0) {
			throw new Exception('Error: ' . $curl->errorCode . ': ' . $curl->errorMessage);
		}

		if ($statusCode === 304) {
			return array();
		}

		if ($statusCode !== 200) {
			$this->handleApiError($curl->response);
		}

		return $curl->response;
	}

	/**
	 * Handle API response
	 *
	 * @param object $response API response
	 * @throws Exception If items array in $response is empty when fetch type is 'details'.
	 */
	private function handleResponse($response) {

		if ($this->fetchType === 'details') {

			if (empty($response->items)) {
				throw new Exception($this->data['details']['type'] . ' not found.');
			}

			$details = array();
			$details['etag'] = $response->etag;

			$details['title'] = $response->items['0']->snippet->title;
			$details['description'] = $response->items['0']->snippet->description;
			$details['published'] = strtotime($response->items['0']->snippet->publishedAt);

			if ($this->data['details']['type'] === 'channel') {
				$details['url'] = $this->endpoint . '/channel/' . $this->data['details']['id'];
				$details['playlist'] = $response->items['0']->contentDetails->relatedPlaylists->uploads;
			}

			if ($this->data['details']['type'] === 'playlist') {
				$details['url'] = $this->endpoint . '/playlist?list=' . $this->data['details']['id'];
				$details['playlist'] = $response->items['0']->id;
			}

			$details['thumbnail'] = $response->items['0']->snippet->thumbnails->default->url;

			$this->data['details'] = array_merge($this->data['details'], $details);
		}

		if ($this->fetchType === 'feed') {
			$feed = array();
			$feed['videos'] = array();

			foreach ($response->entry as $entry) {
				$feed['videos'][] = str_replace('yt:video:', '', $entry->id);
			}

			$this->data['playlist'] = array_merge($this->data['playlist'], $feed);
		}

		if ($this->fetchType === 'playlist') {
			$playlist = array();
			$playlist['etag'] = $response->etag;
			$playlist['videos'] = array();

			foreach ($response->items as $item) {
				$playlist['videos'][] = $item->contentDetails->videoId;
			}

			$this->data['playlist'] = array_merge($this->data['playlist'], $playlist);
		}

		if ($this->fetchType === 'videos') {
			$videos = array();
			$videos['etag'] = $response->etag;
			$videos['items'] = array();

			foreach ($response->items as $item) {
				$video = array();

				$video['id'] = $item->id;
				$video['url'] = $this->endpoint . '/watch?v=' . $item->id;
				$video['title'] = $item->snippet->title;
				$video['description'] = $item->snippet->description;
				$video['published'] = strtotime($item->snippet->publishedAt);
				$video['author'] = $item->snippet->channelTitle;
				$video['tags'] = array();

				if (isset($item->snippet->tags)) {
					$video['tags'] = $item->snippet->tags;
				}

				$video['duration'] = Helper::parseVideoDuration($item->contentDetails->duration);

				if (isset($item->snippet->thumbnails->maxres)) {
					$video['thumbnail'] = $item->snippet->thumbnails->maxres->url;

				} elseif (isset($item->snippet->thumbnails->standard)) {
					$video['thumbnail'] = $item->snippet->thumbnails->standard->url;

				} else {
					$video['thumbnail']  = 'https://i.ytimg.com/vi/' . $item->id . '/hqdefault.jpg';
				}

				$videos['items'][$video['id']] = $video;
			}

			if (!empty($videos['items'])) {
				$this->data['videos'] = array_merge($this->data['videos'], $videos);
			} else {
				$this->data['videos'] = $videos;
			}
		}
	}

	/**
	 * Build API URL for a fetch type
	 *
	 * @return string Returns API URL
	 */
	private function buildApiUrl(string $parameter = '') {

		if ($this->fetchType === 'details') {

			if ($this->data['details']['type'] === 'channel') {
				$parameters = 'channels?part=snippet,contentDetails&id='
					. $this->data['details']['id'] . '&fields=etag,items(snippet(title,description,publishedAt,thumbnails(default(url))),contentDetails(relatedPlaylists(uploads)))';
			}

			if ($this->data['details']['type'] === 'playlist') {
				$parameters = 'playlists?part=snippet,contentDetails&id='
					. $this->data['details']['id'] . '&fields=etag,items(id,snippet(title,description,publishedAt,thumbnails(default(url))))';
			}
		}

		if ($this->fetchType === 'playlist') {
			$parameters = 'playlistItems?part=contentDetails&maxResults=' . Config::get('RESULTS_LIMIT') . '&playlistId='
				. $this->data['details']['playlist'] . '&fields=etag,items(contentDetails(videoId))';
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
