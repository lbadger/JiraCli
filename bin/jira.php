#!/usr/bin/php
<?php
require_once(__DIR__ . '/../vendor/autoload.php');

use WCurtis\Command\FindCommand;
use WCurtis\Command\StartTimerCommand;
use WCurtis\Command\StopTimerCommand;
use WCurtis\Command\LogCommand;
use WCurtis\Command\LogListCommand;
use WCurtis\Command\ConfigCommand;
use WCurtis\Command\IssueCommand;
use WCurtis\Command\ListTimersCommand;
use WCurtis\Command\TimerKillCommand;
use WCurtis\Command\LogTimerCommand;
use WCurtis\Command\CommentListCommand;
use WCurtis\Command\CommentAddCommand;
use Symfony\Component\Console\Application;

$app = new Application('jira-cli', '@package_version@');
$app->add(new FindCommand());
$app->add(new StartTimerCommand());
$app->add(new StopTimerCommand());
$app->add(new LogListCommand());
$app->add(new LogCommand());
$app->add(new ConfigCommand());
$app->add(new IssueCommand());
$app->add(new ListTimersCommand());
$app->add(new TimerKillCommand());
$app->add(new CommentListCommand());
$app->add(new CommentAddCommand());
$app->add(new LogTimerCommand());
$app->run();
