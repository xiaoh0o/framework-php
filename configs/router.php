<?php
/**
 * 路由选择器（通过访问域名匹配配置文件）
 * 调用方：App\Core\Bootstrap
*/
return [
    "frontend" => require(ROOT_PATH."/routes/frontend.php"),
];
