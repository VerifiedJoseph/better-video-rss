<?php

require 'vendor/autoload.php';

use App\Config;
use App\Api;
use App\Page\Index;
use App\Helper\Output;

try {
    $config = new Config();
    $config->checkConfig();

    $api = new Api($config);
    $index = new Index($_POST, $config, $api);
    $index->display();
} catch (Exception $e) {
    Output::error($e->getMessage());
}
