<?php

use Configuration as Config;

class Cache {

	/** @var string $name Cache filename */
	private string $name = '';

	/** @var array $data Cache data */
	private array $data = array();

	/** @var string $path Cache file path */
	private string $path = '';

	/**
	 * Constructor
	 *
	 * @param string $feedId Feed ID
	 */
	public function __construct(string $feedId) {
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

				$decoded = json_decode($contents, true);

				if (!is_null($decoded)) {
					$this->data = $decoded;
				}
			}
		}
	}

	/**
	 * Save cache data to disk
	 *
	 * @param array $data Feed date
	 */
	public function save(array $data = array()) {
		$data = json_encode($data);
		$file = fopen($this->path, 'w');

		fwrite($file, $data);
		fclose($file);
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
