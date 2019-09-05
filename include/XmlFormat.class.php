<?php

class XmlFormat extends Format {
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
	 * Build item content (description)
	 *
	 * @param array $video Video data
	 * @return string Item content as HTML
	 */
	protected function buildContent(array $video) {

		$description = $this->formatDescription($video['description']);
		$published = Helper::convertUnixTime($video['published'], config::get('DATE_FORMAT'));

		$media = <<<EOD
<img src="{$video['thumbnail']}"/>
EOD;

		if ($this->embedVideos === true) {
			$url = $this->embedUrl;

			if (config::get('YOUTUBE_EMBED_PRIVACY')) {
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
}
