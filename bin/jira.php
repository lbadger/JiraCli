#!/usr/bin/php
<?php
require_once(__DIR__ . '/../vendor/autoload.php');

use Symfony\Component\Console\Application;

$app = new Application('jira-cli', '@package_version@');

$commandNamespace = 'WCurtis\\Command\\';

$commands = [
    'CommentAddCommand',
    'CommentListCommand',
    'ConfigCommand',
    'FindCommand',
    'IssueAttachCommand',
    'IssueAttachListCommand',
    'IssueCommand',
    'IssueGetAttachmentCommand',
    'ListTimersCommand',
    'LogCommand',
    'LogListCommand',
    'LogTimerCommand',
    'StartTimerCommand',
    'StopTimerCommand',
    'TimerKillCommand',
];

foreach($commands as $command) {
    $commandClass = $commandNamespace . $command;

    $app->add(new $commandClass());
}

$app->run();
