<?php

namespace Wechat\Controller;
use Think\Controller;

/**
 * -------------------------------------------------------
 * | 微信模块测试
 * |@author: duanbin
 * |@时间: 2016年9月28日 下午5:03:42
 * |@version: 1.0
 * -------------------------------------------------------
 */
class TestController extends Controller
{
    public function index()
    {
    	$this->show('wechat', 'utf-8');
    }
    
    
    
}