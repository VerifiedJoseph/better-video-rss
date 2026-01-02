<?php

declare(strict_types=1);

namespace App;

use App\Config\Validate;

class Config
{
    private Validate $validate;

    /** @var string $minPhpVersion Minimum PHP version */
    private string $minPhpVersion = '8.2.0';

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
        'DISABLE_CSP' => false
    ];

    public function __construct()
    {
        $this->validate = new Validate($this->config);
        $this->validate->version(PHP_VERSION, $this->minPhpVersion);
        $this->validate->extensions($this->extensions);
    }

    /**
     * Check config
     */
    public function checkConfig(): void
    {
        $this->includeConfigFile();

        // Required parameters
        $this->validate->selfUrlPath();
        $this->validate->apiKey();

        // Optional parameters
        $this->validate->timezone();
        $this->validate->dateFormat();
        $this->validate->timeFormat();
        $this->validate->cache();
        $this->validate->cacheViewer();
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
     * @return non-empty-string
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
     * Include config file
     */
    private function includeConfigFile(): void
    {
        if (file_exists('config.php') === true) {
            include_once 'config.php';
        }
    }
}
