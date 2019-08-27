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
		echo $feed;
	}
}
