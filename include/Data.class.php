<?php

class Data {

	/** @var object $cache Cache class object */
	private $cache;

	/** @var string $endpoint YouTube.com Endpoint */
	private $endpoint = 'https://www.youtube.com';

	/** @var array $parts Data part names */
	private $parts = array('details', 'feed', 'videos');

	/** @var array $data Data */
	private $data = array(
		'details' => array(),
		'feed' => array(
			'videos' => array(),
		),
		'videos' => array(
		)
	);

	/** @var array $expiresIn Number of days, hours or minutes that each part expires */
	private $expiresIn = array(
		'details' => '+10 days',
		'feed' => '+10 minutes',
		'videos' => '+10 minutes',
		'videoItems' => '+6 hours'
	);

	/** @var string $workingPart Current part being worked on */
	private $workingPart = '';

	/** @var string $dataUpdated Data update status */
	private $dataUpdated = false;

	/**
	 * Constructor
	 *
	 * @param string $feedId Feed id (channel or playlist ID)
	 * @param string $feedType Feed type (channel or playlist)
	 */
	public function __construct(string $feedId, string $feedType) {
		$this->data['details']['id'] = $feedId;
		$this->data['details']['type'] = $feedType;

		$this->cache = new Cache($feedId);

		// Load cache 
		$this->cache->load();

		// Use data from cache, if found
		$this->setData(
			$this->cache->getData()
		);
	}

	/**
	 * Destructor
	 */
	public function __destruct() {

		// Save data to cache file, if updated
		$this->cache->save(
			$this->getData(),
			$this->getUpdateStatus()
		);
	}

	/**
	 * Returns data
	 *
	 * @return array $data
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Sets data
	 *
	 * @param array $data
	 */
	public function setData(array $data) {

		if (!empty($data)) {
			$this->data = $data;
		}
	}

	/**
	 * Sets working part
	 *
	 * @param string $part
	 */
	public function setWorkingPart(string $part) {
		$this->workingPart = $part;
	}

	/**
	 * Returns data update status
	 *
	 * @return boolean
	 */
	public function getUpdateStatus() {
		return $this->dataUpdated;
	}

	/**
	 * Returns HTTP ETag for a part
	 *
	 * @return string
	 */
	public function getPartEtag() {

		if (isset($this->data[$this->workingPart]['etag'])) {
			return $this->data[$this->workingPart]['etag'];
		}

		return '';
	}

	/**
	 * Returns array of expired data parts
	 *
	 * @return array
	 */
	public function getExpiredParts() {
		$expiredParts = array();

		if (Config::get('DISABLE_CACHE') === true) {
			return $this->parts;
		}

		foreach ($this->data as $partName => $partData) {

			if (!isset($partData['expires'])) {
				$expiredParts[] = $partName;

			} elseif (time() >= $partData['expires']) {
				$expiredParts[] = $partName;
			}
		}

		return $expiredParts;
	}

	/**
	 * Returns playlist ID from details array
	 *
	 * @return string
	 */
	public function getPlaylistId() {
		return $this->data['details']['playlist'];
	}

	/**
	 * Returns expired video IDs as comma separated string
	 *
	 * @return string
	 */
	public function getExpiredVideos() {
		$ExpiredVideos = array();

		// Return all video IDs if videos array is empty or cache is disabled
		if (empty($this->data['videos']['items']) || Config::get('DISABLE_CACHE') === true) {
			return implode(',', $this->data['feed']['videos']);
		}

		foreach ($this->data['feed']['videos'] as $id) {			
			if (!isset($this->data['videos']['items'][$id]) || time() >= $this->data['videos']['items'][$id]['expires']) {
				$ExpiredVideos[] = $id;
			}
		}

		return implode(',', $ExpiredVideos);
	}

	/**
	 * Handle response from YouTube Data API
	 *
	 * @param object|array $response
	 */
	public function handleApiResponse($response) {
		$this->dataUpdated = true;

		if ($this->workingPart === 'details') {

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
			$details['fetched'] = strtotime('now');
			$details['expires'] = strtotime($this->expiresIn['details']);

			$this->data['details'] = array_merge($this->data['details'], $details);
		}

		if ($this->workingPart === 'playlist') {
			$playlist = array();
			$playlist['etag'] = $response->etag;
			$playlist['videos'] = array();

			foreach ($response->items as $item) {
				$playlist['videos'][] = $item->contentDetails->videoId;
			}

			$playlist['fetched'] = strtotime('now');
			$playlist['expires'] = strtotime($this->expiresIn['playlist']);

			$this->data['playlist'] = $playlist;
			$this->orderVideos();
		}

		if ($this->workingPart === 'videos' && !empty($response)) {
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

				$video['fetched'] = strtotime('now');
				$video['expires'] = strtotime($this->expiresIn['videoItems']);

				$this->data['videos']['items'][$video['id']] = $video;
				$this->orderVideos();
			}
		}
	}

	/**
	 * Handle response from YouTube RSS feed
	 *
	 * @param object $response
	 */
	public function handleRssResponse($response) {
		$this->dataUpdated = true;

		$feed = array();
		$feed['videos'] = array();

		foreach ($response->entry as $entry) {
			$feed['videos'][] = str_replace('yt:video:', '', $entry->id);
		}

		$feed['fetched'] = strtotime('now');
		$feed['expires'] = strtotime($this->expiresIn['feed']);

		$this->data['feed'] = $feed;
		$this->orderVideos();
	}

	/**
	 * Order video items by the playlist order
	 *
	 * Video items that do not have a video ID in the playlist array are removed.
	 */
	private function orderVideos() {
		$videos = array();

		foreach ($this->data['feed']['videos'] as $videoId) {

			if (isset($this->data['videos']['items'][$videoId])) {
				$videos[$videoId] = $this->data['videos']['items'][$videoId];
			}
		}

		$this->data['videos']['items'] = $videos;
	}
}
