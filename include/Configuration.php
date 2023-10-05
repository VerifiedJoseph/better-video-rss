<?php

namespace App;

use App\Helper\Validate;
use App\Helper\File;
use App\Exception\ConfigurationException as ConfigException;
use Exception;

class Configuration {

	/** @var string $minPhpVersion Minimum PHP version */
	private static string $minPhpVersion = '8.0.0';

	/** @var int $mkdirMode mkdir() access mode */
	private static int $mkdirMode = 0775;

	/** @var array $userAgent User agent used for Curl requests */
	private static string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; rv:91.0) Gecko/20100101 Firefox/91.0 (BetterVideoRss https://github.com/VerifiedJoseph/BetterVideoRss)';

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
		'ENABLE_CACHE_VIEWER' => false,
		'ENABLE_IMAGE_PROXY' => false
	);

	/** @var array $config Loaded config parameters */
	private static array $config = array();

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
	 * @throws ConfigException if self URL path environment variable is not set.
	 * @throws ConfigException if self URL path does not end with a forward slash.
	 * @throws ConfigException if self URL path does not start with http:// or https://.
	 * @throws ConfigException if YouTube API key environment variable is not set.
	 * @throws ConfigException if timezone environment variable is invalid.
	 * @throws ConfigException if cache directory could not be created.
	 * @throws ConfigException if cache directory is not writable.
	 */
	public static function checkConfig() {
		self::requireConfigFile();
		self::setDefaults();

		if (self::getEnVariable('SELF_URL_PATH') === false) {
			throw new ConfigException('Self URL path must be set. [BVRSS_SELF_URL_PATH]');
		}

		if (Validate::selfUrlSlash(self::getEnVariable('SELF_URL_PATH')) === false) {
			throw new ConfigException('Self URL must end with a forward slash. e.g: ' . self::getEnVariable('SELF_URL_PATH') . '/ [BVRSS_SELF_URL_PATH]');
		}

		if (Validate::selfUrlHttp(self::getEnVariable('SELF_URL_PATH')) === false) {
			throw new ConfigException('Self URL must start with http:// or https:// [BVRSS_SELF_URL_PATH]');
		}

		self::$config['SELF_URL_PATH'] = self::getEnVariable('SELF_URL_PATH');

		if (self::getEnVariable('YOUTUBE_API_KEY') === false) {
			throw new ConfigException('YouTube API key must be set. [BVRSS_YOUTUBE_API_KEY]');
		}

		self::$config['YOUTUBE_API_KEY'] = self::getEnVariable('YOUTUBE_API_KEY');

		if (filter_var(self::getEnVariable('RAW_API_ERRORS'), FILTER_VALIDATE_BOOLEAN) === true) {
			self::$config['RAW_API_ERRORS'] = true;
		}

		if (self::getEnVariable('TIMEZONE') !== false) {
			if (Validate::timezone(self::getEnVariable('TIMEZONE')) === false) {
				throw new ConfigException('Invalid timezone given (' . self::getEnVariable('TIMEZONE') . '). See: https://www.php.net/manual/en/timezones.php [BVRSS_TIMEZONE]');
			}

			self::$config['TIMEZONE'] = self::getEnVariable('TIMEZONE');
		}

		if (self::getEnVariable('DATE_FORMAT') !== false) {
			self::$config['DATE_FORMAT'] = self::getEnVariable('DATE_FORMAT');
		}

		if (self::getEnVariable('TIME_FORMAT') !== false) {
			self::$config['TIME_FORMAT'] = self::getEnVariable('TIME_FORMAT');
		}

		if (self::getEnVariable('CACHE_DIR') !== false) {
			self::$config['CACHE_DIR'] = self::getEnVariable('CACHE_DIR');
		}

		$cacheDir = self::getCacheDirPath();

		if (is_dir($cacheDir) === false && mkdir($cacheDir, self::$mkdirMode) === false) {
			throw new ConfigException('Could not create cache directory [BVRSS_CACHE_DIR]');
		}

		if (is_dir($cacheDir) && is_writable($cacheDir) === false) {
			throw new ConfigException('Cache directory is not writable. [BVRSS_CACHE_DIR]');
		}

		if (filter_var(self::getEnVariable('DISABLE_CACHE'), FILTER_VALIDATE_BOOLEAN) === true) {
			self::$config['DISABLE_CACHE'] = true;
		}

		if (filter_var(self::getEnVariable('ENABLE_CACHE_VIEWER'), FILTER_VALIDATE_BOOLEAN) === true) {
			self::$config['ENABLE_CACHE_VIEWER'] = true;
		}

		if (filter_var(self::getEnVariable('ENABLE_IMAGE_PROXY'), FILTER_VALIDATE_BOOLEAN) === true) {
			self::$config['ENABLE_IMAGE_PROXY'] = true;
		}
	}

	/**
	 * Returns config value
	 *
	 * @param string $key Config key
	 * @return string|boolean
	 * @throws Exception if config key is invalid
	 */
	public static function get(string $key) {

		if (array_key_exists($key, self::$config) === false) {
			throw new Exception('Invalid config key given: ' . $key);
		}

		return self::$config[$key];
	}

	/**
	 * Returns user agent string
	 *
	 * @return string
	 */
	public static function getUserAgent() {
		return self::$userAgent;
	}

	/**
	 * Returns default feed format
	 *
	 * @return string
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
	 * @return string
	 */
	public static function getCacheFileExtension() {
		return self::$cacheFileExtension;
	}

	/**
	 * Returns cache directory as an absolute path
	 *
	 * @return string
	 */
	public static function getCacheDirPath() {
		if(Validate::absolutePath(self::get('CACHE_DIR')) === false) {
			return dirname(__DIR__) . DIRECTORY_SEPARATOR . self::get('CACHE_DIR');
		}

		return self::get('CACHE_DIR');
	}

	/**
	 * Returns current git commit and branch of BetterVideoRss.
	 *
	 * @return string
	 */
	public static function getVersion() {
		$headPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'HEAD';

		if (file_exists($headPath) === true) {
			$headContents = File::read($headPath);

			$refPath = '.git/' . substr($headContents, 5, -1);
			$parts = explode('/', $refPath);

			if(isset($parts[3])) {
				$branch = $parts[3];

				if(file_exists($refPath)) {
					$refContents = File::read($refPath);

					return 'git.' . $branch . '.' . substr($refContents, 0, 7);
				}
			}
		}

		return 'unknown';
	}

	/**
	 * Include (require) config file
	 */
	private static function requireConfigFile() {
		if (file_exists('config.php') === true) {
			require 'config.php';
		}
	}

	/**
	 * Set defaults as config values
	 */
	private static function setDefaults() {
		self::$config = self::$defaults;
	}

	/**
	 * Returns value of environment variable
	 *
	 * Boolean false is returned if the variable does not exist or is empty.
	 *
	 * @param string $name Name of config parameter
	 * @return string|boolean
	 */
	private static function getEnVariable(string $name) {
		$varName = 'BVRSS_' . $name;
		$value = getenv($varName);

		if ($value !== false && empty($value) === false) {
			return getenv($varName);
		}

		return false;
	}
}
