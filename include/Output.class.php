<?php

class Output {
	/**
	 * Outpot error message
	 */
	public static function error(string $message) {
		header('Content-Type: text/plain');
		echo $message;
	}

	/**
	 * Outpot RSS feed
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
