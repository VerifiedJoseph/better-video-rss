<?php

use \Curl\Curl;
use Configuration as Config;

class FeedUrlGenerator {

	/** @var string $apiEndpoint YouTube API Endpoint */
	private $apiEndpoint = 'https://www.googleapis.com/youtube/v3/';

	/**
	 * @var string $query Search query
	 */
	private $query = '';

	/**
	 * @var boolean $embedVideos Embed videos status
	 */
	private $embedVideos = false;

	/**
	 * @var string $feedId YouTube channel or playlist ID
	 */
	private $feedId = '';

	/**
	 * @var string $feedType Feed type (channel or playlist)
	 */
	private $feedType = 'channel';

	/**
	 * @var array $supportedTypes Supported feed types
	 */
	private $supportedTypes = array('channel', 'playlist');

	/**
	 * @var string $feedFormat Feed Format
	 */
	private $feedFormat = 'rss';

	/**
	 * @var array $supportedFormats Feed formats
	 */
	private $supportedFormats = array();

	/**
	 * @var boolean $error Error status
	 */
	private $error = false;

	/**
	 * @var string $errorMessage Error Message
	 */
	private $errorMessage = '';

	/**
	 * Constructor
	 */
	public function __construct(array $supportedFormats) {
		$this->supportedFormats = $supportedFormats;

		$this->checkInputs();

		if (!empty($this->query)) {

			if ($this->feedType === 'channel') {
				$this->findChannel();
			}

			if ($this->feedType === 'playlist') {
				$this->findPlaylist();
			}
		}
	}

	/**
	 * Check user inputs
	 *
	 * @throws Exception if a query parameter is not given
	 * @throws Exception if a type parameter is not given
	 */
	private function checkInputs() {

		if (isset($_POST['query'])) {

			if (empty($_POST['query'])) {
				throw new Exception('Query parameter not given.');
			}

			if (empty($_POST['type'])) {
				throw new Exception('Type parameter not given.');
			}

			if (isset($_POST['type']) && in_array($_POST['type'], $this->supportedTypes)) {
				$this->feedType = $_POST['type'];
			}

			if (isset($_POST['format']) && in_array($_POST['format'], $this->supportedFormats)) {
				$this->feedFormat = $_POST['format'];
			}

			$this->query = $_POST['query'];

			if (isset($_POST['embed_videos'])) {
				$this->embedVideos = true;
			}

		}
	}

	/**
	 * Display HTML
	 *
	 * @echo string $html
	 */
	public function display() {
		$link = '';
		$error = '';
		$channelLink = '';
		$playlistLink = '';

		if (!empty($this->feedId) && $this->error === false) {
			$url = Config::get('SELF_URL_PATH') . '?' . $this->feedType . '_id=' . $this->feedId . '&format=' . $this->feedFormat;

			if ($this->embedVideos === true) {
				$url .= '&embed_videos=true';
			}

			$link = <<<HTML
<p>Feed URL: <a href="{$url}">{$url}</a></p>
HTML;
		} else {

			$error = <<<HTML
<p>{$this->errorMessage}</p>
HTML;
		}

		if ($this->feedType === 'channel') {
			$channelLink = $link;
		}

		if ($this->feedType === 'playlist') {
			$playlistLink = $link;
		}

			$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<title>BetterYouTubeRss</title>
	<meta name="robots" content="noindex, follow">
	<link rel="stylesheet" type="text/css" href="static/style.css" />
</head>
<body>
	<div id="header" class="center">
		BetterYouTubeRss
	</div>
	<div id="main">
		<div id="items">
			<div class="item">
				{$error}
				<h2>Channel</h2>
				<form action="" method="post">
					Channel: <input style="width:280px;" name="query" type="input" placeholder="Username, Channel ID or Channel Title" required><br>
					Embed videos: <input type="checkbox" name="embed_videos" value="yes"><br>
					Feed format: 
					<select name="format">
						<option value="rss">RSS</option>
						<option value="html">HTML</option>
						<option value="json">JSON</option>
					</select><br/>
					<input type="hidden" name="type" value="channel">
					<button style="width:80px;" type="submit">Generate</button>
				</form><br>
				{$channelLink}
			</div>
			<div class="item">
				<h2>Playlist</h2>
				<form action="" method="post">
					Playlist: <input style="width:280px;" name="query" type="input" placeholder="Playlist ID or title" required><br>
					Embed videos: <input type="checkbox" name="embed_videos" value="yes"><br>
					Feed format: 
					<select name="format">
						<option value="rss">RSS</option>
						<option value="html">HTML</option>
						<option value="json">JSON</option>
					</select><br/>
					<input type="hidden" name="type" value="playlist">
					<button style="width:80px;" type="submit">Generate</button>
				</form><br>
				{$playlistLink}
			</div>
			<div class="item">
				<a href="tools.php">Tools</a> - <a href="https://github.com/VerifiedJoseph/BetterYouTubeRss">Source Code</a>
			</div>
		</div>
	</div>
</body>
</html>
HTML;

		echo $html;
	}

	/**
	 * Find channel
	 */
	private function findChannel() {

		if ($this->isChannelId($this->query) === true) {
			$this->feedId = $this->query;

		} else {
			$this->searchApi($this->query);
		}
	}

	/**
	 * Find playlist
	 */
	private function findPlaylist() {

		if ($this->isPlaylistId($this->query) === true) {
			$this->feedId = $this->query;

		} else {
			$this->searchApi($this->query);
		}
	}

	/**
	 * Is query string a channel ID
	 *
	 * @param string $query Query string
	 * @return boolean
	 */
	private function isChannelId(string $query) {

		if (substr($query, 0, 2) === 'UC' && mb_strlen($query, 'utf8') >= 24) {
			return true;
		}

		return false;
	}

	/**
	 * Is query string a playlist ID
	 *
	 * @param string $query Query string
	 * @return boolean
	 */
	private function isPlaylistId(string $query) {

		// Channel uploads playlist
		if (substr($query, 0, 2) === 'UU' && mb_strlen($query, 'utf8') >= 24) {
			return true;
		}

		// Standard playlist
		if (substr($query, 0, 2) === 'PL' && mb_strlen($query, 'utf8') >= 34) {
			return true;
		}

		return false;
	}

	/**
	 * Search YouTube data API for channel or playlist
	 *
	 * @param string $query Query string
	 * @throws Exception if a curl error has occurred
	 * @throws Exception if a API error has occurred
	 * @throws Exception if the channel or playlist was not found
	 */
	private function searchApi(string $query) {

		try {
			$api = new Api();

			if ($this->feedType === 'channel') {
				$response = $api->searchChannels($query);
			}

			if ($this->feedType === 'playlist') {
				$response = $api->searchPlaylists($query);
			}

			if (empty($response->items)) {
				throw new Exception($this->feedType . ' not found');
			}

			if ($this->feedType === 'channel') {
				$this->feedId = $response->items['0']->snippet->channelId;
			}

			if ($this->feedType === 'playlist') {
				$this->feedId = $response->items['0']->id->playlistId;
			}

		} catch (Exception $e) {
			$this->error = true;
			$this->errorMessage = $e->getMessage();
		}
	}
}
