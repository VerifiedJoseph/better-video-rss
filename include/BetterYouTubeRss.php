<?php

use Configuration as Config;

class BetterYouTubeRss {

	/** @var string $feedId YouTube channel or playlist ID */
	private string $feedId = '';

	/** @var string $feedType Feed type (channel or playlist) */
	private string $feedType = 'channel';

	/** @var boolean $embedVideos Embed videos status */
	private bool $embedVideos = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->feedFormat = Config::getDefaultFeedFormat();
		$this->checkInputs();
	}

	/**
	 * Generate feed or index page.
	 */
	public function generate() {

		if (empty($this->getFeedId()) === false) {
			$this->generateFeed();

		} else {
			$this->generateIndex();
		}
	}

	/**
	 * Return Feed type
	 *
	 * @return string
	 */
	private function getFeedType() {
		return $this->feedType;
	}

	/**
	 * Return feed ID
	 *
	 * @return string
	 */
	private function getFeedId() {
		return $this->feedId;
	}

	/**
	 * Return embed video status
	 *
	 * @return boolean
	 */
	private function getEmbedStatus() {
		return $this->embedVideos;
	}

	/**
	 * Check user inputs
	 *
	 * @throws Exception if a invalid format parameter is given.
	 * @throws Exception if a empty channel ID parameter is given.
	 * @throws Exception if a empty playlist ID parameter is given.
	 */
	private function checkInputs() {

		if (isset($_GET['format'])) {
			$format = strtolower($_GET['format']);

			if (in_array($format, Config::getFeedFormats()) === false) {
				throw new Exception('Invalid format parameter given.');
			}

			$this->feedFormat = $format;
		}

		if (isset($_GET['channel_id'])) {

			if (empty($_GET['channel_id'])) {
				throw new Exception('No channel ID parameter given.');
			}

			$this->feedId = $_GET['channel_id'];
			$this->feedType = 'channel';
		}

		if (isset($_GET['playlist_id'])) {

			if (empty($_GET['playlist_id'])) {
				throw new Exception('No playlist ID parameter given.');
			}

			$this->feedId = $_GET['playlist_id'];
			$this->feedType = 'playlist';
		}

		if (isset($_GET['embed_videos'])) {
			$this->embedVideos = filter_var($_GET['embed_videos'], FILTER_VALIDATE_BOOLEAN);
		}
	}

	/**
	 * Generate feed
	 */
	private function generateFeed() {

		$data = new Data(
			$this->getFeedId(),
			$this->getFeedType()
		);

		$fetch = new Fetch(
			$this->getFeedId(),
			$this->getFeedType()
		);

		foreach ($data->getExpiredParts() as $part) {
			$parameter = '';

			if ($part === 'feed') {
				$fetch->feed();
				$data->updateFeed($fetch->getResponse());
			}

			if ($part === 'details') {
				$fetch->api(
					$part,
					$parameter,
					$data->getPartEtag($part)
				);

				$data->updateDetails($fetch->getResponse());
			}

			if ($part === 'videos') {
				$parameter = $data->getExpiredVideos();

				if (empty($parameter)) {
					continue;
				}

				$fetch->api(
					$part,
					$parameter,
					$data->getPartEtag($part)
				);

				$data->updateVideos($fetch->getResponse());
			}
		}

		$formatClass = 'Format\\' . ucfirst($this->feedFormat);

		$format = new $formatClass(
			$data->getData(),
			$this->getEmbedStatus()
		);

		$format->build();

		Output::feed(
			$format->get(),
			$format->getContentType()
		);
	}

	/**
	 * Generate index page with FeedUrlGenerator
	 */
	private function generateIndex() {
		$generator = new FeedUrlGenerator();
		$generator->display();
	}
}
