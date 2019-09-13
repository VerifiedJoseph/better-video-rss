<?php

class Cache {

	/** @var string $name Cache filename */
	private $name = '';

	/** @var array $data Cache data */
	private $data = array();

	private $expiresIn = array(
		'details' => '+10 days',
		'playlist' => '+10 minutes',
		'videos' => '+10 minutes',
		'videoItems' => '+6 hours'
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
	public function __construct(string $feedId, string $feedType) {
		$this->data['details']['id'] = $feedId;
		$this->data['details']['type'] = $feedType;
		$this->setName($feedId);
		$this->setPath();
	}

	/**
	 * Returns cache data
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

		if (file_exists($this->path) && Config::get('DISABLE_CACHE') === false) {
			$handle = fopen($this->path, 'r');

			if ($handle !== false) {
				$contents = fread($handle, filesize($this->path));
				fclose($handle);

				$this->data = json_decode($contents, true);
			}
		}
	}

	/**
	 * Update part of the cache data array
	 *
	 * @param string $part Cache part
	 * @param array $data Data
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
	 * Set cache name from feed ID
	 *
	 * @param string $feedId channel or playlist ID
	 */
	private function setName(string $feedId) {
		$this->name = hash('sha256', $feedId);
	}

	/**
	 * Set cache file path
	 */
	private function setPath() {
		$this->path = Config::get('ABSOLUTE_PATH') . DIRECTORY_SEPARATOR . 
			Config::get('CACHE_DIR') . DIRECTORY_SEPARATOR . $this->name . '.' . Config::get('CACHE_FILENAME_EXT');
	}
}
