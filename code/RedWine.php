<?php
// 六桶酒的升数数组
$arr = [30, 32, 36, 38, 40, 62];
$len = count($arr);
// 随机取两桶假设为白酒
for ($i = 0; $i < $len-1; $i++) {
    for ($j = 1 + $i; $j < $len; $j++) {
        $twoArr = $arr;
        // 去掉早上卖出的两桶白酒
        unset($twoArr[$i]);
        unset($twoArr[$j]);
        $twoArr = array_values($twoArr);
        // 从剩下的取出三桶假设为白酒
        for ($n = 0; $n < $len - 4; $n++) {
            for ($m = 1 + $n; $m < $len - 3; $m++) {
                for ($p = 1 + $m; $p < $len - 2; $p++) {
                    // 早上白酒总和
                    $oneSum = $arr[$i] + $arr[$j];
                    // 下午白酒总和
                    $twoSum = $twoArr[$n] + $twoArr[$m] + $twoArr[$p];
                    // 如果早上卖出白酒的升数的两倍等于下午卖出白酒的升数，那么即符合所求
                    if ($oneSum * 2 == $twoSum) {
                        echo '早上卖出的白酒是：' . $arr[$i] . '、'. $arr[$j] . '<br>';
                        echo '下午卖出的白酒是：' . $twoArr[$n] . '、'. $twoArr[$m] . '、'. $twoArr[$p] . '<br>';
                        echo '所有五桶白酒是：' . $arr[$i] . '、'. $arr[$j] . '、'. $twoArr[$n] . '、'. $twoArr[$m] . '、'. $twoArr[$p] . '<br>';
                        $tmpArr = $twoArr;
                        // 去掉下午卖出的三桶白酒
                        unset($tmpArr[$n]);
                        unset($tmpArr[$m]);
                        unset($tmpArr[$p]);
                        // 剩下的一桶就是所求红酒
                        echo '一桶红酒是：' . current($tmpArr) . '<hr>';
                    }
                }
            }
        }
    }
}