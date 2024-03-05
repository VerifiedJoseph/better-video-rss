<?php

require 'vendor/autoload.php';

use App\Config;
use App\Helper\Output;
use App\Page\CacheViewer;

try {
    $config = new Config();
    $config->checkInstall();
    $config->checkConfig();

    $viewer = new CacheViewer($_POST, $config);
} catch (Exception $e) {
    Output::Error($e->getMessage());
}
