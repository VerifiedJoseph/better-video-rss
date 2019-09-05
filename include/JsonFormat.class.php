<?php
/**
 * JsonFormat - JSON Feed Version 1
 * https://jsonfeed.org/version/1
 *
 * Validators:
 * https://validator.jsonfeed.org/
 * https://json-feed-validator.herokuapp.com/validate
 */
class JsonFormat extends Format {
	/**
	 * Build feed
	 */
	public function build() {

		$feedDescription = $this->data['details']['description'];
		$feedTitle = $this->data['details']['title'];
		$feedAuthor = $this->data['details']['title'];
		$feedHomePageUrl = $this->data['details']['url'];
		$feedUrl = Config::get('SELF_URL_PATH') . '?' . $this->data['details']['type'] . '_id='. $this->data['details']['id'];
		$feedUpdated = Helper::convertUnixTime(strtotime('now'), 'r');
		$feedImage = $this->data['details']['thumbnail'];

		$items = $this->buildItmes();

		$feed = array(
			'version' => 'https://jsonfeed.org/version/1',
			'title' => $feedTitle,
			'description' => $feedDescription,
			'home_page_url' => $feedHomePageUrl,
			'feed_url' => $feedUrl,
			'icon' => $feedImage,
			'items' => $items
		);
		
		$this->feed = json_encode($feed, JSON_PRETTY_PRINT);
	}

	/**
	 * Build feed itmes
	 *
	 * @return string Items as XML
	 */
	protected function buildItmes() {

		$items = array();

		foreach ($this->data['videos']['items'] as $video) {

			$item = array();
			$item['id'] = $video['url'];
			$item['url'] = $video['url'];
			$item['title'] = $video['title'] . ' (' . $video['duration'] . ')';
			$item['date_published'] = Helper::convertUnixTime($video['published'], 'Y-m-d\TH:i:s\Z');
			$item['content_html'] = $this->buildContent($video);
			$item['tags'] = $this->buildCategories($video['tags']);
			$item['attachments'][] = array(
				'url' => $video['thumbnail'],
				'mime_type' => 'image/jpeg',
			);

			$items[] = $item;
		}

		return $items;
	}

	/**
	 * Build item categories
	 *
	 * @param array $categories Item categories
	 * @return array $categories
	 */
	protected function buildCategories(array $categories) {
		return $categories;
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
