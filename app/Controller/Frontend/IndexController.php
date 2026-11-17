<?php
namespace App\Controller\Frontend;

/**
 * 首页控制器
 */
class IndexController extends Controller
{
    public function index()
    {
        return $this->success([
            "time" => date("Y-m-d H:i:s")
        ]);
    }
}
