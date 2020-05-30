<?php

// Class Auto loader
require 'autoload.php';

try {
	Config::checkInstall();
	Config::checkConfig();

	$viewer = new CacheViewer();

} catch (Exception $e) {
	Output::Error($e->getMessage());
}
