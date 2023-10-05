<?php

require 'vendor/autoload.php';
require 'include/version.php';

use App\Configuration as Config;
use App\Helper\Output;
use App\Feed;

try {
    Config::checkInstall();
    Config::checkConfig();

    $feed = new Feed();
    $feed->generate();
    $feed->output();
} catch (Exception $e) {
    Output::error($e->getMessage());
}
