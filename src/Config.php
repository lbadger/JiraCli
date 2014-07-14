<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 7/9/14
 * Time: 10:02 PM
 */

namespace WCurtis;

use WCurtis\Http\CurlClient;
use WCurtis\Jira\JiraUtil;
use WCurtis\Timer\FileTimer;

class Config {
    public static $configFile = '.jiraCliConfig';
    protected static $config;

    public static $defaultConfig = [
        'jira' => [
            'user' => '',
            'pass' => '',
            'url' => ''
        ]
    ];

    protected static function getConfigPath() {
        return Util::joinPaths(getenv("HOME"), self::$configFile);
    }

    protected static function loadConfig() {
        $path = self::getConfigPath();
        if(!is_file($path)) {
            throw new \Exception("No config found. Run `jira config` to fix this.");
        }

        $contents = file_get_contents($path);
        self::$config = json_decode($contents, true);
    }

    public static function Get($key, $default = null) {
        if(!self::$config) self::loadConfig();

        return Util::getFromArray(self::$config, $key, false, $default);
    }

    public static function Set($data) {
        if(!is_string($data)) $data = json_encode($data, JSON_PRETTY_PRINT);

        file_put_contents(self::getConfigPath(), $data);
        self::loadConfig();
    }

    public static function ConfigPresent() {
        $path = self::getConfigPath();
        return !!(is_file($path) && json_decode(file_get_contents($path)));
    }

    public static function GetJiraUtilFromConfig() {
        $jira = self::get('jira');

        $client = new CurlClient();;
        return new JiraUtil($client, $jira['user'], $jira['pass'], $jira['url']);
    }

    public static function GetJiraCliFromConfig() {
        $jira = self::GetJiraUtilFromConfig();

        return new JiraCli($jira, new FileTimer(Config::get('tz')));
    }

    public static function GetTz($tz = null) {
        if($tz) {
            if($tz instanceof \DateTimeZone) return $tz;
            else return new \DateTimeZone($tz);
        }

        $configTz = self::Get('tz');

        if($configTz) return new \DateTimeZone($configTz);

        return date_default_timezone_get();
    }

    public static function GetNow($tz = null) {
        return new \DateTime('now', self::GetTz($tz));
    }

    public static function GetDate($date, $tz = null) {
        $tz = self::GetTz($tz);

        if($date instanceof \DateTime) return $date;

        $created = date_create($date, $tz);

        if(!$date) throw new \Exception("Could not parse date $date");

        return $created;
    }
}
