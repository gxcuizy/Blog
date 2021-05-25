<?php

/**
 * 这是一个测试Demo类
 * Author: gxcuizy
 * Date: 2021-05-25 13:57:16
 * Class TestClass
 */
class TestDemo
{
    // 声明一个常量
    const HELLO_WORLD = 'all';
    // 声明一个公共变量
    public $user_name = '';
    // 声明一个静态变量
    public static $user_age = 18;

    /**
     * 这是一个测试方法
     * @param string $msg 参数说明
     * @return array
     */
    public function testAction($msg = '')
    {
        // 返回数据格式
        $return = array('code' => 200, 'msg' => '');
        // 信息为空直接返回
        if (empty($msg)) {
            return $return;
        }
        // 赋值处理
        $return['msg'] = $msg;
        return $return;
    }

    /**
     * 这是私有方法
     * @param string $arg 参数说明
     * @return string
     */
    private function privateAction($arg = '')
    {
        return $arg;
    }

    /**
     * 获取两个数相加的和
     * @param int $one 第一个数
     * @param int $two 第二个数
     * @return int
     */
    public function getUserAge($one = 0, $two = 0)
    {
        $sum = $one + $two;
        return $sum;
    }

    /**
     * 判断用户是否成年
     * @param int $age 年龄
     */
    public function logicAction($age = 18)
    {
        if ($age >= 18) {
            echo '已成年';
        } else {
            echo '未成年';
        }
    }
}