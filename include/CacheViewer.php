<?php

namespace App;

use App\Config;
use App\Template;
use App\Helper\File;
use App\Helper\Convert;
use App\Helper\Url;
use Exception;

class CacheViewer
{
    /** @var Config Config class instance */
    private Config $config;

    /** @var string $cacheId Current cache file ID */
    private string $cacheId = '';

    /** @var boolean $showRaw Show raw cache file data */
    private bool $showRaw = false;

    /** @var array<int, mixed> $data Data from cache files */
    private array $data = [];

    /** @var int $cacheSize Total size of the cache files */
    private int $cacheSize = 0;

    /**
     * @param Config $config Config class instance
     *
     * @throws Exception if ENABLE_CACHE_VIEWER is false
     * @throws Exception if DISABLE_CACHE is true
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        if ($this->config->get('ENABLE_CACHE_VIEWER') === false) {
            throw new Exception('Cache viewer is disabled.');
        }

        if ($this->config->getCacheDisableStatus() === true) {
            throw new Exception('Cache viewer not available. Cache is disabled.');
        }

        $this->checkInputs();
        $this->loadFiles();
        $this->orderByModified();
        $this->display();
    }

    /**
     * Check user inputs
     *
     * @throws Exception If a cache ID is not given.
     */
    private function checkInputs(): void
    {
        if (isset($_POST['id'])) {
            if (empty($_POST['id'])) {
                throw new Exception('No cache ID parameter given.');
            }

            $this->cacheId = $_POST['id'];
        }

        if (isset($_POST['raw'])) {
            $this->showRaw = true;
        }
    }

    /**
     * Load cache files
     *
     * @throws Exception If a cache file can not be opened.
     * @throws Exception If a cache file can not be decoded.
     */
    private function loadFiles(): void
    {
        $regex = '/.' . preg_quote($this->config->getCacheFileExtension()) . '$/';

        $cacheDirectory = new \RecursiveDirectoryIterator($this->config->getCacheDirPath());
        $cacheFiles = new \RegexIterator($cacheDirectory, $regex);

        foreach ($cacheFiles as $file) {
            $contents = File::read($file->getPathname());
            $data = json_decode($contents, true);

            if (is_null($data) === true) {
                throw new Exception('Failed to decode file: ' . $file->getfilename());
            }

            $this->data[] = array(
                'id' => $file->getBasename('.' . $this->config->getCacheFileExtension()),
                'modified' => $file->getMTime(),
                'size' => $file->getSize(),
                'contents' => $data
            );

            $this->cacheSize += $file->getSize();
        }
    }

    /**
     * Display cache file details
     */
    private function display(): void
    {
        $fileCount = count($this->data);
        $cacheSize = Convert::fileSize($this->cacheSize);
        $tbody = '';

        if (empty($this->data)) {
            $tbody = <<<HTML
<tr class="center">
	<td colspan="6">
		No cache files found. 
	</td>
</tr>
HTML;
        }

        foreach ($this->data as $index => $data) {
            $number = $index + 1;

            $modified = Convert::unixTime($data['modified']);
            $size = Convert::fileSize($data['size']);

            $xmlUrl = Url::getFeed($data['contents']['details']['type'], $data['contents']['details']['id'], 'rss');
            $htmlUrl = Url::getFeed($data['contents']['details']['type'], $data['contents']['details']['id'], 'html');

            $tbody .= <<<HTML
<tr class="center">
	<td id="{$data['id']}">$number</td>
	<td>{$data['contents']['details']['title']}<br>
		<span class="small">
			(Cache ID: {$data['id']}
		</span>
	</td>
	<td>{$data['contents']['details']['type']}</td>	
	<td>{$modified}</td>
	<td>{$size}</td>
	<td class="buttons">
		<div class="left">
			<form action="#{$data['id']}" method="post">
				<input name="id" type="hidden" value="{$data['id']}">
				<button type="submit">View Data</button>
			</form>
		</div>
		<div class="right">
			<form action="#{$data['id']}" method="post">
				<input name="id" type="hidden" value="{$data['id']}">
				<input name="raw" type="hidden">
				<button type="submit">View Raw</button>
			</form>
		</div>
		<div class="left">
			<a target="_blank" href="{$xmlUrl}">
				<button type="submit">View XML</button>
			</a>
		</div>
		<div class="right">
			<a target="_blank" href="{$htmlUrl}">
				<button type="submit">View HTML</button>
			</a>
		</div>
	</td>
</tr>
HTML;

            if (isset($this->cacheId) && $this->cacheId === $data['id']) {
                $tbody .= $this->displayFileDetails($data);
            }
        }

        $html = new Template('cache-viewer.html', [
            'fileCount' => $fileCount,
            'cacheSize' => $cacheSize,
            'tbody' => $tbody
        ]);

        echo $html->render();
    }

    /**
     * Display full details for a single cache file.
     *
     * @param array<string, mixed> $data
     * @return string $html
     */
    private function displayFileDetails(array $data): string
    {
        $tr = '';

        $tdData = <<<HTML
            <a class="right" href="cache-viewer.php">[Close]</a>
        HTML;

        if ($this->showRaw === true) {
            $json = json_encode($data['contents'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            $tdData .= <<<HTML
                <br><textarea cols="140" rows="50">{$json}</textarea>
            HTML;
        } else {
            $tdData .= <<<HTML
                {$this->displayChannel($data['contents']['details'])}<br/>
                {$this->displayFeed($data['contents']['feed'])}<br>
                {$this->displayVideos($data['contents']['videos'])}<br/>
            HTML;
        }

        $tr = <<<HTML
            <tr>
                <td colspan="6">{$tdData}</td>
            </tr>
        HTML;

        return $tr;
    }

    /**
     * Display channel details
     *
     * @param array<string, mixed> $channel
     * @return string $html
     */
    private function displayChannel(array $channel): string
    {
        $fetched = Convert::unixTime($channel['fetched']);
        $expires = Convert::unixTime($channel['expires']);

        $html = <<<HTML
            <strong>Details:</strong>
            <table class="part">
                <tr>
                    <td>
                        <strong>ID:</strong> {$channel['id']}<br>
                        <strong>Title:</strong> {$channel['title']}<br>
                        <strong>URL:</strong> <a target="_blank" href="{$channel['url']}">{$channel['url']}</a><br>
                        <strong>Fetched:</strong> $fetched<br>
                        <strong>Expires:</strong> $expires<br>
                    </td>
                    <td>
                        <strong>Description:</strong><br>
                        <textarea class="description" readonly>{$channel['description']}</textarea>
                    </td>
                    <td>
                        <strong>Thumbnail:</strong><br>
                        <img loading="lazy" src="{$channel['thumbnail']}"/></a>
                    </td>
                </tr>
            </table>
        HTML;

        return $html;
    }

    /**
     * Display feed details
     *
     * @param array<string, mixed> $feed
     * @return string $html
     */
    private function displayFeed(array $feed): string
    {
        $videoIDs = implode(' ', $feed['videos']);

        $fetched = Convert::unixTime($feed['fetched']);
        $expires = Convert::unixTime($feed['expires']);

        $html = <<<HTML
            <strong>Feed:</strong>
            <table class="part">
                <tr>
                    <td>
                        <strong>Video IDs:</strong><br>
                        <textarea class="videos" readonly>{$videoIDs}</textarea><br>
                        <strong>Fetched:</strong> $fetched<br>
                        <strong>Expires:</strong> $expires<br>
                    </td>
                </tr>
            </table>
        HTML;

        return $html;
    }

    /**
     * Display video details
     *
     * @param array<int, array<string, mixed>> $videos
     * @return string $html
     */
    private function displayVideos(array $videos): string
    {
        $videoCount = count($videos);
        $videoHtml = '';

        foreach ($videos as $video) {
            $tags = implode(', ', $video['tags']);
            $tagCount = count($video['tags']);

            $fetched = Convert::unixTime($video['fetched']);
            $expires = Convert::unixTime($video['expires']);
            $published = Convert::unixTime($video['published']);

            $videoHtml .= <<<HTML
                <tr>
                    <td class="videoDetails">
                    <strong>Title:</strong> {$video['title']}<br>
                        <strong>URL:</strong> <a target="_blank" href="{$video['url']}">{$video['url']}</a><br>
                        <strong>Published:</strong> {$published}<br>
                        <strong>Duration:</strong> {$video['duration']}<br>
                        <strong>Fetched:</strong> {$fetched}<br>
                        <strong>Expires:</strong> {$expires}<br>
                    </td>
                    <td>
                        <strong>Description</strong>:<br>
                        <textarea class="description" readonly>{$video['description']}</textarea>
                    </td>
                    <td>
                        <strong>Thumbnail:</strong><br>
                        <a target="_blank" title="{$video['thumbnail']}" href="{$video['thumbnail']}">
                            <img loading="lazy" class="thumbnail" src="{$video['thumbnail']}"/>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <strong>Tags ({$tagCount}) :</strong><br/>
                        <textarea class="tags" readonly>{$tags}</textarea>
                    </td>
                </tr>
            HTML;
        }

        $html = <<<HTML
            <strong>Videos ({$videoCount}):</strong>
            <table class="part">{$videoHtml}</table>
        HTML;

        return $html;
    }

    /**
     * Order cache files by date modified
     */
    private function orderByModified(): void
    {
        $sort = array();

        foreach ($this->data as $key => $item) {
            $sort[$key] = $item['modified'];
        }
        array_multisort($sort, SORT_DESC, $this->data);
    }
}
