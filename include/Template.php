<?php

namespace App;

use App\Helper\File;
use App\Helper\Format;
use App\Exception\ConfigException;

/**
 * Class for rendering pages using HTML/XML templates
 */
class Template
{
    private string $html = '';

    /** @var array<string, string> $variables Template variables */
    private array $variables = [];

    /**
     * @param string $name Template filename
     * @param array<string, mixed> $variables Template variables
     */
    public function __construct(string $name, array $variables)
    {
        $this->variables = $variables;
        $this->load($name);
    }

    /**
     * Render template into page
     *
     * @param bool $minify Minify HTML
     * @return string HTML
     */
    public function render(bool $minify = false): string
    {
        foreach ($this->variables as $name => $value) {
            $name = sprintf('{%s}', $name);
            $this->html = str_replace($name, $value, $this->html);
        }

        if ($minify === true) {
            return Format::minify($this->html);
        }

        return $this->html;
    }

    /**
     * Load Template
     *
     * @param string $name Template filename
     * @throws ConfigException if template file is not found.
     */
    private function load(string $name): void
    {
        $path = 'include/templates/' . $name;

        if (file_exists($path) === false) {
            throw new ConfigException('Template file not found: ' . $path);
        }

        $this->html = File::read($path);
    }
}
