<?php

/**
 * Created by PhpStorm.
 * User: gxcuizy
 * Date: 2021/02/20
 * Time: 下午 13:18
 * api接口请求封装类
 * Class ApiRequest
 */
class ApiRequest
{
    // 接口地址
    private static $url = '';
    // 请求header类型
    private static $type = '';
    // 接口密钥
    private static $token = '';
    // 请求参数
    private static $param = array();
    // 返回数组格式
    private static $result = array(
        'code' => 0,
        'msg' => '',
        'data' => array()
    );
    // header头类型数组
    private static $headers = array(
        '' => array(),
        'form-data' => array('Content-Type: multipart/form-data'),
        'x-www-form-urlencoded' => array('Content-Type：application/x-www-form-urlencoded'),
        'json' => array('Content-Type: application/json'),
    );
    // 接口地址，请求方式，参数的配置
    // 1. required：是否必传（true为必传，false为选传）
    // 2. default：可设置默认值，不传值时取
    // 3. range：可设置传值范围，只可传范围内的值
    // 其他可自由修改进行扩展
    private static $api_config = array(
        // 测试接口1配置
        'test_1' => array(
            'url' => 'https://www.test.com/api/test',
            'type' => 'x-www-form-urlencoded',
            'param' => array(
                'id' => array('required' => true, 'default' => 1),
                'code' => array('required' => true),
                'status' => array('required' => true, 'range' => array(1, 2))
            )
        ),
        // 测试接口2配置
        'test_2' => array(
            'url' => 'https://www.test.com/api/test2',
            'type' => 'form-data',
            'param' => array(
                'type' => array('required' => true, 'default' => 1),
                'num' => array('required' => false)
            )
        ),
    );

    /**
     * 接口请求入口
     * @param string $action 配置的接口键名
     * @param array $param 请求参数
     * @param bool $is_format 是否格式化请求结果
     * @param bool $debug 是否开启调试模式
     * @return array|string 返回请求结果
     */
    public static function request($action = '', $param = array(), $is_format = true, $debug = false)
    {
        // 初始化
        self::initParam();
        // 校验参数，并设置请求参数
        $check = self::setApiParam($action, $param);
        if (!$check) {
            return self::$result;
        }
        // 发起接口请求
        $response = self::curlByPost(self::$url, self::$param, self::$token, self::$type);
        // 无需格式化直接返回
        if (!$is_format) {
            return $response;
        }
        // 设置返回值
        self::setResult($response);
        // 是否调试输出请求信息
        if ($debug) {
            self::echo_msg("url: " . self::$url);
            self::echo_msg("token: " . self::$token);
            self::echo_msg("param: " . json_encode(self::$param));
            self::echo_msg("response: " . $response);
        }
        return self::$result;
    }

    /**
     * 初始化参数值
     */
    private static function initParam()
    {
        self::$url = '';
        self::$type = '';
        self::$token = '';
        self::$param = array();
        self::$result['code'] = 0;
        self::$result['msg'] = '';
        self::$result['data'] = array();
    }

    /**
     * 设置接口请求的参数和地址等请求信息
     * @param string $action 配置的接口键名
     * @param array $param 请求参数
     * @return bool 返回真假
     */
    public static function setApiParam($action = '', $param = array())
    {
        // 检查接口方法是否已配置
        if (!isset(self::$api_config[$action])) {
            self::$result['msg'] = '接口未配置';
            return false;
        }
        // 获取接口配置
        $api_info = self::$api_config[$action];
        $api_param = $api_info['param'];
        // 循环校验参数
        foreach ($api_param as $key => $config) {
            if (!isset($param[$key])) {
                // 字段未传
                if ($config['required'] && !isset($config['default'])) {
                    // 必传，并且没有默认值则报错
                    self::$result['msg'] = '参数[' . $key . ']必传';
                    return false;
                }
                // 非必传，取默认值
                isset($config['default']) && self::$param[$key] = $config['default'];
            } else {
                // 字段已传
                self::$param[$key] = $param[$key];
            }
            // 判断是否在取值范围内
            if (self::$param[$key] && isset($config['range'])) {
                if (!in_array(self::$param[$key], $config['range'])) {
                    // 不在取值范围内
                    self::$result['msg'] = '参数[' . $key . ']的值不在取值范围内';
                    return false;
                }
            }
        }
        // 如果需要获取token密钥，此方法需要to-do，如果其他签名方式可根据实际修改
        self::$token = self::getToken();
        // 接口地址
        if (empty($api_info['url'])) {
            self::$result['msg'] = '接口地址为空';
            return false;
        }
        self::$url = $api_info['url'];
        // Content-Type请求类型
        if (!isset(self::$headers[$api_info['type']])) {
            self::$result['msg'] = '无此请求类型';
            return false;
        }
        self::$type = $api_info['type'];
        return true;
    }

    /**
     * POST请求
     * @param string $url 接口地址
     * @param array $data 请求参数
     * @param string $token 密钥token
     * @param string $type 传参类型
     * @param array $header_ext 扩展的header信息
     * @return bool|string 返回请求结果
     */
    private static function curlByPost($url = '', $data = array(), $token = '', $type = 'json', $header_ext = array())
    {
        $header = self::$headers[$type];
        // 是否需要token
        if ($token) {
            $header[] = "Authorization:$token";
        }
        // 扩展的header信息
        if (!empty($header_ext)) {
            $header = array_merge($header, $header_ext);
        }
        // 发送POST请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * 处理接口响应返回
     * @param string $response 接口返回数据
     */
    private static function setResult($response = '')
    {
        if ($response) {
            // 有返回值
            $result = json_decode($response, true);
            if (isset($result['code']) && $result['code'] == 200) {
                // 请求成功
                self::$result['data'] = $result['data'];
                self::$result['code'] = 200;
            } else {
                // 请求失败
                if (isset($result['code'])) {
                    self::$result['code'] = $result['code'];
                }
                self::$result['msg'] = $response;
            }
        } else {
            // 无返回值异常
            self::$result['msg'] = "empty response.";
        }
    }

    /**
     * 获取密钥token（TODO）
     * @return string
     */
    private static function getToken()
    {
        $token = '';
        return $token;
    }

    /**
     * 打印输出信息
     * @param string $msg 输出文本
     */
    private static function echo_msg($msg = '')
    {
        if (!empty($msg)) {
            $msg = "[" . date("Y-m-d H:i:s") . "] " . $msg . PHP_EOL;
            echo $msg;
            @ob_flush();
            @flush();
        }
    }
}