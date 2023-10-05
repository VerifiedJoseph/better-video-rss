<?php

namespace App\Format;

use App\Configuration as Config;
use App\Helper\Url;

class Html extends Format
{
    /** @var string $contentType HTTP content-type header value */
    protected string $contentType = 'text/html; charset=UTF-8';

    /**
     * Build feed
     */
    public function build()
    {
        $feedDescription = htmlspecialchars($this->data['details']['description'], ENT_QUOTES);
        $feedTitle = $this->data['details']['title'];
        $feedUrl = $this->data['details']['url'];
        $feedImage = $this->data['details']['thumbnail'];

        $rssUrl = htmlspecialchars(Url::getFeed($this->data['details']['type'], $this->data['details']['id'], 'rss', $this->embedVideos));
        $jsonUrl = htmlspecialchars(Url::getFeed($this->data['details']['type'], $this->data['details']['id'], 'json', $this->embedVideos));
        $feedFormatButtons = $this->buildFormatButtons();

        $items = $this->buildItems();

        $this->feed = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<title>{$feedTitle}</title>
	<meta name="robots" content="noindex, follow">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="description" content="{$feedDescription}">
	<link rel="stylesheet" type="text/css" href="static/style.css" />
	<link rel="alternate" type="application/rss+xml" title="{$feedTitle} RSS feed" href="{$rssUrl}">
	<link rel="alternate" type="application/json" title="{$feedTitle} JSON feed" href="{$jsonUrl}">
</head>
<body>
	<header class="center">
		<a target="_blank" href="{$feedUrl}">{$feedTitle}</a>
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
HTML;

        $this->feed = \App\Helper\Format::minify($this->feed);
    }

    /**
     * Build feed items
     *
     * @return string Items as XML
     */
    protected function buildItems()
    {
        $items = '';

        foreach ($this->data['videos'] as $video) {
            $itemTitle = htmlspecialchars($this->buildTitle($video));
            $itemUrl = $video['url'];
            $itemEnclosure = $video['thumbnail'];
            $itemCategories = $this->buildCategories($video['tags']);
            $itemContent = $this->buildContent($video);

            $items .= <<<HTML
<article>
    <h2 class="title"><a target="_blank" href="{$itemUrl}">{$itemTitle}</a></h2>
    {$itemContent}
    {$itemCategories}
</article>
HTML;
        }

        return $items;
    }

    /**
     * Build item categories
     *
     * @param array $categories Item categories
     * @return string Categories as XML
     */
    protected function buildCategories(array $categories)
    {
        $itemCategories = '<strong>Categories:</strong><ul>';

        foreach ($categories as $category) {
            $category = htmlspecialchars($category);
            $itemCategories .=  sprintf('<li>{%s}</li>', $category);
        }

        return $itemCategories . '</ul>';
    }

    /**
     * build format buttons
     *
     * @return string button HTML
     */
    private function buildFormatButtons()
    {
        $html = '';

        foreach (Config::getFeedFormats() as $format) {
            $text = strtoupper($format);
            $url = Url::getFeed($this->data['details']['type'], $this->data['details']['id'], $format, $this->embedVideos);

            $html .= sprintf('<a href="%s"><button>%s</button></a>', $url, $text);
        }

        return $html;
    }
}
