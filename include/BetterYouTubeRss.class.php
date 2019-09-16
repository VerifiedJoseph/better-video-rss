<?php

class BetterYouTubeRss {

	/** @var string $feedId YouTube channel or playlist ID */
	private $feedId = '';

	/** @var string $feedType Feed type (channel or playlist) */
	private $feedType = 'channel';

	/** @var boolean $embedVideos Embed videos status */
	private $embedVideos = false;

	/** @var string $feedFormat Default feed format */
	private $feedFormat = 'rss';

	/** @var array $supportedFormats Supported feed formats */
	private $supportedFeedFormats = array('rss', 'html', 'json');

	/** @var array $parts Cache and fetch parts */
	private $parts = array('details', 'playlist', 'videos');

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->checkInputs();
	}

	/**
	 * Generate feed or index page.
	 */
	public function generate() {

		if (!empty($this->getFeedId())) {
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
	 * Return supported feed formats
	 *
	 * @return string
	 */
	private function getFeedFormats() {
		return $this->supportedFeedFormats;
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
	 * Return cache and fetch parts
	 *
	 * @return array
	 */
	private function getParts() {
		return $this->parts;
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

			if (!in_array($_GET['format'], $this->getFeedFormats())) {
				throw new Exception('Invalid format parameter given.');
			}

			$this->feedFormat = $_GET['format'];
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

		$cache = new Cache(
			$this->getFeedId()
		);
		$cache->load();
		
		$data = new Data(
			$this->getFeedId(),
			$this->getFeedType()
		);

		$data->setData(
			$cache->getData()
		);

		$fetch = new Fetch(
			$this->getFeedId(),
			$this->getFeedType()
		);

		foreach ($data->getExpiredParts() as $part) {
			$data->setWorkingPart($part);

			$parameter = '';

			if (Config::get('ENABLE_HYBRID_MODE') === true && $part === 'playlist') {
				$fetch->feed();

				$data->handleRssResponse(
					$fetch->getResponse()
				);
			} else {
				
				if ($part === 'videos') {
					$parameter = $data->getExpiredVideos();

					if (empty($parameter)) {
						continue;
					}
				}
				
				if ($part === 'playlist') {
					$parameter = $data->getPlaylistId();
				}

				$fetch->api(
					$part,
					$parameter,
					$data->getPartEtag()
				);
				$data->handleApiResponse(
					$fetch->getResponse()
				);
			}
		}

		$cache->save(
			$data->getData(),
			$data->getUpdateStatus()
		);

		if (!in_array($this->feedFormat, $this->getFeedFormats())) {
			throw new Exception('Invalid format parameter given.');
		}

		$formatClass = ucfirst($this->feedFormat) . 'Format';

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

		$generator = new FeedUrlGenerator(
			$this->getFeedFormats()
		);
		$generator->display();

	}
}
