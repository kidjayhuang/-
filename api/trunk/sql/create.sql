## 用户信息表
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `open_id` varchar(64) NOT NULL DEFAULT '' comment '微信的openid',
  `nick_name` varchar(64) NOT NULL DEFAULT '' comment '昵称',
  `union_id` varchar(32) NOT NULL DEFAULT '' comment 'unionid',
  `gender` tinyint(1) NOT NULL DEFAULT '0' comment '性别',
  `country`  varchar(16) NOT NULL DEFAULT '' comment '国家',
  `province` varchar(16) NOT NULL DEFAULT '' comment '省份',
  `city` varchar(32) NOT NULL DEFAULT '' comment '城市',
  `language` varchar(16) NOT NULL DEFAULT '' comment '语言',
  `avatar_url` varchar(255) NOT NULL DEFAULT '' comment '头像',
  `phone_code` varchar(16) NOT NULL DEFAULT '' comment '手机号码',
  `password` varchar(64) NOT NULL DEFAULT '' comment '密码',
  `create_time` bigint(20) NOT NULL DEFAULT '0' comment '创建时间',
  `update_time` bigint(20) NOT NULL DEFAULT '0' comment '更新时间',
  `notice_time` bigint(20) NOT NULL DEFAULT '0' comment '最近查看通知的时间',
  `circle_count` tinyint(3) NOT NULL DEFAULT '0' comment '创建圈子数量',
  PRIMARY KEY (`id`),
  UNIQUE KEY `open_id` (`open_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COMMENT '用户信息表';

#缓存
hash
USER_+ID
nick_name
gender
avatar_url


#活动信息表，创建人
CREATE TABLE `activity` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL DEFAULT '' comment '标题',
  `desc`  varchar(255) NOT NULL DEFAULT '' comment '描述',
  `type` tinyint(3) NOT NULL DEFAULT '0' comment '活动类型，101：即时活动',
  `pic_list` text NOT NULL DEFAULT '' comment '照片列表',
  `search` tinyint(1) NOT NULL DEFAULT '0' comment '是否可被搜索',
  `join_verify` tinyint(1) NOT NULL DEFAULT '0' comment '加入是否需要审核',
  `verify_request`  varchar(255) NOT NULL DEFAULT '' comment '加入申请要求',
  `creator` bigint(20) NOT NULL DEFAULT '0' comment '创建人',
  `member_begin` bigint(20) NOT NULL DEFAULT '0' comment '用户成员数量开始',
  `member_end` bigint(20) NOT NULL DEFAULT '0' comment '用户成员数量结束',
  `action_time` bigint(20) NOT NULL DEFAULT '0' comment '活动举行时间',
  `address` varchar(255) NOT NULL DEFAULT '' comment '地址',
  `address_detail` varchar(255) NOT NULL DEFAULT '' comment '地址详情',
  `latitude` varchar(255) NOT NULL DEFAULT '' comment '经度',
  `longitude` varchar(255) NOT NULL DEFAULT '' comment '纬度',
  `pay_type` tinyint(3) NOT NULL DEFAULT '0' comment '付款方式，101：AA；102：我买单',
  `price_begin` int(8) NOT NULL DEFAULT '0' comment '人均消费最低',
  `price_end` int(8) NOT NULL DEFAULT '0' comment '人均消费最高',
  `create_time` bigint(20) NOT NULL DEFAULT '0' comment '创建时间',
  `update_time` bigint(20) NOT NULL DEFAULT '0' comment '更新时间',
  `member_count` bigint(20) NOT NULL DEFAULT '0' comment '实际用户成员数量',
  `member_verify_count` bigint(20) NOT NULL DEFAULT '0' comment '审核中用户成员数量',
  `status` tinyint(3) NOT NULL DEFAULT '0' comment '状态，101：正常；110：停止报名；120:活动结束；200：取消',
  PRIMARY KEY (`id`),
  INDEX `creator` (`creator`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '活动信息表';

#缓存
hash
A_I_+ID

所有信息


#成员表，成员身份
CREATE TABLE `member` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT '0' comment '用户类型，1：创建人，2：普通成员',
  `activity_id` bigint(20) NOT NULL DEFAULT '0' comment '圈子id',
  `user_id` bigint(20) NOT NULL DEFAULT '0' comment '用户id',
  `status` tinyint(3) NOT NULL DEFAULT '0' comment '状态，101：正常；103：退出',
  `join_time` bigint(20) NOT NULL DEFAULT '0' comment '创建时间',
  `quit_time` bigint(20) NOT NULL DEFAULT '0' comment '退出时间',
  PRIMARY KEY (`id`),
  INDEX `user_id` (`user_id`),
  INDEX `circle_id` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '活动成员信息表';

已经通过的成员列表
M_+activity_id

我创建的活动列表
MY_C_+user_id

我加入的活动列表
MY_J_+user_id


#待审核列表
CREATE TABLE `verify` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) NOT NULL DEFAULT '0' comment '活动id',
  `user_id` bigint(20) NOT NULL DEFAULT '0' comment '用户id',
  `apply_time` bigint(20) NOT NULL DEFAULT '0' comment '创建时间',
  `result` tinyint(3) NOT NULL DEFAULT '0' comment '审核结果，101：通过，102：拒绝',
  `verify_time` bigint(20) NOT NULL DEFAULT '0' comment '审核时间',
  `verify_desc` varchar(64) NOT NULL DEFAULT '' comment '审核说明',
  `notice_id` bigint(20) NOT NULL DEFAULT '0' comment '通知id',
  `remark` varchar(64) NOT NULL DEFAULT '' comment '圈子备注名称',
  PRIMARY KEY (`id`),
  INDEX `user_id` (`user_id`),
  INDEX `circle_id` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '审核信息表';



#缓存

V_+activity_id

V_I_ + verify_id 审核id hash 审核详情


CREATE TABLE `notice` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) NOT NULL DEFAULT '0' comment '活动id',
  `user_id` bigint(20) NOT NULL DEFAULT '0' comment '用户id',
  `verify_id` bigint(20) NOT NULL DEFAULT '0' comment '审核id',
  `create_time` bigint(20) NOT NULL DEFAULT '0' comment '创建时间',
  `type` tinyint(3) NOT NULL DEFAULT '0' comment '消息类型，101：加入消息，102：审核消息，103：退出消息；104：活动回复；105：评论回复',
  `content` varchar(128) NOT NULL DEFAULT '' comment '消息内容',
  `remark` varchar(64) NOT NULL DEFAULT '' comment '消息备注',
  `result` tinyint(3) NOT NULL DEFAULT '0' comment '审核结果，101：通过，102：拒绝，100，待审核',
  PRIMARY KEY (`id`),
  INDEX `user_id` (`user_id`),
  INDEX `activity_id` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '系统消息表';

#缓存
N_ + user_id zset 用户的消息列表
N_I_ + notice_id 消息详情 hash


#回复信息表
CREATE TABLE `reply` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) NOT NULL DEFAULT '0' comment '活动id',
  `user_id` bigint(20) NOT NULL DEFAULT '0' comment '写回复的用户id',
  `to_user_id` bigint(20) NOT NULL DEFAULT '0' comment '针对回复的用户id',
  `type` tinyint(3) NOT NULL DEFAULT '0' comment '回复类型，101：正文回复，102:回复的回复',
  `status` tinyint(3) NOT NULL DEFAULT '0' comment '审核结果，101：正常，104：删除',
  `create_time` bigint(20) NOT NULL DEFAULT '0' comment '创建时间',
  `oper_time` bigint(20) NOT NULL DEFAULT '0' comment '操作时间',
  `content` text NOT NULL DEFAULT '' comment '回复内容',
  PRIMARY KEY (`id`),
  INDEX `user_id` (`user_id`),
  INDEX `to_user_id` (`to_user_id`),
  INDEX `activity_id` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '回复信息表';


#缓存
R_I_reply_id hash 回复详情

R_activity_id  回复列表


#点赞信息表
CREATE TABLE `activity_like` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) NOT NULL DEFAULT '0' comment '文章id',
  `user_id` bigint(20) NOT NULL DEFAULT '0' comment '用户id',
  `create_time` bigint(20) NOT NULL DEFAULT '0' comment '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_user` ( `user_id`, `article_id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '点赞信息表';

L_activity_id zset 用户id列表


