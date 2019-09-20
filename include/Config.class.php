<?php

class Config {

	/** @var string $minPhpVersion Minimum PHP version */
	private static $minPhpVersion = '7.1.0';

	/** @var int $mkdirMode mkdir() access mode */
	private static $mkdirMode = 0775;

	/** @var int $minResults Minimum number of results allowed */
	private static $minResults = 1;

	/** @var int $maxResults Maximum number of results allowed */
	private static $maxResults = 50;

	/**
	 * Check PHP version and loaded extensions
	 *
	 * @throws Exception if PHP version is not supported
	 * @throws Exception if a PHP extension is not loaded
	 */
	public static function checkInstall() {

		if(version_compare(PHP_VERSION, self::$minPhpVersion) === -1) {
			throw new Exception('BetterYouTubeRss requires at least PHP version ' . self::$minPhpVersion . '!');
		}

		if(!extension_loaded('curl')) {
			throw new Exception('Extension Error: cURL extension not loaded.');
		}

		if(!extension_loaded('json')) {
			throw new Exception('Extension Error: JSON extension not loaded.');
		}

		if(!extension_loaded('mbstring')) {
			throw new Exception('Extension Error: Mbstring extension not loaded.');
		}
	}

	/**
	 * Check config constants
	 *
	 * @throws Exception if config.php is not found
	 * @throws Exception if a constant is invalid
	 * @throws Exception if cache directory could not be created
	 * @throws Exception if cache directory is not writable
	 */
	public static function checkConfig() {

		if (!file_exists('config.php')) {
			throw new Exception('Config Error: Configuration file not found. Use config.php-dist to create config.php and edit it.');
		}

		self::requireConfigFile();

		if (empty(constant('SELF_URL_PATH'))) {
			throw new Exception('Config Error: Self URL path must be set. [SELF_URL_PATH]');
		}

		if (empty(constant('YOUTUBE_API_KEY'))) {
			throw new Exception('Config Error: YouTube API key must be set. [YOUTUBE_API_KEY]');
		}

		if (!is_bool(constant('YOUTUBE_EMBED_PRIVACY'))) {
			throw new Exception('Config Error: YouTube Embed Privacy option must be a boolean. [YOUTUBE_EMBED_PRIVACY]');
		}

		if (!is_bool(constant('RAW_API_ERRORS'))) {
			throw new Exception('Config Error: Raw API Errors option must be a boolean. [RAW_API_ERRORS]');
		}

		if (empty(constant('TIMEZONE'))) {
			throw new Exception('Config Error: Timezone must be set. [TIMEZONE]');
		}

		if (empty(constant('DATE_FORMAT'))) {
			throw new Exception('Config Error: DateTime format must be set. [DATE_FORMAT]');
		}

		if (!is_int(constant('RESULTS_LIMIT'))) {
			throw new Exception('Config Error: Results limit option must be a integer. [RESULTS_LIMIT]');
		}

		if ((constant('RESULTS_LIMIT') < self::$minResults) || (constant('RESULTS_LIMIT') > self::$maxResults)) {
			throw new Exception('Config Error: Results limit option must be a integer between 1 and 50, inclusive. [RESULTS_LIMIT]');
		}

		if (empty(constant('CACHE_DIR'))) {
			throw new Exception('Config Error: Cache directory must be set. [CACHE_DIR]');
		}

		$cacheDir = constant('ABSOLUTE_PATH') . DIRECTORY_SEPARATOR . constant('CACHE_DIR');

		if (!is_dir($cacheDir) && !mkdir($cacheDir, self::$mkdirMode)) {
			throw new Exception('Config Error: Could not create cache directory. [CACHE_DIR]');
		}

		if (is_dir($cacheDir) && !is_writable($cacheDir)) {
			throw new Exception('Config Error: Cache directory is not writable. [CACHE_DIR]');
		}

		if (empty(constant('CACHE_FILENAME_EXT'))) {
			throw new Exception('Config Error: Cache filename extension must be set. [CACHE_FILENAME_EXT]');
		}

		if (!is_bool(constant('DISABLE_CACHE'))) {
			throw new Exception('Config Error: Disable cache option must be a boolean. [DISABLE_CACHE]');
		}

		if (!is_bool(constant('ENABLE_CACHE_VIEWER'))) {
			throw new Exception('Config Error: Enable cache viewer option must be a boolean. [ENABLE_CACHE_VIEWER]');
		}

		if (!is_bool(constant('ENABLE_HYBRID_MODE'))) {
			throw new Exception('Config Error: Enable hybrid mode option must be a boolean. [ENABLE_HYBRID_MODE]');
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

		if (!defined($key)) {
			throw new Exception('Invalid config key given:' . $key);
		}

		return constant($key);
	}

	/**
	 * Include (require) config file
	 */
	private function requireConfigFile() {
		require 'config.php';
	}
}
