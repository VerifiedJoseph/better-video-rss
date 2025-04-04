<?php

declare(strict_types=1);

namespace App\FeedFormat;

use App\Template;

class HtmlFormat extends FeedFormat
{
    /** @var string $contentType HTTP content-type header value */
    protected string $contentType = 'text/html; charset=UTF-8';

    /**
     * Build feed
     */
    public function build(): void
    {
        $feedDescription = htmlEntities($this->data['details']['description'], ENT_QUOTES);

        $rssUrl = htmlEntities($this->createFeedUrl('rss'));
        $jsonUrl = htmlEntities($this->createFeedUrl('json'));

        $html = new Template('feed.html', [
            'feedTitle' => htmlEntities($this->data['details']['title'], ENT_QUOTES),
            'feedDescription' => $feedDescription,
            'feedUrl' => $this->data['details']['url'],
            'rssUrl' => $rssUrl,
            'jsonUrl' => $jsonUrl,
            'feedFormatButtons' => $this->buildFormatButtons(),
            'items' => $this->buildItems()
        ]);

        $this->feed = $html->render();
    }

    /**
     * Build feed items
     *
     * @return string Items as HTML
     */
    protected function buildItems(): string
    {
        $items = '';

        foreach ($this->data['videos'] as $video) {
            if ($video['premiere'] === true && $this->ignorePremieres === true) {
                continue;
            }

            $itemTitle = htmlEntities($this->buildTitle($video), ENT_QUOTES);
            $itemUrl = $video['url'];
            $itemCategories = $this->buildCategories($video['tags']);
            $itemContent = $this->buildContent($video);

            $items .= <<<HTML
<article>
    <h2 class="title"><a target="_blank" href="{$itemUrl}">{$itemTitle}</a></h2>
    {$itemContent}
    <div class="categories">
        {$itemCategories}
    </div>
</article>
HTML;
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
        $itemCategories = '<strong>Categories:</strong><ul>';

        foreach ($categories as $category) {
            $category = htmlEntities($category, ENT_QUOTES);
            $itemCategories .=  sprintf('<li>%s</li>', $category);
        }

        return $itemCategories . '</ul>';
    }

    /**
     * build format buttons
     *
     * @return string button HTML
     */
    private function buildFormatButtons(): string
    {
        $html = '';

        foreach ($this->config->getFeedFormats() as $format) {
            $text = strtoupper($format);
            $url = $this->createFeedUrl($format);
            $html .= sprintf('<a href="%s"><button>%s</button></a>', $url, $text);
        }

        return $html;
    }
}
