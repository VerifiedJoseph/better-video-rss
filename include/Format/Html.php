<?php

namespace App\Format;

use App\Configuration as Config;
use App\Template;
use App\Helper\Url;

class Html extends Format
{
    /** @var string $contentType HTTP content-type header value */
    protected string $contentType = 'text/html; charset=UTF-8';

    /**
     * Build feed
     */
    public function build(): void
    {
        $feedDescription = htmlspecialchars($this->data['details']['description'], ENT_QUOTES);

        $rssUrl = htmlspecialchars(
            Url::getFeed($this->data['details']['type'], $this->data['details']['id'], 'rss', $this->embedVideos)
        );

        $jsonUrl = htmlspecialchars(
            Url::getFeed($this->data['details']['type'], $this->data['details']['id'], 'json', $this->embedVideos)
        );

        $html = new Template('feed.html', [
            'feedTitle' => $this->data['details']['title'],
            'feedDescription' => $feedDescription,
            'feedUrl' => $this->data['details']['url'],
            'rssUrl' => $rssUrl,
            'jsonUrl' => $jsonUrl,
            'feedFormatButtons' => $this->buildFormatButtons(),
            'items' => $this->buildItems()
        ]);

        $this->feed = $html->render(minify: true);
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
            $itemTitle = htmlspecialchars($this->buildTitle($video));
            $itemUrl = $video['url'];
            $itemEnclosure = $video['thumbnail'];
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
            $category = htmlspecialchars($category);
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

        foreach (Config::getFeedFormats() as $format) {
            $text = strtoupper($format);
            $url = Url::getFeed(
                $this->data['details']['type'],
                $this->data['details']['id'],
                $format,
                $this->embedVideos
            );

            $html .= sprintf('<a href="%s"><button>%s</button></a> ', $url, $text);
        }

        return $html;
    }
}
