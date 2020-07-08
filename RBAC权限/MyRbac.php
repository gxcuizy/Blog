<?php

namespace app\index\module;

use think\Db;
use think\Request;
use think\Session;

/**
 * 我的简单版权限管理类
 * @author gxcuizy
 * @time 2020.05.08
 */
class MyRbac
{
    /**
     * 检查是否有权限
     * @return bool
     */
    public static function check()
    {
        // 获取控制器和操作方法
        $request = Request::instance();
        $controller = $request->controller();
        $action = $request->action();
        $node = ($controller . '/' . $action);
        $all_node_list = Session::get('all_node_list');
        $user_node_list = Session::get('user_node_list');
        // 这里，校验权限点的方式有两种：1校验全部操作的节点；2校验全部权限点以内的节点，非权限节点内的全通过；
        // 第一种方式：全部操作节点都校验，我这里，选择ajax请求和post操作的请求都不校验权限
        $check = (in_array($node, $all_node_list) && in_array($node, $user_node_list));
        if ($request->isAjax() || $request->isPost()) {
            $check = true;
        }
        // 第二种方式：节点在权限控制范围内，且用户拥有该节点的权限。
        // $check = (in_array($node, $all_node_list) || in_array($node, $user_node_list));
        return $check;
    }

    /**
     * 用户权限存入Session
     * @param int $userId
     */
    public static function saveAccessList($userId = 0)
    {
        // 获取不同平台的所有节点
        $all_node_list = self::getNodeList();
        // 超级管理员拥有全部权限，不受权限限制
        $user_node_list = $all_node_list;
        if (!self::isSuperAdmin()) {
            // 普通用户，获取相应权限节点
            $user_node_list = self::getUserNodeList($userId);
        }
        // 全部节点和用户节点，存入session
        Session::set('all_node_list', $user_node_list);
        Session::set('user_node_list', $all_node_list);
    }

    /*
     * 当前登录用户是否为超级管理员
     * @return bool
     */
    public static function isSuperAdmin()
    {
        // 获取缓存的管理员信息，登录的时候存储的
        $user_name = Session::get('admin_user.user_name');
        $super_secret = Session::get('admin_user.super_secret');
        // 秘钥信息校验，拼接一个字符串_check
        $is_super = (md5($user_name . '_check') === $super_secret);
        return $is_super;
    }

    /**
     * 获取所有的权限节点
     * @return array
     */
    private static function getNodeList()
    {
        $node_list = Db::name('node')->where(['status' => 0])->column('name');
        return $node_list;
    }

    /**
     * 获取用户所有的权限节点
     * @param int $user_id
     * @return array
     */
    private static function getUserNodeList($user_id = 0)
    {
        $list = Db::name('node')->alias('n')
            ->join('role_node rn', 'n.uid = rn.node_id')
            ->join('role r', 'rn.role_id = r.uid')
            ->join('user_role ur', 'r.uid = ur.role_id')
            ->join('user u', 'ur.user_id = u.uid')
            ->where(['u.uid' => $user_id, 'n.status' => 0])
            ->column('n.name');
        return $list;
    }
}
