<?php

namespace App\Format;

use App\Configuration as Config;
use App\Helper\Convert;
use App\Helper\Url;

abstract class Format
{
    /** @var array $data Feed data */
    protected array $data = array();

    /** @var string $feed Formatted feed data */
    protected string $feed = '';

    /** @var string $contentType HTTP content-type header value */
    protected string $contentType = 'text/plain';

    /** @var boolean $embedVideos Embed videos status */
    protected bool $embedVideos = false;

    /**
     * Constructor
     *
     * @param array $data Cache/fetch data
     * @param boolean $embedVideos Embed YouTube videos in feed
     */
    public function __construct(array $data, bool $embedVideos = false)
    {
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
    public function get()
    {
        return $this->feed;
    }

    /**
     * Returns HTTP content-type header value
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Returns HTTP last-modified header value
     *
     * @return string
     */
    public function getLastModified()
    {
        return Convert::unixTime($this->data['updated'], 'D, d M Y H:i:s T');
    }

    /**
     * Build feed items
     *
     * @return string Items as XML
     */
    abstract protected function buildItems();

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
    protected function buildContent(array $video)
    {
        $description = Convert::newlines($video['description']);
        $description = Convert::urls($description);
        $published = Convert::unixTime($video['published'], Config::get('DATE_FORMAT'));
        $datetime = Convert::unixTime($video['published'], 'c');
        $thumbnailUrl = $video['thumbnail'];

        if (Config::get('ENABLE_IMAGE_PROXY') === true && Config::get('DISABLE_CACHE') === false) {
            $thumbnailUrl = Url::getImageProxy(
                $video['id'],
                $this->data['details']['type'],
                $this->data['details']['id']
            );
        }

        $media = sprintf(
            '<a target="_blank" title="Watch on YouTube" href="%s"><img title="video thumbnail" src="%s" loading="lazy"/></a>',
            $video['url'],
            $thumbnailUrl
        );

        if ($this->embedVideos === true) {
            $url = Url::getEmbed($video['id']);

            $media = sprintf(
                '<iframe width="100%" height="410" src="%s" frameborder="0" allow="encrypted-media;" loading="lazy"></iframe>',
                $url
            );
        }

        return <<<HTML
            {$media}<hr/>
            <span>Published: <time datetime="{$datetime}">{$published}</time></span>
            <span> - Duration: <span class="duration">{$video['duration']}</span></span>
            <hr/><p>{$description}</p>
        HTML;
    }

    /**
     * Build item title
     *
     * @param array $video Video data
     * @return string
     */
    protected function buildTitle(array $video)
    {
        $emptyDuration = '00:00';

        if ($video['liveStream'] === true) {
            if ($video['liveStreamStatus'] === 'upcoming') {
                $scheduled = Convert::unixTime(
                    $video['liveStreamScheduled'],
                    Config::get('DATE_FORMAT') . ' ' . Config::get('TIME_FORMAT')
                );

                if ($video['duration'] !== $emptyDuration) { // Has duration, is a video premiere
                    return '[Premiere ' . $scheduled . '] ' . $video['title'] . ' (' . $video['duration'] . ')';
                }

                return '[Live Stream ' . $scheduled . '] ' . $video['title'];
            }

            if ($video['liveStreamStatus'] === 'live') {
                return '[Live] ' . $video['title'];
            }
        }

        return $video['title'] . ' (' . $video['duration'] . ')';
    }
}
