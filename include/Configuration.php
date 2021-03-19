<?php

use Helper\Validate;

class Configuration {

	/** @var string $minPhpVersion Minimum PHP version */
	private static string $minPhpVersion = '7.4.0';

	/** @var int $mkdirMode mkdir() access mode */
	private static int $mkdirMode = 0775;

	/** @var array $endpoints YouTube endpoints */
	private static array $endpoints = array(
		'images' => 'https://i.ytimg.com/vi/',
		'nocookie' => 'https://www.youtube-nocookie.com/',
		'website' => 'https://www.youtube.com/',
		'feed' => 'https://www.youtube.com/feeds/videos.xml',
		'api' => 'https://www.googleapis.com/youtube/v3/'
	);

	/** @var array $feedFormats Supported feed formats */
	private static array $feedFormats = array('rss', 'html', 'json');

	/** @var array $defaultFeedFormats Default feed format */
	private static string $defaultFeedFormat = 'rss';

	/** @var string $cacheFileExtension Cache filename extension */
	private static string $cacheFileExtension = 'cache';

	/** @var string $defaults Default values for optional config parameters */
	private static array $defaults = array(
		'RAW_API_ERRORS' => false,
		'TIMEZONE' => 'UTC',
		'DATE_FORMAT' => 'F j, Y',
		'TIME_FORMAT' => 'H:i',
		'CACHE_DIR' => 'cache',
		'DISABLE_CACHE' => false,
		'ENABLE_CACHE_VIEWER' => false
	);

	/**
	 * Check PHP version and loaded extensions
	 *
	 * @throws Exception if PHP version is not supported
	 * @throws Exception if a PHP extension is not loaded
	 */
	public static function checkInstall() {

		if(version_compare(PHP_VERSION, self::$minPhpVersion) === -1) {
			throw new Exception('BetterVideoRss requires at least PHP version ' . self::$minPhpVersion . '!');
		}

		if(extension_loaded('curl') === false) {
			throw new Exception('Extension Error: cURL extension not loaded.');
		}

		if(extension_loaded('json') === false) {
			throw new Exception('Extension Error: JSON extension not loaded.');
		}

		if(extension_loaded('mbstring') === false) {
			throw new Exception('Extension Error: Mbstring extension not loaded.');
		}
	}

	/**
	 * Check config constants
	 *
	 * @throws Exception if config.php is not found
	 * @throws Exception if a constant is not defined
	 * @throws Exception if cache directory could not be created
	 * @throws Exception if cache directory is not writable
	 */
	public static function checkConfig() {

		if (file_exists('config.php') === false) {
			throw new Exception('Config Error: Configuration file not found. Use config.php-dist to create config.php and edit it.');
		}

		self::requireConfigFile();
		self::setDefaults();

		if (defined('SELF_URL_PATH') === false || empty(constant('SELF_URL_PATH')) === true) {
			throw new Exception('Config Error: Self URL path must be set. [SELF_URL_PATH]');
		}

		if (Validate::selfUrl(constant('SELF_URL_PATH')) === false) {
			throw new Exception('Config Error: Self URL must end with a forward slash. e.g: ' . constant('SELF_URL_PATH') . '/ [SELF_URL_PATH]');
		}

		if (defined('YOUTUBE_API_KEY') === false || empty(constant('YOUTUBE_API_KEY')) === true) {
			throw new Exception('Config Error: YouTube API key must be set. [YOUTUBE_API_KEY]');
		}

		if (is_bool(constant('RAW_API_ERRORS')) === false) {
			throw new Exception('Config Error: Raw API Errors option must be a boolean. [RAW_API_ERRORS]');
		}

		if (empty(constant('TIMEZONE')) === true) {
			throw new Exception('Config Error: Timezone must be set. [TIMEZONE]');
		}

		if (empty(constant('DATE_FORMAT')) === true) {
			throw new Exception('Config Error: Date format must be set. [DATE_FORMAT]');
		}

		if (empty(constant('TIME_FORMAT')) === true) {
			throw new Exception('Config Error: Time format must be set. [TIME_FORMAT]');
		}

		if (empty(constant('CACHE_DIR')) === true) {
			throw new Exception('Config Error: Cache directory must be set. [CACHE_DIR]');
		}

		$cacheDir = self::getCacheDirPath();

		if (is_dir($cacheDir) === false && mkdir($cacheDir, self::$mkdirMode) === false) {
			throw new Exception('Config Error: Could not create cache directory [CACHE_DIR]');
		}

		if (is_dir($cacheDir) && is_writable($cacheDir) === false) {
			throw new Exception('Config Error: Cache directory is not writable. [CACHE_DIR]');
		}

		if (is_bool(constant('DISABLE_CACHE')) === false) {
			throw new Exception('Config Error: Disable cache option must be a boolean. [DISABLE_CACHE]');
		}

		if (is_bool(constant('ENABLE_CACHE_VIEWER')) === false) {
			throw new Exception('Config Error: Enable cache viewer option must be a boolean. [ENABLE_CACHE_VIEWER]');
		}
	}

	/**
	 * Returns config value
	 *
	 * @param string $key Config key
	 * @return constant
	 * @throws Exception if config key is invalid
	 */
	public static function get(string $key) {

		if (defined($key) === false) {
			throw new Exception('Invalid config key given:' . $key);
		}

		return constant($key);
	}

	/**
	 * Returns YouTube endpoint URL
	 *
	 * @param string $name Endpoint name
	 * @return string
	 */
	public static function getEndpoint(string $name) {
		return self::$endpoints[$name];
	}

	/**
	 * Returns default feed format
	 *
	 * @return array
	 */
	public static function getDefaultFeedFormat() {
		return self::$defaultFeedFormat;
	}

	/**
	 * Returns feed formats
	 *
	 * @return array
	 */
	public static function getFeedFormats() {
		return self::$feedFormats;
	}

	/**
	 * Returns cache filename extension
	 *
	 * @return array
	 */
	public static function getCacheFileExtension() {
		return self::$cacheFileExtension;
	}

	/**
	 * Returns cache directory as an absolute path
	 *
	 * @return array
	 */
	public static function getCacheDirPath() {
		if(Validate::absolutePath(self::get('CACHE_DIR')) === false) {
			return self::get('ABSOLUTE_PATH') . DIRECTORY_SEPARATOR . self::get('CACHE_DIR');
		}

		return self::get('CACHE_DIR');
	}

	/**
	 * Include (require) config file
	 */
	private static function requireConfigFile() {
		require 'config.php';
	}

	/**
	 * Set defaults as constants if no user-supplied overrides given in config.php
	 *
	 * @return array
	 */
	private static function setDefaults() {
		foreach (self::$defaults as $param => $value) {

			if (defined($param) === false) {
				define($param, $value);
			}
		}
	}
}
