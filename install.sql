SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `conn_admingroup` (
  `admingid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理组ID',
  `inheritance` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '继承于',
  `alloweditpost` tinyint(1) NOT NULL DEFAULT '0',
  `alloweditpoll` tinyint(1) NOT NULL DEFAULT '0',
  `allowstickthread` tinyint(1) NOT NULL DEFAULT '0',
  `allowmodpost` tinyint(1) NOT NULL DEFAULT '0',
  `allowdelpost` tinyint(1) NOT NULL DEFAULT '0',
  `allowmassprune` tinyint(1) NOT NULL DEFAULT '0',
  `allowrefund` tinyint(1) NOT NULL DEFAULT '0',
  `allowcensorword` tinyint(1) NOT NULL DEFAULT '0',
  `allowviewip` tinyint(1) NOT NULL DEFAULT '0',
  `allowbanip` tinyint(1) NOT NULL DEFAULT '0',
  `allowedituser` tinyint(1) NOT NULL DEFAULT '0',
  `allowmoduser` tinyint(1) NOT NULL DEFAULT '0',
  `allowbanuser` tinyint(1) NOT NULL DEFAULT '0',
  `allowbanvisituser` tinyint(1) NOT NULL DEFAULT '0',
  `allowpostannounce` tinyint(1) NOT NULL DEFAULT '0',
  `allowviewlog` tinyint(1) NOT NULL DEFAULT '0',
  `allowbanpost` tinyint(1) NOT NULL DEFAULT '0',
  `supe_allowpushthread` tinyint(1) NOT NULL DEFAULT '0',
  `allowhighlightthread` tinyint(1) NOT NULL DEFAULT '0',
  `allowlivethread` tinyint(1) NOT NULL DEFAULT '0',
  `allowdigestthread` tinyint(1) NOT NULL DEFAULT '0',
  `allowrecommendthread` tinyint(1) NOT NULL DEFAULT '0',
  `allowbumpthread` tinyint(1) NOT NULL DEFAULT '0',
  `allowclosethread` tinyint(1) NOT NULL DEFAULT '0',
  `allowmovethread` tinyint(1) NOT NULL DEFAULT '0',
  `allowedittypethread` tinyint(1) NOT NULL DEFAULT '0',
  `allowstampthread` tinyint(1) NOT NULL DEFAULT '0',
  `allowstamplist` tinyint(1) NOT NULL DEFAULT '0',
  `allowcopythread` tinyint(1) NOT NULL DEFAULT '0',
  `allowmergethread` tinyint(1) NOT NULL DEFAULT '0',
  `allowsplitthread` tinyint(1) NOT NULL DEFAULT '0',
  `allowrepairthread` tinyint(1) NOT NULL DEFAULT '0',
  `allowwarnpost` tinyint(1) NOT NULL DEFAULT '0',
  `allowviewreport` tinyint(1) NOT NULL DEFAULT '0',
  `alloweditforum` tinyint(1) NOT NULL DEFAULT '0',
  `allowremovereward` tinyint(1) NOT NULL DEFAULT '0',
  `allowedittrade` tinyint(1) NOT NULL DEFAULT '0',
  `alloweditactivity` tinyint(1) NOT NULL DEFAULT '0',
  `allowstickreply` tinyint(1) NOT NULL DEFAULT '0',
  `allowmanagearticle` tinyint(1) NOT NULL DEFAULT '0',
  `allowaddtopic` tinyint(1) NOT NULL DEFAULT '0',
  `allowmanagetopic` tinyint(1) NOT NULL DEFAULT '0',
  `allowdiy` tinyint(1) NOT NULL DEFAULT '0',
  `allowclearrecycle` tinyint(1) NOT NULL DEFAULT '0',
  `allowmanagetag` tinyint(1) NOT NULL DEFAULT '0',
  `alloweditusertag` tinyint(1) NOT NULL DEFAULT '0',
  `managefeed` tinyint(1) NOT NULL DEFAULT '0',
  `managedoing` tinyint(1) NOT NULL DEFAULT '0',
  `manageshare` tinyint(1) NOT NULL DEFAULT '0',
  `manageblog` tinyint(1) NOT NULL DEFAULT '0',
  `managealbum` tinyint(1) NOT NULL DEFAULT '0',
  `managecomment` tinyint(1) NOT NULL DEFAULT '0',
  `managemagiclog` tinyint(1) NOT NULL DEFAULT '0',
  `managereport` tinyint(1) NOT NULL DEFAULT '0',
  `managehotuser` tinyint(1) NOT NULL DEFAULT '0',
  `managedefaultuser` tinyint(1) NOT NULL DEFAULT '0',
  `managevideophoto` tinyint(1) NOT NULL DEFAULT '0',
  `managemagic` tinyint(1) NOT NULL DEFAULT '0',
  `manageclick` tinyint(1) NOT NULL DEFAULT '0',
  `allowmanagecollection` tinyint(1) NOT NULL DEFAULT '0',
  `allowmakehtml` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`admingid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `conn_failedlogin` (
  `ip` char(15) NOT NULL DEFAULT '',
  `username` char(32) NOT NULL DEFAULT '',
  `count` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip`,`username`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `conn_group` (
  `groupid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户组ID',
  `inheritance` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '继承于',
  PRIMARY KEY (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户组' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `conn_profile_academies` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT COMMENT '学院ID',
  `name` varchar(36) NOT NULL COMMENT '学院名称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='学院列表' AUTO_INCREMENT=26 ;

INSERT INTO `conn_profile_academies` (`id`, `name`) VALUES
(1, '材料科学与工程学院'),
(2, '交通学院'),
(3, '管理学院'),
(4, '机电工程学院'),
(5, '能源与动力工程学院'),
(6, '土木工程与建筑学院'),
(7, '汽车工程学院'),
(8, '资源与环境工程学院'),
(9, '信息工程学院'),
(10, '计算机科学与技术学院'),
(11, '自动化学院'),
(12, '航运学院'),
(13, '文法学院'),
(14, '理学院'),
(15, '经济学院'),
(16, '艺术与设计学院'),
(17, '外国语学院'),
(18, '物流工程学院'),
(19, '政治与行政学院'),
(20, '化学工程学院'),
(21, '国际教育学院'),
(22, '网络(继续)教育学院'),
(23, '职业技术学院'),
(24, '体育部'),
(25, '马克思主义学院');

CREATE TABLE IF NOT EXISTS `conn_profile_classes` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '班级ID',
  `aid` tinyint(2) unsigned NOT NULL COMMENT '学院ID',
  `sid` smallint(5) unsigned NOT NULL COMMENT '专业ID',
  `gid` tinyint(1) NOT NULL COMMENT '年级ID',
  `name` varchar(32) NOT NULL COMMENT '班级名称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='班级列表' AUTO_INCREMENT=19 ;

INSERT INTO `conn_profile_classes` (`id`, `aid`, `sid`, `gid`, `name`) VALUES
(1, 10, 4, 4, 'm1301'),
(2, 10, 4, 4, 'm1302'),
(3, 10, 4, 4, 'm1303'),
(4, 10, 4, 4, 'm1304'),
(5, 10, 4, 4, 'y1301'),
(6, 10, 4, 4, 'y1302'),
(7, 10, 4, 4, 'y1303'),
(8, 10, 4, 4, 'y1304'),
(9, 10, 4, 4, 'y1305'),
(10, 10, 4, 4, 'y1306'),
(11, 10, 4, 4, 'y1307'),
(12, 10, 4, 4, 'y1308'),
(13, 10, 4, 4, 'y1309'),
(14, 10, 4, 4, 'y1310'),
(15, 10, 1, 3, 'ZY1201'),
(16, 10, 1, 3, 'ZY1202'),
(17, 10, 1, 2, 'ZY1101'),
(18, 10, 1, 2, 'ZY1102');

CREATE TABLE IF NOT EXISTS `conn_profile_departments` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '部门ID',
  `lid` smallint(5) unsigned NOT NULL COMMENT '社团ID',
  `name` varchar(32) NOT NULL COMMENT '部门名称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='部门列表' AUTO_INCREMENT=11 ;

INSERT INTO `conn_profile_departments` (`id`, `lid`, `name`) VALUES
(1, 2, '秘书部'),
(2, 2, '组织部'),
(3, 2, '外联部'),
(4, 2, '策划部'),
(5, 2, '宣传部'),
(6, 2, '义工部'),
(7, 2, '服务队'),
(8, 1, '测试部门1'),
(9, 1, '测试部门2'),
(10, 2, '测试部门');

CREATE TABLE IF NOT EXISTS `conn_profile_grades` (
  `id` tinyint(1) NOT NULL COMMENT '年级ID',
  `grade` char(4) NOT NULL COMMENT '年级（入学年份）',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='年级列表';

INSERT INTO `conn_profile_grades` (`id`, `grade`) VALUES
(1, '2010'),
(2, '2011'),
(3, '2012'),
(4, '2013');

CREATE TABLE IF NOT EXISTS `conn_profile_leagues` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '社团ID',
  `aid` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '学院ID',
  `name` varchar(32) NOT NULL COMMENT '社团名称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='社团列表' AUTO_INCREMENT=8 ;

INSERT INTO `conn_profile_leagues` (`id`, `aid`, `name`) VALUES
(1, 0, '自强社'),
(2, 10, '自强社'),
(3, 10, '马克思主义理论学习研究协会'),
(4, 10, '青年志愿者协会'),
(5, 0, '学生会'),
(6, 10, '学生会'),
(7, 10, '心理素质拓展协会');

CREATE TABLE IF NOT EXISTS `conn_profile_organizations` (
  `id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '社团ID',
  `aid` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '学院ID',
  `name` varchar(32) NOT NULL COMMENT '社团名称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='组织列表';

CREATE TABLE IF NOT EXISTS `conn_profile_specialties` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '专业ID',
  `aid` tinyint(2) NOT NULL COMMENT '学院ID',
  `name` varchar(64) NOT NULL COMMENT '专业名称',
  `g1` tinyint(1) NOT NULL DEFAULT '0',
  `g2` tinyint(1) NOT NULL DEFAULT '0',
  `g3` tinyint(1) NOT NULL DEFAULT '0',
  `g4` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='专业列表' AUTO_INCREMENT=5 ;

INSERT INTO `conn_profile_specialties` (`id`, `aid`, `name`, `g1`, `g2`, `g3`, `g4`) VALUES
(1, 10, '软件工程', 1, 1, 1, 0),
(2, 10, '物联网工程', 1, 1, 1, 0),
(3, 10, '计算机科学与技术', 1, 1, 1, 0),
(4, 10, '计算机类', 0, 0, 0, 1);

CREATE TABLE IF NOT EXISTS `conn_setting` (
  `skey` varchar(255) NOT NULL DEFAULT '',
  `svalue` text NOT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `conn_setting` (`skey`, `svalue`) VALUES
('closed', '0'),
('closedreason', '这里填关闭注册的原因'),
('copyright', '<script type="text/javascript">document.write((new Date()).getFullYear());</script> &copy; 武汉理工大学自强社'),
('dateconvert', '1'),
('dateformat', 'Y-n-j'),
('debug', '1'),
('failedlogin', 'a:2:{s:5:"count";i:5;s:4:"time";i:1;}'),
('logintip', 'a:2:{i:0;s:27:"用户名不等同于昵称";i:1;s:82:"若验证码图片里的文字不是4个，请点击验证码图片更换验证码";}'),
('logopath', 'source/template/metronic/assets/img/logo-big.png'),
('nocacheheaders', '0'),
('reg', '1'),
('seccodedata', 'a:14:{s:4:"type";i:0;s:5:"width";i:150;s:6:"height";i:60;s:10:"background";i:1;s:10:"adulterate";i:1;s:3:"ttf";i:1;s:5:"angle";i:1;s:7:"warping";i:0;s:7:"scatter";i:0;s:5:"color";i:1;s:4:"size";i:1;s:6:"shadow";i:1;s:8:"animator";i:0;s:6:"length";i:4;}'),
('seccodestatus', 'a:4:{i:0;s:5:"Login";i:1;s:8:"Register";i:2;s:9:"ForgotPwd";i:3;s:8:"Activate";}'),
('sitename', '武理工自强社工时系统'),
('template', 'metronic'),
('timeformat', 'H:i'),
('timeoffset', '8');

CREATE TABLE IF NOT EXISTS `conn_users` (
  `uid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `email` char(40) NOT NULL DEFAULT '',
  `username` char(15) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `emailstatus` tinyint(1) NOT NULL DEFAULT '0',
  `avatarstatus` tinyint(1) NOT NULL DEFAULT '0',
  `videophotostatus` tinyint(1) NOT NULL DEFAULT '0',
  `adminid` tinyint(1) NOT NULL DEFAULT '0',
  `groupid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `groupexpiry` int(10) unsigned NOT NULL DEFAULT '0',
  `extgroupids` char(20) NOT NULL DEFAULT '',
  `regdate` int(10) unsigned NOT NULL DEFAULT '0',
  `credits` int(10) NOT NULL DEFAULT '0',
  `timeoffset` char(4) NOT NULL DEFAULT '',
  `newpm` smallint(6) unsigned NOT NULL DEFAULT '0',
  `newprompt` smallint(6) unsigned NOT NULL DEFAULT '0',
  `accessmasks` tinyint(1) NOT NULL DEFAULT '0',
  `allowadmincp` tinyint(1) NOT NULL DEFAULT '0',
  `conisbind` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `groupid` (`groupid`),
  KEY `conisbind` (`conisbind`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `conn_users` (`uid`, `email`, `username`, `password`, `status`, `emailstatus`, `avatarstatus`, `videophotostatus`, `adminid`, `groupid`, `groupexpiry`, `extgroupids`, `regdate`, `credits`, `timeoffset`, `newpm`, `newprompt`, `accessmasks`, `allowadmincp`, `conisbind`) VALUES
(0, 'demo@demo.com', 'demo', 'e10adc3949ba59abbe56e057f20f883e', 0, 0, 0, 0, 0, 0, 0, '', 0, 0, '', 0, 0, 0, 0, 0);

CREATE TABLE IF NOT EXISTS `conn_users_connect` (
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `conuin` char(40) NOT NULL DEFAULT '',
  `conuinsecret` char(16) NOT NULL DEFAULT '',
  `conopenid` char(32) NOT NULL DEFAULT '',
  `conisfeed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `conispublishfeed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `conispublisht` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `conisregister` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `conisqzoneavatar` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `conisqqshow` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `conuin` (`conuin`),
  KEY `conopenid` (`conopenid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `conn_users_profile` (
  `uid` mediumint(8) unsigned NOT NULL,
  `realname` varchar(255) NOT NULL DEFAULT '',
  `gender` tinyint(1) NOT NULL DEFAULT '0',
  `birthyear` smallint(6) unsigned NOT NULL DEFAULT '0',
  `birthmonth` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `birthday` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `studentid` char(13) NOT NULL COMMENT '学号',
  `grade` smallint(4) unsigned NOT NULL COMMENT '入学年份',
  `academy` tinyint(3) unsigned NOT NULL COMMENT '学院ID',
  `specialty` smallint(5) unsigned NOT NULL COMMENT '专业ID',
  `class` smallint(5) unsigned NOT NULL COMMENT '班级ID',
  `organization` varchar(8) NOT NULL COMMENT '组织',
  `league` varchar(8) NOT NULL COMMENT '社团ID',
  `department` varchar(16) NOT NULL COMMENT '部门ID',
  `constellation` varchar(255) NOT NULL DEFAULT '',
  `zodiac` varchar(255) NOT NULL DEFAULT '',
  `telephone` varchar(255) NOT NULL DEFAULT '',
  `mobile` varchar(255) NOT NULL DEFAULT '',
  `idcardtype` varchar(255) NOT NULL DEFAULT '',
  `idcard` varchar(255) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `zipcode` varchar(255) NOT NULL DEFAULT '',
  `nationality` varchar(255) NOT NULL DEFAULT '',
  `birthprovince` varchar(255) NOT NULL DEFAULT '',
  `birthcity` varchar(255) NOT NULL DEFAULT '',
  `birthdist` varchar(20) NOT NULL DEFAULT '',
  `birthcommunity` varchar(255) NOT NULL DEFAULT '',
  `resideprovince` varchar(255) NOT NULL DEFAULT '',
  `residecity` varchar(255) NOT NULL DEFAULT '',
  `residedist` varchar(20) NOT NULL DEFAULT '',
  `residecommunity` varchar(255) NOT NULL DEFAULT '',
  `residesuite` varchar(255) NOT NULL DEFAULT '',
  `graduateschool` varchar(255) NOT NULL DEFAULT '',
  `company` varchar(255) NOT NULL DEFAULT '',
  `education` varchar(255) NOT NULL DEFAULT '',
  `occupation` varchar(255) NOT NULL DEFAULT '',
  `position` varchar(255) NOT NULL DEFAULT '',
  `revenue` varchar(255) NOT NULL DEFAULT '',
  `affectivestatus` varchar(255) NOT NULL DEFAULT '',
  `lookingfor` varchar(255) NOT NULL DEFAULT '',
  `bloodtype` varchar(255) NOT NULL DEFAULT '',
  `height` varchar(255) NOT NULL DEFAULT '',
  `weight` varchar(255) NOT NULL DEFAULT '',
  `alipay` varchar(255) NOT NULL DEFAULT '',
  `qq` varchar(255) NOT NULL DEFAULT '',
  `site` varchar(255) NOT NULL DEFAULT '',
  `bio` text NOT NULL,
  `interest` text NOT NULL,
  `field1` text NOT NULL,
  `field2` text NOT NULL,
  `field3` text NOT NULL,
  `field4` text NOT NULL,
  `field5` text NOT NULL,
  `field6` text NOT NULL,
  `field7` text NOT NULL,
  `field8` text NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `studentid` (`studentid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `conn_users_profile_setting` (
  `fieldid` varchar(255) NOT NULL DEFAULT '',
  `available` tinyint(1) NOT NULL DEFAULT '0',
  `invisible` tinyint(1) NOT NULL DEFAULT '0',
  `needverify` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `displayorder` smallint(6) unsigned NOT NULL DEFAULT '0',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `unchangeable` tinyint(1) NOT NULL DEFAULT '0',
  `showincard` tinyint(1) NOT NULL DEFAULT '0',
  `showinthread` tinyint(1) NOT NULL DEFAULT '0',
  `showinregister` tinyint(1) NOT NULL DEFAULT '0',
  `allowsearch` tinyint(1) NOT NULL DEFAULT '0',
  `formtype` varchar(255) NOT NULL,
  `size` smallint(6) unsigned NOT NULL DEFAULT '0',
  `choices` text NOT NULL,
  `validate` text NOT NULL,
  PRIMARY KEY (`fieldid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `conn_users_profile_setting` (`fieldid`, `available`, `invisible`, `needverify`, `title`, `description`, `displayorder`, `required`, `unchangeable`, `showincard`, `showinthread`, `showinregister`, `allowsearch`, `formtype`, `size`, `choices`, `validate`) VALUES
('realname', 1, 0, 0, '真实姓名', '', 0, 0, 0, 0, 0, 0, 1, 'text', 0, '', ''),
('gender', 1, 0, 0, '性别', '', 0, 0, 0, 0, 0, 0, 1, 'select', 0, '', ''),
('birthyear', 1, 0, 0, '出生年份', '', 0, 0, 0, 0, 0, 0, 1, 'select', 0, '', ''),
('birthmonth', 1, 0, 0, '出生月份', '', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '', ''),
('birthday', 1, 0, 0, '生日', '', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '', ''),
('constellation', 1, 1, 0, '星座', '星座(根据生日自动计算)', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('zodiac', 1, 1, 0, '生肖', '生肖(根据生日自动计算)', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('telephone', 1, 1, 0, '固定电话', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('mobile', 1, 1, 0, '手机', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('idcardtype', 1, 1, 0, '证件类型', '身份证 护照 驾驶证等', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '身份证\n护照\n驾驶证', ''),
('idcard', 1, 1, 0, '证件号', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('address', 1, 1, 0, '邮寄地址', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('zipcode', 1, 1, 0, '邮编', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('nationality', 0, 0, 0, '国籍', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('birthprovince', 1, 0, 0, '出生省份', '', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '', ''),
('birthcity', 1, 0, 0, '出生地', '', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '', ''),
('birthdist', 1, 0, 0, '出生县', '出生行政区/县', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '', ''),
('birthcommunity', 1, 0, 0, '出生小区', '', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '', ''),
('resideprovince', 1, 0, 0, '居住省份', '', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '', ''),
('residecity', 1, 0, 0, '居住地', '', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '', ''),
('residedist', 1, 0, 0, '居住县', '居住行政区/县', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '', ''),
('residecommunity', 1, 0, 0, '居住小区', '', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '', ''),
('residesuite', 0, 0, 0, '房间', '小区、写字楼门牌号', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('graduateschool', 1, 0, 0, '毕业学校', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('education', 1, 0, 0, '学历', '', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '博士\n硕士\n本科\n专科\n中学\n小学\n其它', ''),
('company', 1, 0, 0, '公司', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('occupation', 1, 0, 0, '职业', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('position', 1, 0, 0, '职位', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('revenue', 1, 1, 0, '年收入', '单位 元', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('affectivestatus', 1, 1, 0, '情感状态', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('lookingfor', 1, 0, 0, '交友目的', '希望在网站找到什么样的朋友', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('bloodtype', 1, 1, 0, '血型', '', 0, 0, 0, 0, 0, 0, 0, 'select', 0, 'A\nB\nAB\nO\n其它', ''),
('height', 0, 1, 0, '身高', '单位 cm', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('weight', 0, 1, 0, '体重', '单位 kg', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('alipay', 1, 1, 0, '支付宝', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('icq', 0, 1, 0, 'ICQ', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('qq', 1, 1, 0, 'QQ', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('yahoo', 0, 1, 0, 'YAHOO帐号', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('msn', 1, 1, 0, 'MSN', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('taobao', 1, 1, 0, '阿里旺旺', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('site', 1, 0, 0, '个人主页', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('bio', 1, 1, 0, '自我介绍', '', 0, 0, 0, 0, 0, 0, 0, 'textarea', 0, '', ''),
('interest', 1, 0, 0, '兴趣爱好', '', 0, 0, 0, 0, 0, 0, 0, 'textarea', 0, '', ''),
('field1', 0, 1, 0, '自定义字段1', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('field2', 0, 1, 0, '自定义字段2', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('field3', 0, 1, 0, '自定义字段3', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('field4', 0, 1, 0, '自定义字段4', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('field5', 0, 1, 0, '自定义字段5', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('field6', 0, 1, 0, '自定义字段6', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('field7', 0, 1, 0, '自定义字段7', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', ''),
('field8', 0, 1, 0, '自定义字段8', '', 0, 0, 0, 0, 0, 0, 0, 'text', 0, '', '');

CREATE TABLE IF NOT EXISTS `conn_users_validate` (
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `submitdate` int(10) unsigned NOT NULL DEFAULT '0',
  `moddate` int(10) unsigned NOT NULL DEFAULT '0',
  `admin` varchar(15) NOT NULL DEFAULT '',
  `submittimes` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  `remark` text NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
