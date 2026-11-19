<?php
namespace App\Core\Log;

interface LogHandlerInterface
{
    public function handle(LogModel $logModel): void;
}
