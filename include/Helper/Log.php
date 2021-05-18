<?php

namespace Helper;

class log {
	/**
	 * Write error message to system log file
	 *
	 * @param string $message Error message
	 */
	public static function error(string $message) {
		error_log('[BetterVideoRss] ' . $message, 0);
	}
}