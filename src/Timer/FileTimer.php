<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 7/9/14
 * Time: 9:35 PM
 */

namespace WCurtis\Timer;

use WCurtis\Util;

class FileTimer extends TimerAbstract {
    protected static $timeFile = '.jiraTimer.json';

    protected function getTimeFilePath() {
        return Util::joinPaths(getenv("HOME"), self::$timeFile);
    }

    public function getCurrentData() {
        if(!is_file($this->getTimeFilePath())) {
            $this->setData([]);
        }

        $contents = file_get_contents($this->getTimeFilePath());
        return json_decode($contents, true);
    }

    protected function setData($data) {
        $contents = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents($this->getTimeFilePath(), $contents);
    }

    public function GetStartTime($name)
    {
        $data = $this->getCurrentData();
        return self::getDate(Util::getFromArray($data, [$name, 'start']));
    }

    public function GetStopTime($name)
    {
        $data = $this->getCurrentData();
        return self::getDate(Util::getFromArray($data, [$name, 'stop']));
    }

    protected function SetStopTime($name, \DateTime $dt)
    {
        $data = $this->getCurrentData();
        if(!isset($data[$name])) $data[$name] = [];

        $data[$name]['stop'] = $dt->format('Y-m-d H:i:s');

        $this->setData($data);

        return $this;
    }

    protected function SetStartTime($name, \DateTime $dt)
    {
        $data = $this->getCurrentData();
        if(!isset($data[$name])) $data[$name] = [];

        $data[$name]['start'] = $dt->format('Y-m-d H:i:s');

        $this->setData($data);

        return $this;
    }

    public function ClearTimer($name) {
        $data = $this->getCurrentData();
        if(isset($data[$name])) unset($data[$name]);

        $this->setData($data);
    }

    protected function SetTimeElapsed($name, $minutes) {
        $data = $this->getCurrentData();
        if(!isset($data[$name])) $data[$name] = [];

        $data[$name]['elapsed'] = $minutes;

        $this->setData($data);
    }

    protected function GetTimeElapsed($name) {
        $data = $this->getCurrentData();

        return isset($data[$name]['elapsed'])
            ? $data[$name]['elapsed']
            : 0;
    }
}
