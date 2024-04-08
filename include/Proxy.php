<?php

namespace App;

use App\Config;
use App\Helper\Validate;
use App\Helper\Output;
use App\Http\Request;
use Exception;

class Proxy
{
    /** @var Config Config class instance */
    private Config $config;

    /** @var Request Request class instance */
    private Request $request;

    /** @var string $feedId YouTube channel or playlist ID */
    private string $feedId = '';

    /** @var string $videoId YouTube video ID */
    private string $videoId = '';

    /** @var string $image Image data */
    private string $image = '';

    /**
     * @param array<string, mixed> $inputs Inputs parameters from `$_GET`
     * @param Config $config Config class instance
     *
     * @throws Exception if ENABLE_IMAGE_PROXY is false
     */
    public function __construct(array $inputs, Config $config, Request $request)
    {
        $this->config = $config;
        $this->request = $request;

        if ($this->config->getImageProxyStatus() === false) {
            throw new Exception('Image proxy is disabled.');
        }

        $this->checkInputs($inputs);
    }

    /**
     * Get image
     *
     * @throws Exception if feed id is not found in cache.
     * @throws Exception if video id is not found in cache.
     */
    public function getImage(): void
    {
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

        $this->image = $this->request->get($url);
    }

    /**
     * Output image
     */
    public function output(): void
    {
        Output::image(
            $this->image,
            'image/jpeg',
            $this->config->getCsp(),
            $this->config->getCspDisabledStatus()
        );
    }

    /**
     * Check user inputs
     *
     * @param array<string, mixed> $inputs Inputs parameters from `$_GET`
     *
     * @throws Exception if a invalid format parameter is given.
     * @throws Exception if an empty or invalid channel ID parameter is given.
     * @throws Exception if an empty or invalid playlist ID parameter is given.
     */
    private function checkInputs(array $inputs): void
    {
        if (isset($inputs['video_id']) === false || empty($inputs['video_id'])) {
            throw new Exception('No video ID parameter given.');
        }

        if (Validate::videoId($inputs['video_id']) === false) {
            throw new Exception('Invalid video ID parameter given.');
        }

        $this->videoId = $inputs['video_id'];

        if (isset($inputs['channel_id'])) {
            if (empty($inputs['channel_id'])) {
                throw new Exception('No channel ID parameter given.');
            }

            if (Validate::channelId($inputs['channel_id']) === false) {
                throw new Exception('Invalid channel ID parameter given.');
            }

            $this->feedId = $inputs['channel_id'];
        }

        if (isset($inputs['playlist_id'])) {
            if (empty($inputs['playlist_id'])) {
                throw new Exception('No playlist ID parameter given.');
            }

            if (Validate::playlistId($inputs['playlist_id']) === false) {
                throw new Exception('Invalid playlist ID parameter given.');
            }

            $this->feedId = $inputs['playlist_id'];
        }

        if (empty($this->feedId)) {
            throw new Exception('No feed ID (channel or playlist) parameter given.');
        }
    }
}
