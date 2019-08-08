<?php

class Cache {

	/** @var string $name Cache filename */
	private $name = '';
	private $data = array(
		'channel' => array(),
		'playlist' => array(),
		'videos' => array(
			'items' => array()
		)
	);

	// TODO: Move to config file?
	private $expiresIn = array(
		'channel' => '+10 days',
		'playlist' => '+10 minutes',
		'videos' => '+10 minutes',
		'videoItems' => '+4 hours'
	);

	/** @var string $path Cache file path */
	private $path = '';

	/** @var boolean $cacheUpdated Cache update status */
	private $cacheUpdated = false;
	
	/**
	 * Constructor
	 *
	 * @param string $channelid YouTube Channel ID
	 */
	public function __construct(string $channelId) {
		$this->data['channel']['id'] = $channelId;
		$this->setName($channelId);
	}

	/**
	 * Return cache data
	 *
	 * @return array $data
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Load cache data from disk
	 */
	public function load() {

		if (Config::get('DisableCache') === true) {
			return false;
		}

		$this->path = Config::get('CacheDirectory') . '/' . $this->name . Config::get('CacheFilenameExt');

		if (file_exists($this->path)) {
			$handle = fopen($this->path, 'r');

			if ($handle !== false) {
				$contents = fread($handle, filesize($this->path));
				fclose($handle);

				$this->data = json_decode($contents, true);
			}
		}
	}

	/**
	 * Returns list of expired videos
	 *
	 * @return string
	 */
	public function getExpiredVideos() {

		$ExpiredVideos = array();

		if (empty($this->data['videos']['items']) || Config::get('DisableCache') === true) {
			return implode(',', $this->data['playlist']['videos']);
		}

		foreach ($this->data['playlist']['videos'] as $id) {

			if (!isset($this->data['videos']['items'][$id]) || time() > $this->data['videos']['items'][$id]['expires']) {
				$ExpiredVideos[] = $id;	
			}
		}

		return implode(',', $ExpiredVideos);
	}

	/**
	 * Check if cache part has expired
	 *
	 * @param string $part Name of cache part
	 */
	public function expired(string $part) {

		if (Config::get('DisableCache') === true) {
			return true;
		}

		if (!isset($this->data[$part]['expires'])) {
			return true;
		}

		if (time() > $this->data[$part]['expires']) {
			return true;
		}

		return false;
	}

	/**
	 * Update cache data array
	 *
	 * @param string $part Name of cache part
	 * @param array $data Data to update
	 */
	public function update(string $part, array $data = array()) {

		$this->cacheUpdated = true;

		if ($part === 'videos') {
			$data = $this->setVideoExpireDate($data);

			$this->data['videos']['items'] = array_merge(
				$this->data['videos']['items'],
				$data['items']
			);

			$this->orderVideos();

		} else {
			$this->data[$part] = $data;
		}

		$this->data[$part]['fetched'] = strtotime('now');
		$this->data[$part]['expires'] = strtotime($this->expiresIn[$part]);
	}

	/**
	 * Save cache data to disk
	 */
	public function save() {

		if ($this->cacheUpdated === true) {
			$data = json_encode($this->data);
			$file = fopen($this->path, 'w');

			fwrite($file, $data);
			fclose($file);
		}
	}

	/**
	 * Set cache name from Channel ID
	 *
	 * @param string $channelId YouTube channel ID
	 */
	private function setName(string $channelId) {
		$this->name = hash('sha256', $channelId);
	}
	
	/**
	 * Set cache expire date for each video
	 *
	 * @param array $data
	 * @return array $videos
	 */
	private function setVideoExpireDate(array $data) {
		
		$videos = array(
			'items' => array()
		);

		foreach ($data['items'] as $video) {
			$video['expires'] = strtotime($this->expiresIn['videoItems']);
			$videos['items'][$video['id']] = $video;
		}

		return $videos;
	}
	
	/**
	 * Order video items by the playlist order
	 *
	 * Video items that do not have a video ID in the playlist array are removed.
	 * 
	 * @param array $data
	 * @return array $videos
	 */
	private function orderVideos() {

		$videos = array();

		foreach ($this->data['playlist']['videos'] as $videoId) {
			$videos[$videoId] = $this->data['videos']['items'][$videoId];
		}

		$this->data['videos']['items'] = $videos;
	}
}
