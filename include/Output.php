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
	 *
	 * @param string $data Feed data
	 * @param string $contentType Content-type header value
	 */
	public static function feed(string $data, string $contentType) {
		header('content-type: ' . $contentType);
		echo $data;
	}
}
