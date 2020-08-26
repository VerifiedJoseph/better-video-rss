<?php

use Configuration as Config;
use Helper\File;
use Helper\Convert;

class CacheViewer {
	/**
	 * @var string $cacheId Current cache file ID
	 */
	private string $cacheId = '';

	/**
	 * @var boolean $showRaw Show raw cache file data
	 */
	private bool $showRaw = false;
	
	/**
	 * @var boolean $showXml Show feed XML
	 */
	private bool $showXml = false;

	/**
	 * @var array $data Data from cache files
	 */
	private array $data = array();

	/**
	 * @var int $cacheSize Total size of the cache files
	 */
	private int $cacheSize = 0;

	/**
	 * Constructor
	 *
	 * @throws Exception if ENABLE_CACHE_VIEWER is false
	 * @throws Exception if DISABLE_CACHE is true
	 */
	public function __construct() {

		if (!Config::get('ENABLE_CACHE_VIEWER')) {
			throw new Exception('Cache viewer is disabled.');
		}

		if (Config::get('DISABLE_CACHE') === true) {
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
	private function checkInputs() {

		if (isset($_POST['id'])) {

			if (empty($_POST['id'])) {
				throw new Exception('No cache ID parameter given.');
			}

			$this->cacheId = $_POST['id'];
		}

		if (isset($_POST['raw'])) {
			$this->showRaw = true;
		}

		if (isset($_POST['xml'])) {
			$this->showXml = true;
		}
	}

	/**
	 * Load cache files
	 *
	 * @throws Exception If a cache file can not be opened.
	 */
	private function loadFiles() {
		$regex = '/.' . preg_quote(Config::get('CACHE_FILENAME_EXT')) . '$/';

		$directoryPath = Config::get('ABSOLUTE_PATH') . DIRECTORY_SEPARATOR . Config::get('CACHE_DIR');
		$cacheDirectory = new RecursiveDirectoryIterator($directoryPath);
		$cacheFiles = new RegexIterator($cacheDirectory, $regex);

		foreach ($cacheFiles as $file) {
			$handle = fopen($file, 'r');

			if (!$handle) {
				throw new Exception('Failed to open file: ' . $file->getPathname());
			}

			// Read file
			$contents = fread($handle, filesize($file));

			// Close file handle
			fclose($handle);

			$data = json_decode($contents, true);
			$this->data[] = array(
				'id' => $file->getBasename('.' . Config::get('CACHE_FILENAME_EXT')),
				'modified' => $file->getMTime(),
				'size' => $file->getSize(),
				'contents' => $data
			);

			$this->cacheSize += $file->getSize();
		}
	}

	/**
	 * Display cache file details
	 *
	 * @return string $html
	 */
	private function display() {
		$fileCount = count($this->data);
		$cacheSize = File::readableSize($this->cacheSize);
		$tbody = '';

		if(empty($this->data)) {
			$tbody = <<<HTML
<tr class="center">
	<td colspan="6">
		No cache files found. 
	</td>
</tr>
HTML;
		}

		foreach ($this->data as $index => $data) {
			$modified = Convert::unixTime($data['modified']);
			$size = File::readableSize($data['size']);

			$tbody .= <<<HTML
<tr class="center">
	<td id="{$data['id']}">$index</td>
	<td>{$data['contents']['details']['title']}<br>
		<span style="font-size:13px;">
			(Cache ID: {$data['id']}
		</span>
	</td>
	<td>{$data['contents']['details']['type']}</td>	
	<td>{$modified}</td>
	<td>{$size}</td>
	<td style="width:170px;">
		<div style="float:left;">
			<form action="#{$data['id']}" method="post">
				<input name="id" type="hidden" value="{$data['id']}">
				<button type="submit">View Data</button>
			</form>
		</div>
		<div style="float: right;">
			<form action="#{$data['id']}" method="post">
				<input name="id" type="hidden" value="{$data['id']}">
				<input name="raw" type="hidden">
				<button type="submit">View Raw</button>
			</form>
		</div>
		<div style="float:left;">
			<form action="#{$data['id']}" method="post">
				<input name="id" type="hidden" value="{$data['id']}">
				<input name="xml" type="hidden">
				<button type="submit">View XML</button>
			</form>
		</div>
	</td>
</tr>
HTML;

			if (isset($this->cacheId) && $this->cacheId === $data['id']) {
				$tbody .= $this->displayFileDetails($data);
			}
		}

		$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<title>CacheViewer</title>
	<link rel="stylesheet" type="text/css" href="static/style.css" />
</head>
<body>
	<div id="header"><a href="index.php">BetterYouTube</a>  &#187; <a href="tools.html">Tools</a> &#187; Cache Viewer</div>
	<div id="main">
		<table style="width:200px; margin-bottom: 10px;">
			<thead>
				<tr>
					<th>Files</th>
					<th>Size</th>
				</tr>
			</thead>
			<tbody>
				<tr class="center">
					<td>{$fileCount}</td>
					<td>{$cacheSize}</td>
				</tr>
			</tbody>
		</table>
		<table style="width:1150px;">
			<thead>
				<tr class="center">
					<th>#</th>
					<th>Name</th>
					<th>Type</th>
					<th>Last Modified</th>
					<th>Size</th>
					<th>View</th>
				</tr>
			</thead>
			<tbody>
				{$tbody}
			<tbody>
		</table>
	</div>
</body>
</html>
HTML;

		echo $html;
	}

	/**
	 * Display full details for a single cache file.
	 *
	 * @param  array $channel
	 * @return string $html
	 */
	private function displayFileDetails(array $data) {
		$tr = '';

		$tdData = <<<HTML
<a style="float: right;" href="cache-viewer.php">[Close]</a>
HTML;

		if ($this->showRaw === true) {
			$json = json_encode($data['contents'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

			$tdData .= <<<HTML
<textarea cols="140" rows="50">{$json}</textarea>
HTML;
		} elseif ($this->showXml === true) {

			$format = new Format\Rss($data['contents'], false);
			$format->build();

			$feedurl = Config::get('SELF_URL_PATH') . '?' . $data['contents']['details']['type'] . '_id='. $data['contents']['details']['id'];

			$tdData .= <<<HTML
Feed URL: <a target="_blank" href="{$feedurl}">{$feedurl}<a/>
<textarea cols="140" rows="50">{$format->get()}</textarea>
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
	<td colspan="6">
		{$tdData}
	</td>
</tr>
HTML;

		return $tr;
	}

	/**
	 * Display channel details
	 *
	 * @param  array $channel
	 * @return string $html
	 */
	private function displayChannel(array $channel) {
		$fetched = Convert::unixTime($channel['fetched']);
		$expires = Convert::unixTime($channel['expires']);

		$html = <<<HTML
<strong>Details:</strong>
<table style="width: 1153px;">
<tr>
	<td>
		<strong>ID:</strong> {$channel['id']}<br>
		<strong>Title:</strong> {$channel['title']}<br>
		<strong>URL:</strong> <a target="_blank" href="{$channel['url']}">{$channel['url']}</a><br>
		<strong>Playlist ID:</strong> {$channel['playlist']}<br>
		<strong>Fetched:</strong> $fetched<br>
		<strong>Expires:</strong> $expires<br>
	</td>
	<td>
		<strong>Description:</strong><br>
		<textarea class="description" readonly>{$channel['description']}</textarea>
	</td>
	<td>
		<strong>Thumbnail:</strong><br>
		<img src="{$channel['thumbnail']}"/></a>
	</td>
</tr>
</table>
HTML;

		return $html;
	}

	/**
	 * Display feed details
	 *
	 * @param 	array $feed
	 * @return string $html
	 */
	private function displayFeed(array $feed) {
		$videoIDs = implode(' ', $feed['videos']);

		$fetched = Convert::unixTime($feed['fetched']);
		$expires = Convert::unixTime($feed['expires']);

		$html = <<<HTML
<strong>Feed:</strong>
<table style="width: 1153px;">
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
	 * @param  array $videos
	 * @return string $html
	 */
	private function displayVideos(array $videos) {
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
	<td style="width:440px;">
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
		<a target="_blank" title="{$video['thumbnail']}" href="{$video['thumbnail']}"><img style="width:100px;" src="{$video['thumbnail']}"/></a>
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
<table style="width: 1153px;">{$videoHtml}</table>
HTML;

		return $html;
	}

	/**
	 * Order cache files by date modified
	 */
	private function orderByModified() {
		$sort = array();
		foreach ($this->data as $key => $item) {
			$sort[$key] = $item['modified'];
		}
		array_multisort($sort, SORT_DESC, $this->data);
	}
}
