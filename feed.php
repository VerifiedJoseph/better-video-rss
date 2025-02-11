<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use App\Config;
use App\Api;
use App\Page\Feed;
use App\Http\Request;
use App\Helper\Output;

try {
    $config = new Config();
    $config->checkConfig();

    $request = new Request($config->getUserAgent());
    $api = new Api($config, $request);
    $feed = new Feed($_GET, $config, $request, $api);
    $feed->generate();
    $feed->output();
} catch (Exception $e) {
    Output::error($e->getMessage());
}
