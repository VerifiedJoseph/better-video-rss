<?php

namespace App\Config;

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
     * Check php version
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
     * Check for required php extensions
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
}
