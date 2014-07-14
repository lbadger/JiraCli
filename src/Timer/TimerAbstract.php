<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 7/8/14
 * Time: 9:59 PM
 */

namespace WCurtis\Timer;

use WCurtis\Config;

/**
 * Class TimerAbstract
 *
 * Intended to persist start and stop times process startup. Filesystem, DB, whatever
 *
 * @package WCurtis\Timer
 */
abstract class TimerAbstract {
    /* Whether to round the time */
    protected $round = true;

    /* Minutes to round to. E.g.:
     * 15
     *  24 => 30
     *  20 => 15
     */
    protected $roundMinutes = 15;

    public function __construct($tz = null) {
        $this->tz = Config::GetTz($tz);
    }

    public abstract function getCurrentData();
    public abstract function GetStartTime($name);
    public abstract function SetStartTime($name, \DateTime $dt);
    public abstract function GetStopTime($name);
    public abstract function SetStopTime($name, \DateTime $dt);
    public abstract function ClearTimer($name);

    public function StartTimer($name, $dt = null) {
        $dt = ($dt === null || !($dt instanceof \DateTime))
            ? Config::GetNow($this->tz)
            : $dt;

        $startTime = $this->GetStartTime($name);

        if($startTime) throw new \Exception("Cannot start an already started timer");

        $this->SetStartTime($name, $dt);

        return $dt;
    }

    public function StopTimer($name, $dt = null) {
        if(!$this->GetStartTime($name)) {
            throw new \Exception("Timer hasn't been started for $name");
        }

        $dt = ($dt === null || !($dt instanceof \DateTime))
            ? Config::GetNow($this->tz)
            : $dt;

        $this->SetStopTime($name, $dt);
    }

    protected function RoundMinutes($seconds, $roundMinutes) {
        $hourPartitions = round($seconds / (60 * $roundMinutes), 0);

        return $hourPartitions * $roundMinutes;
    }

    public function GetElapsed($name, $round = false, $roundMinutes = 15) {
        $startTime = $this->GetStartTime($name);
        $stopTime = $this->GetStopTime($name);

        if(!$startTime || !$stopTime) throw new \Exception("Either no start or stop time");

        $startTs = (int)$startTime->format('U');
        $stopTs = (int)$stopTime->format('U');

        $seconds = $stopTs - $startTs;

        if($round) return $this->RoundMinutes($seconds, $roundMinutes);
        else return ceil($seconds / 60);
    }

    public function setRound($round)
    {
        $this->round = $round;
        return $this;
    }

    public function getRound()
    {
        return $this->round;
    }

    public function setRoundMinutes($roundMinutes)
    {
        $this->roundMinutes = $roundMinutes;
        return $this;
    }

    public function getRoundMinutes()
    {
        return $this->roundMinutes;
    }

    protected static function getDate($thing) {
        if(!$thing) return null;
        if($thing instanceof \DateTime) return $thing;
        if(is_string($thing)) {
            $date = date_create($thing);
            if(!$date) return null;

            return $date;
        }

        return null;
    }
}
