<?php

// Class Auto loader
require 'autoload.php';

use Configuration as Config;
use Helper\Output;

try {
	Config::checkInstall();
	Config::checkConfig();

	$viewer = new CacheViewer();

} catch (Exception $e) {
	Output::Error($e->getMessage());
}
