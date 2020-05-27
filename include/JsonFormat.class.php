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

	/** @var string $contentType HTTP content-type header value */
	protected $contentType = 'application/json';

	/**
	 * Build feed
	 */
	public function build() {
		$feedDescription = $this->data['details']['description'];
		$feedTitle = $this->data['details']['title'];
		$feedAuthor = $this->data['details']['title'];
		$feedHomePageUrl = $this->data['details']['url'];
		$feedUrl = Config::get('SELF_URL_PATH') . '?' . $this->data['details']['type'] . '_id=' . $this->data['details']['id'];
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
			$item['author'] = array(
				'name' => $video['author']
			);
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
}
