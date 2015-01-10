#!/usr/bin/env php
<?php

set_time_limit(0);
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$config = require_once __DIR__ . '/../config.php';

$allPages = false;
if (isset($argv[1]) && $argv[1] == 'all') {
    $allPages = true;
}

$crawler = new Mtt\Crawler($config);
$crawler->run($allPages);
