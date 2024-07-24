<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use App\Template;
use App\Exception\ConfigException;
use App\Helper\Format;

#[CoversClass(Template::class)]
#[UsesClass(ConfigException::class)]
#[UsesClass(App\Helper\File::class)]
#[UsesClass(App\Helper\Format::class)]
class TemplateTest extends TestCase
{
    /**
     * Test Template class
     */
    public function testTemplate(): void
    {
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

        $this->assertXmlStringEqualsXmlFile('tests/files/expected-rss-feed.xml', $template->render());
    }

    /**
     * Test render() method with minification enabled
     */
    public function testRenderWithMinification(): void
    {
        $expected = Format::minify(
            file_get_contents('tests/files/expected-rss-feed.xml')
        );

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
        $this->assertEquals($expected, $template->render(true));
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
