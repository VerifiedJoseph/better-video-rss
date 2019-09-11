<?php

class RssFormat extends Format {

	/** @var string $contentType HTTP content-type header value */
	protected $contentType = 'text/xml; charset=UTF-8';

	/**
	 * Build feed
	 */
	public function build() {

		$feedDescription = $this->xmlEncode($this->data['details']['description']);
		$feedTitle = $this->xmlEncode($this->data['details']['title']);
		$feedAuthor = $this->xmlEncode($this->data['details']['title']);
		$feedUrl = $this->xmlEncode($this->data['details']['url']);
		$feedUpdated = $this->xmlEncode(
			Helper::convertUnixTime(strtotime('now'), 'r')
		);
		$feedImage = $this->xmlEncode($this->data['details']['thumbnail']);

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
	 * Build feed itmes
	 *
	 * @return string Items as XML
	 */
	protected function buildItmes() {

		$items = '';

		foreach ($this->data['videos']['items'] as $video) {

			$itemTitle = $this->xmlEncode($video['title']);
			$itemAuthor = $this->xmlEncode($video['author']);
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
	<author>
		<name>{$itemAuthor}</name>
	</author>
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
	 * @param array $categories Item categories
	 * @return string Categories as XML
	 */
	protected function buildCategories(array $categories) {

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
	 * Convert special characters to HTML entities
	 *
	 * @param string $text
	 * @return string String with encoded characters
	 */
	private function xmlEncode($text) {
		return htmlspecialchars($text, ENT_XML1);
	}
}
