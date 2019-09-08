<?php

class Output {
	/**
	 * Output error message
	 */
	public static function error(string $message) {
		header('Content-Type: text/plain');
		echo $message;
	}

	/**
	 * Output feed
	 */	
	
	/**
	 * Output feed
	 *
	 * @param string $data Feed data
	 * @param string $contentType Content-type header value
	 */
	public static function feed(string $data, string $contentType) {
		header('content-type: ' . $contentType);
		echo $data;
	}
	
	/**
	 * Output XML
	 */
	public static function xml(string $feed) {
		header('content-type: text/xml; charset=UTF-8');
		echo $feed;
	}

	/**
	 * Output JSON
	 */
	public static function json(string $feed) {
		header('content-type: application/json');
		echo $feed;
	}

	/**
	 * Output HTML
	 */
	public static function html(string $feed) {
		header('content-type: text/html; charset=UTF-8');
		echo $feed;
	}
}
