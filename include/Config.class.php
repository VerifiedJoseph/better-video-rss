<?php

class Config {

	private static $keys = array(
		'YouTubeApiKey' => 'YOUTUBE_API_KEY',
		'YouTubeEmbedPrivacy' => 'YOUTUBE_EMBED_PRIVACY',
		'RawApiErrors' => 'RAW_API_ERRORS',
		'Timezone' => 'TIMEZONE',
		'DateFormat' => 'DATE_FORMAT',
		'ResultsLimit' => 'RESULTS_LIMIT',
		'CacheDirectory' => 'CACHE_DIR',
		'DisableCache' => 'DISABLE_CACHE',
		'EnableCacheViewer' => 'ENABLE_CACHE_VIEWER'
	);

	/**
	 * Check PHP version and loaded extensions
	 *
	 * @throws Exception if PHP version is not supported or extension is not loaded
	 */
	public static function checkInstall() {

		if(version_compare(PHP_VERSION, '7.1.0') === -1) {
			throw new Exception('BetterYouTubeRss requires at least PHP version 7.1.0!');
		}

		if(!extension_loaded('curl')) {
			throw new Exception('Extension Error: cURL extension not loaded.');
		}

		if(!extension_loaded('openssl')) {
			throw new Exception('Extension Error: openssl extension not loaded.');
		}

		if(!extension_loaded('json')) {
			throw new Exception('Extension Error: JSON extension not loaded.');
		}
	}

	/**
	 * Check config constants
	 *
	 * @throws Exception if constant is invalid
	 */
	public static function checkConfig() {

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

		if ((constant('RESULTS_LIMIT') < 1) || (constant('RESULTS_LIMIT') > 50)) {
			throw new Exception('Config Error: Results limit option must be a integer between 1 and 50, inclusive. [RESULTS_LIMIT]');
		}

		if (empty(constant('CACHE_DIR'))) {
			throw new Exception('Config Error: Cache directory must be set. [CACHE_DIR]');
		}

		if (!is_dir(constant('CACHE_DIR')) && !mkdir(constant('CACHE_DIR'), 0700)) {
			throw new Exception('Config Error: Could not create cache directory. [CACHE_DIR]');
		}

		if (is_dir(constant('CACHE_DIR')) && !is_writable(constant('CACHE_DIR'))) {
			throw new Exception('Config Error: Cache directory is not writable. [CACHE_DIR]');
		}

		if (!is_bool(constant('DISABLE_CACHE'))) {
			throw new Exception('Config Error: Disable cache option must be a boolean. [DISABLE_CACHE]');
		}

		if (!is_bool(constant('ENABLE_CACHE_VIEWER'))) {
			throw new Exception('Config Error: Enable cache viewer option must be a boolean. [ENABLE_CACHE_VIEWER]');
		}
	}

	public static function get(string $key) {

		if (!isset(self::$keys[$key])) {
			throw new Exception('Invalid config key given:' . $key);
		}

		return constant(self::$keys[$key]);
	}
}
