<?php

namespace App\Format;

use App\Configuration as Config;
use App\Helper\Convert;
use App\Helper\Url;

class Rss extends Format
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
        $feedAuthor = $this->xmlEncode($this->data['details']['title']);
        $feedUrl = $this->xmlEncode($this->data['details']['url']);
        $feedUpdated = $this->xmlEncode(
            Convert::unixTime($this->data['updated'], 'r')
        );
        $feedImage = $this->xmlEncode($this->data['details']['thumbnail']);

        $selfUrl = $this->xmlEncode(
            Url::getFeed($this->data['details']['type'], $this->data['details']['id'], 'rss', $this->embedVideos)
        );

        $items = $this->buildItems();

        $this->feed = <<<XML
<?xml version="1.0" encoding="utf-8"?>
    <rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">
        <channel>
            <title>{$feedTitle}</title>
            <link>{$feedUrl}</link>
            <atom:link href="{$selfUrl}" rel="self"/>
            <description>{$feedDescription}</description>
            <pubDate>{$feedUpdated}</pubDate>
            <image>
                <url>{$feedImage}</url>
            </image>
            {$items}
        </channel>
    </rss>
XML;

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

            if (Config::get('ENABLE_IMAGE_PROXY') === true) {
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
    private function xmlEncode($text)
    {
        return htmlspecialchars($text, ENT_XML1);
    }
}
