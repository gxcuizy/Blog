<?php

/**
 * 冒泡排序算法
 * @param array $arr
 * @return array
 */
function bubble_sort($arr) {
    // 判断参数是否为数组，且不为空
    if (!is_array($arr) || empty($arr)) {
        return $arr;
    }
    // 循环需要冒泡的轮数
    for ($i = 1, $len = count($arr); $i < $len; $i++) {
        // 循环每轮需要比较的次数
        for ($j = 0; $j < $len - $i; $j++) {
            // 大的数，交换位置，往后挪
            if ($arr[$j] > $arr[$j + 1]) {
                $temp = $arr[$j + 1];
                $arr[$j + 1] = $arr[$j];
                $arr[$j] = $temp;
            }
        }
    }
    return $arr;
}


/**
 * 选择排序法
 * @param array $arr
 * @return array
 */
function select_sort($arr) {
    // 判断参数是否为数组，且不为空
    if (!is_array($arr) || empty($arr)) {
        return $arr;
    }
    $len = count($arr);
    for ($i = 0; $i < $len - 1; $i++) {
        // 假设最小数的位置
        $min = $i;
        // 用假设的最小数和$i后面的数循环比较，找到实际的最小数
        for ($j = $i + 1; $j < $len; $j++) {
            // 后面的数比假设的最小数小，替换最小数
            if ($arr[$min] > $arr[$j]) {
                $min = $j;
            }
        }
        // 假设的最小数和实际不符，交换位置
        if ($min != $i) {
            $temp = $arr[$min];
            $arr[$min] = $arr[$i];
            $arr[$i] = $temp;
        }
    }
    return $arr;
}


/**
 * 插入排序法
 * @param array $arr
 * @return array
 */
function insert_sort($arr) {
    // 判断参数是否为数组，且不为空
    if (!is_array($arr) || empty($arr)) {
        return $arr;
    }
    $len = count($arr);
    for ($i = 1; $i < $len; $i++) {
        // 当前需要比较的临时数
        $tmp = $arr[$i];
        // 循环比较临时数所在位置前面的数
        for ($j = $i - 1; $j >= 0; $j--) {
            // 前面的数比临时数大，则交换位置
            if ($arr[$j] > $tmp) {
                $arr[$j + 1] = $arr[$j];
                $arr[$j] = $tmp;
            }
        }
    }
    return $arr;
}

/**
 * 快速排序法
 * @param array $arr
 * @return array
 */
function quick_sort($arr) {
    // 判断参数是否为数组，且不为空
    if (!is_array($arr) || empty($arr)) {
        return $arr;
    }
    // 数组长度为1停止排序
    $len = count($arr);
    if ($len == 1) {
        return $arr;
    }
    // 声明左右两个空数组
    $left = $right = [];
    // 循环遍历，把第一个元素当做基准数
    for ($i = 1; $i < $len; $i++) {
        // 比较当前数的大小，并放入对应的左右数组
        if ($arr[$i] > $arr[0]) {
            $right[] = $arr[$i];
        } else {
            $left[] = $arr[$i];
        }
    }
    // 递归比较
    $left = quick_sort($left);
    $right = quick_sort($right);
    // 左右两列以及基准数合并
    return array_merge($left, [$arr[0]], $right);
}


// 待排序数组
$arr = [1, 4, 5, 9, 3, 8, 6];
// 调用排序方法
$sort_arr = bubble_sort($arr);
// 输出打印
print_r($sort_arr);