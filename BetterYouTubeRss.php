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
		$parameter = '';

		if ($cache->expired($part)) {

			if (Config::get('ENABLE_HYBRID_MODE') === true && $part === 'playlist') {
				$parameter = $betterRss->getChannelId();
			}

			if ($part === 'videos') {
				$parameter = $cache->getExpiredVideos();

				if (empty($parameter)) {
					continue;
				}
			}

			$fetch->part($part, $parameter);
			$cache->update($part, $fetch->getData($part));
		}
	}

	$cache->save();

	$feed = new Feed(
		$cache->getData(),
		$betterRss->getEmbedStatus()
	);

	$feed->build();
	Output::xml($feed->get());

} catch (Exception $e) {
	Output::Error($e->getMessage());
}
