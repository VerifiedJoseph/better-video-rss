<?php

namespace Helper;

use Configuration as Config;

class Url {

	/** @var array $thumbnailTypes Supported YouTube thumbnail types */
	private static array $thumbnailTypes = array(
		'hqdefault', 'sddefault', 'maxresdefault'
	);

	/**
	 * Create a feed URL 
	 *
	 * @param string $type Feed type
	 * @param string $id Feed id
	 * @param string $format Feed format
	 * @param boolean $embed Embed video status
	 * @return string
	 */
	public static function getFeed(string $type, string $id, string $format, bool $embed = false) {
		$url = Config::get('SELF_URL_PATH') . '?' . $type . '_id=' . $id . '&format=' . $format;

		if ($embed === true) {
			$url .= '&embed_videos=true';
		}

		return $url;
	}
	
	/**
	 * Create a YouTube channel URL 
	 *
	 * @param string $channelId YouTube channel ID
	 * @return string
	 */
	public static function getChannel(string $channelId) {
		return Config::getEndpoint('website') . 'channel/' . $channelId;
	}

	/**
	 * Create a YouTube playlist URL 
	 *
	 * @param string $playlistId YouTube playlist ID
	 * @return string
	 */
	public static function getPlaylist(string $playlistId) {
		return Config::getEndpoint('website') . 'playlist?list=' . $playlistId;
	}

	/**
	 * Create a YouTube video URL 
	 *
	 * @param string $videoId YouTube video ID
	 * @return string
	 */
	public static function getVideo(string $videoId) {
		return Config::getEndpoint('website') . 'watch?v=' . $videoId;
	}

	/**
	 * Create a YouTube video embed URL 
	 *
	 * @param string $videoId YouTube video ID
	 * @return string
	 */
	public static function getEmbed(string $videoId) {
		return Config::getEndpoint('nocookie') . 'embed/' . $videoId;
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

		return Config::getEndpoint('images') . $videoId . '/' . $type . '.jpg';
	}
}
