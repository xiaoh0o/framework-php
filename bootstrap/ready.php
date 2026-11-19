<?php
## 准备阶段

require(__DIR__."/defines.php");
require(__DIR__."/functions.php");

// 时区
date_default_timezone_set("Asia/Shanghai");

// 开启 SESSION
if (PHP_SAPI != "cli" && session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
