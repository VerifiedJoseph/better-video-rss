<?php

class CacheViewer {
	/**
	 * @var array $cacheId Current cache file ID
	 */
	private $cacheId = '';

	/**
	 * @var boolean $showRaw Show raw cache file data
	 */
	private $showRaw = false;

	/**
	 * @var array $data Data from cache files
	 */
	private $data = array();

	/**
	 * @var int $cacheSize Total size of the cache files
	 */
	private $cacheSize = 0;

	/**
	 * Constructor
	 *
	 * @throws Exception If EnableCacheViewer is false.
	 */
	public function __construct() {

		if (!Config::get('EnableCacheViewer')) {
			throw new Exception('Cache viewer is disabled.');
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

	}

	/**
	 * Load cache files
	 *
	 * @throws Exception If a cache file can not be opened.
	 */
	private function loadFiles() {

		$regex = '/.' . preg_quote(Config::get('CacheFilenameExt')) . '$/';

		$directoryPath = '..' . DIRECTORY_SEPARATOR . Config::get('CacheDirectory');
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
			'id' => $file->getBasename('.' . Config::get('CacheFilenameExt')),
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
		$cacheSize = FileHelper::readableFileSize($this->cacheSize);
		$tbody = '';

		foreach ($this->data as $index => $data) {

			$modified = Helper::convertUnixTime($data['modified']);
			$size = FileHelper::readableFileSize($data['size']);

			$tbody .= <<<HTML
<tr>
	<td id="{$data['id']}">$index</td>
	<td>{$data['id']}<br>
		<span style="font-size:13px;">
			(Channel ID: {$data['contents']['channel']['id']} Title: {$data['contents']['channel']['title']})
		</span>
	</td>
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
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
	<div id="header">Cache Viewer</div>
	<div id="main">
		<table style="width:200px; margin-bottom: 10px;">
			<thead>
				<tr>
					<th>Files</th>
					<th>Size</th>
				</tr>
			</thead>
			<tbody>
				<tr style="text-align:center">
					<td>{$fileCount}</td>
					<td>{$cacheSize}</td>
				</tr>
			</tbody>
		</table>
		<table style="width:1150px;">
			<thead>
				<tr>
					<th>#</th>
					<th>Cache ID</th>
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

		if ($this->showRaw === true) {
			$json = json_encode($data['contents'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

			$tdData = <<<HTML
<a style="float: right;" href="cache-viewer.php">[Close]</a>
<textarea cols="140" rows="50">{$json}</textarea>
HTML;
		} else {

			$tdData = <<<HTML
<a style="float: right;" href="cache-viewer.php#{$data['id']}">[Close]</a>
{$this->displayChannel($data['contents']['channel'])}<br/>
{$this->displayplaylist($data['contents']['playlist'])}<br>
{$this->displayVideos($data['contents']['videos'])}<br/>
HTML;
		}

		$tr = <<<HTML
<tr>
	<td colspan="5">
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

		$fetched = Helper::convertUnixTime($channel['fetched']);
		$expires = Helper::convertUnixTime($channel['expires']);

		$html = <<<HTML
<strong>Channel:</strong>
<table style="width: 1153px;">
<tr>
	<td>
		<strong>ID</strong>: {$channel['id']}<br>
		<strong>Title</strong>: {$channel['title']}<br>
		<strong>URL</strong>: <a target="_blank" href="{$channel['url']}">{$channel['url']}</a><br>
		<strong>Published</strong>: {$channel['published']}<br>
		<strong>Playlist ID</strong>: {$channel['playlist']}<br>
		<strong>Fetched:</strong> $fetched<br>
		<strong>Expires</strong>: $expires<br>
	</td>
	<td>
		<strong>Description</strong>:<br>
		<textarea cols="70" rows="4" readonly>{$channel['description']}</textarea>
	</td>
	<td>
		<strong>Thumbnail</strong>:<br>
		<img src="{$channel['thumbnail']}"/></a>
	</td>
</tr>
</table>
HTML;

		return $html;
	}

	/**
	 * Display playlist details
	 *
	 * @param  array $playlist
	 * @return string $html
	 */
	private function displayPlaylist(array $playlist) {

		$videoIDs = implode(' ', $playlist['videos']);

		$fetched = Helper::convertUnixTime($playlist['fetched']);
		$expires = Helper::convertUnixTime($playlist['expires']);

		$html = <<<HTML
<strong>Playlist:</strong>
<table style="width: 1153px;">
<tr>
	<td>
		<strong>Video IDs:</strong><br>
		<textarea cols="140" rows="2" readonly>{$videoIDs}</textarea>
		<strong>Fetched:</strong> $fetched<br>
		<strong>Expires</strong>: $expires<br>
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

		$videoCount = count($videos['items']);
		$videoHtml = '';

		foreach ($videos['items'] as $video) {

			$tags = implode(', ', $video['tags']);
			$tagCount = count($video['tags']);

			$fetched = Helper::convertUnixTime($video['fetched']);
			$expires = Helper::convertUnixTime($video['expires']);

			$videoHtml .= <<<HTML
<tr>
	<td style="width:440px;">
		<strong>Title</strong>: {$video['title']}<br>
		<strong>URL</strong>: <a target="_blank" href="{$video['url']}">{$video['url']}</a><br>
		<strong>Published</strong>: {$video['published']}<br>
		<strong>Duration</strong>: {$video['duration']}<br>
		<strong>Fetched:</strong> {$fetched}<br>
		<strong>Expires</strong>: {$expires}<br>
	</td>
	<td>
		<strong>Description</strong>:<br>
		<textarea cols="70" rows="5" readonly>{$video['description']}</textarea>
	</td>
	<td>
		<strong>Thumbnail</strong>:<br>
		<a target="_blank" title="{$video['thumbnail']}" href="{$video['thumbnail']}"><img style="width:100px;" src="{$video['thumbnail']}"/></a>
	</td>
</tr>
<tr>
	<td colspan="3">
		<strong>Tags ({$tagCount}) :</strong>
		<textarea cols="140" rows="2" readonly="">{$tags}</textarea>
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
	 * Convert Unix timestamp into a readable format
	 *
	 * @param  string $timestamp Unix timestamp
	 * @return string
	 */
	private function convertUnixTime(int $timestamp = 0) {	
		$dt = new DateTime();
		$dt->setTimestamp($timestamp);
		$dt->setTimezone(new DateTimeZone(config::get('Timezone')));

		return $formatted = $dt->format('Y-m-d H:i:s');
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
