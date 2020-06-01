CREATE TABLE `chat_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `fd` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推送ID',
  `user_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `avatar` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '头像',
  `online_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '在线状态：0离线，1在线',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;