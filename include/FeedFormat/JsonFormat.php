<?php

/**
 * JsonFormat - JSON Feed Version 1
 * https://jsonfeed.org/version/1
 *
 * Validators:
 * https://validator.jsonfeed.org/
 * https://json-feed-validator.herokuapp.com/validate
 */

declare(strict_types=1);

namespace App\FeedFormat;

use App\Helper\Convert;
use App\Helper\Json as Json;

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
     * @return array<int, array<string, mixed>> Items an an array
     */
    protected function buildItems(): array
    {
        $items = array();
        foreach ($this->data['videos'] as $video) {
            if ($video['premiere'] === true && $this->ignorePremieres === true) {
                continue;
            }

            $attachmentUrl = $video['thumbnail'];
            $datePublished = Convert::unixTime(
                $video['published'],
                'Y-m-d\TH:i:s\Z',
                $this->config->getTimezone()
            );

            $item = [
                'id' => $video['url'],
                'url' => $video['url'],
                'author' => [
                    'name' => $video['author']
                ],
                'title' => $this->buildTitle($video),
                'date_published' => $datePublished,
                'content_html' => $this->buildContent($video),
                'tags' => $this->buildCategories($video['tags']),
                'attachments' => [
                    [
                        'url' => $attachmentUrl,
                        'mime_type' => 'image/jpeg',
                    ]
                ]
            ];

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
