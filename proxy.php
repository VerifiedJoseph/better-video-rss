<?php

require 'vendor/autoload.php';
require 'include/version.php';

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
