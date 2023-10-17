<?php

namespace App;

use App\Config;
use App\Helper\Validate;
use App\Helper\Output;
use Exception;

class Proxy
{
    /** @var Config Config class instance */
    private Config $config;

    /** @var string $feedId YouTube channel or playlist ID */
    private string $feedId = '';

    /** @var string $videoId YouTube video ID */
    private string $videoId = '';

    /** @var string $image Image data */
    private string $image = '';

    /**
     * @param Config $config Config class instance
     *
     * @throws Exception if ENABLE_IMAGE_PROXY is false
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        if ($this->config->get('ENABLE_IMAGE_PROXY') === false) {
            throw new Exception('Image proxy is disabled.');
        }

        $this->checkInputs();
    }

    /**
     * Get image
     *
     * @throws Exception if feed id is not found in cache.
     * @throws Exception if video id is not found in cache.
     */
    public function getImage(): void
    {
        $fetch = new Fetch($this->config);
        $cache = new Cache(
            $this->feedId,
            $this->config
        );

        $cache->load();
        $data = $cache->getData();

        if (empty($data) === true) {
            throw new Exception('Feed ID not in cache');
        }

        $videos = $data['feed']['videos'];

        if (in_array($this->videoId, $videos) === false) {
            throw new Exception('Video ID not in cache');
        }

        $key = array_search($this->videoId, $videos);
        $url = $data['videos'][$key]['thumbnail'];

        $this->image = $fetch->thumbnail($url);
    }

    /**
     * Output image
     */
    public function output(): void
    {
        Output::image($this->image);
    }

    /**
     * Check user inputs
     *
     * @throws Exception if a invalid format parameter is given.
     * @throws Exception if an empty or invalid channel ID parameter is given.
     * @throws Exception if an empty or invalid playlist ID parameter is given.
     */
    private function checkInputs(): void
    {
        if (isset($_GET['video_id']) === false || empty($_GET['video_id'])) {
            throw new Exception('No video ID parameter given.');
        }

        if (Validate::videoId($_GET['video_id']) === false) {
            throw new Exception('Invalid video ID parameter given.');
        }

        $this->videoId = $_GET['video_id'];

        if (isset($_GET['channel_id'])) {
            if (empty($_GET['channel_id'])) {
                throw new Exception('No channel ID parameter given.');
            }

            if (Validate::channelId($_GET['channel_id']) === false) {
                throw new Exception('Invalid channel ID parameter given.');
            }

            $this->feedId = $_GET['channel_id'];
        }

        if (isset($_GET['playlist_id'])) {
            if (empty($_GET['playlist_id'])) {
                throw new Exception('No playlist ID parameter given.');
            }

            if (Validate::playlistId($_GET['playlist_id']) === false) {
                throw new Exception('Invalid playlist ID parameter given.');
            }

            $this->feedId = $_GET['playlist_id'];
        }

        if (empty($this->feedId)) {
            throw new Exception('No feed ID (channel or playlist) parameter given.');
        }
    }
}
