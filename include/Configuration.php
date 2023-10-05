<?php

namespace App;

use App\Helper\Validate;
use App\Helper\File;
use App\Exception\ConfigurationException as ConfigException;
use Exception;

class Configuration
{
    /** @var string $minPhpVersion Minimum PHP version */
    private static string $minPhpVersion = '8.0.0';

    /** @var array<int, string> $extensions Required PHP extensions */
    private static array $extensions = ['curl', 'json', 'mbstring'];

    /** @var int $mkdirMode mkdir() access mode */
    private static int $mkdirMode = 0775;

    /** @var string $userAgent User agent used for Curl requests */
    private static string $userAgent = 'BetterVideoRss (+https://github.com/VerifiedJoseph/BetterVideoRss)';

    /** @var array<int, string> $feedFormats Supported feed formats */
    private static array $feedFormats = ['rss', 'html', 'json'];

    /** @var string $defaultFeedFormats Default feed format */
    private static string $defaultFeedFormat = 'rss';

    /** @var string $cacheFileExtension Cache filename extension */
    private static string $cacheFileExtension = 'cache';

    /** @var array<string, mixed> $defaults Default values for optional config parameters */
    private static array $defaults = [
        'RAW_API_ERRORS' => false,
        'TIMEZONE' => 'UTC',
        'DATE_FORMAT' => 'F j, Y',
        'TIME_FORMAT' => 'H:i',
        'CACHE_DIR' => 'cache',
        'DISABLE_CACHE' => false,
        'ENABLE_CACHE_VIEWER' => false,
        'ENABLE_IMAGE_PROXY' => false
    ];

    /** @var array<string, mixed> $config Loaded config parameters */
    private static array $config = [];

    /**
     * Check PHP version and loaded extensions
     *
     * @throws Exception if PHP version is not supported
     * @throws Exception if a PHP extension is not loaded
     */
    public static function checkInstall(): void
    {
        if (version_compare(PHP_VERSION, self::$minPhpVersion) === -1) {
            throw new Exception('BetterVideoRss requires at least PHP version ' . self::$minPhpVersion);
        }

        foreach (self::$extensions as $ext) {
            if (extension_loaded($ext) === false) {
                throw new Exception(sprintf('Extension Error: %s extension not loaded.', $ext));
            }
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
    public static function checkConfig(): void
    {
        self::requireConfigFile();
        self::setDefaults();

        if (self::hasEnv('SELF_URL_PATH') === false) {
            throw new ConfigException('Self URL path must be set. [BVRSS_SELF_URL_PATH]');
        }

        if (Validate::selfUrlSlash((string) self::getEnv('SELF_URL_PATH')) === false) {
            throw new ConfigException(sprintf(
                'Self URL must end with a forward slash. e.g: %s [BVRSS_SELF_URL_PATH]',
                self::getEnv('SELF_URL_PATH')
            ));
        }

        if (Validate::selfUrlHttp((string) self::getEnv('SELF_URL_PATH')) === false) {
            throw new ConfigException('Self URL must start with http:// or https:// [BVRSS_SELF_URL_PATH]');
        }

        self::$config['SELF_URL_PATH'] = self::getEnv('SELF_URL_PATH');

        if (self::hasEnv('YOUTUBE_API_KEY') === false) {
            throw new ConfigException('YouTube API key must be set. [BVRSS_YOUTUBE_API_KEY]');
        }

        self::$config['YOUTUBE_API_KEY'] = self::getEnv('YOUTUBE_API_KEY');

        if (filter_var(self::getEnv('RAW_API_ERRORS'), FILTER_VALIDATE_BOOLEAN) === true) {
            self::$config['RAW_API_ERRORS'] = true;
        }

        if (self::hasEnv('TIMEZONE') === true) {
            if (Validate::timezone((string) self::getEnv('TIMEZONE')) === false) {
                throw new ConfigException(sprintf(
                    'Invalid timezone given (%s). See: https://www.php.net/manual/en/timezones.php [BVRSS_TIMEZONE]',
                    self::getEnv('TIMEZONE')
                ));
            }

            self::$config['TIMEZONE'] = self::getEnv('TIMEZONE');
        }

        if (self::hasEnv('DATE_FORMAT') === true) {
            self::$config['DATE_FORMAT'] = self::getEnv('DATE_FORMAT');
        }

        if (self::hasEnv('TIME_FORMAT') === true) {
            self::$config['TIME_FORMAT'] = self::getEnv('TIME_FORMAT');
        }

        if (self::hasEnv('CACHE_DIR') === true) {
            self::$config['CACHE_DIR'] = self::getEnv('CACHE_DIR');
        }

        $cacheDir = self::getCacheDirPath();

        if (is_dir($cacheDir) === false && mkdir($cacheDir, self::$mkdirMode) === false) {
            throw new ConfigException('Could not create cache directory [BVRSS_CACHE_DIR]');
        }

        if (is_dir($cacheDir) && is_writable($cacheDir) === false) {
            throw new ConfigException('Cache directory is not writable. [BVRSS_CACHE_DIR]');
        }

        if (self::getEnv('DISABLE_CACHE') === 'true') {
            self::$config['DISABLE_CACHE'] = true;
        }

        if (self::getEnv('ENABLE_CACHE_VIEWER') === 'true') {
            self::$config['ENABLE_CACHE_VIEWER'] = true;
        }

        if (self::getEnv('ENABLE_IMAGE_PROXY') === 'true') {
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
    public static function get(string $key)
    {
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
    public static function getUserAgent(): string
    {
        return self::$userAgent;
    }

    /**
     * Returns default feed format
     *
     * @return string
     */
    public static function getDefaultFeedFormat(): string
    {
        return self::$defaultFeedFormat;
    }

    /**
     * Returns feed formats
     *
     * @return array<int, string>
     */
    public static function getFeedFormats(): array
    {
        return self::$feedFormats;
    }

    /**
     * Returns cache filename extension
     *
     * @return string
     */
    public static function getCacheFileExtension(): string
    {
        return self::$cacheFileExtension;
    }

    /**
     * Returns cache directory as an absolute path
     *
     * @return string
     */
    public static function getCacheDirPath(): string
    {
        if (Validate::absolutePath((string) self::get('CACHE_DIR')) === false) {
            return dirname(__DIR__) . DIRECTORY_SEPARATOR . self::get('CACHE_DIR');
        }

        return (string) self::get('CACHE_DIR');
    }

    /**
     * Returns current git commit and branch of BetterVideoRss.
     *
     * @return string
     */
    public static function getVersion(): string
    {
        $headPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'HEAD';

        if (file_exists($headPath) === true) {
            $headContents = File::read($headPath);

            $refPath = '.git/' . substr($headContents, 5, -1);
            $parts = explode('/', $refPath);

            if (isset($parts[3])) {
                $branch = $parts[3];

                if (file_exists($refPath)) {
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
    private static function requireConfigFile(): void
    {
        if (file_exists('config.php') === true) {
            require 'config.php';
        }
    }

    /**
     * Set defaults as config values
     */
    private static function setDefaults(): void
    {
        self::$config = self::$defaults;
    }

    /**
     * Check for an environment variable
     *
     * @param string $name Variable name excluding prefix
     */
    private static function hasEnv(string $name): bool
    {
        if (getenv('BVRSS_' . $name) === false) {
            return false;
        }

        return true;
    }

    /**
     * Get an environment variable
     *
     * @param string $name Variable name excluding prefix
     */
    private static function getEnv(string $name): mixed
    {
        return getenv('BVRSS_' . $name);
    }
}
