DROP TABLE IF EXISTS mhs_activation;
CREATE TABLE mhs_activation (
  `uid` mediumint(8) unsigned NOT NULL COMMENT '用户ID',
  `email` char(40) NOT NULL DEFAULT '' COMMENT '用户邮箱',
  `username` char(15) NOT NULL DEFAULT '' COMMENT '用户名',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审核状态',
  `submittime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请时间',
  `verifytime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `realname` varchar(15) NOT NULL DEFAULT '' COMMENT '真实名字',
  `gender` tinyint(1) NOT NULL DEFAULT '0' COMMENT '性别',
  `qq` varchar(11) NOT NULL DEFAULT '' COMMENT 'QQ号码',
  `studentid` char(13) NOT NULL COMMENT '学号',
  `grade` smallint(4) unsigned NOT NULL COMMENT '入学年份',
  `academy` tinyint(3) unsigned NOT NULL COMMENT '学院ID',
  `specialty` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '专业ID',
  `class` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '班级ID',
  `organization` text NOT NULL COMMENT '组织',
  `league` text NOT NULL COMMENT '社团ID',
  `department` text NOT NULL COMMENT '部门ID',
  `remark` text COMMENT '留言',
  `operator` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '审核员UID',
  `operatorname` varchar(30) NOT NULL DEFAULT '' COMMENT '审核员名字',
  `verifytext` text COMMENT '审核信息',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `studentid` (`studentid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='激活申请记录';

DROP TABLE IF EXISTS mhs_activity;
CREATE TABLE mhs_activity (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `name` varchar(90) NOT NULL DEFAULT '' COMMENT '活动名称',
  `place` varchar(90) NOT NULL DEFAULT '' COMMENT '活动地点',
  `intro` text NOT NULL COMMENT '活动介绍',
  `starttime` int(10) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endtime` int(10) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `available` tinyint(1) NOT NULL DEFAULT '1' COMMENT '活动有效性',
  `sponsor` text NOT NULL COMMENT '主办者（发起者）',
  `undertaker` text NOT NULL COMMENT '承办者',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='活动' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_admingroup;
CREATE TABLE mhs_admingroup (
  `gid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理组ID',
  `level` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '管理组级别，数字越小级别越高',
  `inherit` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '继承于',
  `name` varchar(255) NOT NULL COMMENT '组头衔',
  `note` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `permit` text NOT NULL COMMENT '权限',
  PRIMARY KEY (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='管理组' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_announcement;
CREATE TABLE mhs_announcement (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `author` varchar(15) NOT NULL DEFAULT '',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `displayorder` tinyint(3) NOT NULL DEFAULT '0',
  `starttime` int(10) unsigned NOT NULL DEFAULT '0',
  `endtime` int(10) unsigned NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `timespan` (`starttime`,`endtime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='公告' AUTO_INCREMENT=4;

INSERT INTO mhs_announcement (`id`, `author`, `subject`, `type`, `displayorder`, `starttime`, `endtime`, `message`) VALUES
(1, '', '既不回头，何必不忘；既然无缘，何须誓言；今日种种，似水无痕；明夕何夕，君已陌路', 2, 0, 1397375100, 0, 'http://hitokoto.us/view/1324011466000'),
(2, '', '挡着在我们面前的是巨大庞然的人生，阻隔在我们中间的是广阔无际的时间，对于他们，我们无能为力⋯⋯', 1, 0, 1397378021, 0, '挡着在我们面前的是巨大庞然的人生，阻隔在我们中间的是广阔无际的时间，对于他们，我们无能为力⋯⋯'),
(3, 'auth', '就是因为你不好，才要留在你身边，给你幸福。', 1, 0, 0, 0, '就是因为你不好，才要留在你身边，给你幸福。\r\n就是因为你不好，才要留在你身边，给你幸福。\r\n就是因为你不好，才要留在你身边，给你幸福。\r\n\r\n就是因为你不好，才要留在你身边，给你幸福。\r\n就是因为你不好，才要留在你身边，给你幸福。');

DROP TABLE IF EXISTS mhs_failedlogin;
CREATE TABLE mhs_failedlogin (
  `ip` char(15) NOT NULL DEFAULT '',
  `username` char(32) NOT NULL DEFAULT '',
  `count` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='登录失败记录';

DROP TABLE IF EXISTS mhs_group;
CREATE TABLE mhs_group (
  `gid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户组ID',
  `inherit` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '继承于',
  `name` varchar(255) NOT NULL COMMENT '组头衔',
  PRIMARY KEY (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户组' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_manhours;
CREATE TABLE mhs_manhours (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '工时ID索引',
  `uid` mediumint(8) unsigned NOT NULL COMMENT '用户ID',
  `manhour` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '工时',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态，0无效，1有效，2等待审核，3复查中',
  `aid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '活动ID',
  `actname` varchar(90) NOT NULL DEFAULT '' COMMENT '活动名称',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  `applytime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请时间',
  `verifytime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `operator` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '审核员',
  `remark` text NOT NULL COMMENT '申请留言',
  `verifytext` text NOT NULL COMMENT '审核留言',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='工时' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_profile_academies;
CREATE TABLE mhs_profile_academies (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT COMMENT '学院ID',
  `name` varchar(36) NOT NULL COMMENT '学院名称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='学院' AUTO_INCREMENT=26;

INSERT INTO mhs_profile_academies (`id`, `name`) VALUES
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

DROP TABLE IF EXISTS mhs_profile_classes;
CREATE TABLE mhs_profile_classes (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '班级ID',
  `aid` tinyint(2) unsigned NOT NULL COMMENT '学院ID',
  `sid` smallint(5) unsigned NOT NULL COMMENT '专业ID',
  `gid` tinyint(1) NOT NULL COMMENT '年级ID',
  `name` varchar(32) NOT NULL COMMENT '班级名称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='班级' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_profile_departments;
CREATE TABLE mhs_profile_departments (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '部门ID',
  `lid` smallint(5) unsigned NOT NULL COMMENT '社团ID',
  `name` varchar(32) NOT NULL COMMENT '部门名称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='部门' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_profile_grades;
CREATE TABLE mhs_profile_grades (
  `id` tinyint(1) NOT NULL COMMENT '年级ID',
  `grade` char(4) NOT NULL COMMENT '年级（入学年份）',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='年级';

INSERT INTO mhs_profile_grades (`id`, `grade`) VALUES
(1, '2010'),
(2, '2011'),
(3, '2012'),
(4, '2013');

DROP TABLE IF EXISTS mhs_profile_leagues;
CREATE TABLE mhs_profile_leagues (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '社团ID',
  `aid` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '学院ID',
  `name` varchar(32) NOT NULL COMMENT '社团名称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='社团' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_profile_organizations;
CREATE TABLE mhs_profile_organizations (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '社团ID',
  `aid` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '学院ID',
  `name` varchar(32) NOT NULL COMMENT '社团名称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='组织';

DROP TABLE IF EXISTS mhs_profile_specialties;
CREATE TABLE mhs_profile_specialties (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '专业ID',
  `aid` tinyint(2) NOT NULL COMMENT '学院ID',
  `name` varchar(64) NOT NULL COMMENT '专业名称',
  `g1` tinyint(1) NOT NULL DEFAULT '0',
  `g2` tinyint(1) NOT NULL DEFAULT '0',
  `g3` tinyint(1) NOT NULL DEFAULT '0',
  `g4` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='专业' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_session;
CREATE TABLE mhs_session (
  `sid` char(6) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `ip1` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ip2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ip3` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ip4` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username` char(15) NOT NULL DEFAULT '',
  `lastactivity` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`),
  KEY `uid` (`uid`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='会话Session';

DROP TABLE IF EXISTS mhs_setting;
CREATE TABLE mhs_setting (
  `skey` varchar(255) NOT NULL DEFAULT '',
  `svalue` text NOT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='设置';

INSERT INTO mhs_setting (`skey`, `svalue`) VALUES
('closed', '0'),
('closereason', '这里填关闭注册的原因'),
('copyright', '2014 &copy; 武汉理工大学自强社'),
('dateconvert', '0'),
('dateformat', 'Y-n-j'),
('debug', '1'),
('failedlogin', 'a:2:{s:5:"count";i:5;s:4:"time";i:1;}'),
('logintip', 'a:2:{i:0;s:27:"用户名不等同于昵称";i:1;s:82:"若验证码图片里的文字不是4个，请点击验证码图片更换验证码";}'),
('logopath', 'static/images/logo-whut-index.png'),
('mail', 'a:12:{s:4:"type";s:4:"SMTP";s:8:"template";s:8:"sysemail";s:8:"pop3host";s:10:"pop.qq.com";s:8:"pop3port";i:995;s:8:"smtphost";s:11:"smtp.qq.com";s:8:"smtpport";i:465;s:10:"smtpsecure";s:3:"ssl";s:8:"smtpauth";i:1;s:8:"username";s:7:"gwc0721";s:8:"password";s:9:"xsszxgGWC";s:4:"from";s:14:"gwc0721@qq.com";s:8:"fromname";s:7:"gwc0721";}'),
('nocacheheaders', '1'),
('regopen', '1'),
('seccodedata', 'a:14:{s:4:"type";i:0;s:5:"width";i:150;s:6:"height";i:60;s:10:"background";i:1;s:10:"adulterate";i:1;s:3:"ttf";i:1;s:5:"angle";i:1;s:7:"warping";i:0;s:7:"scatter";i:0;s:5:"color";i:1;s:4:"size";i:1;s:6:"shadow";i:1;s:8:"animator";i:0;s:6:"length";i:4;}'),
('seccodestatus', 'a:5:{i:0;s:5:"Login";i:1;s:8:"Register";i:2;s:9:"ForgotPwd";i:3;s:8:"Activate";i:4;s:5:"cgpwd";}'),
('sitename', '武理工自强社工时系统'),
('template', 'metronic'),
('timeformat', 'H:i'),
('timeoffset', '8'),
('tos', '#### 一、基本条款\n\n 1. 在开始使用点点网（下称“本网站”）提供的所有服务之前，用户需认真阅读、充分理解本《点点网服务条款》（下称“《条款》”），尤其包括免除或者限制点点网责任的条款及对用户的权利限制条款。请您审慎阅读并选择接受或不接受本《条款》内容。如果您选择不接受本《条款》内的任何内容，您无权注册和使用本网站提供的所有服务。一旦您注册或者登录点点网，即表示您接受本《条款》的内容，同意受到本《条款》的各项约束。\n 2. 您可以通过以下任何一种方式接受本《条款》：\n  - 在点点网平台任一用户界面中，点击表示接受或同意本《条款》的全部条款的选项；\n  - 实际使用点点网平台及点点网提供的其他相关服务。您对点点网平台及其他相关服务的使用将被视为您自实际使用起便接受了本《条款》的全部条款。\n 3. 本《条款》可由点点网随时更新，更新后的协议条款一旦公布即代替原来的协议条款。用户可随时登录网站查阅最新版协议条款。在点点网修改《协议》条款后，如果用户不接受修改后的条款，请立即停止使用点点网提供的服务，如果用户继续使用点点网提供的服务将被视为已接受了修改后的协议。'),
('regclosedreason', '当前关闭注册，请联系管理员'),
('actopen', '1'),
('actclosedreason', '当前关闭激活！详情请咨询管理员'),
('multilang', '0'),
('icp', ''),
('statcode', ''),
('service', ''),
('session', '1');

DROP TABLE IF EXISTS mhs_users;
CREATE TABLE mhs_users (
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
  `lastlogin` int(10) unsigned NOT NULL DEFAULT '0',
  `manhour` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '总工时',
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_users_connect;
CREATE TABLE mhs_users_connect (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='QQ互联';

DROP TABLE IF EXISTS mhs_users_profile;
CREATE TABLE mhs_users_profile (
  `uid` mediumint(8) unsigned NOT NULL,
  `realname` varchar(255) NOT NULL DEFAULT '' COMMENT '真实名字',
  `gender` tinyint(1) NOT NULL DEFAULT '0' COMMENT '性别',
  `qq` varchar(11) NOT NULL DEFAULT '' COMMENT 'QQ号码',
  `birthyear` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '出生年份',
  `birthmonth` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '出生月份',
  `birthday` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '出生日期',
  `studentid` char(13) NOT NULL COMMENT '学号',
  `grade` smallint(4) unsigned NOT NULL COMMENT '入学年份',
  `academy` tinyint(3) unsigned NOT NULL COMMENT '学院ID',
  `specialty` smallint(5) unsigned NOT NULL COMMENT '专业ID',
  `class` smallint(5) unsigned NOT NULL COMMENT '班级ID',
  `organization` varchar(16) NOT NULL COMMENT '组织',
  `league` varchar(16) NOT NULL COMMENT '社团ID',
  `department` varchar(32) NOT NULL COMMENT '部门ID',
  `constellation` varchar(255) NOT NULL DEFAULT '' COMMENT '星座',
  `zodiac` varchar(255) NOT NULL DEFAULT '' COMMENT '生肖',
  `telephone` varchar(255) NOT NULL DEFAULT '' COMMENT '固定电话',
  `mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '手机',
  `idcardtype` varchar(255) NOT NULL DEFAULT '' COMMENT '证件类型',
  `idcard` varchar(255) NOT NULL DEFAULT '' COMMENT '证件号',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `zipcode` varchar(255) NOT NULL DEFAULT '' COMMENT '邮编',
  `nationality` varchar(255) NOT NULL DEFAULT '' COMMENT '国籍',
  `birthprovince` varchar(255) NOT NULL DEFAULT '' COMMENT '出生省份',
  `birthcity` varchar(255) NOT NULL DEFAULT '' COMMENT '出生地',
  `birthdist` varchar(20) NOT NULL DEFAULT '' COMMENT '出生县',
  `birthcommunity` varchar(255) NOT NULL DEFAULT '' COMMENT '出生小区',
  `resideprovince` varchar(255) NOT NULL DEFAULT '' COMMENT '居住省份',
  `residecity` varchar(255) NOT NULL DEFAULT '' COMMENT '居住地',
  `residedist` varchar(20) NOT NULL DEFAULT '' COMMENT '居住县',
  `residecommunity` varchar(255) NOT NULL DEFAULT '' COMMENT '居住小区',
  `residesuite` varchar(255) NOT NULL DEFAULT '' COMMENT '房间',
  `graduateschool` varchar(255) NOT NULL DEFAULT '' COMMENT '毕业学校',
  `company` varchar(255) NOT NULL DEFAULT '' COMMENT '公司',
  `education` varchar(255) NOT NULL DEFAULT '' COMMENT '学历',
  `occupation` varchar(255) NOT NULL DEFAULT '' COMMENT '职业',
  `position` varchar(255) NOT NULL DEFAULT '' COMMENT '职位',
  `revenue` varchar(255) NOT NULL DEFAULT '' COMMENT '年收入',
  `affectivestatus` varchar(255) NOT NULL DEFAULT '' COMMENT '情感状态',
  `lookingfor` varchar(255) NOT NULL DEFAULT '',
  `bloodtype` varchar(255) NOT NULL DEFAULT '' COMMENT '血型',
  `height` varchar(255) NOT NULL DEFAULT '' COMMENT '身高',
  `weight` varchar(255) NOT NULL DEFAULT '' COMMENT '体重',
  `alipay` varchar(255) NOT NULL DEFAULT '' COMMENT '支付宝账号',
  `icq` varchar(255) NOT NULL DEFAULT '' COMMENT 'ICQ账号',
  `yahoo` varchar(255) NOT NULL DEFAULT '' COMMENT '雅虎账号',
  `msn` varchar(255) NOT NULL DEFAULT '' COMMENT 'MSN账号',
  `taobao` varchar(255) NOT NULL DEFAULT '' COMMENT '淘宝账号',
  `site` varchar(255) NOT NULL DEFAULT '' COMMENT '个人主页',
  `bio` text NOT NULL,
  `interest` text NOT NULL COMMENT '兴趣',
  `field1` text NOT NULL COMMENT '自定义字段1',
  `field2` text NOT NULL COMMENT '自定义字段2',
  `field3` text NOT NULL COMMENT '自定义字段3',
  `field4` text NOT NULL COMMENT '自定义字段4',
  `field5` text NOT NULL COMMENT '自定义字段5',
  `field6` text NOT NULL COMMENT '自定义字段6',
  `field7` text NOT NULL COMMENT '自定义字段7',
  `field8` text NOT NULL COMMENT '自定义字段8',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `studentid` (`studentid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户资料';

DROP TABLE IF EXISTS mhs_users_profile_setting;
CREATE TABLE mhs_users_profile_setting (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='资料字段';

INSERT INTO mhs_users_profile_setting (`fieldid`, `available`, `invisible`, `needverify`, `title`, `description`, `displayorder`, `required`, `unchangeable`, `showincard`, `showinthread`, `showinregister`, `allowsearch`, `formtype`, `size`, `choices`, `validate`) VALUES
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

DROP TABLE IF EXISTS mhs_users_validate;
CREATE TABLE mhs_users_validate (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户校验';
