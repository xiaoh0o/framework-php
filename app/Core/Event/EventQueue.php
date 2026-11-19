<?php
namespace App\Core\Event;

use App\Core\Queue\Queue;

class EventQueue extends Queue
{
    public function __construct()
    {
        parent::__construct("Event", "redis");
    }
}
