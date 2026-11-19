<?php
namespace App\Core\Event;

use App\Core\Log\Logger;

class EventLogger extends Logger
{
    public function __construct()
    {
        parent::__construct("Event");
    }
}
