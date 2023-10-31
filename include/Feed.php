<?php

namespace App;

use App\Config;
use App\Helper\Validate;
use App\Helper\Output;
use Exception;

class Feed
{
    /** @var Config Config class instance */
    private Config $config;

    /** @var Api Api class instance */
    private Api $api;

    /** @var array<string, mixed> $feedData Feed data from data class */
    private array $feedData = [];

    /** @var string $feedId YouTube channel or playlist ID */
    private string $feedId = '';

    /** @var string $feedType Feed type (channel or playlist) */
    private string $feedType = 'channel';

    /** @var string $feedFormat Feed format */
    private string $feedFormat = '';

    /** @var boolean $embedVideos Embed videos status */
    private bool $embedVideos = false;

    /**
     * @param array<string, mixed> $inputs Inputs parameters from `$_GET`
     * @param Config $config Config class instance
     * @param Api $api Api class instance
     */
    public function __construct(array $inputs, Config $config, Api $api)
    {
        $this->config = $config;
        $this->api = $api;

        $this->feedFormat = $this->config->getDefaultFeedFormat();

        $this->checkInputs($inputs);
    }

    /**
     * Generate feed
     */
    public function generate(): void
    {
        $fetch = new Fetch($this->config);

        $data = new Data(
            $this->getFeedId(),
            $this->getFeedType(),
            $this->config
        );

        foreach ($data->getExpiredParts() as $part) {
            if ($part === 'feed') {
                $response = $fetch->feed(
                    $this->getFeedId(),
                    $this->getFeedType()
                );

                $data->updateFeed($response);
            }

            if ($part === 'details') {
                $response = $this->api->getDetails(
                    $this->getFeedType(),
                    $this->getFeedId(),
                    $data->getPartEtag($part)
                );

                $data->updateDetails($response);
            }

            if ($part === 'videos') {
                $videos = $data->getExpiredVideos();

                if (empty($videos)) {
                    continue;
                }

                $response = $this->api->getVideos($videos);

                $data->updateVideos($response);
            }
        }

        $data->save();
        $this->feedData = $data->getData();
    }

    public function output(): void
    {
        $formatClass = sprintf('App\FeedFormat\%sFormat', ucfirst($this->getFeedFormat()));

        /** @var FeedFormat\RssFormat|FeedFormat\JsonFormat|FeedFormat\HtmlFormat */
        $format = new $formatClass(
            $this->getFeedData(),
            $this->getEmbedStatus(),
            $this->config
        );

        $format->build();

        Output::feed(
            $format->get(),
            $format->getContentType(),
            $format->getLastModified(),
            $this->config->getCsp(),
            $this->config->getCspDisabledStatus()
        );
    }

    /**
     * Check user inputs
     *
     * @param array<string, mixed> $inputs Inputs parameters from `$_GET`
     *
     * @throws Exception if an invalid format parameter is given.
     * @throws Exception if no channel or playlist ID parameter given.
     * @throws Exception if an empty or invalid channel ID parameter is given.
     * @throws Exception if an empty or invalid playlist ID parameter is given.
     */
    private function checkInputs($inputs): void
    {
        if (isset($inputs['format']) && empty($inputs['format']) === false) {
            $format = strtolower($inputs['format']);

            if (Validate::feedFormat($format, $this->config->getFeedFormats()) === false) {
                throw new Exception('Invalid format parameter given.');
            }

            $this->feedFormat = $format;
        }

        if (isset($inputs['channel_id'])) {
            if (empty($inputs['channel_id']) === true || Validate::channelId($inputs['channel_id']) === false) {
                throw new Exception('Invalid channel ID parameter given.');
            }

            $this->feedId = $inputs['channel_id'];
            $this->feedType = 'channel';
        } elseif (isset($inputs['playlist_id'])) {
            if (empty($inputs['playlist_id']) === true || Validate::playlistId($inputs['playlist_id']) === false) {
                throw new Exception('Invalid playlist ID parameter given.');
            }

            $this->feedId = $inputs['playlist_id'];
            $this->feedType = 'playlist';
        }

        if (isset($inputs['embed_videos'])) {
            $this->embedVideos = filter_var($inputs['embed_videos'], FILTER_VALIDATE_BOOLEAN);
        }

        if (empty($this->feedId) === true) {
            throw new Exception('No channel or playlist ID parameter given.');
        }
    }

    /**
     * Return feed data
     *
     * @return array<string, mixed>
     */
    private function getFeedData(): array
    {
        return $this->feedData;
    }

    /**
     * Return feed type
     *
     * @return string
     */
    private function getFeedType(): string
    {
        return $this->feedType;
    }

    /**
     * Return feed ID
     *
     * @return string
     */
    private function getFeedId(): string
    {
        return $this->feedId;
    }

    /**
     * Return feed format
     *
     * @return string
     */
    private function getFeedFormat(): string
    {
        return $this->feedFormat;
    }

    /**
     * Return embed video status
     *
     * @return boolean
     */
    private function getEmbedStatus(): bool
    {
        return $this->embedVideos;
    }
}
