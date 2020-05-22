<?php
/**
 * 获取两个标准时间格式的时间差（Y-m-d H:i:s）
 * @param string $start_time 开始时间
 * @param string $end_time 结束时间
 * @return array
 */
function get_diff_time_data($start_time = '', $end_time = '')
{
    $data = ['diff' => 0, 'day' => 0, 'hour' => 0, 'minute' => 0, 'second' => 0];
    if (empty($start_time) || empty($end_time)) {
        return $data;
    }
    // 开始时间的时间戳
    $start_time = strtotime($start_time);
    // 结束时间的时间戳
    $end_time = strtotime($end_time);
    // 得出时间戳差值
    $diff = $end_time - $start_time;
    if ($diff < 0) {
        return $data;
    }
    // 舍去法取整，获取天数
    $day = floor($diff / 3600 / 24);
    // 小时数
    $hour = floor(($diff % (3600 * 24)) / 3600);
    // 分钟
    $minute = floor(($diff % (3600 * 24)) % 3600 / 60);
    // 秒数
    $second = floor(($diff % (3600 * 24)) % 60);
    $data['diff'] = $diff;
    $data['day'] = $day;
    $data['hour'] = $hour;
    $data['minute'] = $minute;
    $data['second'] = $second;
    return $data;
}

/**
 * 手动创建一个唯一的UUID
 * @return string
 */
function create_uuid()
{
    // 根据当前时间（微秒计）生成唯一ID
    mt_srand((double)microtime() * 10000);
    $uuid = strtolower(md5(uniqid(rand(), true)));
    return $uuid;
}

/**
 * 返回格式化时间
 * @param int $time 时间戳
 * @param string $format 时间格式
 * @return bool|string
 */
function time_format($time = 0, $format = '')
{
    // 默认时间格式
    if (empty($format)) {
        $format = 'Y-m-d H:i:s';
    }
    $format_time = date($format, $time);
    return $format_time;
}

/**
 * 返回格式化数字
 * @param int $number 待格式化数字
 * @param int $decimals 保留小数位数，默认2位
 * @param string $dec_point 整数和小数分隔符号
 * @param string $thousands_sep 整数部分每三位数读分隔符号
 * @return string
 */
function number_format_plus($number = 0, $decimals = 2, $dec_point = '.', $thousands_sep = ',')
{
    $format_num = '0.00';
    if (is_numeric($number)) {
        $format_num = number_format($number, $decimals, $dec_point, $thousands_sep);
    }
    return $format_num;
}

/**
 * 人民币数字小写转大写
 * @param string $number 人民币数值
 * @param string $int_unit 币种单位，默认"元"，有的需求可能为"圆"
 * @param bool $is_round 是否对小数进行四舍五入
 * @param bool $is_extra_zero 是否对整数部分以0结尾，小数存在的数字附加0,比如1960.30
 * @return string
 */
function rmb_format($money = 0, $int_unit = '元', $is_round = true, $is_extra_zero = false)
{
    // 非数字，原样返回
    if (!is_numeric($money)) {
        return $money;
    }
    // 将数字切分成两段
    $parts = explode('.', $money, 2);
    $int = isset($parts[0]) ? strval($parts[0]) : '0';
    $dec = isset($parts[1]) ? strval($parts[1]) : '';
    // 如果小数点后多于2位，不四舍五入就直接截，否则就处理
    $dec_len = strlen($dec);
    if (isset($parts[1]) && $dec_len > 2) {
        $dec = $is_round ? substr(strrchr(strval(round(floatval("0." . $dec), 2)), '.'), 1) : substr($parts [1], 0, 2);
    }
    // 当number为0.001时，小数点后的金额为0元
    if (empty($int) && empty($dec)) {
        return '零';
    }
    // 定义
    $chs = ['0', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖'];
    $uni = ['', '拾', '佰', '仟'];
    $dec_uni = ['角', '分'];
    $exp = ['', '万'];
    $res = '';
    // 整数部分从右向左找
    for ($i = strlen($int) - 1, $k = 0; $i >= 0; $k++) {
        $str = '';
        // 按照中文读写习惯，每4个字为一段进行转化，i一直在减
        for ($j = 0; $j < 4 && $i >= 0; $j++, $i--) {
            // 非0的数字后面添加单位
            $u = $int{$i} > 0 ? $uni [$j] : '';
            $str = $chs [$int{$i}] . $u . $str;
        }
        // 去掉末尾的0
        $str = rtrim($str, '0');
        // 替换多个连续的0
        $str = preg_replace("/0+/", "零", $str);
        if (!isset($exp [$k])) {
            // 构建单位
            $exp [$k] = $exp [$k - 2] . '亿';
        }
        $u2 = $str != '' ? $exp [$k] : '';
        $res = $str . $u2 . $res;
    }
    // 如果小数部分处理完之后是00，需要处理下
    $dec = rtrim($dec, '0');
    // 小数部分从左向右找
    if (!empty($dec)) {
        $res .= $int_unit;
        // 是否要在整数部分以0结尾的数字后附加0，有的系统有这要求
        if ($is_extra_zero) {
            if (substr($int, -1) === '0') {
                $res .= '零';
            }
        }
        for ($i = 0, $cnt = strlen($dec); $i < $cnt; $i++) {
            // 非0的数字后面添加单位
            $u = $dec{$i} > 0 ? $dec_uni [$i] : '';
            $res .= $chs [$dec{$i}] . $u;
            if ($cnt == 1)
                $res .= '整';
        }
        // 去掉末尾的0
        $res = rtrim($res, '0');
        // 替换多个连续的0
        $res = preg_replace("/0+/", "零", $res);
    } else {
        $res .= $int_unit . '整';
    }
    return $res;
}

/**
 * 导出excel表格数据
 * @param array $data 表格数据，一个二维数组
 * @param array $title 第一行标题，一维数组
 * @param string $filename 下载的文件名
 */
function export_excel($data = [], $title = [], $filename = '')
{
    // 默认文件名为时间戳
    if (empty($filename)) {
        $filename = time();
    }
    // 定义输出header信息
    header("Content-type:application/octet-stream;charset=GBK");
    header("Accept-Ranges:bytes");
    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=" . $filename . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    ob_start();
    echo "<head><meta http-equiv='Content-type' content='text/html;charset=GBK' /></head> <table border=1 style='text-align:center'>\n";
    // 导出xls开始，先写表头
    if (!empty($title)) {
        foreach ($title as $k => $v) {
            $title[$k] = iconv("UTF-8", "GBK//IGNORE", $v);
        }
        $title = "<td>" . implode("</td>\t<td>", $title) . "</td>";
        echo "<tr>$title</tr>\n";
    }
    // 再写表数据
    if (!empty($data)) {
        foreach ($data as $key => $val) {
            foreach ($val as $ck => $cv) {
                if (is_numeric($cv) && strlen($cv) < 12) {
                    $data[$key][$ck] = '<td>' . mb_convert_encoding($cv, "GBK", "UTF-8") . "</td>";
                } else {
                    $data[$key][$ck] = '<td style="vnd.ms-excel.numberformat:@;">' . iconv("UTF-8", "GBK//IGNORE", $cv) . "</td>";
                }
            }
            $data[$key] = "<tr>" . implode("\t", $data[$key]) . "</tr>";
        }
        echo implode("\n", $data);
    }
    echo "</table>";
    ob_flush();
    exit;
}

/**
 * 支持断点续传，下载文件
 * @param string $file 下载文件完整路径
 */
function download_file_resume($file)
{
    // 检测文件是否存在
    if (!is_file($file)) {
        die("非法文件下载！");
    }
    // 打开文件
    $fp = fopen("$file", "rb");
    // 获取文件大小
    $size = filesize($file);
    // 获取文件名称
    $filename = basename($file);
    // 获取文件扩展名
    $file_extension = strtolower(substr(strrchr($filename, "."), 1));
    // 根据扩展名 指出输出浏览器格式
    switch ($file_extension) {
        case "exe":
            $ctype = "application/octet-stream";
            break;
        case "zip":
            $ctype = "application/zip";
            break;
        case "mp3":
            $ctype = "audio/mpeg";
            break;
        case "mpg":
            $ctype = "video/mpeg";
            break;
        case "avi":
            $ctype = "video/x-msvideo";
            break;
        default:
            $ctype = "application/force-download";
    }
    // 通用header头信息
    header("Cache-Control:");
    header("Cache-Control: public");
    header("Content-Type: $ctype");
    header("Content-Disposition: attachment; filename=$filename");
    header("Accept-Ranges: bytes");
    // 如果有$_SERVER['HTTP_RANGE']参数
    if (isset($_SERVER['HTTP_RANGE'])) {
        // 断点后再次连接$_SERVER['HTTP_RANGE']的值
        list($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);
        str_replace($range, "-", $range);
        // 文件总字节数
        $size2 = $size - 1;
        // 获取下次下载的长度
        $new_length = $size2 - $range;
        header("HTTP/1.1 206 Partial Content");
        // 输入总长
        header("Content-Length: $new_length");
        header("Content-Range: bytes $range$size2/$size");
        // 设置指针位置
        fseek($fp, $range);
    } else {
        // 第一次连接下载
        $size2 = $size - 1;
        header("Content-Range: bytes 0-$size2/$size");
        // 输出总长
        header("Content-Length: " . $size);
    }
    // 虚幻输出
    while (!feof($fp)) {
        // 设置文件最长执行时间
        set_time_limit(0);
        // 输出文件
        print(fread($fp, 1024 * 8));
        // 输出缓冲
        flush();
        ob_flush();
    }
    fclose($fp);
    exit;
}

/**
 * 获取用户真实的IP地址
 * @return mixed
 */
function get_real_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * 获取短网址链接
 * @param string $url 长网址
 * @return string
 */
function get_short_url($url = '')
{
    // 直接请求第三方接口地址，获取短URL
    $api_url = 'http://tinyurl.com/api-create.php?url=';
    $short_url = file_get_contents($api_url . $url);
    return $short_url;
}

// **********转换类***************

/**
 * 将xml格式转换为数组
 * @param string $xml xml字符串
 * @return mixed
 */
function xml_to_array($xml = '')
{
    // 利用函数simplexml_load_string()把xml字符串载入对象中
    $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    // 编码对象后，再解码即可得到数组
    $arr = json_decode(json_encode($obj), true);
    return $arr;
}

/**
 * 隐藏手机号中间四位数为****
 * @param string $mobile 正常手机号
 * @return mixed
 */
function replace_phone($mobile = '')
{
    $new_mobile = substr_replace($mobile, '****', 3, 4);
    return $new_mobile;
}

/**
 * 最简单的Ajax请求返回数据格式
 * @param string $msg 返回提示信息
 * @param int $code 返回标识符号
 * @param array $data 返回数据
 */
function ajax_return($msg = '', $code = 0, $data = [])
{
    $return['code'] = $code;
    $return['msg'] = $msg;
    $return['data'] = $data;
    exit(json_encode($return, JSON_UNESCAPED_UNICODE));
}

/**
 * 截取字符串，超出部分用省略符号显示
 * @param string $text 待截取字符串
 * @param int $length 截取长度，默认全部截取
 * @param string $rep 截取超出替换的字符串，默认为省略号
 * @return string
 */
function cut_string($text = '', $length = 0, $rep = '…')
{
    if (!empty($length) && mb_strlen($text, 'utf8') > $length) {
        $text = mb_substr($text, 0, $length, 'utf8') . $rep;
    }
    return $text;
}

/**
 * CURL请求之GET方式
 * @param string $url 请求接口地址
 * @return bool|mixed
 */
function curl_get($url = '')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // 不验证SSL证书。
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

/**
 * CURL请求之POST方式
 * @param string $url 请求接口地址
 * @param array $data 请求参数
 * @param int $timeout 超时时间
 * @return mixed
 */
function curl_post($url = '', $data = [], $timeout = 3000)
{
    $post_data = http_build_query($data, '', '&');
    header("Content-type:text/html;charset=utf-8");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

/**
 * 获取毫秒数
 * @return string
 */
function get_millisecond()
{
    list($t1, $t2) = explode(' ', microtime());
    $ms = sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    return $ms;
}

/**
 * 日期时间显示格式转换
 * @param int $time 时间戳
 * @return bool|string
 */
function transfer_show_time($time = 0)
{
    // 时间显示格式
    $day_time = date("m-d H:i", $time);
    $hour_time = date("H:i", $time);
    // 时间差
    $diff_time = time() - $time;
    $date = $day_time;
    if ($diff_time < 60) {
        $date = '刚刚';
    } else if ($diff_time < 60 * 60) {
        $min = floor($diff_time / 60);
        $date = $min . '分钟前';
    } else if ($diff_time < 60 * 60 * 24) {
        $h = floor($diff_time / (60 * 60));
        $date = $h . '小时前 ' . $hour_time;
    } else if ($diff_time < 60 * 60 * 24 * 3) {
        $day = floor($diff_time / (60 * 60 * 24));
        if ($day == 1) {
            $date = '昨天 ' . $day_time;
        } else {
            $date = '前天 ' . $day_time;
        }
    }
    return $date;
}

// ***********身份证号的信息

/**
 * 校验是否为合法的身份证号
 * @param string $id_card 身份证号
 * @return bool
 */
function check_id_card($id_card = '')
{
    $pattern = '/^\d{6}((1[89])|(2\d))\d{2}((0\d)|(1[0-2]))((3[01])|([0-2]\d))\d{3}(\d|X)$/i';
    $res = preg_match($pattern, $id_card) ? true : false;
    return $res;
}

/**
 * 通过身份证号计算年龄
 * @param string $id_card 身份证号
 * @return int
 */
function get_age_by_id_card($id_card = '')
{
    // 获取出生年月日
    $year = substr($id_card, 6, 4);
    $month = substr($id_card, 10, 2);
    $day = substr($id_card, 12, 2);
    // 计算时间年月日差
    $age = date("Y", time()) - $year;
    $diff_month = date("m") - $month;
    $diff_day = date("d") - $day;
    // 不满周岁年龄减1
    if ($age < 0 || $diff_month < 0 || $diff_day < 0) {
        $age--;
    }
    return $age;
}

/**
 * 通过身份证获取所属星座
 * @param string $id_card 身份证号
 * @return string
 */
function get_constellation_by_card($id_card = '')
{
    // 截取生日的时间
    $birthday = substr($id_card, 10, 4);
    $month = substr($birthday, 0, 2);
    $day = substr($birthday, 2);
    // 判断时间范围获取星座
    $constellation = "";
    if (($month == 1 && $day >= 21) || ($month == 2 && $day <= 19)) $constellation = "水瓶座";
    else if (($month == 2 && $day >= 20) || ($month == 3 && $day <= 20)) $constellation = "双鱼座";
    else if (($month == 3 && $day >= 21) || ($month == 4 && $day <= 20)) $constellation = "白羊座";
    else if (($month == 4 && $day >= 21) || ($month == 5 && $day <= 21)) $constellation = "金牛座";
    else if (($month == 5 && $day >= 22) || ($month == 6 && $day <= 21)) $constellation = "双子座";
    else if (($month == 6 && $day >= 22) || ($month == 7 && $day <= 22)) $constellation = "巨蟹座";
    else if (($month == 7 && $day >= 23) || ($month == 8 && $day <= 23)) $constellation = "狮子座";
    else if (($month == 8 && $day >= 24) || ($month == 9 && $day <= 23)) $constellation = "处女座";
    else if (($month == 9 && $day >= 24) || ($month == 10 && $day <= 23)) $constellation = "天秤座";
    else if (($month == 10 && $day >= 24) || ($month == 11 && $day <= 22)) $constellation = "天蝎座";
    else if (($month == 11 && $day >= 23) || ($month == 12 && $day <= 21)) $constellation = "射手座";
    else if (($month == 12 && $day >= 22) || ($month == 1 && $day <= 20)) $constellation = "魔羯座";
    return $constellation;
}

/**
 * 通过身份证获取所属生肖
 * @param string $id_card 身份证号
 * @return string
 */
function get_animal_by_card($id_card = '')
{
    // 获取出生年份
    $start = 1901;
    $end = substr($id_card, 6, 4);
    $remainder = ($start - $end) % 12;
    // 计算所属生肖
    $animal = "";
    if ($remainder == 1 || $remainder == -11) $animal = "鼠";
    else if ($remainder == 0) $animal = "牛";
    else if ($remainder == 11 || $remainder == -1) $animal = "虎";
    else if ($remainder == 10 || $remainder == -2) $animal = "兔";
    else if ($remainder == 9 || $remainder == -3) $animal = "龙";
    else if ($remainder == 8 || $remainder == -4) $animal = "蛇";
    else if ($remainder == 7 || $remainder == -5) $animal = "马";
    else if ($remainder == 6 || $remainder == -6) $animal = "羊";
    else if ($remainder == 5 || $remainder == -7) $animal = "猴";
    else if ($remainder == 4 || $remainder == -8) $animal = "鸡";
    else if ($remainder == 3 || $remainder == -9) $animal = "狗";
    else if ($remainder == 2 || $remainder == -10) $animal = "猪";
    return $animal;
}

/**
 * 通过身份证获取性别
 * @param string $id_card 身份证号
 * @return string
 */
function get_sex($id_card = '')
{
    // 第十七位数字，偶数标识女性，奇数标识男性
    $sex_num = substr($id_card, 16, 1);
    $sex = $sex_num % 2 === 0 ? '女' : '男';
    return $sex;
}

/**
 * 通过身份证判断是否成年
 * @param string $id_card 身份证号
 * @return bool
 */
function check_adult($id_card = '')
{
    // 获取出生年月日
    $year = substr($id_card, 6, 4);
    $month = substr($id_card, 10, 2);
    $day = substr($id_card, 12, 2);
    // 18年的秒数
    $adult_time = 18 * 365 * 24 * 60 * 60;
    // 出生年月日的时间戳
    $birthday_time = mktime(0, 0, 0, $month, $day, $year);
    // 是否成年，默认未成年
    $is_adult = false;
    // 超过18岁则成年
    if ((time() - $birthday_time) > $adult_time) {
        $is_adult = true;
    }
    return $is_adult;
}

/**
 * 通过身份证号获取省市区地址信息
 * @param string $id_card
 * @return array
 */
function get_address_by_id_card($id_card = '')
{
    // 先引入政府代码数组数据文件
    require 'area_code.php';
    require 'province_code.php';
    // 获取政府代码前两位和前六位
    $two_code = substr($id_card, 0, 2);
    $six_code = substr($id_card, 0, 6);
    // 获取城市信息
    $province = $province_code[$two_code];
    $address = $area_code[$six_code];
    $res = [
        'province' => $province,
        'area' => $address
    ];
    return $res;
}

// ***********验证类

/**
 * 校验是否为合法格式的手机号
 * @param string $mobile 手机号码
 * @return bool
 */
function check_mobile($mobile = '')
{
    // 非数字直接false
    if (!is_numeric($mobile)) {
        return false;
    }
    $pattern = '/^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,3,7,8]{1}\d{8}$|^18[\d]{9}$|^19[9]{1}\d{8}$/';
    $res = preg_match($pattern, $mobile) ? true : false;
    return $res;
}

/**
 * 校验是否为合法格式的电话号码
 * @param string $telephone 电话号码
 * @return bool
 */
function check_telephone($telephone = '')
{
    $pattern = '/^(0[0-9]{2,3})?[-]?\d{7,8}$/';
    $res = preg_match($pattern, $telephone) ? true : false;
    return $res;
}

/**
 * 校验是否为合法格式的邮箱
 * @param string $email 邮箱
 * @return bool
 */
function check_email($email = '')
{
    $pattern = '/([\w\-]+\@[\w\-]+\.[\w\-]+)/';
    $res = preg_match($pattern, $email) ? true : false;
    return $res;
}

/**
 *  校验是否为合法的IP地址
 * @param string $ip IP地址
 * @return bool
 */
function check_ip($ip = '')
{
    $pattern = '/^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$/';
    $res = preg_match($pattern, $ip) ? true : false;
    return $res;
}

/**
 * 校验指定范围长度的字符串名称
 * @param string $name 名称
 * @param int $min 最小长度
 * @param int $max 最大长度
 * @param string $char 字符串类型：EN英文，CN中文，ALL全部字符
 * @return bool
 */
function check_name($name = '',$min = 2, $max = 20,  $char = 'ALL')
{
    switch ($char) {
        case 'EN':
            $pattern = '/^[a-zA-Z]{' . $min . ',' . $max . '}$/iu';
            break;
        case 'CN':
            $pattern = '/^[_\x{4e00}-\x{9fa5}]{' . $min . ',' . $max . '}$/iu';
            break;
        default:
            $pattern = '/^[_\w\d\x{4e00}-\x{9fa5}]{' . $min . ',' . $max . '}$/iu';
    }
    $res = preg_match($pattern, $name) ? true : false;
    return $res;
}

/**
 * 校验是否为合法的邮政编码
 * @param string $code 邮政编码
 * @return bool
 */
function check_post_code($code = '')
{
    $pattern = '/\d{6}/';
    $res = preg_match($pattern, $code) ? true : false;
    return $res;
}

/**
 * 校验是否为合法格式的日期
 * @param string $date 日期
 * @param string $sep 分隔符，默认为横线-
 * @return bool
 */
function check_date($date = '', $sep = '-')
{
    $date_arr = explode($sep, $date);
    $res = false;
    // 校验日期是否为合法数字
    if (count($date_arr) == 3 && is_numeric($date_arr[0]) && is_numeric($date_arr[1]) && is_numeric($date_arr[2])) {
        $res = checkdate($date_arr[1], $date_arr[2], $date_arr[0]);
    }
    return $res;
}

/**
 * 校验是否为合法格式的时间
 * @param string $time 时分秒时间
 * @param string $sep 分隔符，默认为冒号:
 * @return bool
 */
function check_time($time = '', $sep = ":")
{
    $time_arr = explode($sep, $time);
    $res = false;
    // 校验时间的时分秒是否在合理范围内
    if (count($time_arr) == 3 && is_numeric($time_arr[0]) && is_numeric($time_arr[1]) && is_numeric($time_arr[2])) {
        if (($time_arr[0] >= 0 && $time_arr[0] <= 23) && ($time_arr[1] >= 0 && $time_arr[1] <= 59) && ($time_arr[2] >= 0 && $time_arr[2] <= 59)) {
            $res = true;
        }
    }
    return $res;
}

// ***********功能类

/**
 * 获取指定长度的随机字符串
 * @param int $length 随机字符串的长度
 * @return string
 */
function get_random_str($length = 9)
{
    // 字符串集合，全部大小写字母和数字
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $str = '';
    // 循环多次，随机逐次获取
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $str;
}

/**
 * 统计文章中文字和图片的数量
 * @param string $html 文章html字符串
 * @return array
 */
function count_word_img($html = '')
{
    // 匹配img标签的数量
    preg_match_all('/<img /i', $html, $match_arr);
    $img_count = count($match_arr[0]);
    // 统计非img标签的数量，可根据实际情况进行调整表达式
    $pattern = '/\[#img_[0-9]+_[a-z]*_[0-9]+_[a-zA-Z]*/i';
    preg_match_all($pattern, $html, $match_arr);
    $img_count += count($match_arr[0]);
    // 去掉图片img标签
    $html = preg_replace("/<img([^>].+)>/iU", "", $html);
    // 去掉非标签的图片
    $html = preg_replace($pattern, "", $html);
    // 去掉全部空格
    $html = str_replace(' ', '', $html);
    // 先去除HTML和PHP标记，再统计字数
    $word_count = mb_strlen(trim(strip_tags($html)), 'UTF-8');
    return ['word_count' => $word_count, 'img_count' => $img_count];
}

/**
 * 格式化打印输出内容
 * @param void $data 需要打印的内容
 * @param bool $exit
 */
function dump_plus($data, $exit = true)
{
    // 自定义样式，方便美观查看
    $output = '<pre style="display: block;background-color: #f5f5f5;border: 1px solid #cccccc;padding: 10px;margin: 45px 0 0 0;font-size: 13px;line-height: 1.5;border-radius: 4px;">';
    // boolean或者null类型直接文字输出，其他print_r格式化输出
    if (is_bool($data)) {
        $show = $data ? 'true' : 'false';
    } else if (is_null($data)) {
        $show = 'null';
    } else {
        $show = var_export($data, true);
    }
    // 拼接文本输出
    $output .= $show;
    $output .= '</pre>';
    echo $output;
    // 是否中断执行
    if ($exit) {
        exit();
    }
}

/**
 * 通过IP地址获取位置信息
 * markdown快速使用 ![](https://tool.lu/netcard/)
 * @param string $ip IP地址
 * @return mixed
 */
function get_ip_info($ip = '')
{
    $api = 'http://www.geoplugin.net/json.gp?ip=';
    $res_data = file_get_contents($api . $ip);
    $res = json_decode($res_data, true);
    $data = [];
    if ($res['geoplugin_status'] == '200') {
        // 国家
        $data['country'] = $res['geoplugin_countryName'];
        // 省份
        $data['province'] = $res['geoplugin_regionName'];
        // 城市
        $data['city'] = $res['geoplugin_city'];
        // 经度
        $data['latitude'] = $res['geoplugin_latitude'];
        // 纬度
        $data['longitude'] = $res['geoplugin_longitude'];
        // 其他值，根据需要自定义
    }
    return $data;
}

/**
 * 发红包，金额随机
 * @param int $total 红包金额
 * @param int $num 红包个数
 * @param float $min 红包最小金额
 * @@return array
 */
function get_red_packet($total = 0, $num = 1, $min = 0.01)
{
    $data = [];
    for ($i = 1; $i < $num; $i++) {
        // 随机金额安全上限控制
        $safe_total = ($total - ($num - $i) * $min) / ($num - $i);
        $money = mt_rand($min * 100, $safe_total * 100) / 100;
        $total -= $money;
        // sort为领取顺序，money为红包金额，balance为领取后的余额
        $data[] = [
            'sort' => $i,
            'money' => $money,
            'balance' => $total
        ];
    }
    // 最后一个红包
    $data[] = [
        'sort' => $num,
        'money' => $total,
        'balance' => 0
    ];
    return $data;
}

/**
 * 获取文字的首字母
 * @param string $str 文字字符串
 * @return string
 */
function get_first_char($str = '')
{
    $first_char = $str[0];
    // 判断是否为字符串
    if (ord($first_char) >= ord("A") && ord($first_char) <= ord("z")) {
        return strtoupper($first_char);
    }
    $str = iconv("UTF-8", "gb2312", $str);
    $asc = ord($str[0]) * 256 + ord($str[1]) - 65536;
    $first_char = '';
    if ($asc >= -20319 and $asc <= -20284) $first_char = "A";
    else if ($asc >= -20283 and $asc <= -19776) $first_char = "B";
    else if ($asc >= -19775 and $asc <= -19219) $first_char = "C";
    else if ($asc >= -19218 and $asc <= -18711) $first_char = "D";
    else if ($asc >= -18710 and $asc <= -18527) $first_char = "E";
    else if ($asc >= -18526 and $asc <= -18240) $first_char = "F";
    else if ($asc >= -18239 and $asc <= -17923) $first_char = "G";
    else if ($asc >= -17922 and $asc <= -17418) $first_char = "H";
    else if ($asc >= -17417 and $asc <= -16475) $first_char = "J";
    else if ($asc >= -16474 and $asc <= -16213) $first_char = "K";
    else if ($asc >= -16212 and $asc <= -15641) $first_char = "L";
    else if ($asc >= -15640 and $asc <= -15166) $first_char = "M";
    else if ($asc >= -15165 and $asc <= -14923) $first_char = "N";
    else if ($asc >= -14922 and $asc <= -14915) $first_char = "O";
    else if ($asc >= -14914 and $asc <= -14631) $first_char = "P";
    else if ($asc >= -14630 and $asc <= -14150) $first_char = "Q";
    else if ($asc >= -14149 and $asc <= -14091) $first_char = "R";
    else if ($asc >= -14090 and $asc <= -13319) $first_char = "S";
    else if ($asc >= -13318 and $asc <= -12839) $first_char = "T";
    else if ($asc >= -12838 and $asc <= -12557) $first_char = "W";
    else if ($asc >= -12556 and $asc <= -11848) $first_char = "X";
    else if ($asc >= -11847 and $asc <= -11056) $first_char = "Y";
    else if ($asc >= -11055 and $asc <= -10247) $first_char = "Z";
    return $first_char;
}

/**
 * 删除指定目录下的文件夹和文件
 * @param string $path 目录路径
 */
function delete_dir($path = '')
{
    // 为空默认当前目录
    if ($path == '') {
        $path = realpath('.');
    }
    // 判断目录是否存在
    if (!is_dir($path)) {
        exit('目录【' . $path . '】不存在');
    }
    // 删除path目录最后的/
    if (substr($path, -1, 1) == '/') {
        $path = substr_replace($path, '', -1, 1);
    }
    // 扫描一个文件夹内的所有文件夹和文件
    $file_arr = scandir($path);
    foreach ($file_arr as $file) {
        // 排除当前目录.与父级目录..
        if ($file != "." && $file != "..") {
            // 如果是目录则递归子目录
            $this_file = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($this_file)) {
                // 继续循环遍历子目录
                delete_dir($this_file);
                // 删除空文件夹
                @rmdir($this_file);
            } else if (is_file($this_file)) {
                // 文件类型直接删除
                unlink($this_file);
            }
        }
    }
}