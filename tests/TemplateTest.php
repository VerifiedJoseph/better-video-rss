<?php

use PHPUnit\Framework\TestCase;
use App\Template;
use App\Exception\ConfigException;

class TemplateTest extends TestCase
{
    /**
     * Test `fromUrl()` with a YouTube channel URL
     */
    public function testTemplate(): void
    {
        $file = file_get_contents('tests/files/template-feed-render.xml');

        $variables = [
            'feedTitle' => 'phpunit',
            'feedUrl' => 'https://example.com/feed.rss',
            'selfUrl' => 'https://example.com',
            'feedDescription' => 'Feed for phpunit',
            'feedUpdated' => '1970-01-01 00:00:00',
            'feedImage' => 'https://example.com/inage.png',
            'items' => '<items>items here</items>'
        ];

        $template = new Template('feed.xml', $variables);

        $this->assertEquals($file, $template->render());
    }

    /**
     * Test Template class with a missing template file
     *
     * An exception should be thrown.
     */
    public function testFileNotFoundException(): void
    {
        $this->expectException(ConfigException::class);

        new Template('missing-file.html', []);
    }
}
