<?php

use \Curl\Curl;
use Configuration as Config;
use Helper\Validate;
use Helper\Url;

class Index {

	/** @var string $query Search query */
	private string $query = '';

	/** @var boolean $embedVideos Embed videos status */
	private bool $embedVideos = false;

	/** @var string $feedId YouTube channel or playlist ID */
	private string $feedId = '';

	/** @var string $feedType Feed type (channel or playlist) */
	private string $feedType = 'channel';

	/** @var array $supportedTypes Supported feed types */
	private array $supportedTypes = array('channel', 'playlist');

	/** @var string $feedFormat Feed Format */
	private string $feedFormat = '';

	/** @var bool $fromUrl Query string is from a URL */
	private bool $fromUrl = false;

	/** @var boolean $error Error status */
	private bool $error = false;

	/** @var string $errorMessage Error Message */
	private string $errorMessage = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->feedFormat = Config::getDefaultFeedFormat();

		try {
			$this->checkInputs();
			$this->generate();

		} catch(Exception $e) {
			$this->error = true;
			$this->errorMessage = $e->getMessage();
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
		$fromUrlLink = '';

		$version = Config::getVersion();

		if ($this->error === true) {
			$error = <<<HTML
				<div id="error"><strong>{$this->errorMessage}</strong></div>
			HTML;
		}

		if ($this->error === false && empty($this->feedId) === false) {
			$url = Url::getFeed($this->feedType, $this->feedId, $this->feedFormat, $this->embedVideos);

			$link = <<<HTML
				Feed URL: <a href="{$url}">{$url}</a>
			HTML;

			if ($this->fromUrl === true) {
				$fromUrlLink = $link;

			} elseif ($this->feedType === 'channel') {
				$channelLink = $link;

			} elseif ($this->feedType === 'playlist') {
				$playlistLink = $link;
			}
		}

		$selectHtml = $this->createFormatSelect();
		$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<title>BetterVideoRss</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="robots" content="noindex, nofollow">
	<link rel="stylesheet" type="text/css" href="static/style.css" />
</head>
<body>
	<header class="center">
		BetterVideoRss
	</header>
	<div id="main">
		<div id="items">
			{$error}
			<div class="item">
				<h2>Channel</h2>
				<form action="" method="post">
					<input type="hidden" name="type" value="channel">
					<p class="margin">
						<label>Channel:
							<input class="input" name="query" type="input" placeholder="Username, Channel ID or Channel Title" required>
						</label>
					</p>
					<p class="margin">
						<label>Embed videos:
							<input type="checkbox" name="embed_videos" value="yes">
						</label>
					</p>
					<p class="margin">
						<label>Feed format: 
							{$selectHtml}
						</label>
					</p>
					<p class="margin">
						<button type="submit">Generate</button>
					</p>
				</form>
				<p class="feedUrl">{$channelLink}</p>
			</div>
			<div class="item">
				<h2>Playlist</h2>
				<form action="" method="post">
					<input type="hidden" name="type" value="playlist">
					<p class="margin">
						<label>Playlist:
						<input class="input" name="query" type="input" placeholder="Playlist ID or title" required>
						</label>
					</p>
					<p class="margin">
						<label>Embed videos:
							<input type="checkbox" name="embed_videos" value="yes">
						</label>
					</p>
					<p class="margin">
						<label>Feed format: 
							{$selectHtml}
						</label>
					</p>
					<p class="margin">
						<button type="submit">Generate</button>
					</p>
				</form>
				<p class="feedUrl">{$playlistLink}</p>
			</div>
			<div class="item">
				<h2>URL</h2>
				<form action="" method="post">
					<input type="hidden" name="type" value="url">
					<p class="margin">
						<label>URL:
						<input class="input" name="query" type="input" placeholder="youtube.com URL" required>
						</label>
					</p>
					<p class="margin">
						<label>Embed videos:
							<input type="checkbox" name="embed_videos" value="yes">
						</label>
					</p>
					<p class="margin">
						<label>Feed format: 
							{$selectHtml}
						</label>
					</p>
					<p class="margin">
						<button type="submit">Generate</button>
					</p>
				</form>
				<p class="feedUrl">{$fromUrlLink}</p>
			</div>
			<div class="item">
				<p><a href="tools.html">Tools</a> - <a href="https://github.com/VerifiedJoseph/BetterVideoRss">Source Code</a></p><span class="small">version: {$version}</span>
			</div>
		</div>
	</div>
</body>
</html>
HTML;

		echo $html;
	}

	/**
	 * Check user inputs
	 *
	 * @throws Exception if a query parameter is not given
	 * @throws Exception if a type parameter is not given
	 * @throws Exception if a query parameter is not a valid YouTube URL when type is URL
	 */
	private function checkInputs() {
		if (isset($_POST['query'])) {

			if (empty($_POST['query'])) {
				throw new Exception('Query parameter not given.');
			}

			if (isset($_POST['type']) === false || empty($_POST['type'])) {
				throw new Exception('Type parameter not given.');
			}

			if (in_array($_POST['type'], $this->supportedTypes)) {
				$this->feedType = $_POST['type'];
			}

			if ($_POST['type'] === 'url') {
				$this->fromUrl = true;

				if (Validate::YouTubeUrl($_POST['query']) === false) {
					throw new Exception('URL is not a valid YouTube URL.');
				}
			}

			if (isset($_POST['format']) && in_array($_POST['format'], Config::getFeedFormats())) {
				$this->feedFormat = $_POST['format'];
			}

			$this->query = $_POST['query'];
		}

		if (isset($_POST['embed_videos'])) {
			$this->embedVideos = true;
		}
	}

	/**
	 * Generate feed URL
	 *
	 * @throws Exception if a query parameter is not a supported YouTube URL
	 */
	private function generate() {
		if (empty($this->query) === false) {

			if ($this->fromUrl === true) {
				$detect = new Detect();

				if ($detect->fromUrl($this->query) === false) {
					throw new Exception('Unsupported YouTube URL.');
				}

				$this->feedType = $detect->getType();
				$this->query = $detect->getValue();
			}

			if ($this->feedType === 'channel') {
				$this->findChannel();
			}

			if ($this->feedType === 'playlist') {
				$this->findPlaylist();
			}
		}
	}

	/**
	 * Find channel
	 */
	private function findChannel() {
		if (Validate::channelId($this->query) === true) {
			$this->feedId = $this->query;

		} else {
			$this->searchApi($this->query);
		}
	}

	/**
	 * Find playlist
	 */
	private function findPlaylist() {
		if (Validate::playlistId($this->query) === true) {
			$this->feedId = $this->query;

		} else {
			$this->searchApi($this->query);
		}
	}

	/**
	 * Search YouTube data API for channel or playlist
	 *
	 * @param string $query Query string
	 * @throws Exception if the channel or playlist was not found
	 */
	private function searchApi(string $query) {
		$api = new Api();

		if ($this->feedType === 'channel') {
			$response = $api->searchChannels($query);
		}

		if ($this->feedType === 'playlist') {
			$response = $api->searchPlaylists($query);
		}

		if (empty($response->items)) {
			throw new Exception(ucfirst($this->feedType) . ' not found');
		}

		if ($this->feedType === 'channel') {
			$this->feedId = $response->items['0']->id->channelId;
		}

		if ($this->feedType === 'playlist') {
			$this->feedId = $response->items['0']->id->playlistId;
		}
	}

	/**
	 * Create feed format drop-down
	 *
	 * @return string $html
	 */
	private function createFormatSelect() {
		$html = '<select name="format">';

		foreach (Config::getFeedFormats() as $key => $format) {
			$name = strtoupper($format);

			$html .= <<<HTML
				<option value="{$format}">$name</option>
			HTML;
		}

		return $html .= '</select>';
	}
}
