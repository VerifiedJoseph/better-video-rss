<?php

// Class Auto loader
require 'autoload.php';

use Configuration as Config;

try {
	Config::checkInstall();
	Config::checkConfig();

	$viewer = new CacheViewer();

} catch (Exception $e) {
	Output::Error($e->getMessage());
}
