<?php

namespace App;

use App\Config;
use App\Helper\Validate;
use App\Helper\Url;
use Exception;

class Index
{
    /** @var Config Config class instance */
    private Config $config;

    /** @var string $query Search query */
    private string $query = '';

    /** @var boolean $embedVideos Embed videos status */
    private bool $embedVideos = false;

    /** @var string $feedId YouTube channel or playlist ID */
    private string $feedId = '';

    /** @var string $feedType Feed type (channel or playlist) */
    private string $feedType = '';

    /** @var array<int, string> $supportedTypes Supported feed types */
    private array $supportedTypes = ['channel', 'playlist'];

    /** @var string $feedFormat Feed Format */
    private string $feedFormat = '';

    /** @var bool $fromUrl Query string is from a URL */
    private bool $fromUrl = false;

    /** @var boolean $error Error status */
    private bool $error = false;

    /** @var string $errorMessage Error Message */
    private string $errorMessage = '';

    /**
     * @param Config $config Config class instance
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->feedFormat = $this->config->getDefaultFeedFormat();

        try {
            $this->checkInputs();
            $this->generate();
        } catch (Exception $e) {
            $this->error = true;
            $this->errorMessage = $e->getMessage();
        }
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

        $version = $this->config->getVersion();

        if ($this->error === true) {
            $error = sprintf('<div id="error"><strong>%s</strong></div>', $this->errorMessage);
        }

        if ($this->error === false && empty($this->feedId) === false) {
            $url = Url::getFeed($this->feedType, $this->feedId, $this->feedFormat, $this->embedVideos);

            $link = sprintf('Feed URL: <a href="%s">%s</a>', $url, $url);

            if ($this->fromUrl === true) {
                $fromUrlLink = $link;
            } elseif ($this->feedType === 'channel') {
                $channelLink = $link;
            } elseif ($this->feedType === 'playlist') {
                $playlistLink = $link;
            }
        }

        $html = new Template('index-page.html', [
            'error' => $error,
            'selectHtml' => $this->createFormatSelect(),
            'channelLink' => $channelLink,
            'playlistLink' => $playlistLink,
            'fromUrlLink' => $fromUrlLink,
            'version' => $version
        ]);

        echo $html->render(minify: true);
    }

    /**
     * Check user inputs
     *
     * @throws Exception if a query parameter is not given
     * @throws Exception if a type parameter is not given
     * @throws Exception if a query parameter is not a valid YouTube URL when type is URL
     */
    private function checkInputs(): void
    {
        if (isset($_POST['query'])) {
            if (empty($_POST['query'])) {
                throw new Exception('Query parameter not given.');
            }

            if (isset($_POST['type']) === false || empty($_POST['type'])) {
                throw new Exception('Type parameter not given.');
            }

            if (in_array($_POST['type'], $this->supportedTypes) === false) {
                throw new Exception('Unknown type parameter given.');
            }

            $this->feedType = $_POST['type'];

            if ($_POST['type'] === 'url') {
                $this->fromUrl = true;

                if (Validate::youTubeUrl($_POST['query']) === false) {
                    throw new Exception('URL is not a valid YouTube URL.');
                }
            }

            if (isset($_POST['format']) && in_array($_POST['format'], $this->config->getFeedFormats())) {
                $this->feedFormat = $_POST['format'];
            }

            $this->query = $_POST['query'];
        }

        if (isset($_POST['embed_videos'])) {
            $this->embedVideos = true;
        }
    }

    /**
     * Generate feed URL
     *
     * @throws Exception if a query parameter is not a supported YouTube URL
     */
    private function generate(): void
    {
        if (empty($this->query) === false) {
            if ($this->fromUrl === true) {
                $detect = new Detect();

                if ($detect->fromUrl($this->query) === false) {
                    throw new Exception('Unsupported YouTube URL.');
                }

                $this->feedType = $detect->getType();
                $this->query = $detect->getValue();
            }

            if ($this->feedType === 'channel') {
                $this->feedId = $this->findChannel($this->query);
            } else {
                $this->feedId = $this->findPlaylist($this->query);
            }
        }
    }

    /**
     * Find channel
     *
     * @param string $query
     * @return string Channel Id
     *
     * @throws Exception if channel is not found
     */
    private function findChannel(string $query): string
    {
        if (Validate::channelId($query) === true) {
            return $query;
        }

        $api = new Api($this->config);
        $response = $api->searchChannels($query);

        if (empty($response->items)) {
            throw new Exception('Channel not found');
        }

        return $response->items[0]->id->channelId;
    }

    /**
     * Find playlist
     *
     * @param string $query
     * @return string Playlist Id
     *
     * @throws Exception if playlist is not found
     */
    private function findPlaylist(string $query): string
    {
        if (Validate::playlistId($query) === true) {
            return $query;
        }

        $api = new Api($this->config);
        $response = $api->searchPlaylists($query);

        if (empty($response->items)) {
            throw new Exception('Playlist not found');
        }

        return $response->items[0]->id->playlistId;
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
}
