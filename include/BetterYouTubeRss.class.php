<?php

class BetterYouTubeRss {

	/** @var string $channelId YouTube channel ID */
	private $channelId = '';
	private $embedVideos = false;

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

		if (empty($_GET['channel_id'])) {
			throw new Exception('No channel ID parameter given.');
		}

		$this->channelId = $_GET['channel_id'];

		if (isset($_GET['embed_videos'])) {
			$embedVideos = filter_var($_GET['embed_videos'], FILTER_VALIDATE_BOOLEAN);  
		}
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
