<?php

// Composer Auto loader
require __DIR__ . '/vendor/autoload.php';

// Class Auto loader
require __DIR__ . '/autoload.php';

use Configuration as Config;
use Helper\Output;

try {
	Config::checkInstall();
	Config::checkConfig();

	$feed = new Feed();
	$feed->generate();
	$feed->output();

} catch (Exception $e) {
	Output::error($e->getMessage());
}
