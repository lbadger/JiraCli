#!/usr/bin/php
<?php
require_once(__DIR__ . '/../vendor/autoload.php');

use Symfony\Component\Console\Application;

$app = new Application('jira-cli', '@package_version@');

$commandNamespace = 'WCurtis\\Command\\';

/** This is pretty dirty, but it works for now. Glob all the files in the Command namespace,
 * instantiate and add to the $app.
 */
$files = glob(__DIR__ . '/../src/Command/*Command.php');

foreach($files as $file) {
    if(!preg_match('/\/([a-zA-Z]+Command)\.php$/', $file, $match)) continue;

    $commandClass = $commandNamespace . $match[1];

    $app->add(new $commandClass());
}

$app->run();
