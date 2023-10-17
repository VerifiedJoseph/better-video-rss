<?php

require 'vendor/autoload.php';
require 'include/version.php';

use App\Config;
use App\Helper\Output;
use App\CacheViewer;

try {
    $config = new Config();
    $config->checkInstall();
    $config->checkConfig();

	$viewer = new CacheViewer($config);

} catch (Exception $e) {
	Output::Error($e->getMessage());
}
