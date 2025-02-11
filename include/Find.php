<?php

declare(strict_types=1);

namespace App;

use Exception;

class Find
{
    /** @var string $type Content type */
    private string $type;

    /** @var Api Api class instance */
    private Api $api;

    /** @var string $id Feed id */
    private string $id = '';

    /** @var string $title Feed title */
    private string $title = '';

    /**
     * @param string $type Type of content to lookup (channel or playlist)
     * @param Api $api Api class instance
     */
    public function __construct(string $type, Api $api)
    {
        $this->type = $type;
        $this->api = $api;
    }

    /**
     * Lookup a channel or playlist using the YouTube search API
     *
     * @param string $query Search query
     */
    public function lookup(string $query): void
    {
        if ($this->type === 'channel') {
            $response = $this->api->searchChannels($query);
        } else {
            $response = $this->api->searchPlaylists($query);
        }

        if (empty($response->items)) {
            throw new Exception(ucfirst($this->type) . ' not found');
        }

        $this->title = $response->items[0]->snippet->title;
        $this->id = $response->items[0]->id->channelId ?? $response->items[0]->id->playlistId;
    }

    /**
     * Returns feed ID found using search API
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns title ID found using search API
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}
