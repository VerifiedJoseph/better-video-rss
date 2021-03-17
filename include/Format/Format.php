<?php

namespace Format;

use Configuration as Config;
use Helper\Convert;

abstract class Format {

	/** @var array $data Feed data */
	protected array $data = array();

	/** @var string $feed Formatted feed data */
	protected string $feed = '';

	/** @var string $contentType HTTP content-type header value */
	protected string $contentType = 'text/plain';

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
	 * Returns formatted feed data
	 *
	 * @return string
	 */
	public function get() {
		return $this->feed;
	}

	/**
	 * Returns HTTP content-type header value
	 *
	 * @return string
	 */
	public function getContentType() {
		return $this->contentType;
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
	 */
	abstract protected function buildCategories(array $categories);

	/**
	 * Build item content (description)
	 *
	 * @param array $video Video data
	 * @return string
	 */
	protected function buildContent(array $video) {
		$description = Convert::newlines($video['description']);
		$description = Convert::urls($description);
		$published = Convert::unixTime($video['published'], config::get('DATE_FORMAT'));
		$datetime = Convert::unixTime($video['published'], 'c');
		$media = <<<EOD
<a target="_blank" title="Watch on YouTube" href="{$video['url']}"><img title="video thumbnail" src="{$video['thumbnail']}"/></a>
EOD;

		if ($this->embedVideos === true) {
			$url = Url::getEmbed($video['id']);

			$media = <<<EOD
<iframe width="100%" height="410" src="{$url}" frameborder="0" allow="encrypted-media;" allowfullscreen></iframe>
EOD;
		}

		return <<<EOD
{$media}<hr/>Published: <time datetime="{$datetime}">{$published}</time> - Duration: <span class="duration">{$video['duration']}</span><hr/><p>{$description}</p>
EOD;
	}

	/**
	 * Build item title
	 *
	 * @param array $video Video data
	 * @return string
	 */
	protected function buildTitle(array $video) {
		$emptyDuration = '00:00';

		if (isset($video['liveStream'])) {
			$scheduled = Convert::unixTime(
				$video['liveStreamScheduled'],
				config::get('DATE_FORMAT') . ' ' . config::get('TIME_FORMAT')
			);

			// Scheduled
			if ($video['liveStreamScheduled'] > strtotime('now')) {
				if ($video['duration'] !== $emptyDuration) { // Has duration, is a video premiere
					return '[Premiere ' . $scheduled . '] ' . $video['title'] . ' (' . $video['duration'] . ')';
				}

				return '[Live Stream ' . $scheduled . '] ' . $video['title'];
			}

			// Broadcasting
			if ($video['duration'] === $emptyDuration) {
				return '[Live] ' . $video['title'];
			}
		}

		return $video['title'] . ' (' . $video['duration'] . ')';
	}
}
