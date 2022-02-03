<?php

namespace Sagar290\Logable\Classes;



use Illuminate\Support\Facades\Log;

class Logable extends Log
{
    private $arrived;
    private $left;
    private $duration;
    private string $channel;


    public function __construct()
    {
        $this->channel = 'logable';
    }

    public function log($message, $level = 'info')
    {
        self::channel($this->channel)->$level($message);
    }

    public function setArrivingTime($time)
    {
        return $this->arrived =  $time;
    }

    public function setLeavingTime($time)
    {
        return $this->left = $time;
    }

    public function getArrivedTime()
    {
        return $this->arrived;
    }

    public function getLeftTime()
    {
        return $this->left;
    }

    public function getTotalDuration()
    {
        return $this->duration = $this->left - $this->arrived;
    }
}
