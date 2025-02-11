<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use App\Config;
use App\Api;
use App\Page\Index;
use App\Helper\Output;
use App\Http\Request;

try {
    $config = new Config();
    $config->checkConfig();

    $request = new Request($config->getUserAgent());
    $api = new Api($config, $request);
    $index = new Index($_POST, $config, $api);
    $index->display();
} catch (Exception $e) {
    Output::error($e->getMessage());
}
