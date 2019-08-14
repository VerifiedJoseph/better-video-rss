<?php

use \Curl\Curl;

class FeedUrlGenerator {
	/**
	 * @var string $query Search query
	 */
	private $query = '';

	/**
	 * @var string $channelId YouTube channel ID
	 */
	private $channelId = '';

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
	public function __construct() {
		$this->checkInputs();

		if (!empty($this->query)) {
			$this->findChannel();
		}

		$this->display();
	}

	/**
	 * Check user inputs
	 *
	 * @throws Exception if a query is not given
	 */
	private function checkInputs() {

		if (isset($_POST['query'])) {

			if (empty($_POST['query'])) {
				throw new Exception('Query parameter not given.');
			}

			$this->query = $_POST['query'];
		}
	}

	/**
	 * Display HTML
	 *
	 * @echo string $html
	 */
	private function display() {

		$link = '';
		$error = '';

		if (!empty($this->channelId) && $this->error === false) {
			$url = Config::get('SELF_URL_PATH') . 'BetterYouTubeRss.php?channel_id=' . $this->channelId;
			$link = <<<HTML
<p>Feed URL: <a href="{$url}">{$url}</a></p>
HTML;
		}

		if ($this->error) {
			$link = <<<HTML
<p>{$this->errorMessage}</p>
HTML;
		}

		$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Feed URL Generator</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
	<div id="header"><a href="index.php">Tools</a> &#187; Feed URL Generator</div>
	<div id="main">
		<form action="" method="post">
			<input style="width:280px;" name="query" type="input" placeholder="Username, Channel ID or Channel Title">
			<button style="width:80px;" type="submit">Generate</button>
		</form><br>
		{$link}{$error}
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
			$this->channelId = $this->query;

		} else {
			$this->searchApi($this->query);
		}
	}

	/**
	 * Is query string a channel ID
	 *
	 8 param string $query Query string
	 * @return boolean
	 */
	private function isChannelId(string $query) {

		if (substr($query, 0, 2) === 'UC' && mb_strlen($query, 'utf8') >= 24) {
			return true;
		}

		return false;
	}

	/**
	 * Is query string a channel ID
	 *
	 8 param string $query Query string
	 * @throws Exception if a curl error has occurred
	 * @throws Exception if a API error has occurred
	 * @throws Exception if the channel was not found
	 */
	private function searchApi(string $query) {

		try {

			$url = 'https://www.googleapis.com/youtube/v3/search?part=snippet&fields=items(snippet(channelId))&q=' .
				urlencode($query) . '&type=channel&maxResults=1&prettyPrint=false&key=' . Config::get('YOUTUBE_API_KEY');

			$curl = new Curl();
			$curl->get($url);

			$statusCode = $curl->getHttpStatusCode();
			$errorCode = $curl->getCurlErrorCode();
			$response =	$curl->response;

			if ($errorCode !== 0) {
				throw new Exception('Error: ' . $curl->errorCode . ': ' . $curl->errorMessage);
			}

			if ($statusCode !== 200) {
				throw new Exception('An API error occurred.');
			}

			if (empty($response->items)) {
				throw new Exception('Channel not found');
			}

			$this->channelId = $response->items['0']->snippet->channelId;

		} catch (Exception $e) {
			$this->error = true;
			$this->errorMessage = $e->getMessage();
		}
	}
}
