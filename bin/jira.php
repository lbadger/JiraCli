#!/usr/bin/php
<?php
require_once(__DIR__ . '/../vendor/autoload.php');

use Symfony\Component\Console\Application;

$app = new Application('jira-cli', '@package_version@');

$commandNamespace = 'WCurtis\\Command\\';

$files = glob(__DIR__ . '/../src/Command/*Command.php');

foreach($files as $file) {
    if(!preg_match('/\/([a-zA-Z]+Command)\.php$/', $file, $match)) continue;

    $commandClass = $commandNamespace . $match[1];

    $app->add(new $commandClass());
}

$app->run();
