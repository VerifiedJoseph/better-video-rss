<?php

// Composer Auto loader
require __DIR__ . '/vendor/autoload.php';

// Class Auto loader
require __DIR__ . '/include/autoload.php';

// Config file
require __DIR__ . '/config.php';

try {

	Config::checkInstall();
	Config::checkConfig();

	$betterRss = new BetterYouTubeRss();
	$betterRss->generate();

} catch (Exception $e) {
	Output::Error($e->getMessage());
}
