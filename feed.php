<?php

require 'vendor/autoload.php';

use App\Config;
use App\Api;
use App\Page\Feed;
use App\Helper\Output;

try {
    $config = new Config();
    $config->checkInstall();
    $config->checkConfig();

    $api = new Api($config);
    $feed = new Feed($_GET, $config, $api);
    $feed->generate();
    $feed->output();
} catch (Exception $e) {
    Output::error($e->getMessage());
}
