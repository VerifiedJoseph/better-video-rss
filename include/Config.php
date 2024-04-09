<?php

namespace App;

use App\Config\Validate;
use Exception;

class Config
{
    private Validate $validate;

    /** @var string $minPhpVersion Minimum PHP version */
    private string $minPhpVersion = '8.1.0';

    /** @var array<int, string> $extensions Required PHP extensions */
    private array $extensions = ['curl', 'json', 'mbstring', 'simplexml'];

    /** @var string $userAgent User agent used for HTTP requests */
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
        $this->validate = new Validate($this->config);
        $this->validate->version(PHP_VERSION, $this->minPhpVersion);
        $this->validate->extensions($this->extensions);

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
     * Check config
     */
    public function checkConfig(): void
    {
        $this->requireConfigFile();

        // Required parameters
        $this->validate->selfUrlPath();
        $this->validate->apiKey();

        // Optional parameters
        $this->validate->timezone();
        $this->validate->dateFormat();
        $this->validate->timeFormat();
        $this->validate->cache();
        $this->validate->cacheViewer();
        $this->validate->imageProxy();
        $this->validate->cspStatus();
        $this->validate->rawApiErrors();

        $this->config = $this->validate->getConfig();
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
        return Version::getCacheFormatVersion();
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
        return Version::getVersion();
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
        if (Helper\Validate::absolutePath($this->config['CACHE_DIR']) === false) {
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
}
