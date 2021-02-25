<?php

class Output {
	/**
	 * Output error message
	 *
	 * @param string $message Error message
	 * @param int $code HTTP esponse code
	 */
	public static function error(string $message, int $code = 400) {
		http_response_code($code);
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
