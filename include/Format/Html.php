<?php

namespace Format;

use Configuration as Config;
use Helper\Convert;
use Helper\Url;

class Html extends Format {

	/** @var string $contentType HTTP content-type header value */
	protected string $contentType = 'text/html; charset=UTF-8';

	/**
	 * Build feed
	 */
	public function build() {
		$feedDescription = htmlspecialchars($this->data['details']['description'], ENT_QUOTES);
		$feedTitle = $this->data['details']['title'];
		$feedUrl = $this->data['details']['url'];
		$feedImage = $this->data['details']['thumbnail'];

		$rssLink = Url::getFeed($this->data['details']['type'], $this->data['details']['id'], 'rss', $this->embedVideos);
		$feedFormatButtons = $this->buildFormatButtons();

		$items = $this->buildItmes();

		$this->feed = <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
	<title>{$feedTitle}</title>
	<meta name="robots" content="noindex, follow">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="description" content="{$feedDescription}">
	<link rel="stylesheet" type="text/css" href="static/style.css" />
	<link rel="alternate" type="application/rss+xml" title="{$feedTitle}" href="{$rssLink}">
</head>
<body>
	<header class="center">
		<a href="{$feedUrl}">{$feedTitle}</a>
	</header>
	<main>
		<section id="links">
			Feed format: $feedFormatButtons
		</section>
		<section id="items">
			{$items}
		</section>
	</main>
	<footer class="center">
		BetterVideoRss - <a href="https://github.com/VerifiedJoseph/BetterVideoRss">Source Code</a>
	</footer>
</body>
</html>
EOD;

	}

	/**
	 * Build feed itmes
	 *
	 * @return string Items as XML
	 */
	protected function buildItmes() {
		$items = '';

		foreach ($this->data['videos'] as $video) {
			$itemTitle = $this->buildTitle($video);
			$itemUrl = $video['url'];
			$itemEnclosure = $video['thumbnail'];
			$itemCategories = $this->buildCategories($video['tags']);
			$itemContent = $this->buildContent($video);

			$items .= <<<EOD
<article>
	<h2 class="title"><a href="{$itemUrl}">{$itemTitle}</a></h2>
	{$itemContent}
	{$itemCategories}
</article>
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
		$itemCategories = '<strong>Categories:</strong><ul>';

		foreach($categories as $category) {
			$itemCategories .= <<<EOD
<li>{$category}</li>
EOD;
		}

		return $itemCategories . '</ul>';
	}

	/**
	 * build format buttons
	 *
	 * @return string button HTML
	 */
	private function buildFormatButtons() {
		$html = '';

		foreach (Config::getFeedFormats() as $format) {
			$text = strtoupper($format);
			$url = Url::getFeed($this->data['details']['type'], $this->data['details']['id'], $format, $this->embedVideos);

			$html .= <<<EOD
<a href="{$url}"><button>{$text}</button></a> 
EOD;
		}

		return $html;
	}
}
