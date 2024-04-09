<?php

namespace App\Config;

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
}
