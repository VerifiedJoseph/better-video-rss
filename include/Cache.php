<?php

namespace App;

use App\Configuration as Config;
use App\Helper\File;

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

		if (Config::get('DISABLE_CACHE') === false) {
			$contents = File::read($this->path);
			$decoded = json_decode($contents, true);

			if (is_null($decoded) === false) {
				$this->data = $decoded;
			}
		}
	}

	/**
	 * Save cache data to disk
	 *
	 * @param array $data Feed date
	 */
	public function save(array $data = array()) {

		if (Config::get('DISABLE_CACHE') === false) {
			$data = json_encode($data);
			File::write($this->path, $data);
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
		$this->path = Config::getCacheDirPath() . DIRECTORY_SEPARATOR . $this->name . '.' . Config::getCacheFileExtension();
	}
}
