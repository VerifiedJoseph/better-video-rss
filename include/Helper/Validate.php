<?php

namespace Helper;

use Configuration as Config;

class Validate {

	/** @var string $channelIdRegex channel ID regex */
	private static string $channelIdRegex = '/^UC[a-zA-Z0-9_-]+$/';

	/** @var string $playlistIdRegex playlist ID regex */
	private static string $playlistIdRegex = '/^(?:UU|PL)[a-zA-Z0-9_-]+$/';
	
	/** @var string $videoIdRegex Video ID regex */
	private static string $videoIdRegex = '/^[a-zA-Z0-9_-]+$/';

	/** @var string $youTubeUrlRegex YouTube URL regex */
	private static string $youTubeUrlRegex = '/^(?:https?:\/\/)?(?:www\.)?youtube\.com/';

	/**
	 * Validate a feed format
	 *
	 * @param string $format Feed format
	 * @return boolean
	 */
	public static function feedFormat(string $format) {
		if (in_array($format, Config::getFeedFormats())) {
			return true;
		}

		return false;
	}

	/**
	 * Validate a channel ID
	 *
	 * @param string $channelId
	 * @return boolean
	 */
	public static function channelId(string $channelId) {
		if(preg_match(self::$channelIdRegex, $channelId)) {
			return true;
		}

		return false;
	}

	/**
	 * Validate a playlist ID
	 *
	 * @param string $playlistId
	 * @return boolean
	 */
	public static function playlistId(string $playlistId) {
		if(preg_match(self::$playlistIdRegex, $playlistId)) {
			return true;
		}

		return false;
	}

	/**
	 * Validate a video ID
	 *
	 * @param string $playlistId
	 * @return boolean
	 */
	public static function videoId(string $videoId) {
		if(preg_match(self::$videoIdRegex, $videoId)) {
			return true;
		}

		return false;
	}

	/**
	 * Validate a YouTube URL
	 *
	 * @param string $url
	 * @return boolean
	 */
	public static function YouTubeUrl(string $url) {
		if(preg_match(self::$youTubeUrlRegex, $url)) {
			return true;
		}

		return false;
	}

	/**
	 * Validate self URL
	 *
	 * @param string $url
	 * @return boolean
	 */
	public static function selfUrl(string $url) {
		if (substr($url, -1) === '/') {
			return true;
		}

		return false;
	}

	/**
	 * Validate an absolute path.
	 *
	 * @param string $path Cache path
	 * @return boolean
	 */
	public static function absolutePath(string $path) {
		if (substr($path, 0, 1) === '/' || strpos($path, ':\\') !== false) {
			return true;
		}

		return false;
	}
}
