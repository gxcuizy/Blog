<?php

/**
 * 自动生成数据字典上传到showdoc项目中
 * User: gxcuizy
 * Date: 2021/05/10 0011
 * Time: 下午 15:17
 * Class GetMysqlDict
 */
class GetMysqlDict
{
    // 数据库连接配置信息
    private $host = '127.0.0.1';
    private $user_name = 'root';
    private $password = 'root';
    private $db_name = 'test';
    private $port = 3306;
    private $conn;
    // showdoc文档API密钥配置，获取方法：https://www.showdoc.com.cn/page/741656402509783
    private $api_key = '6b0ddb543b53f5002f6033cb2b00cec01908536369';
    private $api_token = '9da3190d0dda1118de0e8bde08907fc51712469974';

    /**
     * 构造函数，连接数据库
     * GetMysqlDict constructor.
     */
    public function __construct()
    {
        // 创建连接
        $this->conn = new mysqli($this->host, $this->user_name, $this->password, $this->db_name, $this->port);
        // 检测连接
        if ($this->conn->connect_error) {
            exit("数据库连接失败: " . $this->conn->connect_error);
        }
        $this->echoMsg('数据库连接成功');
    }

    /**
     * 执行入口
     */
    public function run()
    {
        // 获取数据表
        $table_list = $this->getTableList();
        $this->echoMsg('数据表总数：' . count($table_list));
        // 循环表获取结构信息
        $request_num = 0;
        foreach ($table_list as $table) {
            // 频率控制，10分钟内只能请求1000次
            if ($request_num >= 1000) {
                $request_num = 0;
                $this->echoMsg('频率控制，请等待10分钟后继续');
                sleep(600);
            }
            // 获取数据结构
            $msg = '表名：' . $table['Name'] . '（' . $table['Comment'] . '）';
            // 字典表头信息
            $table_dict = '#### ' . $table['Name'] . ' ' . $table['Comment'] . PHP_EOL;
            $table_dict .= '| 字段名称 | 类型长度 | 是否NULL | 默认值 | 注释 |' . PHP_EOL;
            $table_dict .= '| --- | --- | --- | --- | --- |' . PHP_EOL;
            // 获取表字段信息
            $dict_list = $this->getDictList($table['Name']);
            foreach ($dict_list as $dict) {
                $c_name = $dict['COLUMN_NAME'];
                $c_type = $dict['COLUMN_TYPE'];
                $c_null = $dict['IS_NULLABLE'];
                $c_default = $dict['COLUMN_DEFAULT'];
                $c_comment = $dict['COLUMN_COMMENT'];
                $table_dict .= '| ' . $c_name . ' | ' . $c_type . ' | ' . $c_null . ' | ' . $c_default . ' | ' . $c_comment . ' |' . PHP_EOL;
            }
            // 利用showdoc文档在线展示数据字典
            $response = $this->apiPost($table['Name'], $table_dict);
            if ($response['error_code'] == 0) {
                $msg .= ' 生成文档成功';
            } else {
                $msg .= ' 生成文档失败（' . $response['error_message'] . '）';
            }
            $request_num++;
            $this->echoMsg($msg);
        }
    }

    /**
     * 获取数据表列表
     * @return array
     */
    private function getTableList()
    {
        // 查看所有表信息
        $sql = 'show table status;';
        $result = $this->conn->query($sql);
        // 循环获取表数据
        $table_list = array();
        while ($row = $result->fetch_assoc()) {
            $table_list[] = $row;
        }
        return $table_list;
    }

    /**
     * 获取表结构信息
     * @param string $table
     * @return array
     */
    private function getDictList($table = '')
    {
        // 获取表结构信息（COLUMN_NAME,COLUMN_TYPE,NUMERIC_SCALE,IS_NULLABLE,COLUMN_DEFAULT,COLUMN_COMMENT）
        $sql = "select * from information_schema.COLUMNS where table_schema='" . $this->db_name . "' and table_name='" . $table . "';";
        $result = $this->conn->query($sql);
        $dict_list = array();
        while ($row = $result->fetch_assoc()) {
            $dict_list[] = $row;
        }
        return $dict_list;
    }

    /**
     * 发送接口请求，生成文档
     * @param string $title 页面标题（请保证其唯一）
     * @param string $content 页面内容（支持Markdown和HTML）
     * @param string $name 目录名（可选参数）
     * @param int $number 页面序号（默认99，越小越靠前）
     * @return array
     */
    private function apiPost($title = '', $content = '', $name = '', $number = 99)
    {
        // 接口地址，如果是自己利用开源搭建的，则接口地址为：http://xx.com/server/index.php?s=/api/item/updateByApi
        $url = 'https://www.showdoc.cc/server/api/item/updateByApi';
        // 请求参数
        $data = array(
            'api_key' => $this->api_key,
            'api_token' => $this->api_token,
            'cat_name' => $name,
            'page_title' => $title,
            'page_content' => $content,
            's_number' => $number
        );
        // 发送POST请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    /**
     * 打印输出信息
     * @param string $msg
     */
    private function echoMsg($msg = '')
    {
        if (!empty($msg)) {
            $msg = "[" . date("Y-m-d H:i:s") . "] " . $msg . PHP_EOL;
            echo $msg;
            @ob_flush();
            @flush();
        }
    }

    /**
     * 析构函数，关闭数据库连接
     */
    public function __destruct()
    {
        $this->conn->close();
        $this->echoMsg('已关闭数据库连接');
    }
}

// 实例化类并执行
$obj = new GetMysqlDict;
$obj->run();