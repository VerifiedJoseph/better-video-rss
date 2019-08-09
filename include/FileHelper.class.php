<?php

class FileHelper {

	/** @var int $byteCountGb Number of bytes in a GB */
	private static $byteCountGb = 1073741824;

	/** @var int $byteCountMb Number of bytes in a MB */
	private static $byteCountMb = 1048576;

	/** @var int $byte_countKb Number of bytes in a KB */
	private static $byteCountKb = 1024;

	/** @var int $numDecimalPlaces Number of decimal places to round to. */
	private static $numDecimalPlaces = 2;

	/** @var int $minByteCount Minimum byte count */
	private static $minByteCount = 1;

	/**
	 * Convert file size into a readable format from bytes (GB, MB, KB)
	 *
	 * @param int $bytes File size in bytes
	 * @return string $string Readable file size
	 */
	public static function readableFileSize (int $bytes = 0) {
	
		if ($bytes >= self::$byteCountGb) { // 1GB or greater
			$string = round($bytes / self::$byteCountGb, self::$numDecimalPlaces) . ' GB';

		} elseif ($bytes >= self::$byteCountMb) { // 1MB or greater
			$string = round($bytes / self::$byteCountMb, self::$numDecimalPlaces) . ' MB';
		
		} elseif ($bytes >= self::$byteCountKb) { // 1KB or greater
			$string = round($bytes / self::$byteCountKb, self::$numDecimalPlaces) . ' KB';
		
		} elseif ($bytes > self::$minByteCount) { // Greater than 1 byte
			$string = $bytes . ' bytes';

		} elseif ($bytes === self::$minByteCount) { // 1 byte
			$string = $bytes . ' byte';
		
		} else { // 0 bytes
			$string = '0 bytes';
		}

		return $string;
	}
}
