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

	$betterRss = new BetterVideoRss();
	$betterRss->generate();

} catch (Exception $e) {
	Output::Error($e->getMessage());
}
