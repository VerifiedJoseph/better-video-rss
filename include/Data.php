<?php

use Configuration as Config;
use Helper\Convert;

class Data {

	/** @var object $cache Cache class object */
	private Cache $cache;

	/** @var string $endpoint YouTube.com Endpoint */
	private string $endpoint = 'https://www.youtube.com';

	/** @var array $parts Data part names */
	private array $parts = array('details', 'feed', 'videos');

	/** @var array $data Data */
	private array $data = array(
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
		'videos' => '+6 hours',
	);

	/** @var bool $dataUpdated Data update status */
	private bool $dataUpdated = false;

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
	 * @param string $part
	 * @return string
	 */
	public function getPartEtag(string $part) {

		if (isset($this->data[$part]['etag'])) {
			return $this->data[$part]['etag'];
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
		if (empty($this->data['videos']) || Config::get('DISABLE_CACHE') === true) {
			return implode(',', $this->data['feed']['videos']);
		}

		foreach ($this->data['feed']['videos'] as $id) {
			$key = array_search($id, array_column($this->data['videos'], 'id'));

			if (!isset($this->data['videos'][$key]['expires']) || time() >= $this->data['videos'][$key]['expires']) {
				$ExpiredVideos[] = $id;
			}
		}

		return implode(',', $ExpiredVideos);
	}

	/**
	 * Update channel or playlist details with response from YouTube Data API
	 *
	 * @param object|array $response
	 */
	public function updateDetails($response) {
		$this->dataUpdated = true;

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

	/**
	 * Update video details with response from YouTube Data API
	 *
	 * @param object|array $response
	 */
	public function updateVideos($response) {
		$this->dataUpdated = true;

		if (empty($response) === false) {
			$videos = $this->data['videos'];

			foreach ($response->items as $item) {
				$key = array_search($item->id, array_column($this->data['videos'], 'id'));
				$video = $this->data['videos'][$key];

				$video['description'] = $item->snippet->description;
				$video['duration'] = Convert::videoDuration($item->contentDetails->duration);
				$video['tags'] = array();

				if (isset($item->snippet->tags)) {
					$video['tags'] = $item->snippet->tags;
				}

				if (isset($item->snippet->thumbnails->maxres)) {
					$video['thumbnail'] = $item->snippet->thumbnails->maxres->url;

				} elseif (isset($item->snippet->thumbnails->standard)) {
					$video['thumbnail'] = $item->snippet->thumbnails->standard->url;

				} else {
					$video['thumbnail']  = 'https://i.ytimg.com/vi/' . $item->id . '/hqdefault.jpg';
				}

				$video['fetched'] = strtotime('now');
				$video['expires'] = strtotime($this->expiresIn['videos']);

				$videos[$key] = $video;
			}

			$this->data['videos'] = $videos;
		}
	}

	/**
	 * Handle response from YouTube RSS feed
	 *
	 * @param object $response
	 */
	public function updateFeed($response) {
		$this->dataUpdated = true;

		$feed = array(
			'videos' => array()
		);

		$videos = $this->data['videos'];

		foreach ($response->entry as $entry) {
			$id = str_replace('yt:video:', '', $entry->id);
			$key = array_search($id, array_column($videos, 'id'));

			$feed['videos'][] = $id;

			$video = array();
			$video['id'] = $id;
			$video['url'] = $this->endpoint . '/watch?v=' . $id;
			$video['title'] = (string)$entry->title;
			$video['author'] = (string)$entry->author->name;
			$video['published'] = strtotime((string)$entry->published);

			if ($key !== false) {
				$videos[$key] = array_merge($video, $videos[$key]);
			} else {
				$videos[] = $video;
			}
		}

		$feed['fetched'] = strtotime('now');
		$feed['expires'] = strtotime($this->expiresIn['feed']);

		$this->data['videos'] = $videos;
		$this->data['feed'] = $feed;
		$this->removeOldVideos();
	}

	/**
	 * Removes videos that are no longer in the RSS feed from YouTube
	 */
	private function removeOldVideos() {
		$videos = array();

		foreach ($this->data['feed']['videos'] as $videoId) {
			$key = array_search($videoId, array_column($this->data['videos'], 'id'));

			if ($key !== false) {
				$videos[] = $this->data['videos'][$key];
			}
		}

		$this->data['videos'] = $videos;
	}
}
