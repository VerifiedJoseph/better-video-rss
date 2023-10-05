<?php

namespace App;

use App\Configuration as Config;
use App\Helper\Validate;
use App\Helper\Output;
use Exception;

class Feed
{
    /** @var array $feedData Feed data from data class */
    private array $feedData = array();

    /** @var string $feedId YouTube channel or playlist ID */
    private string $feedId = '';

    /** @var string $feedType Feed type (channel or playlist) */
    private string $feedType = 'channel';

    /** @var string $feedFormat Feed format */
    private string $feedFormat = '';

    /** @var boolean $embedVideos Embed videos status */
    private bool $embedVideos = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->feedFormat = Config::getDefaultFeedFormat();
        $this->checkInputs();
    }

    /**
     * Generate feed
     */
    public function generate()
    {
        $api = new Api();
        $fetch = new Fetch();

        $data = new Data(
            $this->getFeedId(),
            $this->getFeedType()
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
                $response = $api->getDetails(
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

                $response = $api->getVideos(
                    $videos,
                    $data->getPartEtag($part)
                );

                $data->updateVideos($response);
            }
        }

        $this->feedData = $data->getData();
    }

    public function output()
    {
        $formatClass = 'App\Format\\' . ucfirst($this->getFeedFormat());

        $format = new $formatClass(
            $this->getFeedData(),
            $this->getEmbedStatus()
        );

        $format->build();

        Output::feed(
            $format->get(),
            $format->getContentType(),
            $format->getLastModified()
        );
    }

    /**
     * Check user inputs
     *
     * @throws Exception if an invalid format parameter is given.
     * @throws Exception if no channel or playlist ID parameter given.
     * @throws Exception if an empty or invalid channel ID parameter is given.
     * @throws Exception if an empty or invalid playlist ID parameter is given.
     */
    private function checkInputs()
    {

        if (isset($_GET['format']) && empty($_GET['format']) === false) {
            $format = strtolower($_GET['format']);

            if (Validate::feedFormat($format) === false) {
                throw new Exception('Invalid format parameter given.');
            }

            $this->feedFormat = $format;
        }

        if (isset($_GET['channel_id'])) {
            if (empty($_GET['channel_id']) === true || Validate::channelId($_GET['channel_id']) === false) {
                throw new Exception('Invalid channel ID parameter given.');
            }

            $this->feedId = $_GET['channel_id'];
            $this->feedType = 'channel';
        }

        if (isset($_GET['playlist_id'])) {
            if (empty($_GET['playlist_id']) === true || Validate::playlistId($_GET['playlist_id']) === false) {
                throw new Exception('Invalid playlist ID parameter given.');
            }

            $this->feedId = $_GET['playlist_id'];
            $this->feedType = 'playlist';
        }

        if (isset($_GET['embed_videos'])) {
            $this->embedVideos = filter_var($_GET['embed_videos'], FILTER_VALIDATE_BOOLEAN);
        }

        if (empty($this->feedId) === true) {
            throw new Exception('No channel or playlist ID parameter given.');
        }
    }

    /**
     * Return feed data
     *
     * @return array
     */
    private function getFeedData()
    {
        return $this->feedData;
    }

    /**
     * Return feed type
     *
     * @return string
     */
    private function getFeedType()
    {
        return $this->feedType;
    }

    /**
     * Return feed ID
     *
     * @return string
     */
    private function getFeedId()
    {
        return $this->feedId;
    }

    /**
     * Return feed format
     *
     * @return string
     */
    private function getFeedFormat()
    {
        return $this->feedFormat;
    }

    /**
     * Return embed video status
     *
     * @return boolean
     */
    private function getEmbedStatus()
    {
        return $this->embedVideos;
    }
}
