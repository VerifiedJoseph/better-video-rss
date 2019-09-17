<?php

// Class Auto loader
require '../include/autoload.php';

// Config file
require '../config.php';

try {

	Config::checkInstall();
	Config::checkConfig();

	$viewer = new CacheViewer();

} catch (Exception $e) {
	Output::Error($e->getMessage());
}
