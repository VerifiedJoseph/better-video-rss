<?php

class BetterYouTubeRss {

	/** @var string $channelId YouTube channel ID */
	private $channelId = '';
	
	/** @var boolean $embedVideos Embed videos status */
	private $embedVideos = false;
	
	/** @var string $feedFormat Feed format */
	private $feedFormat = 'rss';

	/**
	 * @var array $supportedFormats Supported feed formats 
	 */
	private $supportedFeedFormats = array('rss', 'html');
	
	/** @var array $parts Cache and fetch parts */
	private $parts = array('channel', 'playlist', 'videos');

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
		
		if (!empty($_GET['channel_id'])) {
			$this->channelId = $_GET['channel_id'];
		}

		if (isset($_GET['embed_videos'])) {
			$this->embedVideos = filter_var($_GET['embed_videos'], FILTER_VALIDATE_BOOLEAN);
		}
	}

	public function generateFeed() {
		
		$cache = new Cache(
			$this->getChannelId()
		);

		$cache->load();

		$fetch = new Fetch(
			$cache->getData()
		);

		foreach($this->getParts() as $part) {
			$parameter = '';

			if ($cache->expired($part)) {

				if (Config::get('ENABLE_HYBRID_MODE') === true && $part === 'playlist') {
					$parameter = $this->getChannelId();
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
	 * Return Channel ID
	 *
	 * @return string
	 */
	public function getChannelId() {
		return $this->channelId;
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
