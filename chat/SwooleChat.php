<?php

Class SwooleChat
{
    // websocket连接信息
    protected $host = '127.0.0.1';
    protected $port = 9502;
    protected $ws;
    // mysql连接信息
    protected $user_name = 'root';
    protected $pwd = 'root';
    protected $db_name = 'chat';
    protected $conn;
    // type=chat聊天消息，type=on上线通知，type=off离线通知，type=open连接初始化
    protected $return = ['type' => 'chat', 'msg' => '', 'time' => '0', 'data' => ''];

    /**
     * 初始化构造函数
     */
    public function __construct()
    {
        $this->ws = new Swoole\WebSocket\Server($this->host, $this->port);
        $this->initConn();
        mysqli_query($this->conn, "DELETE FROM chat_user WHERE 1");
    }

    /**
     * 服务端执行入口
     */
    public function run()
    {
        $this->open();
        $this->message();
        $this->close();
        $this->ws->start();
    }

    /**
     * 监听WebSocket连接打开
     */
    private function open()
    {
        $this->ws->on('open', function ($ws, $request) {
            // 用户存入数据库
            $this->initConn();
            $user_name = $request->get['user_name'];
            $fd = $request->fd;
            $sql = "INSERT INTO chat_user (fd, user_name, add_time) VALUES ($fd, '{$user_name}', " . time() . ")";
            $this->conn->query($sql);
            // 推送上线通知
            $this->return['type'] = 'on';
            $this->return['msg'] = '欢迎【' . $user_name . '】进入聊天室';
            // 用户列表
            $sql = "SELECT fd,user_name FROM chat_user WHERE online_status = 1";
            $result = $this->conn->query($sql);
            $user_list = [];
            if (mysqli_num_rows($result) > 0) {
                // 给其他用户推送上线通知
                while ($row = mysqli_fetch_assoc($result)) {
                    $user_list[] = ['fd' => $row['fd'], 'name' => $row['user_name']];
                    if ($row['fd'] != $fd) {
                        $this->return['data'] = ['fd' => $fd, 'name' => $user_name];
                        $ws->push($row['fd'], json_encode($this->return));
                    }
                }
            }
            // 在线用户
            $this->return['type'] = 'open';
            $this->return['data'] = ['user_list' => $user_list];
            $ws->push($fd, json_encode($this->return));
        });
    }

    /**
     * 监听WebSocket消息
     */
    private function message()
    {
        $this->ws->on('message', function ($ws, $frame) {
            $this->initConn();
            // 聊天消息
            $fd = $frame->fd;
            $message = $frame->data;
            $this->return['type'] = 'chat';
            $this->return['msg'] = $message;
            // 用户名称
            $sql = "SELECT user_name FROM chat_user WHERE fd = {$fd} LIMIT 1";
            $res = $this->conn->query($sql);
            $user = $res->fetch_assoc();
            $user_name = $user['user_name'];
            // 用户列表
            $sql = "SELECT fd,user_name FROM chat_user WHERE online_status = 1 ORDER BY id desc";
            $result = $this->conn->query($sql);
            if (mysqli_num_rows($result) > 0) {
                // 推送发送的消息
                while ($row = mysqli_fetch_assoc($result)) {
                    $this->return['data'] = ['user_name' => $user_name, 'from' => $fd, 'to' => $row['fd'], 'time' => date('Y-m-d H:i:s')];
                    $ws->push($row['fd'], json_encode($this->return));
                }
            }
        });
    }

    /**
     * 监听WebSocket连接关闭
     */
    private function close()
    {
        $this->ws->on('close', function ($ws, $fd) {
            $this->initConn();
            // 用户下线
            $sql = "UPDATE chat_user SET online_status = 0 WHERE fd = $fd";
            $this->conn->query($sql);
            // 用户名称
            $sql = "SELECT user_name FROM chat_user WHERE fd = {$fd} LIMIT 1";
            $res = $this->conn->query($sql);
            $user = $res->fetch_assoc();
            $user_name = $user['user_name'];
            // 用户列表
            $sql = "SELECT fd,user_name FROM chat_user WHERE online_status = 1 ORDER BY id desc";
            $result = $this->conn->query($sql);
            if (mysqli_num_rows($result) > 0) {
                // 给其他用户推送下线通知
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($row['fd'] != $fd) {
                        $this->return['type'] = 'off';
                        $this->return['msg'] = '【' . $user_name . '】离开聊天室';
                        $this->return['data'] = ['fd' => $fd];
                        $ws->push($row['fd'], json_encode($this->return));
                    }
                }
            }
        });
    }

    /**
     * MySQL连接
     */
    private function initConn()
    {
        // 建立mysql数据库链接
        $this->conn = new mysqli($this->host, $this->user_name, $this->pwd, $this->db_name);
        mysqli_query($this->conn, "set character set 'utf8'");
        mysqli_query($this->conn, "set names 'utf8'");
    }
}

// 运行服务器端
$chat = new SwooleChat();
$chat->run();