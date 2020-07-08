<?php

/**
 * 通过新浪微博API，生成短链接，支持一次性转多个长链接
 * Class shortClass
 * @time 2018-08-14
 * @author gxcuizy
 */
Class ShortLink {
    // APPkey，我在网上找的（https://fengmk2.com/blog/appkey.html），可以自己申请
    protected $appKey = '569452181';
    // 转短连接API地址
    protected $shortUrl = 'https://api.weibo.com/2/short_url/shorten.json?';

    /**
     * 生成短链接
     * @param array $longUrl 长链接数组
     * @return array 返回短连接数据
     */
    public function getShortUrl($longUrl = []) {
        $code = true;
        $msg = '请求成功！';
        $result = [];
        // 长链接数组为空，不处理
        if (empty($longUrl)) {
            $code = false;
            $msg = '长链接数据不能为空';
            return ['code' => $code, 'msg' => $msg, 'result' => $result];
        }
        // 拼接请求URL
        $longUrlStr = $this->_getLongUrl($longUrl);
        $shortUrl = $this->shortUrl;
        $appKey = $this->appKey;
        $param = 'source=' . $appKey . '&' . $longUrlStr;
        $curlUrl = $shortUrl . $param;
        // 发送CURL请求
        $result = $this->_sendCurl($curlUrl);
        return ['code' => $code, 'msg' => $msg, 'result' => $result];
    }

    /**
     * 获取请求URL字符串
     * @param array $longUrl 长链接数组
     * @return string 长链接URL字符串
     */
    private function _getLongUrl($longUrl = []) {
        $str = '';
        foreach ($longUrl as $url) {
            $str .= ('url_long=' . $url . '&');
        }
        $newStr = substr($str, 0, strlen($str) - 1);
        return $newStr;
    }

    /**
     * 发送CURL请求（GET）
     * @param string $curlUrl 请求地址
     * @return array 返回信息
     */
    private function _sendCurl($curlUrl) {
        // 初始化
        $ch = curl_init();
        // 设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 执行并获取HTML文档内容
        $output = curl_exec($ch);
        // 释放curl句柄
        curl_close($ch);
        // Json数据转为数组
        $result = json_decode($output, true);
        return $result;
    }
}

// 实例化对象
$shortObj = new ShortLink();
// 多个连接可以直接放到数组中，类似$longUrl = ['url1', 'url2', ……]
$longUrl = ['http://blog.y0701.com/index.html'];
// 开始转长链接为短链接
$result = $shortObj->getShortUrl($longUrl);
print_r($result);