#!/usr/bin/env php
<?php

set_time_limit(0);
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$config = require_once __DIR__ . '/../config.php';

$crawler = new Mtt\Crawler($config);
$crawler->run();
