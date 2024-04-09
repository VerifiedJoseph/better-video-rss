<?php

require 'vendor/autoload.php';

use App\Config;
use App\Helper\Output;
use App\Http\Request;
use App\Proxy;

try {
    $config = new Config();
    $config->checkConfig();

    $request = new Request($config->getUserAgent());
    $proxy = new Proxy($_GET, $config, $request);
    $proxy->getImage();
    $proxy->output();
} catch (Exception $e) {
    Output::error($e->getMessage());
}
