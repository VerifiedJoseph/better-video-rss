<?php

namespace App\FeedFormat;

use App\Config;
use App\Helper\Convert;
use App\Helper\Format;
use App\Helper\Url;

abstract class FeedFormat
{
    /** @var Config Config class instance */
    protected Config $config;

    /** @var array<string, mixed> $data Feed data */
    protected array $data = [];

    /** @var string $feed Formatted feed data */
    protected string $feed = '';

    /** @var string $contentType HTTP content-type header value */
    protected string $contentType = 'text/plain';

    /** @var boolean $embedVideos Embed videos status */
    protected bool $embedVideos = false;

    /** @var boolean $ignorePremieres Ignore upcoming video premieres */
    protected bool $ignorePremieres = false;

    /**
     * @param array<string, mixed> $data Cache/fetch data
     * @param boolean $embedVideos Embed YouTube videos in feed
     * @param Config $config Config class instance
     */
    public function __construct(array $data, bool $embedVideos, bool $ignorePremieres, Config $config)
    {
        $this->config = $config;
        $this->data = $data;
        $this->embedVideos = $embedVideos;
        $this->ignorePremieres = $ignorePremieres;
    }

    /**
     * Build feed
     */
    abstract public function build(): void;

    /**
     * Returns formatted feed data
     *
     * @return string
     */
    public function get(): string
    {
        return $this->feed;
    }

    /**
     * Returns HTTP content-type header value
     *
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Returns HTTP last-modified header value
     *
     * @return string
     */
    public function getLastModified(): string
    {
        return Convert::unixTime(
            $this->data['updated'],
            'D, d M Y H:i:s T',
            $this->config->getTimezone()
        );
    }

    /**
     * Build feed items
     */
    abstract protected function buildItems(): mixed;

    /**
     * Build item categories
     *
     * @param array<int, string> $categories Item categories
     */
    abstract protected function buildCategories(array $categories): mixed;

    /**
     * Build item content (description)
     *
     * @param array<string, mixed> $video Video data
     * @return string
     */
    protected function buildContent(array $video): string
    {
        $description = Convert::newlines(strip_tags($video['description']));
        $description = Convert::urls($description);
        $thumbnailUrl = $video['thumbnail'];
        $published = Convert::unixTime(
            (int) $video['published'],
            $this->config->getDateFormat(),
            $this->config->getTimezone()
        );
        $datetime = Convert::unixTime(
            (int) $video['published'],
            'c',
            $this->config->getTimezone()
        );

        if ($this->config->getImageProxyStatus() === true && $this->config->getCacheDisableStatus() === false) {
            $thumbnailUrl = Url::getImageProxy(
                $this->config->getSelfUrl(),
                $video['id'],
                $this->data['details']['type'],
                $this->data['details']['id']
            );
        }

        $media = <<<HTML
<a target="_blank" title="Watch on YouTube" href="{$video['url']}">
<img title="video thumbnail" src="{$thumbnailUrl}" loading="lazy"/>
</a>
HTML;

        if ($this->embedVideos === true) {
            $url = Url::getEmbed($video['id']);

            $media = <<<HTML
<div class="videoWrapper">
    <iframe width="100%" height="410" src="{$url}" frameborder="0" allow="encrypted-media;" loading="lazy"></iframe>
</div>
HTML;
        }

        $html = <<<HTML
            {$media}
            <div class="description">
                <hr/>
                <span>Published: <time datetime="{$datetime}">{$published}</time></span>
                <span> - Duration: <span class="duration">{$video['duration']}</span></span>
                <hr/><p>{$description}</p>
            </div>
        HTML;

        return Format::minify(trim($html));
    }

    /**
     * Build item title
     *
     * @param array<string, mixed> $video Video data
     * @return string
     */
    protected function buildTitle(array $video): string
    {
        $emptyDuration = '00:00';

        if ($video['premiere'] === true) {
            $scheduled = $this->getFormattedScheduledDate($video['scheduled']);

            return sprintf('[Premiere %s] %s (%s)', $scheduled, $video['title'], $video['duration']);
        }

        if ($video['liveStream'] === true) {
            if ($video['liveStreamStatus'] === 'upcoming') {
                $scheduled = $this->getFormattedScheduledDate($video['scheduled']);

                // deprecated: used by v1.4.0 and before
                if ($video['duration'] !== $emptyDuration) { // Has duration, is a video premiere
                    return sprintf('[Premiere %s] %s (%s)', $scheduled, $video['title'], $video['duration']);
                }

                return sprintf('[Live Stream %s] %s ', $scheduled, $video['title']);
            }

            if ($video['liveStreamStatus'] === 'live') {
                return '[Live] ' . $video['title'];
            }
        }

        return sprintf('%s (%s)', $video['title'], $video['duration']);
    }

    /**
     * Get formatted scheduled date string
     *
     * @param int $scheduled Unix timestamp
     */
    private function getFormattedScheduledDate(int $scheduled = 0): string
    {
        $datetimeFormat = sprintf(
            '%s %s',
            $this->config->getDateFormat(),
            $this->config->getTimeFormat()
        );

        return Convert::unixTime(
            $scheduled,
            $datetimeFormat,
            $this->config->getTimezone()
        );
    }
}
