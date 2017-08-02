<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2016/12/19
 * Time: 16:38
 */

namespace Frontend\Controller;

use Think\Controller;

class EmptyController extends Controller
{
    public function index(){
        $this->error("无法加载控制器：".CONTROLLER_NAME);
    }
}