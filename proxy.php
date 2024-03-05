<?php

require 'vendor/autoload.php';
require 'include/version.php';

use App\Config;
use App\Helper\Output;
use App\Proxy;

try {
    $config = new Config();
    $config->checkConfig();

    $proxy = new Proxy($_GET, $config);
    $proxy->getImage();
    $proxy->output();
} catch (Exception $e) {
    Output::error($e->getMessage());
}
