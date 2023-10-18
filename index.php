<?php

require 'vendor/autoload.php';
require 'include/version.php';

use App\Config;
use App\Helper\Output;
use App\Index;

try {
    $config = new Config();
    $config->checkInstall();
    $config->checkConfig();

    $index = new Index($_POST, $config);
    $index->display();
} catch (Exception $e) {
    Output::error($e->getMessage());
}
