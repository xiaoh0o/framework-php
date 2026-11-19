<?php
namespace App\Core\Event;

/**
 * 保存事件主进程id
 */
class EventStore
{
    protected string $file = ROOT_PATH."/storage/event.pid";

    public function __construct()
    {
        $dir = dirname($this->file);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    // 保存
    public function set(int $pid)
    {
        file_put_contents($this->file, $pid);
    }

    public function get()
    {
        if (file_exists($this->file)) {
            $pid = file_get_contents($this->file);
            return intval($pid);
        } else {
            return 0;
        }
    }

    public function delete()
    {
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }
}
