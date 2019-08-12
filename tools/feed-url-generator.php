<?php

// Composer Auto loader
require '../vendor/autoload.php';

// Class Auto loader
require '../include/autoload.php';

// Config file
require '../config.php';

try {

	Config::checkInstall();
	Config::checkConfig();

	$generator = new FeedUrlGenerator();

} catch (Exception $e) {
	Output::Error($e->getMessage());
}
