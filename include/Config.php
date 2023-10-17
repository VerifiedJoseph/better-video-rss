<?php

namespace App;

use App\Helper\Validate;
use App\Exception\ConfigException as ConfigException;
use Exception;

class Config
{
    /** @var string $minPhpVersion Minimum PHP version */
    private string $minPhpVersion = '8.0.0';

    /** @var array<int, string> $extensions Required PHP extensions */
    private array $extensions = ['curl', 'json', 'mbstring', 'simplexml'];

    /** @var int $mkdirMode mkdir() access mode */
    private int $mkdirMode = 0775;

    /** @var string $userAgent User agent used for Curl requests */
    private string $userAgent = 'BetterVideoRss (+https://github.com/VerifiedJoseph/BetterVideoRss)';

    /** @var array<int, string> $feedFormats Supported feed formats */
    private array $feedFormats = ['rss', 'html', 'json'];

    /** @var string $defaultFeedFormats Default feed format */
    private string $defaultFeedFormat = 'rss';

    /** @var string $cacheFileExtension Cache filename extension */
    private string $cacheFileExtension = 'cache';

    /** @var array<string, mixed> $defaults Default values for optional config parameters */
    private array $defaults = [
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
    private array $config = [];

    /**
     * Check PHP version and loaded extensions
     *
     * @throws Exception if PHP version is not supported
     * @throws Exception if a PHP extension is not loaded
     * @throws Exception if a api-endpoints.json is not found
     */
    public function checkInstall(): void
    {
        if (version_compare(PHP_VERSION, $this->minPhpVersion) === -1) {
            throw new Exception('BetterVideoRss requires at least PHP version ' . $this->minPhpVersion);
        }

        foreach ($this->extensions as $ext) {
            if (extension_loaded($ext) === false) {
                throw new Exception(sprintf('Extension Error: %s extension not loaded.', $ext));
            }
        }

        if (file_exists('include/api-endpoints.json') == false) {
            throw throw new Exception('File not found: include/api-endpoints.json');
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
    public function checkConfig(): void
    {
        $this->requireConfigFile();
        $this->setDefaults();

        if ($this->hasEnv('SELF_URL_PATH') === false || $this->getEnv('SELF_URL_PATH') === '') {
            throw new ConfigException('Self URL path must be set. [BVRSS_SELF_URL_PATH]');
        }

        if (Validate::selfUrlSlash((string) $this->getEnv('SELF_URL_PATH')) === false) {
            throw new ConfigException(sprintf(
                'Self URL must end with a forward slash. e.g: %s [BVRSS_SELF_URL_PATH]',
                $this->getEnv('SELF_URL_PATH')
            ));
        }

        if (Validate::selfUrlHttp((string) $this->getEnv('SELF_URL_PATH')) === false) {
            throw new ConfigException('Self URL must start with http:// or https:// [BVRSS_SELF_URL_PATH]');
        }

        $this->config['SELF_URL_PATH'] = $this->getEnv('SELF_URL_PATH');

        if ($this->hasEnv('YOUTUBE_API_KEY') === false || $this->getEnv('YOUTUBE_API_KEY') === '') {
            throw new ConfigException('YouTube API key must be set. [BVRSS_YOUTUBE_API_KEY]');
        }

        $this->config['YOUTUBE_API_KEY'] = $this->getEnv('YOUTUBE_API_KEY');

        if ($this->getEnv('RAW_API_ERRORS') === 'true') {
            $this->config['RAW_API_ERRORS'] = true;
        }

        if ($this->hasEnv('TIMEZONE') === true && $this->getEnv('TIMEZONE') !== '') {
            if (Validate::timezone((string) $this->getEnv('TIMEZONE')) === false) {
                throw new ConfigException(sprintf(
                    'Invalid timezone given (%s). See: https://www.php.net/manual/en/timezones.php [BVRSS_TIMEZONE]',
                    $this->getEnv('TIMEZONE')
                ));
            }

            $this->config['TIMEZONE'] = $this->getEnv('TIMEZONE');
        }

        if ($this->hasEnv('DATE_FORMAT') === true && $this->getEnv('DATE_FORMAT') !== '') {
            $this->config['DATE_FORMAT'] = $this->getEnv('DATE_FORMAT');
        }

        if ($this->hasEnv('TIME_FORMAT') === true && $this->getEnv('TIME_FORMAT') !== '') {
            $this->config['TIME_FORMAT'] = $this->getEnv('TIME_FORMAT');
        }

        if ($this->hasEnv('CACHE_DIR') === true && $this->getEnv('CACHE_DIR') !== '') {
            $this->config['CACHE_DIR'] = $this->getEnv('CACHE_DIR');
        }

        $cacheDir = $this->getCacheDirPath();

        if (is_dir($cacheDir) === false && mkdir($cacheDir, $this->mkdirMode) === false) {
            throw new ConfigException('Could not create cache directory [BVRSS_CACHE_DIR]');
        }

        if (is_dir($cacheDir) && is_writable($cacheDir) === false) {
            throw new ConfigException('Cache directory is not writable. [BVRSS_CACHE_DIR]');
        }

        if ($this->getEnv('DISABLE_CACHE') === 'true') {
            $this->config['DISABLE_CACHE'] = true;
        }

        if ($this->getEnv('ENABLE_CACHE_VIEWER') === 'true') {
            $this->config['ENABLE_CACHE_VIEWER'] = true;
        }

        if ($this->getEnv('ENABLE_IMAGE_PROXY') === 'true') {
            $this->config['ENABLE_IMAGE_PROXY'] = true;
        }
    }

    /**
     * Returns config value
     *
     * @param string $key Config key
     * @return string|boolean
     * @throws Exception if config key is invalid
     */
    public function get(string $key)
    {
        if (array_key_exists($key, $this->config) === false) {
            throw new Exception('Invalid config key given: ' . $key);
        }

        return $this->config[$key];
    }

    /**
     * Returns version string
     * @return string
     */
    public function getVersion(): string
    {
        return (string) constant('VERSION');
    }

    /**
     * Returns user agent string
     *
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * Returns default feed format
     *
     * @return string
     */
    public function getDefaultFeedFormat(): string
    {
        return $this->defaultFeedFormat;
    }

    /**
     * Returns feed formats
     *
     * @return array<int, string>
     */
    public function getFeedFormats(): array
    {
        return $this->feedFormats;
    }

    /**
     * Returns cache filename extension
     *
     * @return string
     */
    public function getCacheFileExtension(): string
    {
        return $this->cacheFileExtension;
    }

    /**
     * Returns cache directory as an absolute path
     *
     * @return string
     */
    public function getCacheDirPath(): string
    {
        if (Validate::absolutePath((string) $this->get('CACHE_DIR')) === false) {
            return dirname(__DIR__) . DIRECTORY_SEPARATOR . $this->get('CACHE_DIR');
        }

        return (string) $this->get('CACHE_DIR');
    }

    /**
     * Include (require) config file
     */
    private function requireConfigFile(): void
    {
        if (file_exists('config.php') === true) {
            require 'config.php';
        }
    }

    /**
     * Set defaults as config values
     */
    private function setDefaults(): void
    {
        $this->config = $this->defaults;
    }

    /**
     * Check for an environment variable
     *
     * @param string $name Variable name excluding prefix
     */
    private function hasEnv(string $name): bool
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
    private function getEnv(string $name): mixed
    {
        return getenv('BVRSS_' . $name);
    }
}
