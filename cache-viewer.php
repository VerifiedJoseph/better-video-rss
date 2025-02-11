<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use App\Config;
use App\Helper\Output;
use App\Page\CacheViewer;

try {
    $config = new Config();
    $config->checkConfig();

    $viewer = new CacheViewer($_POST, $config);
} catch (Exception $e) {
    Output::Error($e->getMessage());
}
