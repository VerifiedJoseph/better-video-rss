<?php

namespace App\FeedFormat;

use App\Template;
use App\Helper\Convert;
use App\Helper\Url;

class Rss extends FeedFormat
{
    /** @var string $contentType HTTP content-type header value */
    protected string $contentType = 'text/xml; charset=UTF-8';

    /**
     * Build feed
     */
    public function build(): void
    {
        $feedDescription = $this->xmlEncode($this->data['details']['description']);
        $feedTitle = $this->xmlEncode($this->data['details']['title']);
        $feedUrl = $this->xmlEncode($this->data['details']['url']);
        $feedUpdated = $this->xmlEncode(
            Convert::unixTime($this->data['updated'], 'r')
        );
        $feedImage = $this->xmlEncode($this->data['details']['thumbnail']);

        $selfUrl = $this->xmlEncode(
            Url::getFeed($this->data['details']['type'], $this->data['details']['id'], 'rss', $this->embedVideos)
        );

        $xml = new Template('feed.xml', [
            'feedTitle' => $feedTitle,
            'feedUrl' => $feedUrl,
            'feedDescription' => $feedDescription,
            'feedUpdated' => $feedUpdated,
            'feedImage' => $feedImage,
            'selfUrl' => $selfUrl,
            'items' => $this->buildItems()
        ]);

        $this->feed = $xml->render(minify: true);
    }

    /**
     * Build feed items
     *
     * @return string Items as XML
     */
    protected function buildItems(): string
    {
        $items = '';

        foreach ($this->data['videos'] as $video) {
            $itemTitle = $this->xmlEncode(
                $this->buildTitle($video)
            );
            $itemAuthor = $this->xmlEncode($video['author']);
            $itemUrl = $this->xmlEncode($video['url']);
            $itemTimestamp = $this->xmlEncode(
                Convert::unixTime($video['published'], 'r')
            );
            $itemCategories = $this->buildCategories($video['tags']);
            $itemContent = $this->xmlEncode($this->buildContent($video));
            $itemEnclosure = $this->xmlEncode($video['thumbnail']);

            if ($this->config->get('ENABLE_IMAGE_PROXY') === true) {
                $itemEnclosure = $this->xmlEncode(
                    Url::getImageProxy(
                        $video['id'],
                        $this->data['details']['type'],
                        $this->data['details']['id']
                    )
                );
            }

            $items .= <<<XML
                <item>
                    <title>{$itemTitle}</title>
                    <pubDate>{$itemTimestamp}</pubDate>
                    <link>{$itemUrl}</link>
                    <guid isPermaLink="true">{$itemUrl}</guid>
                    <author>
                        <name>{$itemAuthor}</name>
                    </author>
                    <content:encoded>{$itemContent}</content:encoded>
                    <enclosure url="{$itemEnclosure}" type="image/jpeg" />
                    {$itemCategories}
                </item>
            XML;
        }

        return $items;
    }

    /**
     * Build item categories
     *
     * @param array<int, string> $categories Item categories
     * @return string Categories as XML
     */
    protected function buildCategories(array $categories): string
    {
        $itemCategories = '';

        foreach ($categories as $category) {
            $category = $this->xmlEncode($category);

            $itemCategories .= <<<XML
                <category>{$category}</category>
            XML;
        }

        return $itemCategories;
    }

    /**
     * Convert special characters to HTML entities
     *
     * @param string $text
     * @return string String with encoded characters
     */
    private function xmlEncode($text): string
    {
        return htmlspecialchars($text, ENT_XML1);
    }
}
