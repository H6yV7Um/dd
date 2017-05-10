## 点滴数据库(for huihui)
create database dd;

use dd;

## 分类表
CREATE TABLE `category` (
  `catId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `catName` varchar(155) NOT NULL COMMENT '分类名称',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父分类Id',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `description` varchar(155) NOT NULL DEFAULT '' COMMENT '分类简介',
  `createdTime` int(11) NOT NULL COMMENT '创建时间',
  `updatedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`catId`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='分类表';
INSERT INTO `category` VALUES (1,'兼职招聘',0,100,'兼职招聘',0,'2016-03-13 02:59:17'),(2,'旅游度假',0,100,'旅游度假',0,'2016-03-13 02:59:25'),(3,'教育培训',0,100,'教育培训',0,'2016-03-13 02:59:34'),(4,'旅游开团',0,100,'旅游开团',0,'2016-03-13 02:59:42'),(6,'家教',1,100,'兼职招聘-子类',0,'2016-03-04 17:53:42'),(7,'会计',1,100,'兼职招聘-子类',0,'2016-03-04 17:53:42'),(8,'兼职',1,100,'兼职招聘-子类',0,'2016-03-04 17:53:42'),(9,'宣传员',1,100,'兼职招聘-子类',0,'2016-03-04 17:53:42'),(10,'国内游',2,100,'旅游度假-子类',0,'2016-03-04 17:53:42'),(11,'省内游',2,100,'旅游度假-子类',0,'2016-03-04 17:53:42'),(12,'省外游',2,100,'旅游度假-子类',0,'2016-03-04 17:53:42'),(13,'考研',3,100,'教育培训-子类',0,'2016-03-04 17:53:42'),(14,'考博',3,100,'教育培训-子类',0,'2016-03-04 17:53:42'),(15,'会计证',3,100,'教育培训-子类',0,'2016-03-04 17:53:42'),(16,'培训班',3,100,'教育培训-子类',0,'2016-03-04 17:53:42');


## 基础信息表
/*
字段:
分类: 下拉
标题:
图片: file上传
内容:
要求:
地址:
详细地址:
联系人:
联系电话:
备注:(可选)
*/
CREATE TABLE `information` (
  `infoId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `catId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类',
  `userId` int(10) unsigned NOT NULL COMMENT '发布人Id',
  `title` varchar(155) NOT NULL COMMENT '标题',
  `image` varchar(155) NOT NULL DEFAULT '' COMMENT '图片（1张）',
  `content` text NOT NULL COMMENT '内容',
  `demand` text NOT NULL COMMENT '要求',
  `extra` text NOT NULL COMMENT '附加信息',
  `address` int(10) unsigned NOT NULL DEFAULT '100000' COMMENT '地址：省市区, 以地区码表示',
  `detailAddress` varchar(155) NOT NULL DEFAULT '' COMMENT '详细地址',
  `contact` varchar(155) NOT NULL COMMENT '联系人',
  `phoneNum` varchar(155) NOT NULL COMMENT '联系电话',
  `isNew` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '最新',
  `isHot` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '热门',
  `isRec` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '推荐',
  `isTop` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '置顶',
  `viewCnt` int(10) unsigned NOT NULL DEFAULT '0',
  `commCnt` int(10) unsigned NOT NULL DEFAULT '0',
  `upCnt` int(10) unsigned NOT NULL DEFAULT '0',
  `downCnt` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(11) NOT NULL COMMENT '创建时间',
  `updatedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`infoId`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COMMENT='分类信息基础表';


## 兼职招聘表
/*
兼职独有字段:
薪水
薪资类型: 0：面议，1：小时，2：日，3：周，4：月，5：年
招聘人数:
工期的开始日期, 结束日期
每天工作的开始时间, 结束时间
*/
CREATE TABLE `recruitment` (
  `infoId` int(10) unsigned NOT NULL,
  `salary` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '单位：分',
  `salaryType` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：面议，1：小时，2：日，3：周，4：月，5：年',
  `number` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '招聘人数',
  `startDate` date NOT NULL COMMENT '开始日期',
  `endDate` date NOT NULL COMMENT '结束日期',
  `startTime` time NOT NULL COMMENT '开始时间',
  `endTime` time NOT NULL COMMENT '结束时间',
  `createdTime` int(11) NOT NULL COMMENT '创建时间',
  `updatedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`infoId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='招聘信息表';



## 旅游度假表
/*
价格
宾馆类型
旅程开始日期, 结束日期
旅程出发地点, 返回地点
旅程去时的交通工具, 返回时的交通工具

*/
CREATE TABLE `travel` (
  `infoId` int(10) unsigned NOT NULL,
  `price` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '价格',
  `hotelType` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1: 露营, 2: 农家乐, 3: 旅社, 4: 宾馆',
  `startDate` date NOT NULL COMMENT '开始日期',
  `endDate` date NOT NULL COMMENT '结束日期',
  `startPlace` varchar(155) NOT NULL COMMENT '出发地点',
  `endPlace` varchar(155) NOT NULL COMMENT '结束地点',
  `goVehicle` tinyint(1) unsigned NOT NULL COMMENT '去交通：1：大巴，2：火车，3：飞机，4：轮船',
  `backVehicle` tinyint(1) unsigned NOT NULL COMMENT '回交通：1：大巴，2：火车，3：飞机，4：轮船',
  `createdTime` int(11) NOT NULL COMMENT '创建时间',
  `updatedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`infoId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='旅游信息表';



## 教育培训表
/*
价格
培训开始日期, 结束日期
上课开始时间, 放学结束时间
*/
CREATE TABLE `education` (
  `infoId` int(10) unsigned NOT NULL,
  `price` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '价格',
  `startDate` date NOT NULL COMMENT '开始日期',
  `endDate` date NOT NULL COMMENT '结束日期',
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `createdTime` int(11) NOT NULL COMMENT '创建时间',
  `updatedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`infoId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='教育信息表';


### 首页推荐表
CREATE TABLE `recommend` (
  `recId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `infoId` int(10) unsigned NOT NULL DEFAULT '0',
  `sort` int(10) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL COMMENT '推荐类型: 1: 首页轮播图, 2: 首页列表',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`recId`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='推荐表';



## 顶/踩表
CREATE TABLE `up_down` (
  `udId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `infoId` int(10) unsigned NOT NULL COMMENT '兼职/旅游/教育infoId',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1: 赞, 2:踩',
  `createdTime` int(11) NOT NULL COMMENT '创建时间',
  `updatedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`udId`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='顶踩表';


## 评论表
/*
评论的信息
内容
*/
CREATE TABLE `comment` (
  `commId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `infoId` int(10) unsigned NOT NULL COMMENT '兼职/旅游/教育infoId',
  `content` varchar(155) NOT NULL,
  `createdTime` int(11) NOT NULL COMMENT '创建时间',
  `updatedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`commId`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='评论表';



## 收藏表
CREATE TABLE `collection` (
  `collId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `infoId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `createdTime` int(11) NOT NULL COMMENT '创建时间',
  `updatedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`collId`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='收藏表';



## 预约表
CREATE TABLE `yu_yue` (
  `yyId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `infoId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `name` varchar(155) NOT NULL,
  `phoneNum` varchar(155) NOT NULL,
  `number` int(11) NOT NULL,
  `extra` text,
  `createdTime` int(11) NOT NULL COMMENT '创建时间',
  `updatedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`yyId`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='预约表';



## 用户表
/*
用户名
头像
性别
生日
手机号
邮箱
地址
详细地址
*/
CREATE TABLE `user` (
  `userId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(155) NOT NULL COMMENT '用户名',
  `password` varchar(155) NOT NULL COMMENT '密码',
  `pwdStr` varchar(155) NOT NULL DEFAULT '' COMMENT '初始密码的随机后缀',
  `userIcon` varchar(155) NOT NULL DEFAULT '' COMMENT '头像',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '性别：1：男，2：女',
  `birthDay` date NOT NULL DEFAULT '1970-01-01',
  `phoneNum` varchar(155) NOT NULL DEFAULT '',
  `email` varchar(155) NOT NULL DEFAULT '' COMMENT '邮箱',
  `address` varchar(155) NOT NULL DEFAULT '' COMMENT '地址：省-市-区',
  `detailAddress` varchar(155) NOT NULL DEFAULT '' COMMENT '详细地址',
  `isComplete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否完善简历',
  `isLock` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否锁定',
  `isAuth` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否认证',
  `createdTime` int(11) NOT NULL COMMENT '创建时间',
  `updatedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`userId`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `phoneNum` (`phoneNum`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='用户表';


## 简历表
/*
头像
真实姓名
性别：1：男，2：女
年龄
名族
政治面貌: 1: 群众, 2: 共青团员, 3: 预备党员, 4: 党员
籍贯
学校
专业
教育水平: 1: 初中及以下, 2: 高中, 3: 大学, 4: 研究生, 5: 博士
求职意向
手机号
邮箱
地址
详细地址
特长
所获荣誉
工作经验
自我评价
*/
CREATE TABLE `resume` (
  `userId` int(10) unsigned NOT NULL COMMENT '用户Id',
  `userIcon` varchar(155) NOT NULL COMMENT '头像',
  `realName` varchar(155) NOT NULL COMMENT '姓名',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '性别：1：男，2：女',
  `age` tinyint(1) unsigned NOT NULL COMMENT '年龄',
  `nation` varchar(155) NOT NULL COMMENT '民族',
  `politicalStatus` tinyint(1) NOT NULL DEFAULT '1' COMMENT '政治面貌: 1: 群众, 2: 共青团员, 3: 预备党员, 4: 党员',
  `nativePlace` varchar(155) NOT NULL COMMENT '籍贯',
  `school` varchar(155) NOT NULL COMMENT '学校',
  `major` varchar(155) NOT NULL COMMENT '专业',
  `educationLevel` tinyint(1) NOT NULL COMMENT '教育水平: 1: 初中及以下, 2: 高中, 3: 大学, 4: 研究生, 5: 博士',
  `jobIntention` varchar(155) NOT NULL COMMENT '求职意向',
  `phoneNum` varchar(155) NOT NULL COMMENT '手机号',
  `email` varchar(155) NOT NULL COMMENT '邮箱',
  `address` varchar(155) NOT NULL COMMENT '地址：省-市-区',
  `detailAddress` varchar(155) NOT NULL COMMENT '详细地址',
  `specialty` varchar(155) NOT NULL DEFAULT '' COMMENT '特长',
  `honor` text COMMENT '所获荣誉',
  `experience` text COMMENT '工作经验',
  `selfIntroduce` text COMMENT '自我评价',
  `createdTime` int(11) NOT NULL COMMENT '创建时间',
  `updatedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='个人简历表';
