<?php

declare(strict_types=1);

namespace App\Page;

use App\Config;
use App\Api;
use App\Find;
use App\Detect;
use App\Template;
use App\Helper\Output;
use App\Helper\Validate;
use App\Helper\Url;
use Exception;

class Index
{
    /** @var Config Config class instance */
    private Config $config;

    /** @var Api Api class instance */
    private Api $api;

    /** @var string $query Search query */
    private string $query = '';

    /** @var boolean $embedVideos Embed videos status */
    private bool $embedVideos = false;

    /** @var boolean $ignorePremieres Ignore upcoming video premieres */
    private bool $ignorePremieres = false;

    /** @var string $feedTitle YouTube channel or playlist title */
    private string $feedTitle = '';

    /** @var string $feedId YouTube channel or playlist ID */
    private string $feedId = '';

    /** @var string $feedType Feed type (channel or playlist) */
    private string $feedType = '';

    /** @var array<int, string> $supportedTypes Supported form types */
    private array $supportedTypes = ['channel', 'playlist', 'url'];

    /** @var string $feedFormat Feed Format */
    private string $feedFormat = '';

    /** @var bool $fromUrl Query string is from a URL */
    private bool $fromUrl = false;

    /** @var boolean $error Error status */
    private bool $error = false;

    /** @var string $errorMessage Error Message */
    private string $errorMessage = '';

    /**
     * @param array<string, mixed> $inputs Inputs parameters from `$_POST`
     * @param Config $config Config class instance
     * @param Api $api Api class instance
     */
    public function __construct(array $inputs, Config $config, Api $api)
    {
        $this->config = $config;
        $this->api = $api;

        $this->feedFormat = $this->config->getDefaultFeedFormat();

        $this->checkInputs($inputs);
        $this->generate();
    }

    /**
     * Display HTML
     */
    public function display(): void
    {
        $link = '';
        $error = '';
        $channelLink = '';
        $playlistLink = '';
        $fromUrlLink = '';

        if ($this->error === true) {
            $error = sprintf('<div id="error"><strong>%s</strong></div>', $this->errorMessage);
        }

        if ($this->error === false && empty($this->feedId) === false) {
            $url = Url::getFeed(
                $this->config->getSelfUrl(),
                $this->feedType,
                $this->feedId,
                $this->feedFormat,
                $this->embedVideos,
                $this->ignorePremieres
            );

            $link = $this->createFeedLink($url);

            if ($this->fromUrl === true) {
                $fromUrlLink = $link;
            } elseif ($this->feedType === 'channel') {
                $channelLink = $link;
            } else {
                $playlistLink = $link;
            }
        }

        $html = new Template('index-page.html', [
            'error' => $error,
            'selectHtml' => $this->createFormatSelect(),
            'channelLink' => $channelLink,
            'playlistLink' => $playlistLink,
            'fromUrlLink' => $fromUrlLink,
            'version' => $this->config->getVersion()
        ]);

        Output::html(
            $html->render(minify: true),
            $this->config->getCsp(),
            $this->config->getCspDisabledStatus()
        );
    }

    /**
     * Check user inputs
     *
     * @param array<string, mixed> $inputs Inputs parameters from `$_POST`
     *
     * @throws Exception if a query parameter is not given
     * @throws Exception if a type parameter is not given
     * @throws Exception if a query parameter is not a valid YouTube URL when type is URL
     */
    private function checkInputs(array $inputs): void
    {
        if (isset($inputs['query'])) {
            if (empty($inputs['query'])) {
                throw new Exception('Query parameter not given.');
            }

            if (isset($inputs['type']) === false || empty($inputs['type'])) {
                throw new Exception('Type parameter not given.');
            }

            if (in_array($inputs['type'], $this->supportedTypes) === false) {
                throw new Exception('Unknown type parameter given.');
            }

            $this->feedType = $inputs['type'];

            if ($inputs['type'] === 'url') {
                $this->fromUrl = true;
            }

            if (isset($inputs['format']) && in_array($inputs['format'], $this->config->getFeedFormats())) {
                $this->feedFormat = $inputs['format'];
            }

            $this->query = $inputs['query'];
        }

        if (isset($inputs['embed_videos'])) {
            $this->embedVideos = filter_var($inputs['embed_videos'], FILTER_VALIDATE_BOOLEAN);
        }

        if (isset($inputs['ignore_premieres'])) {
            $this->ignorePremieres = filter_var($inputs['ignore_premieres'], FILTER_VALIDATE_BOOLEAN);
        }
    }

    /**
     * Generate feed URL
     */
    private function generate(): void
    {
        try {
            if (empty($this->query) === false) {
                if ($this->fromUrl === true) {
                    $detect = new Detect();

                    if (Validate::youTubeUrl($this->query) === false) {
                        throw new Exception('URL is not a valid YouTube URL.');
                    }

                    if ($detect->fromUrl($this->query) === false) {
                        throw new Exception('Unsupported YouTube URL.');
                    }

                    $this->feedType = $detect->getType();
                    $this->query = $detect->getValue();
                }

                if ($this->validateFeedId($this->query) === true) {
                    $this->feedId = $this->query;
                } else {
                    $find = new Find($this->feedType, $this->api);
                    $find->lookup($this->query);

                    $this->feedId = $find->getId();
                    $this->feedTitle = $find->getTitle();
                }
            }
        } catch (Exception $e) {
            $this->error = true;
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * Validate a feed id
     *
     * @param string $query
     */
    private function validateFeedId(string $query): bool
    {
        if ($this->feedType === 'playlist') {
            return Validate::playlistId($query);
        }

        return Validate::channelId($query);
    }

    /**
     * Create feed format drop-down
     *
     * @return string $html
     */
    private function createFormatSelect(): string
    {
        $options = '';
        foreach ($this->config->getFeedFormats() as $format) {
            $options .= sprintf('<option value="%s">%s</option>', $format, strtoupper($format));
        }

        return sprintf('<select name="format">%s</select>', $options);
    }

    /**
     * Create feed link
     *
     * @param $url $url Feed url
     * @return string
     */
    private function createFeedLink(string $url): string
    {
        if ($this->feedTitle !== '') {
            return sprintf(
                'Feed URL for <strong>%s</strong>:<br><a href="%s">%s</a>',
                htmlEntities($this->feedTitle, ENT_QUOTES),
                $url,
                $url,
            );
        }

        return sprintf('Feed URL:<br><a href="%s">%s</a>', $url, $url);
    }
}
