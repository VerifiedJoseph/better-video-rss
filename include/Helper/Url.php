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

    /** @var array<string, string> $apiEndpoints YouTube API endpoints */
    private static array $apiEndpoints = [];

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

        switch ($type) {
            case 'channel':
            case 'playlist':
            case 'videos':
                $url .= self::getApiEndpoint($type, $parameter);
                break;
            case 'searchChannels':
            case 'searchPlaylists':
                $url .= self::getApiEndpoint($type, urlencode($parameter));
                break;
        }

        return $url . '&prettyPrint=false&key=' . $apiKey;
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

    /**
     * Returns YouTube API endpoint URL with parameters
     *
     * @param string $name Endpoint name
     * @param string $var Endpoint variable
     * @return string
     */
    private static function getApiEndpoint(string $name, string $var)
    {
        if (self::$apiEndpoints === []) {
            self::$apiEndpoints = Json::decodeToArray(
                (string) file_get_contents('include/api-endpoints.json'),
            );
        }

        return str_replace('{param}', $var, self::$apiEndpoints[$name]);
    }
}
