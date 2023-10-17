<?php

namespace App;

use App\Config;
use App\Helper\File;

class Cache
{
    /** @var Config Config class instance */
    private Config $config;

    /** @var string $name Cache filename */
    private string $name = '';

    /** @var array<string, mixed> $data Cache data */
    private array $data = [];

    /** @var string $path Cache file path */
    private string $path = '';

    /**
     * @param string $feedId Feed ID
     * @param Config Config class instance
     */
    public function __construct(string $feedId, Config $config)
    {
        $this->config = $config;
        $this->setName($feedId);
        $this->setPath();
    }

    /**
     * Returns cache data
     *
     * @return array<string, mixed> $data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Load cache data from disk
     */
    public function load(): void
    {
        if ($this->config->getCacheDisableStatus() === false) {
            $contents = File::read($this->path);
            $decoded = json_decode($contents, true);

            if (is_null($decoded) === false) {
                $this->data = $decoded;
            }
        }
    }

    /**
     * Save cache data to disk
     *
     * @param array<string, mixed> $data Feed date
     */
    public function save(array $data = []): void
    {
        if ($this->config->getCacheDisableStatus() === false) {
            $data = (string) json_encode($data);
            File::write($this->path, $data);
        }
    }

    /**
     * Set cache name from feed ID
     *
     * @param string $feedId channel or playlist ID
     */
    private function setName(string $feedId): void
    {
        $this->name = hash('sha256', $feedId);
    }

    /**
     * Set cache file path
     */
    private function setPath(): void
    {
        $this->path = $this->config->getCacheDirPath() . DIRECTORY_SEPARATOR .
        $this->name . '.' . $this->config->getCacheFileExtension();
    }
}
