<?php

/**
 * 普通递归
 * @param int $n
 * @return int
 */
function fib($n = 1)
{
    // 低位处理
    if ($n < 3) {
        return 1;
    }
    // 递归计算前两位
    return fib($n - 1) + fib($n - 2);
}

/**
 * 递归优化
 * @param int $n
 * @param int $a
 * @param int $b
 * @return int
 */
function fib_2($n = 1, $a = 1, $b = 1)
{
    if ($n > 2) {
        // 存储前一位，优化递归计算
        return fib_2($n - 1, $a + $b, $a);
    }
    return $a;
}

/**
 * 记忆化自底向上
 * @param int $n
 * @return int
 */
function fib_3($n = 1)
{
    $list = [];
    for ($i = 0; $i <= $n; $i++) {
        // 从低到高位数，依次存入数组中
        if ($i < 2) {
            $list[] = $i;
        } else {
            $list[] = $list[$i - 1] + $list[$i - 2];
        }
    }
    // 返回最后一个数，即第N个数
    return $list[$n];
}

/**
 * 自底向上进行迭代
 * @param int $n
 * @return int
 */
function fib_4($n = 1)
{
    // 低位处理
    if ($n <= 0) {
        return 0;
    }
    if ($n < 3) {
        return 1;
    }
    $a = 0;
    $b = 1;
    // 循环计算
    for ($i = 2; $i < $n; $i++) {
        $b = $a + $b;
        $a = $b - $a;
    }
    return $b;
}

/**
 * 公式法
 * @param int $n
 * @return int
 */
function fib_5($n = 1)
{
    // 黄金分割比
    $radio = (1 + sqrt(5)) / 2;
    // 斐波那契序列和黄金分割比之间的关系计算
    $num = intval(round(pow($radio, $n) / sqrt(5)));
    return $num;
}

/**
 * 无敌欠揍法
 * @param int $n
 * @return int
 */
function fib_6($n = 1)
{
    // 列举了30个数
    $list = [1, 1, 2, 3, 5, 8, 13, 21, 34, 55, 89, 144, 233, 377, 610, 987, 1597, 2584, 4181, 6765, 10946, 17711, 28657, 46368, 75025, 121393, 196418, 317811, 514229, 832040, 1346269];
    return $list[$n];
}