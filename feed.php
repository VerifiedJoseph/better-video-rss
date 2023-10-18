<?php

require 'vendor/autoload.php';
require 'include/version.php';

use App\Config;
use App\Helper\Output;
use App\Feed;

try {
    $config = new Config();
    $config->checkInstall();
    $config->checkConfig();

    $feed = new Feed($_GET, $config);
    $feed->generate();
    $feed->output();
} catch (Exception $e) {
    Output::error($e->getMessage());
}
