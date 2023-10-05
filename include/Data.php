<?php

namespace App;

use App\Configuration as Config;
use App\Cache;
use App\Helper\Convert;
use App\Helper\Url;
use stdClass;

class Data
{
    /** @var Cache $cache Cache class object */
    private Cache $cache;

    /** @var array<int, string> $parts Data part names */
    private array $parts = ['details', 'feed', 'videos'];

    /** @var array<string, mixed> $data Data */
    private array $data = array(
        'details' => array(),
        'feed' => array(
            'videos' => array(),
        ),
        'videos' => array()
    );

    /** @var array<string, string> $expires Number of days, hours or minutes that each part expires */
    private $expires = [
        'details' => '+30 days',
        'feed' => '+10 minutes',
        'videos' => '+1 hour',
    ];

    /** @var bool $updated Data update status */
    private bool $updated = false;

    /**
     * Constructor
     *
     * @param string $feedId Feed id (channel or playlist ID)
     * @param string $feedType Feed type (channel or playlist)
     */
    public function __construct(string $feedId, string $feedType)
    {
        $this->data['details']['id'] = $feedId;
        $this->data['details']['type'] = $feedType;

        $this->cache = new Cache($feedId);

        // Load cache
        $this->cache->load();

        // Use data from cache, if found
        $this->setData(
            $this->cache->getData()
        );
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        // Save data to cache file, if updated
        if ($this->getUpdateStatus() === true) {
            $this->cache->save(
                $this->getData()
            );
        }
    }

    /**
     * Returns data
     *
     * @return array<string, array<mixed>> $data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Sets data
     *
     * @param array<mixed> $data
     */
    public function setData(array $data): void
    {
        if (empty($data) === false) {
            $this->data = $data;
        }
    }

    /**
     * Returns HTTP ETag for a part
     *
     * @param string $part
     * @return string
     */
    public function getPartEtag(string $part): string
    {
        if (isset($this->data[$part]['etag'])) {
            return $this->data[$part]['etag'];
        }

        return '';
    }

    /**
     * Returns array of expired data parts
     *
     * @return array<int, string>
     */
    public function getExpiredParts(): array
    {
        $expiredParts = array();

        if (Config::get('DISABLE_CACHE') === true) {
            return $this->parts;
        }

        foreach ($this->data as $partName => $partData) {
            if (isset($partData['expires']) === false) {
                $expiredParts[] = $partName;
            } elseif (time() >= $partData['expires']) {
                $expiredParts[] = $partName;
            }
        }

        return $expiredParts;
    }

    /**
     * Returns expired video IDs as comma separated string
     *
     * @return string
     */
    public function getExpiredVideos(): string
    {
        $expiredVideos = array();

        // Return all video IDs if videos array is empty or cache is disabled
        if (empty($this->data['videos']) || Config::get('DISABLE_CACHE') === true) {
            return implode(',', $this->data['feed']['videos']);
        }

        foreach ($this->data['feed']['videos'] as $id) {
            $key = array_search($id, array_column($this->data['videos'], 'id'));

            if (
                isset($this->data['videos'][$key]['expires']) === false ||
                time() >= $this->data['videos'][$key]['expires']
            ) {
                $expiredVideos[] = $id;
            }
        }

        return implode(',', $expiredVideos);
    }

    /**
     * Update channel or playlist details with response from YouTube Data API
     *
     * @param mixed $response
     */
    public function updateDetails($response): void
    {
        $this->updated = true;

        $details = $this->data['details'];

        if (empty($response) === false) {
            $details['etag'] = $response->etag;

            $details['title'] = $response->items['0']->snippet->title;
            $details['description'] = $response->items['0']->snippet->description;

            if ($this->data['details']['type'] === 'channel') {
                $details['url'] = Url::getChannel($this->data['details']['id']);
            }

            if ($this->data['details']['type'] === 'playlist') {
                $details['url'] = Url::getPlaylist($this->data['details']['id']);
            }

            $details['thumbnail'] = $response->items['0']->snippet->thumbnails->default->url;
            $details['fetched'] = strtotime('now');
        }

        $details['expires'] = strtotime($this->expires['details']);

        $this->data['details'] = $details;
        $this->data['updated'] = strtotime('now');
    }

    /**
     * Update video details with response from YouTube Data API
     *
     * @param mixed $response
     */
    public function updateVideos($response): void
    {
        $this->updated = true;

        if (empty($response) === false) {
            $videos = $this->data['videos'];

            /** @var stdClass $item  */
            foreach ($response->items as $item) {
                $key = array_search($item->id, array_column($this->data['videos'], 'id'));
                $video = $this->data['videos'][$key];

                $video['duration'] = Convert::videoDuration($item->contentDetails->duration);
                $video['tags'] = array();
                $video['liveStream'] = false;

                if ($item->snippet->liveBroadcastContent !== 'none') {
                    $video['liveStream'] = true;
                    $video['liveStreamStatus'] = $item->snippet->liveBroadcastContent;
                    $video['liveStreamScheduled'] = 0;

                    if (isset($item->liveStreamingDetails->scheduledStartTime)) {
                        $video['liveStreamScheduled'] = strtotime($item->liveStreamingDetails->scheduledStartTime);
                    }
                }

                if (isset($item->snippet->tags)) {
                    $video['tags'] = $item->snippet->tags;
                }

                // Never use '_live.jpg' thumbnails returned by the API. Live thumbnails sometimes return 404.
                if (isset($item->snippet->thumbnails->maxres)) {
                    $video['thumbnail'] = Url::getThumbnail($item->id, 'maxresdefault');
                } elseif (isset($item->snippet->thumbnails->standard)) {
                    $video['thumbnail'] = Url::getThumbnail($item->id, 'sddefault');
                } else {
                    $video['thumbnail'] = Url::getThumbnail($item->id, 'hqdefault');
                }

                $video['fetched'] = strtotime('now');
                $video['expires'] = strtotime($this->expires['videos']);

                $videos[$key] = $video;
            }

            $this->data['videos'] = $videos;
            $this->data['updated'] = strtotime('now');
        }
    }

    /**
     * Handle response from YouTube RSS feed
     *
     * @param string $response
     */
    public function updateFeed($response): void
    {
        $this->updated = true;

        $feed = array(
            'videos' => array()
        );

        $videos = $this->data['videos'];

        /** @var \SimpleXMLElement $xml */
        $xml = simplexml_load_string($response);

        // Get namespaces from XML
        $namespaces = $xml->getNamespaces(true);

        foreach ($xml->entry as $entry) {
            $mediaNodes = $entry->children($namespaces['media']);
            $ytNodes = $entry->children($namespaces['yt']);

            $id = (string)$ytNodes->videoId;
            $key = array_search($id, array_column($videos, 'id'));

            $feed['videos'][] = $id;

            $video = array();
            $video['id'] = $id;
            $video['url'] = Url::getVideo($id);
            $video['title'] = (string)$entry->title;
            $video['description'] = (string)$mediaNodes->group->description;
            $video['author'] = (string)$entry->author->name;
            $video['published'] = strtotime((string)$entry->published);

            if ($key !== false) {
                $videos[$key] = array_merge($video, $videos[$key]);
            } else {
                $videos[] = $video;
            }
        }

        $feed['fetched'] = strtotime('now');
        $feed['expires'] = strtotime($this->expires['feed']);

        $this->data['videos'] = $videos;
        $this->data['feed'] = $feed;
        $this->data['updated'] = strtotime('now');

        $this->removeOldVideos();
    }

    /**
     * Returns data update status
     *
     * @return boolean
     */
    private function getUpdateStatus(): bool
    {
        return $this->updated;
    }

    /**
     * Removes videos that are no longer in the RSS feed from YouTube
     */
    private function removeOldVideos(): void
    {
        $videos = [];

        foreach ($this->data['feed']['videos'] as $videoId) {
            $key = array_search($videoId, array_column($this->data['videos'], 'id'));

            if ($key !== false) {
                $videos[] = $this->data['videos'][$key];
            }
        }

        $this->data['videos'] = $videos;
    }
}
