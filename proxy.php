<?php

require 'vendor/autoload.php';

use App\Config;
use App\Helper\Output;
use App\Page\Proxy;

try {
    $config = new Config();
    $config->checkInstall();
    $config->checkConfig();

    $proxy = new Proxy($_GET, $config);
    $proxy->getImage();
    $proxy->output();
} catch (Exception $e) {
    Output::error($e->getMessage());
}
