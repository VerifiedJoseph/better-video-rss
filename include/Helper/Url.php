<?php

namespace App\Helper;

use App\Configuration as Config;

class Url {

	/** @var array $endpoints YouTube endpoints */
	private static array $endpoints = array(
		'images' => 'https://i.ytimg.com/vi/',
		'nocookie' => 'https://www.youtube-nocookie.com/',
		'website' => 'https://www.youtube.com/',
		'feed' => 'https://www.youtube.com/feeds/videos.xml',
		'api' => 'https://www.googleapis.com/youtube/v3/'
	);

	/** @var array $thumbnailTypes Supported YouTube thumbnail types */
	private static array $thumbnailTypes = array(
		'hqdefault', 'sddefault', 'maxresdefault'
	);

	/**
	 * Create a feed URL for BetterVideoRss
	 *
	 * @param string $type Feed type
	 * @param string $id Feed id
	 * @param string $format Feed format
	 * @param boolean $embed Embed video status
	 * @return string
	 */
	public static function getFeed(string $type, string $id, string $format, bool $embed = false) {
		$url = Config::get('SELF_URL_PATH') . 'feed.php?' . $type . '_id=' . $id . '&format=' . $format;

		if ($embed === true) {
			$url .= '&embed_videos=true';
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
	public static function getImageProxy(string $videoId, string $feedType, string $feedId) {
		return Config::get('SELF_URL_PATH') . 'proxy.php?video_id=' . $videoId . '&' . $feedType . '_id=' . $feedId;
	}

	/**
	 * Create a YouTube RSS feed URL (https://www.youtube.com/feeds/videos.xml)
	 *
	 * @param string $type Feed type
	 * @param string $id channel or playlist id
	 * @return string
	 */
	public static function getRssFeed(string $type, string $id) {
		return self::getEndpoint('feed') . '?' . $type . '_id=' . $id;
	}

	/**
	 * Create a YouTube channel URL
	 *
	 * @param string $channelId YouTube channel ID
	 * @return string
	 */
	public static function getChannel(string $channelId) {
		return self::getEndpoint('website') . 'channel/' . $channelId;
	}

	/**
	 * Create a YouTube playlist URL
	 *
	 * @param string $playlistId YouTube playlist ID
	 * @return string
	 */
	public static function getPlaylist(string $playlistId) {
		return self::getEndpoint('website') . 'playlist?list=' . $playlistId;
	}

	/**
	 * Create a YouTube video URL
	 *
	 * @param string $videoId YouTube video ID
	 * @return string
	 */
	public static function getVideo(string $videoId) {
		return self::getEndpoint('website') . 'watch?v=' . $videoId;
	}

	/**
	 * Create a YouTube video embed URL
	 *
	 * @param string $videoId YouTube video ID
	 * @return string
	 */
	public static function getEmbed(string $videoId) {
		return self::getEndpoint('nocookie') . 'embed/' . $videoId;
	}

	/**
	 * Create a YouTube thumbnail URL
	 *
	 * @param string $videoID YouTube video ID
	 * @param string $type YouTube thumbnail type (hqdefault, sddefault or maxresdefault)
	 * @return string
	 */
	public static function getThumbnail(string $videoId, string $type) {
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
	 * @return string Returns url
	 */
	public static function getApi(string $type, string $parameter) {
		$url = self::getEndpoint('api');

		switch ($type) {
			case 'channel':
				$url .= 'channels?part=snippet,contentDetails&id='
					. $parameter . '&fields=etag,items(snippet(title,description,thumbnails(default(url))))';
				break;
			case 'playlist':
				$url .= 'playlists?part=snippet,contentDetails&id='
					. $parameter . '&fields=etag,items(snippet(title,description,thumbnails(default(url))))';
				break;
			case 'videos':
				$url .= 'videos?part=id,snippet,contentDetails,liveStreamingDetails&id='
					. $parameter . '&fields=etag,items(id,snippet(tags,liveBroadcastContent,thumbnails(standard(url),maxres(url))),contentDetails(duration),liveStreamingDetails(scheduledStartTime))';
				break;
			case 'searchChannels':
				$url .= 'search?part=id&fields=items(id(channelId))&q='
					. urlencode($parameter) . '&type=channel&maxResults=1';
				break;
			case 'searchPlaylists':
				$url .= 'search?part=id&fields=items(id(playlistId))&q='
					. urlencode($parameter) . '&type=playlist&maxResults=1';
				break;
		}

		return $url . '&prettyPrint=false&key=' . Config::get('YOUTUBE_API_KEY');
	}

	/**
	 * Returns YouTube endpoint URL
	 *
	 * @param string $name Endpoint name
	 * @return string
	 */
	private static function getEndpoint(string $name) {
		return self::$endpoints[$name];
	}
}
