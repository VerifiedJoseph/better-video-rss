<?php

require 'vendor/autoload.php';
require 'include/version.php';

use App\Configuration as Config;
use App\Helper\Output;
use App\CacheViewer;

try {
	Config::checkInstall();
	Config::checkConfig();

	$viewer = new CacheViewer();

} catch (Exception $e) {
	Output::Error($e->getMessage());
}
