<?php

namespace App\Config;

use App\Helper;
use App\Exception\ConfigException;

class Validate extends Base
{
    /** @var array<string, mixed> $config Config */
    private array $config = [];

    /**
     * @param array<string, mixed> $defaults Config defaults
     */
    public function __construct(array $defaults)
    {
        $this->config = $defaults;
    }
    
    /**
     * Returns config
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Validate php version
     *
     * @param string $version php version
     * @param string $minimumVersion Minimum required PHP version
     * @throws ConfigException if PHP version not supported.
     */
    public function version(string $version, string $minimumVersion): void
    {
        if (version_compare($version, $minimumVersion) === -1) {
            throw new ConfigException('BetterVideoRss requires at least PHP version ' . $minimumVersion);
        }
    }

    /**
     * Validate required php extensions are loaded
     *
     * @param array<int, string> $required Required php extensions
     * @throws ConfigException if a required PHP extension is not loaded.
     */
    public function extensions(array $required): void
    {
        foreach ($required as $ext) {
            if (extension_loaded($ext) === false) {
                throw new ConfigException(sprintf('PHP extension error: %s extension not loaded.', $ext));
            }
        }
    }

    /**
     * Validate `BVRSS_SELF_URL_PATH` environment variable
     *
     * @throws ConfigException if self URL path environment variable is not set.
     * @throws ConfigException if self URL path does not end with a forward slash.
     * @throws ConfigException if self URL path does not start with http:// or https://.
     */
    public function selfUrlPath(): void
    {
        if ($this->hasEnv('SELF_URL_PATH') === false || $this->getEnv('SELF_URL_PATH') === '') {
            throw new ConfigException('Self URL path must be set. [BVRSS_SELF_URL_PATH]');
        }

        if (Helper\Validate::selfUrlSlash((string) $this->getEnv('SELF_URL_PATH')) === false) {
            throw new ConfigException('Self URL must end with a forward slash. [BVRSS_SELF_URL_PATH]');
        }

        if (Helper\Validate::selfUrlHttp((string) $this->getEnv('SELF_URL_PATH')) === false) {
            throw new ConfigException('Self URL must start with http:// or https:// [BVRSS_SELF_URL_PATH]');
        }

        $this->config['SELF_URL_PATH'] = $this->getEnv('SELF_URL_PATH');
    }

    /**
     * Validate `BVRSS_YOUTUBE_API_KEY` environment variable
     *
     * @throws ConfigException if YouTube API key environment variable is not set.
     */
    public function apiKey(): void
    {
        if ($this->hasEnv('YOUTUBE_API_KEY') === false || $this->getEnv('YOUTUBE_API_KEY') === '') {
            throw new ConfigException('YouTube API key must be set. [BVRSS_YOUTUBE_API_KEY]');
        }

        $this->config['YOUTUBE_API_KEY'] = $this->getEnv('YOUTUBE_API_KEY');
    }

    /**
     * Validate `BVRSS_TIMEZONE` environment variable
     * @throws ConfigException if invalid timezone is given
     */
    public function timezone(): void
    {
        if ($this->hasEnv('TIMEZONE') === true && $this->getEnv('TIMEZONE') !== '') {
            if (Validate::timezone((string) $this->getEnv('TIMEZONE')) === false) {
                throw new ConfigException(sprintf(
                    'Invalid timezone given (%s). See: https://www.php.net/manual/en/timezones.php [BVRSS_TIMEZONE]',
                    $this->getEnv('TIMEZONE')
                ));
            }

            $this->config['TIMEZONE'] = $this->getEnv('TIMEZONE');
        }
    }

    /**
     * Validate `BVRSS_DATE_FORMAT` environment variable
     */
    public function dateFormat(): void
    {
        if ($this->hasEnv('DATE_FORMAT') === true && $this->getEnv('DATE_FORMAT') !== '') {
            $this->config['DATE_FORMAT'] = $this->getEnv('DATE_FORMAT');
        }
    }

    /**
     * Validate `BVRSS_TIME_FORMAT` environment variable
     */
    public function timeFormat(): void
    {
        if ($this->hasEnv('TIME_FORMAT') === true && $this->getEnv('TIME_FORMAT') !== '') {
            $this->config['TIME_FORMAT'] = $this->getEnv('TIME_FORMAT');
        }
    }

    /**
     * Validate cache environment variables
     * - `BVRSS_DISABLE_CACHE`
     * - `BVRSS_CACHE_DIR`
     */
    public function cache(): void
    {
        if ($this->getEnv('DISABLE_CACHE') === 'true') {
            $this->config['DISABLE_CACHE'] = true;
        }

        if ($this->config['DISABLE_CACHE'] === false) {
            if ($this->hasEnv('CACHE_DIR') === true && $this->getEnv('CACHE_DIR') !== '') {
                $this->config['CACHE_DIR'] = $this->getEnv('CACHE_DIR');
            }

            if (is_dir($this->config['CACHE_DIR']) === false && mkdir($this->config['CACHE_DIR']) === false) {
                throw new ConfigException('Could not create cache directory [BVRSS_CACHE_DIR]');
            }

            if (is_dir($this->config['CACHE_DIR']) && is_writable($this->config['CACHE_DIR']) === false) {
                throw new ConfigException('Cache directory is not writable. [BVRSS_CACHE_DIR]');
            }
        }
    }

    /**
     * Validate `BVRSS_ENABLE_CACHE_VIEWER` environment variable
     */
    public function cacheViewer(): void
    {
        if ($this->getEnv('ENABLE_CACHE_VIEWER') === 'true') {
            $this->config['ENABLE_CACHE_VIEWER'] = true;
        }
    }

    /**
     * Validate `BVRSS_ENABLE_IMAGE_PROXY` environment variable
     */
    public function imageProxy(): void
    {
        if ($this->getEnv('ENABLE_IMAGE_PROXY') === 'true') {
            $this->config['ENABLE_IMAGE_PROXY'] = true;
        }
    }

    /**
     * Validate `BVRSS_DISABLE_CSP` environment variable
     */
    public function cspStatus(): void
    {
        if ($this->getEnv('DISABLE_CSP') === 'true') {
            $this->config['DISABLE_CSP'] = true;
        }
    }

    /**
     * Validate `BVRSS_RAW_API_ERRORS` environment variable
     */
    public function rawApiErrors(): void
    {
        if ($this->getEnv('RAW_API_ERRORS') === 'true') {
            $this->config['RAW_API_ERRORS'] = true;
        }
    }
}
