<?php
namespace Api\Controller;
use Think\Controller;

/**
 * -------------------------------------------------------
 * | api模块测试
 * |@author: duanbin
 * |@时间: 2016年9月28日 下午5:05:35
 * |@version: 1.0
 * -------------------------------------------------------
 */
class TestController extends Controller
{
    public function index()
    {
    	$this->show('test', 'utf-8');
    }
}