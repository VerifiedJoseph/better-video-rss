<?php

class BetterYouTubeRss {

	/** @var string $feedId YouTube channel or playlist ID */
	private $feedId = '';

	/** @var string $feedType Feed type (channel or playlist) */
	private $feedType = 'channel';

	/** @var boolean $embedVideos Embed videos status */
	private $embedVideos = false;
	
	/** @var string $feedFormat Feed format */
	private $feedFormat = 'rss';

	/**
	 * @var array $supportedFormats Supported feed formats 
	 */
	private $supportedFeedFormats = array('rss', 'html');
	
	/** @var array $parts Cache and fetch parts */
	private $parts = array('details', 'playlist', 'videos');

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->checkInputs();
	}

	/**
	 * Check user inputs
	 *
	 * @throws Exception If a channel ID is not given.
	 */
	private function checkInputs() {

		if (isset($_GET['channel_id']) && empty($_GET['channel_id'])) {
			throw new Exception('No channel ID parameter given.');
		}

		if (isset($_GET['format']) && in_array($_GET['format'], $this->supportedFeedFormats)) {
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
				throw new Exception('No channel ID parameter given.');
			}

			$this->feedId = $_GET['playlist_id'];
			$this->feedType = 'playlist';
		}

		if (isset($_GET['embed_videos'])) {
			$this->embedVideos = filter_var($_GET['embed_videos'], FILTER_VALIDATE_BOOLEAN);
		}
	}

	public function generateFeed() {
		
		$cache = new Cache(
			$this->getFeedId(),
			$this->getFeedType()
		);

		$cache->load();

		$fetch = new Fetch(
			$cache->getData()
		);

		foreach($this->getParts() as $part) {
			$parameter = '';

			if ($cache->expired($part)) {

				if (Config::get('ENABLE_HYBRID_MODE') === true && $part === 'playlist') {
					$parameter = $this->getFeedId();
				}

				if ($part === 'videos') {
					$parameter = $cache->getExpiredVideos();

					if (empty($parameter)) {
						continue;
					}
				}

				$fetch->part($part, $parameter);
				$cache->update($part, $fetch->getData($part));
			}
		}

		$cache->save();

		switch ($this->feedFormat) {
    		case 'rss':

				$feed = new FeedXml(
					$cache->getData(),
					$this->getEmbedStatus()
				);

				$feed->build();
				Output::xml($feed->get());

        		break;
			case 'html':

				$feed = new FeedHtml(
					$cache->getData(),
					$this->getEmbedStatus()
				);
	
				$feed->build();
				Output::html($feed->get());
		}
	}
	
	public function generateIndex() {
		
		$generator = new FeedUrlGenerator();
		$generator->display();
		
	}

	/**
	 * Return Feed type
	 *
	 * @return string
	 */
	public function getFeedType() {
		return $this->feedType;
	}

	/**
	 * Return feed ID
	 *
	 * @return string
	 */
	public function getFeedId() {
		return $this->feedId;
	}

	/**
	 * Return embed video status
	 *
	 * @return boolean
	 */
	public function getEmbedStatus() {
		return $this->embedVideos;
	}

	/**
	 * Return cache and fetch parts
	 *
	 * @return array
	 */
	public function getParts() {
		return $this->parts;
	}
}
