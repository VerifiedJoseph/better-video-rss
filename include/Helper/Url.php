<?php

namespace App\Helper;

class Url
{
    /** @var array<string, string> $endpoints YouTube endpoints */
    private static array $endpoints = [
        'images' => 'https://i.ytimg.com/vi/',
        'nocookie' => 'https://www.youtube-nocookie.com/',
        'website' => 'https://www.youtube.com/',
        'feed' => 'https://www.youtube.com/feeds/videos.xml',
        'api' => 'https://www.googleapis.com/youtube/v3/'
    ];

    /** @var array<string, array<mixed>> $apiEndpoints YouTube API endpoints and query components */
    private static array $apiEndpoints = [
        'channel' => [
            'endpoint' => 'channels',
            'query' => [
                'id' => '',
                'part' => [
                    'snippet',
                    'contentDetails'
                ],
                'fields' => [
                    'etag',
                    'items(snippet(title,description,thumbnails(default(url))))'
                ]
            ],
        ],
        'playlist' => [
            'endpoint' => 'playlists',
            'query' => [
                'id' => '',
                'part' => [
                    'snippet' ,
                    'contentDetails'
                ],
                'fields' => [
                    'etag',
                    'items(snippet(title,description,thumbnails(default(url))))'
                ],
            ],
        ],
        'videos' => [
            'endpoint' => 'videos',
            'id' => '',
            'query' => [
                'id' => '',
                'part' => [
                    'id',
                    'snippet',
                    'contentDetails' ,
                    'liveStreamingDetails',
                ],
                'fields' => [
                    'etag',
                    'items(id,snippet(tags,liveBroadcastContent,thumbnails(standard(url),maxres(url)))',
                    'contentDetails(duration)',
                    'liveStreamingDetails(scheduledStartTime))'
                ]
            ],
        ],
        'searchChannels' => [
            'endpoint' => 'search',
            'query' => [
                'part' => [
                    'id',
                    'snippet',
                ],
                'fields' => [
                    'items(id(channelId)',
                    'snippet(title))'
                ],
                'q' => '',
                'type' => 'channel',
                'maxResults' => 1
            ],
        ],
        'searchPlaylists' => [
            'endpoint' => 'search',
            'query' => [
                'part' => [
                    'id',
                    'snippet',
                ],
                'fields' => [
                    'items(id(playlistId)',
                    'snippet(title))'
                ],
                'q' => '',
                'type' => 'playlist',
                'maxResults' => 1
            ],
        ]
    ];

    /** @var array<int, string> $thumbnailTypes Supported YouTube thumbnail types */
    private static array $thumbnailTypes = [
        'hqdefault',
        'sddefault',
        'maxresdefault'
    ];

    /**
     * Create a feed URL for BetterVideoRss
     *
     * @param string $type Feed type
     * @param string $id Feed id
     * @param string $format Feed format
     * @param boolean $embed Embed video status
     * @param boolean $ignorePremieres Ignore premieres status
     * @return string
     */
    public static function getFeed(
        string $selfUrl,
        string $type,
        string $id,
        string $format,
        bool $embed = false,
        bool $ignorePremieres = false
    ) {
        $url = $selfUrl . 'feed.php?' . $type . '_id=' . $id . '&format=' . $format;

        if ($embed === true) {
            $url .= '&embed_videos=true';
        }

        if ($ignorePremieres === true) {
            $url .= '&ignore_premieres=true';
        }

        return $url;
    }

    /**
     * Create a proxy URL for an image
     *
     * @param string $videoId Video id
     * @param string $feedType Feed type
     * @param string $feedId Feed id
     * @return string
     */
    public static function getImageProxy(string $selfUrl, string $videoId, string $feedType, string $feedId)
    {
        return $selfUrl . 'proxy.php?video_id=' . $videoId . '&' . $feedType . '_id=' . $feedId;
    }

    /**
     * Create a YouTube RSS feed URL (https://www.youtube.com/feeds/videos.xml)
     *
     * @param string $type Feed type
     * @param string $id channel or playlist id
     * @return string
     */
    public static function getRssFeed(string $type, string $id)
    {
        return self::getEndpoint('feed') . '?' . $type . '_id=' . $id;
    }

    /**
     * Create a YouTube channel URL
     *
     * @param string $channelId YouTube channel ID
     * @return string
     */
    public static function getChannel(string $channelId)
    {
        return self::getEndpoint('website') . 'channel/' . $channelId;
    }

    /**
     * Create a YouTube playlist URL
     *
     * @param string $playlistId YouTube playlist ID
     * @return string
     */
    public static function getPlaylist(string $playlistId)
    {
        return self::getEndpoint('website') . 'playlist?list=' . $playlistId;
    }

    /**
     * Create a YouTube video URL
     *
     * @param string $videoId YouTube video ID
     * @return string
     */
    public static function getVideo(string $videoId)
    {
        return self::getEndpoint('website') . 'watch?v=' . $videoId;
    }

    /**
     * Create a YouTube video embed URL
     *
     * @param string $videoId YouTube video ID
     * @return string
     */
    public static function getEmbed(string $videoId)
    {
        return self::getEndpoint('nocookie') . 'embed/' . $videoId;
    }

    /**
     * Create a YouTube thumbnail URL
     *
     * @param string $videoId YouTube video ID
     * @param string $type YouTube thumbnail type (hqdefault, sddefault or maxresdefault)
     * @return string
     */
    public static function getThumbnail(string $videoId, string $type): string
    {
        if (in_array($type, self::$thumbnailTypes) === false) {
            $type = self::$thumbnailTypes[0];
        }

        return self::getEndpoint('images') . $videoId . '/' . $type . '.jpg';
    }

    /**
     * Create a YouTube API URL
     *
     * @param string $type API request type
     * @param string $parameter API request Parameter
     * @param string $apiKey YouTube API key
     * @return string Returns url
     */
    public static function getApi(string $type, string $parameter, string $apiKey)
    {
        $url = self::getEndpoint('api');
        $endpoint = self::$apiEndpoints[$type]['endpoint'];
        $query = [];

        foreach (self::$apiEndpoints[$type]['query'] as $name => $section) {
            if (is_array($section) === true) {
                $query[$name] = implode(',', $section);
            } elseif ($name === 'q') {
                $query[$name] = urlencode($parameter);
            } else {
                $query[$name] = $parameter;
            }
        }

        $query['prettyPrint'] = 'true';
        $query['key'] = $apiKey;

        return $url . $endpoint . '?' . http_build_query($query);
    }

    /**
     * Returns YouTube endpoint URL
     *
     * @param string $name Endpoint name
     * @return string
     */
    private static function getEndpoint(string $name)
    {
        return self::$endpoints[$name];
    }
}
