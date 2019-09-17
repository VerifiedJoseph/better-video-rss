<?php

// Composer Auto loader
require __DIR__ . '/vendor/autoload.php';

// Class Auto loader
require __DIR__ . '/include/autoload.php';

try {

	Config::checkInstall();
	Config::checkConfig();

	$betterRss = new BetterYouTubeRss();
	$betterRss->generate();

} catch (Exception $e) {
	Output::Error($e->getMessage());
}
