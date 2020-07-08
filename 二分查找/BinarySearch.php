<?php
/**
 * 二分查找算法
 * @param array $arr 待查找区间
 * @param int $number 查找数
 * @return int        返回找到的键
 */
function binary_search($arr, $number) {
    // 非数组或者数组为空，直接返回-1
    if (!is_array($arr) || empty($arr)) {
        return -1;
    }
    // 初始变量值
    $len = count($arr);
    $lower = 0;
    $high = $len - 1;
    // 最低点比最高点大就退出
    while ($lower <= $high) {
        // 以中间点作为参照点比较
        $middle = intval(($lower + $high) / 2);
        if ($arr[$middle] > $number) {
            // 查找数比参照点小，舍去右边
            $high = $middle - 1;
        } else if ($arr[$middle] < $number) {
            // 查找数比参照点大，舍去左边
            $lower = $middle + 1;
        } else {
            // 查找数与参照点相等，则找到返回
            return $middle;
        }
    }
    // 未找到，返回-1
    return -1;
}

/**
 * @param array $arr 待查找区间
 * @param int $number 查找数
 * @param int $lower 区间最低点
 * @param int $high 区间最高点
 * @return int
 */
function binary_search_recursion(&$arr, $number, $lower, $high) {
    // 以区间的中间点作为参照点比较
    $middle = intval(($lower + $high) / 2);
    // 最低点比最高点大就退出
    if ($lower > $high) {
        return -1;
    }
    if ($number > $arr[$middle]) {
        // 查找数比参照点大，舍去左边继续查找
        return binary_search_recursion($arr, $number, $middle + 1, $high);
    } elseif ($number < $arr[$middle]) {
        // 查找数比参照点小，舍去右边继续查找
        return binary_search_recursion($arr, $number, $lower, $middle - 1);
    } else {
        return $middle;
    }
}

// 待查找区间
$arr = [1, 3, 7, 9, 11, 57, 63, 99];
// 非递归查找57所在的位置
$find_key = binary_search($arr, 57);
// 递归查找57所在的位置
$find_key_r = binary_search_recursion($arr, 57, 0, count($arr));
// 输出打印
print_r($find_key);
print_r($find_key_r);
