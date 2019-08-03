<?php

class Cache {

	/** @var string $name Cache filename */
	private $name = '';
	private $data = array(
		'channel' => array(), 
		'playlist' => array(),
		'videos' => array()
	);

	private $expiresIn = array(
		'channel' => '+10 days',
		'playlist' => '+10 minutes',
		'videos' => '+10 minutes',
	);

	/** @var string $path Cache file path */
	private $path = '';

	/** @var string $folder Cache folder */
	private $folder = 'cache';

	/** @var string $path Cache file extension */
	private $fileExt = '.cache';

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
		
		if (!is_dir($this->folder)) {
			mkdir($this->folder, 0700);
		}

		$this->path = $this->folder . '/' . $this->name . $this->fileExt;

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
		$this->data[$part] = $data;
		$this->data[$part]['expires'] = strtotime($this->expiresIn[$part]);
	}

	/**
	 * Save cache data to disk
	 */
	public function save() {

		if (Config::get('DisableCache') === true) {
			return false;
		}

		$data = json_encode($this->data, true);
		$file = fopen($this->path, "w");

		fwrite($file, $data);
		fclose($file);
	}

	/**
	 * Set cache name from Channel ID
	 *
	 * @param string $channelId YouTube channel ID
	 */
	private function setName(string $channelId) {
		$this->name = hash('sha256', $channelId);
	}
}
