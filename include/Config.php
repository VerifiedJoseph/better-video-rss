<?php

namespace App;

use App\Helper\Validate;
use App\Exception\ConfigException;
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
    private string $userAgent = 'BetterVideoRss/%s (+https://github.com/VerifiedJoseph/BetterVideoRss)';

    /** @var array<int, string> $cspParts Content Security Policy header parts */
    private array $cspParts = [
        "default-src 'self'",
        "base-uri 'self'",
        "img-src 'self' i.ytimg.com yt3.ggpht.com",
        "script-src 'none'",
        "connect-src 'none'",
        "upgrade-insecure-requests"
    ];

    /** @var array<int, string> $feedFormats Supported feed formats */
    private array $feedFormats = ['rss', 'html', 'json'];

    /** @var string $defaultFeedFormats Default feed format */
    private string $defaultFeedFormat = 'rss';

    /** @var array<string, mixed> $config Config with default values for optional parameters */
    private array $config = [
        'RAW_API_ERRORS' => false,
        'TIMEZONE' => 'UTC',
        'DATE_FORMAT' => 'F j, Y',
        'TIME_FORMAT' => 'H:i',
        'CACHE_DIR' => 'cache',
        'DISABLE_CACHE' => false,
        'ENABLE_CACHE_VIEWER' => false,
        'ENABLE_IMAGE_PROXY' => false,
        'DISABLE_CSP' => false
    ];

    public function __construct()
    {
        $this->checkInstall();
    }

    /**
     * Check PHP version and loaded extensions
     *
     * @throws Exception if PHP version is not supported
     * @throws Exception if a PHP extension is not loaded
     * @throws Exception if a api-endpoints.json is not found
     */
    private function checkInstall(): void
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
     */
    public function checkConfig(): void
    {
        $this->requireConfigFile();

        if ($this->hasEnv('SELF_URL_PATH') === false || $this->getEnv('SELF_URL_PATH') === '') {
            throw new ConfigException('Self URL path must be set. [BVRSS_SELF_URL_PATH]');
        }

        if (Validate::selfUrlSlash((string) $this->getEnv('SELF_URL_PATH')) === false) {
            throw new ConfigException('Self URL must end with a forward slash. [BVRSS_SELF_URL_PATH]');
        }

        if (Validate::selfUrlHttp((string) $this->getEnv('SELF_URL_PATH')) === false) {
            throw new ConfigException('Self URL must start with http:// or https:// [BVRSS_SELF_URL_PATH]');
        }

        $this->config['SELF_URL_PATH'] = $this->getEnv('SELF_URL_PATH');

        if ($this->hasEnv('YOUTUBE_API_KEY') === false || $this->getEnv('YOUTUBE_API_KEY') === '') {
            throw new ConfigException('YouTube API key must be set. [BVRSS_YOUTUBE_API_KEY]');
        }

        $this->config['YOUTUBE_API_KEY'] = $this->getEnv('YOUTUBE_API_KEY');

        $this->checkOptional();
        $this->checkCache();
    }

    /**
     * Check cache parameters
     *
     * @throws ConfigException if cache directory could not be created.
     * @throws ConfigException if cache directory is not writable.
     */
    public function checkCache(): void
    {
        if ($this->getEnv('DISABLE_CACHE') === 'true') {
            $this->config['DISABLE_CACHE'] = true;
        }

        if ($this->getEnv('ENABLE_CACHE_VIEWER') === 'true') {
            $this->config['ENABLE_CACHE_VIEWER'] = true;
        }

        if ($this->config['DISABLE_CACHE'] === false) {
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
        }
    }

    /**
     * Check optional parameters excluding cache (see `checkCache()`)
     *
     * @throws ConfigException if timezone environment variable is invalid.
     */
    public function checkOptional(): void
    {
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

        if ($this->getEnv('ENABLE_IMAGE_PROXY') === 'true') {
            $this->config['ENABLE_IMAGE_PROXY'] = true;
        }

        if ($this->getEnv('DISABLE_CSP') === 'true') {
            $this->config['DISABLE_CSP'] = true;
        }
    }

    /**
     * Returns Content Security Policy header value
     * @return string
     */
    public function getCsp(): string
    {
        return implode('; ', $this->cspParts);
    }

    /**
     * Returns content security policy disabled status
     * @return boolean
     */
    public function getCspDisabledStatus(): bool
    {
        return $this->config['DISABLE_CSP'];
    }

    /**
     * Returns self URL
     * @return string
     */
    public function getSelfUrl(): string
    {
        return $this->config['SELF_URL_PATH'];
    }

    /**
     * Returns cache directory
     * @return string
     */
    public function getCacheDirectory(): string
    {
        return $this->config['CACHE_DIR'];
    }

    /**
     * Returns cache disabled status
     * @return boolean
     */
    public function getCacheDisableStatus(): bool
    {
        return $this->config['DISABLE_CACHE'];
    }

    /**
     * Returns cache viewer status
     * @return boolean
     */
    public function getCacheViewerStatus(): bool
    {
        return $this->config['ENABLE_CACHE_VIEWER'];
    }

    /**
     * Returns cache format version
     * @return int
     */
    public function getCacheFormatVersion(): int
    {
        return (int) constant('CACHE_FORMAT_VERSION');
    }

    /**
     * Returns image proxy status
     * @return boolean
     */
    public function getImageProxyStatus(): bool
    {
        return $this->config['ENABLE_IMAGE_PROXY'];
    }

    /**
     * Returns YouTube API key
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->config['YOUTUBE_API_KEY'];
    }

    /**
     * Returns timezone
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->config['TIMEZONE'];
    }

    /**
     * Returns date format
     * @return string
     */
    public function getDateFormat(): string
    {
        return $this->config['DATE_FORMAT'];
    }

    /**
     * Returns time format
     * @return string
     */
    public function getTimeFormat(): string
    {
        return $this->config['TIME_FORMAT'];
    }

    /**
     * Returns raw API error status
     * @return boolean
     */
    public function getRawApiErrorStatus(): bool
    {
        return $this->config['RAW_API_ERRORS'];
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
        return sprintf($this->userAgent, $this->getVersion());
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
     * Returns cache directory as an absolute path
     *
     * @return string
     */
    public function getCacheDirPath(): string
    {
        if (Validate::absolutePath($this->config['CACHE_DIR']) === false) {
            return dirname(__DIR__) . DIRECTORY_SEPARATOR . $this->config['CACHE_DIR'];
        }

        return $this->config['CACHE_DIR'];
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
