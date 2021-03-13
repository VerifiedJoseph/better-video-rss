<?php

class Detect {

	/** @var array $type Feed type detected from URL */
	private string $type = '';

	/** @var array $value Vaule (channel id, username etc) extracted from URL */
	private string $value = '';

	/**
	 * @var string $channelUrlRegex Channel URL Regex
	 *
	 * Supported format:
	 *  https://www.youtube.com/channel/UCBa659QWEk1AI4Tg--mrJ2A
	 */
	private string $channelUrlRegex = '/youtube\.com\/channel\/(UC[\w-]+)/';

	/**
	 * @var string $playlistUrlRegex Playlist URL regex
	 *
	 * Supported formats:
	 * 	https://www.youtube.com/playlist?list=PLzJtNZQKmXCtHYHWR-uvUpGHbKKWBOARC
	 * 	https://www.youtube.com/playlist?list=UUBa659QWEk1AI4Tg--mrJ2A
	 *	https://www.youtube.com/watch?v=TfVYxnhuEdU&list=UUBa659QWEk1AI4Tg--mrJ2A
	 */
	private string $playlistUrlRegex = '/youtube\.com\/(?:playlist\/\?|[\w?=&]+)list=((?:PL|UU)[\w-]+)/';

	/**
	 * @var string $usernameUrlRegex Channel username URL regex
	 *
	 * Supported formats:
	 *  https://www.youtube.com/c/TomScottGo
	 *  https://www.youtube.com/user/enyay
	 */
	private string $usernameUrlRegex = '/youtube\.com\/(?:c|user)\/([a-zA-z0-9]+)/';

	/**
	 * @var string $rssFeedUrlRegex YouTube RSS feed URL regex
	 *
	 * Supported formats:
	 *  https://www.youtube.com/feeds/videos.xml?user=enyay
	 *  https://www.youtube.com/feeds/videos.xml?channel_id=UCBa659QWEk1AI4Tg--mrJ2A
	 *  https://www.youtube.com/feeds/videos.xml?playlist_id=PLzJtNZQKmXCtHYHWR-uvUpGHbKKWBOARC
	 */
	private string $rssFeedUrlRegex = '/youtube\.com\/feeds\/videos\.xml\?(channel_id|user|playlist_id)=([\w-]+)/';

	/**
	 * Find and extract channel ID, channel username or playlist ID from a URL using regex
	 *
	 * @param string $url URL
	 * @return boolean
	 */
	public function fromUrl($url) {

		if (preg_match($this->channelUrlRegex, $url, $match) || preg_match($this->usernameUrlRegex, $url, $match)) {
			$this->type = 'channel';
			$this->value = $match[1];

			return true;
		}

		if (preg_match($this->playlistUrlRegex, $url, $match)) {
			$this->type = 'playlist';
			$this->value = $match[1];

			return true;
		}

		if (preg_match($this->rssFeedUrlRegex, $url, $match)) {
			$this->type = 'channel';

			if ($match[1] == 'playlist_id') {
				$this->type = 'playlist';
			}

			$this->value = $match[2];

			return true;
		}

		return false;
	}

	/**
	 * Returns type of feed detected (channel or playlist)
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns value extracted with regex
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}
}
