<?php

/**
 * 获取所有的质数
 * @param array $arr
 * @return array
 */
function get_prime_number($arr = []) {
    // 质数数组
    $primeArr = [];
    // 循环所有备选数
    foreach ($arr as $value) {
        // 备选数和备选数的中间数以下的数字整除比较
        for ($i = 2; $i <= floor($value / 2); $i++) {
            // 能够整除，则不是质数，退出循环
            if ($value % $i == 0) {
                break;
            }
        }
        // 被除数$j比备选数的中间数大的则为质数
        // 这样判断的依据：
        // 假如备选数为质数，则内层的for循环不会break退出，则执行完毕，$i会继续+1，即最后$i = floor($value / 2) + 1
        // 假如备选数不为质数，则内层的for循环遇到整除就会break退出，$i不会继续+1，即最后$i <= floor($value / 2)
        if ($value != 1 && $i > floor($value / 2)) {
            $primeArr[] = $value;
        }
    }
    return $primeArr;
}

/**
 * 获取所有的质数
 * @param array $arr
 * @return array
 */
function get_prime_number_two($arr = []) {
    // 质数数组
    $primeArr = [];
    // 循环所有备选数
    foreach ($arr as $value) {
        // 备选数和备选数的中间数以下的数字整除比较
        for ($i = 2; $i <= floor($value / $i); $i++) {
            // 能够整除，则不是质数，退出循环
            if ($value % $i == 0) {
                break;
            }
        }
        // 被除数$j比备选数的中间数大的则为质数
        // 这样判断的依据：
        // 假如备选数为质数，则内层的for循环不会break退出，则执行完毕，$i会继续+1，即最后$i = floor($value / $i) + 1
        // 假如备选数不为质数，则内层的for循环遇到整除就会break退出且$i不会继续+1，即最后$i <= floor($value / $i)
        if ($value != 1 && $i > floor($value / $i)) {
            $primeArr[] = $value;
        }
    }
    return $primeArr;
}

/**
 * 获取所有的质数
 * @param array $arr
 * @return array
 */
function get_prime_number_three($arr = []) {
    // 质数数组
    $primeArr = $arr;
    // 循环所有备选数
    foreach ($primeArr as $key => $value) {
        if ($value == 1) {
            unset($primeArr[$key]);
            continue;
        }
        // 备选数和备选数的中间数以下的数字整除比较
        for ($i = 2; $i <= floor($value / $i); $i++) {
            // 能够整除，则不是质数，从数组中删除且退出循环
            if ($value % $i == 0) {
                unset($primeArr[$key]);
                break;
            }
        }
    }
    // 重置数组索引返回
    return array_values($primeArr);
}

// 所有备选数数组
$numberArr = range(1, 10, 1);
// 获取备选数中的所有质数
$primeNumberArr = get_prime_number($numberArr);
// 输出打印
print_r($primeNumberArr);