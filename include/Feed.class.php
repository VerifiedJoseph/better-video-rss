<?php

class Feed {

	private $data = array();
	private $feed = '';

	private $urlRegex = '/https?:\/\/(?:www\.)?(?:[a-zA-Z0-9-.]{2,256}\.[a-z]{2,20})(\:[0-9]{2,4})?(?:\/[a-zA-Z0-9@:%_\+.~#?&\/\/=\-*]+|\/)?/';

	private $embedUrl = 'https://www.youtube.com';
	private $embedUrlNoCookie = 'https://www.youtube-nocookie.com';

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
	public function build() {

		$feedDescription = $this->xmlEncode($this->data['channel']['description']);
		$feedTitle = $this->xmlEncode($this->data['channel']['title']);
		$feedAuthor = $this->xmlEncode($this->data['channel']['title']);
		$feedUrl = $this->xmlEncode($this->data['channel']['url']);
		$feedUpdated = $this->xmlEncode(
			Helper::convertUnixTime(strtotime('now'), 'r')
		);
		$feedImage = $this->xmlEncode($this->data['channel']['thumbnail']);

		$items = $this->buildItmes();

		$this->feed = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
	<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
	<channel>
		<title>{$feedTitle}</title>
		<link>{$feedUrl}</link>
		<description>{$feedDescription}</description>
		<pubDate>{$feedUpdated}</pubDate>
		<image>
			<url>{$feedImage}</url>
		</image>
		{$items}
	</channel>
	</rss>
EOD;

	}

	/**
	 * Get feed
	 *
	 * @return string Returns RSS feed
	 */
	public function get() {
		return $this->feed;
	}

	/**
	 * Build feed itmes
	 *
	 * @return string Returns feed items
	 */
	private function buildItmes() {

		$items = '';

		foreach ($this->data['videos']['items'] as $video) {

			$itemTitle = $this->xmlEncode($video['title']);
			$itemUrl = $this->xmlEncode($video['url']);
			$itemTimestamp = $this->xmlEncode(
				Helper::convertUnixTime($video['published'], 'r')
			);
			$itemEnclosure = $this->xmlEncode($video['thumbnail']);
			$itemCategories = $this->buildCategories($video['tags']);
			$itemContent = $this->xmlEncode($this->buildContent($video));

			$items .= <<<EOD
<item>
	<title>{$itemTitle} ({$video['duration']})</title>
	<pubDate>{$itemTimestamp}</pubDate>
	<link>{$itemUrl}</link>
	<guid isPermaLink="true">{$itemUrl}</guid>
	<content:encoded>{$itemContent}</content:encoded>
	<enclosure url="{$itemEnclosure}" type="image/jpeg" />
	{$itemCategories}
</item>
EOD;
		}

		return $items;
	}

	/**
	 * Build item categories
	 *
	 * @param array $categorie Item categories
	 * @return string Returns item categories
	 */
	private function buildCategories(array $categories) {

		$itemCategories = '';

		foreach($categories as $category) {
			$category = $this->xmlEncode($category);

			$itemCategories .= <<<EOD
<category>{$category}</category>
EOD;
		}

		return $itemCategories;
	}

	/**
	 * Build item content (description)
	 *
	 * @param array $video Video data
	 * @return string Returns item conten
	 */
	private function buildContent(array $video) {

		$description = $this->formatDescription($video['description']);
		$published = Helper::convertUnixTime($video['published'], config::get('DateFormat'));

		$media = <<<EOD
<a target="_blank" title="Watch" href="https://youtube.com/watch?v={$video['id']}"><img src="{$video['thumbnail']}"/></a>
EOD;

		if ($this->embedVideos === true) {
			$url = $this->embedUrl;

			if (config::get('YouTubeEmbedPrivacy')) {
				$url = $this->embedUrlNoCookie;
			}

		$media = <<<EOD
<iframe width="100%" height="410" src="{$url}/embed/{$video['id']}" frameborder="0" allow="encrypted-media;" allowfullscreen></iframe>
EOD;
		}

		return <<<EOD
<a target="_blank" title="Watch" href="https://youtube.com/watch?v={$video['id']}">{$media}</a>
<hr/>Published: {$published} - Duration: {$video['duration']}<hr/><p>{$description}</p>
EOD;
	}

	/**
	 * Format video description
	 * Converts URLs to HTMl links
	 *
	 * @param string $description
	 * @return string Returns formatted video description
	 */
	private function formatDescription(string $description) {

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
	 * @return string Returns string with onvert characters
	 */
	private function xmlEncode($text) {
		return htmlspecialchars($text, ENT_XML1);
	}
}
