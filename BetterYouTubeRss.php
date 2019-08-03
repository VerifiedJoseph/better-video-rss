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

	$cache = new Cache(
		$betterRss->getChannelId()
	);

	$cache->load();

	$fetch = new Fetch(
		$cache->getData()
	);

	foreach($betterRss->getParts() as $part) {
		if ($cache->expired($part)) {
			$fetch->part($part);
			$cache->update($part, $fetch->getData($part));
		}
	}

	$cache->save();

	$feed = new Feed(
		$cache->getData(),
		$betterRss->getEmbedStatus()
	);

	$feed->build();
	Output::feed($feed->get());

} catch (Exception $e) {
	Output::Error($e->getMessage());
}
