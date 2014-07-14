<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 7/8/14
 * Time: 10:14 PM
 */

namespace WCurtis\Timer;

use WCurtis\Util;

/**
 * Class MemoryTimer
 *
 * Mostly for testing, since it won't persist at all.
 *
 * @package WCurtis\Timer
 */
class MemoryTimer extends TimerAbstract {
    protected $times = [];

    public function GetStartTime($name)
    {
        return Util::getFromArray($this->times, [$name, 'start']);
    }

    public function SetStartTime($name, \DateTime $dt)
    {
        if(!isset($this->times[$name])) $this->times[$name] = [];
        $this->times[$name]['start'] = $dt;

        return $this;
    }

    public function GetStopTime($name)
    {
        return Util::getFromArray($this->times, [$name, 'stop']);
    }

    public function SetStopTime($name, \DateTime $dt)
    {
        if(!isset($this->times[$name])) $this->times[$name] = [];
        $this->times[$name]['stop'] = $dt;

        return $this;
    }

    public function ClearTimer($name) {
        if(isset($this->times[$name])) unset($this->times[$name]);
    }

    public function GetCurrentData() {
        return $this->times;
    }
}
