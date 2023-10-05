<?php

// Composer Auto loader
require __DIR__ . '/vendor/autoload.php';

// Class Auto loader
require __DIR__ . '/autoload.php';

use App\Configuration as Config;
use App\Helper\Output;
use App\Proxy;

try {
	Config::checkInstall();
	Config::checkConfig();

	$proxy = new Proxy();
	$proxy->getImage();
	$proxy->output();

} catch (Exception $e) {
	Output::error($e->getMessage());
}
