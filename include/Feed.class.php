<?php

abstract class Feed {

	protected $data = array();
	protected $feed = '';

	protected $urlRegex = '/https?:\/\/(?:www\.)?(?:[a-zA-Z0-9-.]{2,256}\.[a-z]{2,20})(\:[0-9]{2,4})?(?:\/[a-zA-Z0-9@:%_\+.,~#"!?&\/\/=\-*]+|\/)?/';

	protected $embedUrl = 'https://www.youtube.com';
	protected $embedUrlNoCookie = 'https://www.youtube-nocookie.com';

	/**
	 * Constructor
	 *
	 * @param array $data Cache/fetch data
	 * @param boolean $embedVideos Embed YouTube videos in feed
	 */
	public function __construct(array $data, bool $embedVideos = false) {
		$this->data = $data;
		$this->embedVideos = $embedVideos;
	}

	/**
	 * Build feed
	 */
	abstract public function build();

	/**
	 * Returns feed
	 *
	 * @return string
	 */
	public function get() {
		return $this->feed;
	}

	/**
	 * Build feed itmes
	 *
	 * @return string Items as XML
	 */
	abstract protected function buildItmes();

	/**
	 * Build item categories
	 *
	 * @param array $categories Item categories
	 * @return string Categories as XML
	 */
	abstract protected function buildCategories(array $categories);

	/**
	 * Build item content (description)
	 *
	 * @param array $video Video data
	 * @return string Item content as HTML
	 */
	abstract protected function buildContent(array $video);

	/**
	 * Format video description
	 * Converts URLs to HTMl links
	 *
	 * @param string $description
	 * @return string Formatted video description
	 */
	protected function formatDescription(string $description) {

		if (empty($description)) {
			return ' ';
		}

		$formatted = '';
		$lines = explode("\n", $description);

		foreach ($lines as $index => $line) {
			if(preg_match($this->urlRegex, $line, $matches)) {
				$line = str_replace($matches[0], '<a target="_blank" href="' . $matches[0] . '">' . $matches[0] . '</a>', $line);
			}

			$formatted .= $line . '<br/>';
		}

		return $formatted;
	}

	/**
	 * Convert special characters to HTML entities
	 *
	 * @param string $text
	 * @return string String with encoded characters
	 */
	protected function xmlEncode($text) {
		return htmlspecialchars($text, ENT_XML1);
	}
}
