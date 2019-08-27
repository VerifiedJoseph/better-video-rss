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
		echo $feed;
	}
}
