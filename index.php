<?php

require 'vendor/autoload.php';

use App\Configuration as Config;
use App\Helper\Output;
use App\Index;

try {
	Config::checkInstall();
	Config::checkConfig();

	$index = new Index();
	$index->display();

} catch (Exception $e) {
	Output::error($e->getMessage());
}
