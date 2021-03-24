<?php

/**
 * 单例类（单例类不能被直接实例化，只能是由自身实例化）
 * Class Single
 */
class Single
{
    // 实例化对象变量
    public static $instance;

    /**
     * 构造函数私有化，防止外部实例化调用
     * Single constructor.
     */
    private function __construct()
    {
    }

    /**
     * 复制对象函数私有化，防止复制对象
     */
    private function __clone()
    {
    }

    /**
     * 获取静态实例
     * @return Single
     */
    public static function getInstance()
    {
        if (self::$instance instanceof self === false) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 设置程序参数（TO-DO）
     * @return mixed
     */
    public function setParam()
    {
        return self::$instance;
    }

    /**
     * 测试方法
     */
    public function test()
    {
        echo 'Hello World.';
    }
}

// 测试调用
$instance = Single::getInstance();
$instance->test();