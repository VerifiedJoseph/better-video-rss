<?php

class FeedHtml extends Feed {
	/**
	 * Build feed
	 */
	public function build() {

		$feedDescription = $this->data['details']['description'];
		$feedTitle = $this->data['details']['title'];
		$feedAuthor = $this->data['details']['title'];
		$feedUrl = $this->data['details']['url'];
		$feedUpdated = Helper::convertUnixTime(strtotime('now'), 'r');
		$feedImage = $this->data['details']['thumbnail'];

		$rssLink = Config::get('SELF_URL_PATH') . '?' . $this->data['details']['type'] . '_id=' . $this->data['details']['id'];

		if ($this->embedVideos === true) {
			$rssLink .= '&embed_videos=true';
		}

		$items = $this->buildItmes();

		$this->feed = <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
	<title>{$feedTitle}</title>
	<meta name="robots" content="noindex, follow">
	<link rel="stylesheet" type="text/css" href="static/style.css" />
</head>
<body>
	<div id="header" class="center">
		<a href="{$feedUrl}">{$feedTitle}</a>
	</div>
	<div id="main">
		<div id="items">
			<div class="item">
				Feed format: <a href="{$rssLink}&format=rss"><button>RSS</button></a> <a href="{$rssLink}&format=html"><button>HTML</button></a>
			</div>
			{$items}
		</div>
	</div>
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

		foreach ($this->data['videos']['items'] as $video) {

			$itemTitle = $video['title'];
			$itemUrl = $video['url'];
			$itemEnclosure = $video['thumbnail'];
			$itemCategories = $this->buildCategories($video['tags']);
			$itemContent = $this->buildContent($video);

			$items .= <<<EOD
<div class="item">
	<div class="title">
		<h2><a href="{$itemUrl}">{$itemTitle} ({$video['duration']})</a></h2>
	</div>
{$itemContent}
{$itemCategories}
			</div>
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

		$itemCategories = '<strong>Categories:</strong> <ul>';

		foreach($categories as $category) {
			$category = $this->xmlEncode($category);

			$itemCategories .= <<<EOD
<li>{$category}</li>
EOD;
		}

		return $itemCategories . '</ul>';
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
<a target="_blank" title="Watch" href="https://youtube.com/watch?v={$video['id']}"><img src="{$video['thumbnail']}"/></a>
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
