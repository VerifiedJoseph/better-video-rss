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
	
	if (!empty($betterRss->getFeedId())) {
		$betterRss->generateFeed();
	
	} else {
		$betterRss->generateIndex();
	}

} catch (Exception $e) {
	Output::Error($e->getMessage());
}
