<?php

/**
 * JsonFormat - JSON Feed Version 1
 * https://jsonfeed.org/version/1
 *
 * Validators:
 * https://validator.jsonfeed.org/
 * https://json-feed-validator.herokuapp.com/validate
 */

namespace App\FeedFormat;

use App\Helper\Convert;
use App\Helper\Json as Json;
use App\Helper\Url;

class JsonFormat extends FeedFormat
{
    /** @var string $contentType HTTP content-type header value */
    protected string $contentType = 'application/json';

    /**
     * Build feed
     */
    public function build(): void
    {
        $feedDescription = $this->data['details']['description'];
        $feedTitle = $this->data['details']['title'];
        $feedHomePageUrl = $this->data['details']['url'];
        $feedUrl = $this->createFeedUrl('json');
        $feedImage = $this->data['details']['thumbnail'];
        $items = $this->buildItems();
        $feed = array(
            'version' => 'https://jsonfeed.org/version/1',
            'title' => $feedTitle,
            'description' => $feedDescription,
            'home_page_url' => $feedHomePageUrl,
            'feed_url' => $feedUrl,
            'icon' => $feedImage,
            'items' => $items
        );
        $this->feed = Json::encode($feed, JSON_PRETTY_PRINT);
    }

    /**
     * Build feed items
     *
     * @return array<int, array<string, string>> Items an an array
     */
    protected function buildItems(): array
    {
        $items = array();
        foreach ($this->data['videos'] as $video) {
            if ($video['premiere'] === true && $this->ignorePremieres === true) {
                continue;
            }

            $item = array();
            $item['id'] = $video['url'];
            $item['url'] = $video['url'];
            $item['author'] = array(
                'name' => $video['author']
            );
            $item['title'] = $this->buildTitle($video);
            $attachmentUrl = $video['thumbnail'];
            if ($this->config->getImageProxyStatus() === true) {
                $attachmentUrl = Url::getImageProxy(
                    $this->config->getSelfUrl(),
                    $video['id'],
                    $this->data['details']['type'],
                    $this->data['details']['id']
                );
            }

            $item['date_published'] = Convert::unixTime(
                $video['published'],
                'Y-m-d\TH:i:s\Z',
                $this->config->getTimezone()
            );
            $item['content_html'] = $this->buildContent($video);
            $item['tags'] = $this->buildCategories($video['tags']);
            $item['attachments'][] = array(
                'url' => $attachmentUrl,
                'mime_type' => 'image/jpeg',
            );
            $items[] = $item;
        }

        return $items;
    }

    /**
     * Build item categories
     *
     * @param array<int, string> $categories Item categories
     * @return array<int, string> $categories
     */
    protected function buildCategories(array $categories): array
    {
        return $categories;
    }
}
