DROP TABLE IF EXISTS mhs_users;
CREATE TABLE mhs_users (
  uid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  email char(40) NOT NULL DEFAULT '',
  username char(15) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  emailstatus tinyint(1) NOT NULL DEFAULT '0',
  avatarstatus tinyint(1) NOT NULL DEFAULT '0',
  videophotostatus tinyint(1) NOT NULL DEFAULT '0',
  adminid tinyint(1) NOT NULL DEFAULT '0',
  groupid smallint(6) unsigned NOT NULL DEFAULT '0',
  groupexpiry int(10) unsigned NOT NULL DEFAULT '0',
  extgroupids char(20) NOT NULL DEFAULT '',
  regdate int(10) unsigned NOT NULL DEFAULT '0',
  lastlogin int(10) unsigned NOT NULL DEFAULT '0',
  manhour smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '总工时',
  rank mediumint(8) unsigned NOT NULL DEFAULT '0',
  credits int(10) NOT NULL DEFAULT '0',
  timeoffset char(4) NOT NULL DEFAULT '',
  newpm smallint(6) unsigned NOT NULL DEFAULT '0',
  newprompt smallint(6) unsigned NOT NULL DEFAULT '0',
  accessmasks tinyint(1) NOT NULL DEFAULT '0',
  allowadmincp tinyint(1) NOT NULL DEFAULT '0',
  conisbind tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  UNIQUE KEY username (username),
  KEY email (email),
  KEY groupid (groupid),
  KEY conisbind (conisbind),
  KEY rank (rank)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_activation;
CREATE TABLE mhs_activation (
  uid mediumint(8) unsigned NOT NULL COMMENT '用户ID',
  email char(40) NOT NULL DEFAULT '' COMMENT '用户邮箱',
  username char(15) NOT NULL DEFAULT '' COMMENT '用户名',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审核状态',
  submittime int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请时间',
  verifytime int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  realname varchar(15) NOT NULL DEFAULT '' COMMENT '真实名字',
  gender tinyint(1) NOT NULL DEFAULT '0' COMMENT '性别',
  qq varchar(11) NOT NULL DEFAULT '' COMMENT 'QQ号码',
  studentid char(13) NOT NULL COMMENT '学号',
  grade smallint(4) unsigned NOT NULL COMMENT '入学年份',
  academy tinyint(3) unsigned NOT NULL COMMENT '学院ID',
  specialty smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '专业ID',
  class smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '班级ID',
  organization text NOT NULL COMMENT '组织',
  league text NOT NULL COMMENT '社团ID',
  department text NOT NULL COMMENT '部门ID',
  remark text COMMENT '留言',
  operator mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '审核员UID',
  operatorname varchar(30) NOT NULL DEFAULT '' COMMENT '审核员名字',
  verifytext text COMMENT '审核信息',
  PRIMARY KEY (uid),
  UNIQUE KEY studentid (studentid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='激活申请记录';

DROP TABLE IF EXISTS mhs_activity;
CREATE TABLE mhs_activity (
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `name` varchar(90) NOT NULL DEFAULT '' COMMENT '活动名称',
  place varchar(90) NOT NULL DEFAULT '' COMMENT '活动地点',
  intro text NOT NULL COMMENT '活动介绍',
  starttime int(10) NOT NULL DEFAULT '0' COMMENT '开始时间',
  endtime int(10) NOT NULL DEFAULT '0' COMMENT '结束时间',
  available tinyint(1) NOT NULL DEFAULT '1' COMMENT '活动有效性',
  academy tinyint(2) unsigned NOT NULL DEFAULT '0',
  sponsor text NOT NULL COMMENT '主办者（发起者）',
  undertaker text NOT NULL COMMENT '承办者',
  PRIMARY KEY (id),
  KEY academy (academy),
  KEY available (available)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='活动' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_admingroup;
CREATE TABLE mhs_admingroup (
  gid smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理组ID',
  parent smallint(6) unsigned NOT NULL DEFAULT '0',
  relation varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL COMMENT '组头衔',
  note varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  formula varchar(512) NOT NULL DEFAULT '',
  permit text NOT NULL COMMENT '权限',
  PRIMARY KEY (gid)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='管理组' AUTO_INCREMENT=2;

DROP TABLE IF EXISTS mhs_announcement;
CREATE TABLE mhs_announcement (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '公告id',
  author varchar(15) NOT NULL DEFAULT '' COMMENT '作者姓名',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '公告标题',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '公告类型 (0:文字公告 1:网址链接)',
  displayorder tinyint(3) NOT NULL DEFAULT '0' COMMENT '显示顺序',
  starttime int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  endtime int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  message text NOT NULL COMMENT '消息',
  academy tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '学院ID',
  PRIMARY KEY (id),
  KEY timespan (starttime,endtime),
  KEY academy (academy)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='公告' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_cron;
CREATE TABLE mhs_cron (
  cronid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  available tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('user','system','plugin') NOT NULL DEFAULT 'user',
  `name` char(50) NOT NULL DEFAULT '',
  filename char(50) NOT NULL DEFAULT '',
  lastrun int(10) unsigned NOT NULL DEFAULT '0',
  nextrun int(10) unsigned NOT NULL DEFAULT '0',
  weekday tinyint(1) NOT NULL DEFAULT '0',
  `day` tinyint(2) NOT NULL DEFAULT '0',
  `hour` tinyint(2) NOT NULL DEFAULT '0',
  `minute` char(36) NOT NULL DEFAULT '',
  PRIMARY KEY (cronid),
  KEY nextrun (available,nextrun)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_failedlogin;
CREATE TABLE mhs_failedlogin (
  ip char(15) NOT NULL DEFAULT '',
  username char(32) NOT NULL DEFAULT '',
  count tinyint(1) unsigned NOT NULL DEFAULT '0',
  lastupdate int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (ip,username)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='登录失败记录';

DROP TABLE IF EXISTS mhs_group;
CREATE TABLE mhs_group (
  gid smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户组ID',
  inherit smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '继承于',
  `name` varchar(255) NOT NULL COMMENT '组头衔',
  PRIMARY KEY (gid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户组' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_manhours;
CREATE TABLE mhs_manhours (
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '工时ID索引',
  uid mediumint(8) unsigned NOT NULL COMMENT '用户ID',
  manhour smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '工时',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态，0无效，1有效，2等待审核，3复查中',
  aid mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '活动ID',
  actname varchar(90) NOT NULL DEFAULT '' COMMENT '活动名称',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  applytime int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请时间',
  verifytime int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  operator mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '审核员',
  remark text NOT NULL COMMENT '申请留言',
  verifytext text NOT NULL COMMENT '审核留言',
  PRIMARY KEY (id),
  KEY uid (uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='工时' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_profile_academies;
CREATE TABLE mhs_profile_academies (
  id tinyint(2) unsigned NOT NULL AUTO_INCREMENT COMMENT '学院ID',
  `name` varchar(36) NOT NULL COMMENT '学院名称',
  PRIMARY KEY (id)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COMMENT='学院' AUTO_INCREMENT=26;

DROP TABLE IF EXISTS mhs_profile_classes;
CREATE TABLE mhs_profile_classes (
  id smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '班级ID',
  aid tinyint(2) unsigned NOT NULL COMMENT '学院ID',
  sid smallint(5) unsigned NOT NULL COMMENT '专业ID',
  gid tinyint(1) NOT NULL COMMENT '年级ID',
  `name` varchar(32) NOT NULL COMMENT '班级名称',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='班级' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_profile_departments;
CREATE TABLE mhs_profile_departments (
  id smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '部门ID',
  lid smallint(5) unsigned NOT NULL COMMENT '社团ID',
  `name` varchar(32) NOT NULL COMMENT '部门名称',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='部门' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_profile_grades;
CREATE TABLE mhs_profile_grades (
  id tinyint(1) NOT NULL COMMENT '年级ID',
  grade char(4) NOT NULL COMMENT '年级（入学年份）',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='年级';

DROP TABLE IF EXISTS mhs_profile_leagues;
CREATE TABLE mhs_profile_leagues (
  id smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '社团ID',
  aid tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '学院ID',
  `name` varchar(32) NOT NULL COMMENT '社团名称',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='社团' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_profile_organizations;
CREATE TABLE mhs_profile_organizations (
  id smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '社团ID',
  aid tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '学院ID',
  `name` varchar(32) NOT NULL COMMENT '社团名称',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='组织' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_profile_specialties;
CREATE TABLE mhs_profile_specialties (
  id smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '专业ID',
  aid tinyint(2) NOT NULL COMMENT '学院ID',
  `name` varchar(64) NOT NULL COMMENT '专业名称',
  g1 tinyint(1) NOT NULL DEFAULT '0',
  g2 tinyint(1) NOT NULL DEFAULT '0',
  g3 tinyint(1) NOT NULL DEFAULT '0',
  g4 tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='专业' AUTO_INCREMENT=1;

DROP TABLE IF EXISTS mhs_session;
CREATE TABLE mhs_session (
  sid char(6) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  ip1 tinyint(3) unsigned NOT NULL DEFAULT '0',
  ip2 tinyint(3) unsigned NOT NULL DEFAULT '0',
  ip3 tinyint(3) unsigned NOT NULL DEFAULT '0',
  ip4 tinyint(3) unsigned NOT NULL DEFAULT '0',
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  username char(15) NOT NULL DEFAULT '',
  lastactivity int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (sid),
  KEY uid (uid)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='会话Session';

DROP TABLE IF EXISTS mhs_setting;
CREATE TABLE mhs_setting (
  skey varchar(255) NOT NULL DEFAULT '',
  svalue text NOT NULL,
  PRIMARY KEY (skey)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='设置';

DROP TABLE IF EXISTS mhs_users_connect;
CREATE TABLE mhs_users_connect (
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  conuin char(40) NOT NULL DEFAULT '',
  conuinsecret char(16) NOT NULL DEFAULT '',
  conopenid char(32) NOT NULL DEFAULT '',
  conisfeed tinyint(1) unsigned NOT NULL DEFAULT '0',
  conispublishfeed tinyint(1) unsigned NOT NULL DEFAULT '0',
  conispublisht tinyint(1) unsigned NOT NULL DEFAULT '0',
  conisregister tinyint(1) unsigned NOT NULL DEFAULT '0',
  conisqzoneavatar tinyint(1) unsigned NOT NULL DEFAULT '0',
  conisqqshow tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  KEY conuin (conuin),
  KEY conopenid (conopenid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='QQ互联';

DROP TABLE IF EXISTS mhs_users_profile;
CREATE TABLE mhs_users_profile (
  uid mediumint(8) unsigned NOT NULL,
  realname varchar(255) NOT NULL DEFAULT '' COMMENT '真实名字',
  gender tinyint(1) NOT NULL DEFAULT '0' COMMENT '性别',
  qq varchar(11) NOT NULL DEFAULT '' COMMENT 'QQ号码',
  birthyear smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '出生年份',
  birthmonth tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '出生月份',
  birthday tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '出生日期',
  studentid char(13) NOT NULL COMMENT '学号',
  grade smallint(4) unsigned NOT NULL COMMENT '入学年份',
  academy tinyint(2) unsigned NOT NULL COMMENT '学院ID',
  specialty varchar(60) NOT NULL COMMENT '专业',
  class varchar(30) NOT NULL COMMENT '班级',
  organization varchar(255) NOT NULL COMMENT '组织',
  league varchar(255) NOT NULL COMMENT '社团',
  department varchar(255) NOT NULL COMMENT '部门',
  constellation varchar(255) NOT NULL DEFAULT '' COMMENT '星座',
  zodiac varchar(255) NOT NULL DEFAULT '' COMMENT '生肖',
  telephone varchar(255) NOT NULL DEFAULT '' COMMENT '固定电话',
  mobile varchar(255) NOT NULL DEFAULT '' COMMENT '手机',
  idcardtype varchar(255) NOT NULL DEFAULT '' COMMENT '证件类型',
  idcard varchar(255) NOT NULL DEFAULT '' COMMENT '证件号',
  address varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  zipcode varchar(255) NOT NULL DEFAULT '' COMMENT '邮编',
  nationality varchar(255) NOT NULL DEFAULT '' COMMENT '国籍',
  birthprovince varchar(255) NOT NULL DEFAULT '' COMMENT '出生省份',
  birthcity varchar(255) NOT NULL DEFAULT '' COMMENT '出生地',
  birthdist varchar(20) NOT NULL DEFAULT '' COMMENT '出生县',
  birthcommunity varchar(255) NOT NULL DEFAULT '' COMMENT '出生小区',
  resideprovince varchar(255) NOT NULL DEFAULT '' COMMENT '居住省份',
  residecity varchar(255) NOT NULL DEFAULT '' COMMENT '居住地',
  residedist varchar(20) NOT NULL DEFAULT '' COMMENT '居住县',
  residecommunity varchar(255) NOT NULL DEFAULT '' COMMENT '居住小区',
  residesuite varchar(255) NOT NULL DEFAULT '' COMMENT '房间',
  graduateschool varchar(255) NOT NULL DEFAULT '' COMMENT '毕业学校',
  company varchar(255) NOT NULL DEFAULT '' COMMENT '公司',
  education varchar(255) NOT NULL DEFAULT '' COMMENT '学历',
  occupation varchar(255) NOT NULL DEFAULT '' COMMENT '职业',
  position varchar(255) NOT NULL DEFAULT '' COMMENT '职位',
  revenue varchar(255) NOT NULL DEFAULT '' COMMENT '年收入',
  affectivestatus varchar(255) NOT NULL DEFAULT '' COMMENT '情感状态',
  lookingfor varchar(255) NOT NULL DEFAULT '',
  bloodtype varchar(255) NOT NULL DEFAULT '' COMMENT '血型',
  height varchar(255) NOT NULL DEFAULT '' COMMENT '身高',
  weight varchar(255) NOT NULL DEFAULT '' COMMENT '体重',
  alipay varchar(255) NOT NULL DEFAULT '' COMMENT '支付宝账号',
  icq varchar(255) NOT NULL DEFAULT '' COMMENT 'ICQ账号',
  yahoo varchar(255) NOT NULL DEFAULT '' COMMENT '雅虎账号',
  msn varchar(255) NOT NULL DEFAULT '' COMMENT 'MSN账号',
  taobao varchar(255) NOT NULL DEFAULT '' COMMENT '淘宝账号',
  site varchar(255) NOT NULL DEFAULT '' COMMENT '个人主页',
  bio text NOT NULL,
  interest text NOT NULL COMMENT '兴趣',
  field1 text NOT NULL COMMENT '自定义字段1',
  field2 text NOT NULL COMMENT '自定义字段2',
  field3 text NOT NULL COMMENT '自定义字段3',
  field4 text NOT NULL COMMENT '自定义字段4',
  field5 text NOT NULL COMMENT '自定义字段5',
  field6 text NOT NULL COMMENT '自定义字段6',
  field7 text NOT NULL COMMENT '自定义字段7',
  field8 text NOT NULL COMMENT '自定义字段8',
  PRIMARY KEY (uid),
  UNIQUE KEY studentid (studentid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户资料';

DROP TABLE IF EXISTS mhs_users_profile_setting;
CREATE TABLE mhs_users_profile_setting (
  fieldid varchar(255) NOT NULL DEFAULT '',
  available tinyint(1) NOT NULL DEFAULT '0',
  invisible tinyint(1) NOT NULL DEFAULT '0',
  needverify tinyint(1) NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL DEFAULT '',
  description varchar(255) NOT NULL DEFAULT '',
  displayorder smallint(6) unsigned NOT NULL DEFAULT '0',
  required tinyint(1) NOT NULL DEFAULT '0',
  unchangeable tinyint(1) NOT NULL DEFAULT '0',
  showincard tinyint(1) NOT NULL DEFAULT '0',
  showinthread tinyint(1) NOT NULL DEFAULT '0',
  showinregister tinyint(1) NOT NULL DEFAULT '0',
  allowsearch tinyint(1) NOT NULL DEFAULT '0',
  formtype varchar(255) NOT NULL,
  size smallint(6) unsigned NOT NULL DEFAULT '0',
  choices text NOT NULL,
  validate text NOT NULL,
  PRIMARY KEY (fieldid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='资料字段';

DROP TABLE IF EXISTS mhs_users_validate;
CREATE TABLE mhs_users_validate (
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  submitdate int(10) unsigned NOT NULL DEFAULT '0',
  moddate int(10) unsigned NOT NULL DEFAULT '0',
  admin varchar(15) NOT NULL DEFAULT '',
  submittimes tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  message text NOT NULL,
  remark text NOT NULL,
  PRIMARY KEY (uid),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户校验';

INSERT INTO mhs_admingroup VALUES ('1','0','',0xe7ab99e995bf,0xe69c80e9ab98e7baa7e7aea1e79086e7bb84efbc8ce585b7e5a487e7b3bbe7bb9fe585a8e983a8e69d83e99990,'','');

INSERT INTO mhs_profile_academies VALUES ('1',0xe69d90e69699e7a791e5ada6e4b88ee5b7a5e7a88be5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('2',0xe4baa4e9809ae5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('3',0xe7aea1e79086e5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('4',0xe69cbae794b5e5b7a5e7a88be5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('5',0xe883bde6ba90e4b88ee58aa8e58a9be5b7a5e7a88be5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('6',0xe59c9fe69ca8e5b7a5e7a88be4b88ee5bbbae7ad91e5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('7',0xe6b1bde8bda6e5b7a5e7a88be5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('8',0xe8b584e6ba90e4b88ee78eafe5a283e5b7a5e7a88be5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('9',0xe4bfa1e681afe5b7a5e7a88be5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('10',0xe8aea1e7ae97e69cbae7a791e5ada6e4b88ee68a80e69cafe5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('11',0xe887aae58aa8e58c96e5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('12',0xe888aae8bf90e5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('13',0xe69687e6b395e5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('14',0xe79086e5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('15',0xe7bb8fe6b58ee5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('16',0xe889bae69cafe4b88ee8aebee8aea1e5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('17',0xe5a496e59bbde8afade5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('18',0xe789a9e6b581e5b7a5e7a88be5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('19',0xe694bfe6b2bbe4b88ee8a18ce694bfe5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('20',0xe58c96e5ada6e5b7a5e7a88be5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('21',0xe59bbde99985e69599e882b2e5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('22',0xe7bd91e7bb9c28e7bba7e7bbad29e69599e882b2e5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('23',0xe8818ce4b89ae68a80e69cafe5ada6e999a2);
INSERT INTO mhs_profile_academies VALUES ('24',0xe4bd93e882b2e983a8);
INSERT INTO mhs_profile_academies VALUES ('25',0xe9a9ace5858be6809de4b8bbe4b989e5ada6e999a2);

INSERT INTO mhs_setting VALUES (0x616374636c6f736564726561736f6e,0xe5bd93e5898de585b3e997ade6bf80e6b4bbefbc81e8afa6e68385e8afb7e592a8e8afa2e7aea1e79086e59198);
INSERT INTO mhs_setting VALUES (0x6163746f70656e,0x31);
INSERT INTO mhs_setting VALUES (0x636c6f736564,'0');
INSERT INTO mhs_setting VALUES (0x636c6f7365726561736f6e,0xe7ab99e782b9e58d87e7baa7e4b8adefbc8ce8afb7e7a88de5908ee8aebfe997ae);
INSERT INTO mhs_setting VALUES (0x636f70797269676874,0x323031342026636f70793b20e6ada6e6b189e79086e5b7a5e5a4a7e5ada6e887aae5bcbae7a4be);
INSERT INTO mhs_setting VALUES (0x64617465636f6e76657274,0x31);
INSERT INTO mhs_setting VALUES (0x64617465666f726d6174,0x592d6d2d64);
INSERT INTO mhs_setting VALUES (0x6465627567,0x31);
INSERT INTO mhs_setting VALUES (0x6661696c65646c6f67696e,0x613a323a7b733a353a22636f756e74223b693a353b733a343a2274696d65223b693a313b7d);
INSERT INTO mhs_setting VALUES (0x677261646573,0x323031312c323031322c323031332c32303134);
INSERT INTO mhs_setting VALUES (0x696370,'');
INSERT INTO mhs_setting VALUES (0x6c6f676f70617468,0x7374617469632f696d616765732f6c6f676f2d6d68732e706e67);
INSERT INTO mhs_setting VALUES (0x6d61696c,0x613a31323a7b733a343a2274797065223b733a343a22534d5450223b733a383a2274656d706c617465223b733a383a22737973656d61696c223b733a383a22706f7033686f7374223b733a31373a22706f702e65786d61696c2e71712e636f6d223b733a383a22706f7033706f7274223b693a3939353b733a383a22736d7470686f7374223b733a31383a22736d74702e65786d61696c2e71712e636f6d223b733a383a22736d7470706f7274223b693a3436353b733a31303a22736d7470736563757265223b733a333a2273736c223b733a383a22736d747061757468223b693a313b733a383a22757365726e616d65223b733a31393a226e6f7265706c7940776875747a71732e636f6d223b733a383a2270617373776f7264223b733a31313a22776875747a717332303134223b733a343a2266726f6d223b733a31393a226e6f7265706c7940776875747a71732e636f6d223b733a383a2266726f6d6e616d65223b733a32373a22e6ada6e6b189e79086e5b7a5e5a4a7e5ada6e887aae5bcbae7a4be223b7d);
INSERT INTO mhs_setting VALUES (0x6d756c74696c616e67,'0');
INSERT INTO mhs_setting VALUES (0x6e6f636163686568656164657273,0x31);
INSERT INTO mhs_setting VALUES (0x707764736166657479,0x31);
INSERT INTO mhs_setting VALUES (0x726567636c6f736564726561736f6e,0xe5bd93e5898de585b3e997ade6b3a8e5868cefbc8ce8afb7e88194e7b3bbe7aea1e79086e59198);
INSERT INTO mhs_setting VALUES (0x7265676f70656e,0x31);
INSERT INTO mhs_setting VALUES (0x736563636f646564617461,0x613a31343a7b733a343a2274797065223b693a303b733a353a227769647468223b693a3135303b733a363a22686569676874223b693a36303b733a31303a226261636b67726f756e64223b693a313b733a31303a226164756c746572617465223b693a313b733a333a22747466223b693a313b733a353a22616e676c65223b693a313b733a373a2277617270696e67223b693a303b733a373a2273636174746572223b693a303b733a353a22636f6c6f72223b693a313b733a343a2273697a65223b693a313b733a363a22736861646f77223b693a313b733a383a22616e696d61746f72223b693a303b733a363a226c656e677468223b693a343b7d);
INSERT INTO mhs_setting VALUES (0x736563636f6465737461747573,0x613a353a7b693a303b733a353a224c6f67696e223b693a313b733a383a225265676973746572223b693a323b733a393a22466f72676f74507764223b693a333b733a383a224163746976617465223b693a343b733a353a226367707764223b7d);
INSERT INTO mhs_setting VALUES (0x73657276696365,'');
INSERT INTO mhs_setting VALUES (0x73657373696f6e,0x31);
INSERT INTO mhs_setting VALUES (0x736974656e616d65,0xe6ada6e79086e5b7a5e887aae5bcbae7a4bee5b7a5e697b6e7b3bbe7bb9f);
INSERT INTO mhs_setting VALUES (0x73746174636f6465,'');
INSERT INTO mhs_setting VALUES (0x74656d706c617465,0x6d6574726f6e6963);
INSERT INTO mhs_setting VALUES (0x74696d65666f726d6174,0x483a69);
INSERT INTO mhs_setting VALUES (0x74696d656f6666736574,0x38);
INSERT INTO mhs_setting VALUES (0x746f73,0xe7bd91e7ab99e69c8de58aa1e69da1e6acbee58685e5aeb9efbc8ce694afe68c81204d61726b646f776e20e8afade6b395);

INSERT INTO mhs_users_profile_setting VALUES (0x7265616c6e616d65,'1','0','0',0xe79c9fe5ae9ee5a793e5908d,'','0','0','0','0','0','0','1',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x67656e646572,'1','0','0',0xe680a7e588ab,'','0','0','0','0','0','0','1',0x73656c656374,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x626972746879656172,'1','0','0',0xe587bae7949fe5b9b4e4bbbd,'','0','0','0','0','0','0','1',0x73656c656374,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x62697274686d6f6e7468,'1','0','0',0xe587bae7949fe69c88e4bbbd,'','0','0','0','0','0','0','0',0x73656c656374,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6269727468646179,'1','0','0',0xe7949fe697a5,'','0','0','0','0','0','0','0',0x73656c656374,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x636f6e7374656c6c6174696f6e,'1','1','0',0xe6989fe5baa7,0xe6989fe5baa728e6a0b9e68daee7949fe697a5e887aae58aa8e8aea1e7ae9729,'0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x7a6f64696163,'1','1','0',0xe7949fe88296,0xe7949fe8829628e6a0b9e68daee7949fe697a5e887aae58aa8e8aea1e7ae9729,'0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x74656c6570686f6e65,'1','1','0',0xe59bbae5ae9ae794b5e8af9d,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6d6f62696c65,'1','1','0',0xe6898be69cba,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x69646361726474797065,'1','1','0',0xe8af81e4bbb6e7b1bbe59e8b,0xe8baabe4bbbde8af8120e68aa4e785a720e9a9bee9a9b6e8af81e7ad89,'0','0','0','0','0','0','0',0x73656c656374,'0',0xe8baabe4bbbde8af810ae68aa4e785a70ae9a9bee9a9b6e8af81,'');
INSERT INTO mhs_users_profile_setting VALUES (0x696463617264,'1','1','0',0xe8af81e4bbb6e58fb7,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x61646472657373,'1','1','0',0xe982aee5af84e59cb0e59d80,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x7a6970636f6465,'1','1','0',0xe982aee7bc96,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6e6174696f6e616c697479,'0','0','0',0xe59bbde7b18d,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x626972746870726f76696e6365,'1','0','0',0xe587bae7949fe79c81e4bbbd,'','0','0','0','0','0','0','0',0x73656c656374,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x626972746863697479,'1','0','0',0xe587bae7949fe59cb0,'','0','0','0','0','0','0','0',0x73656c656374,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x626972746864697374,'1','0','0',0xe587bae7949fe58ebf,0xe587bae7949fe8a18ce694bfe58cba2fe58ebf,'0','0','0','0','0','0','0',0x73656c656374,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6269727468636f6d6d756e697479,'1','0','0',0xe587bae7949fe5b08fe58cba,'','0','0','0','0','0','0','0',0x73656c656374,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x72657369646570726f76696e6365,'1','0','0',0xe5b185e4bd8fe79c81e4bbbd,'','0','0','0','0','0','0','0',0x73656c656374,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x72657369646563697479,'1','0','0',0xe5b185e4bd8fe59cb0,'','0','0','0','0','0','0','0',0x73656c656374,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x72657369646564697374,'1','0','0',0xe5b185e4bd8fe58ebf,0xe5b185e4bd8fe8a18ce694bfe58cba2fe58ebf,'0','0','0','0','0','0','0',0x73656c656374,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x726573696465636f6d6d756e697479,'1','0','0',0xe5b185e4bd8fe5b08fe58cba,'','0','0','0','0','0','0','0',0x73656c656374,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x7265736964657375697465,'0','0','0',0xe688bfe997b4,0xe5b08fe58cbae38081e58699e5ad97e6a5bce997a8e7898ce58fb7,'0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x67726164756174657363686f6f6c,'1','0','0',0xe6af95e4b89ae5ada6e6a0a1,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x656475636174696f6e,'1','0','0',0xe5ada6e58e86,'','0','0','0','0','0','0','0',0x73656c656374,'0',0xe58d9ae5a3ab0ae7a195e5a3ab0ae69cace7a7910ae4b893e7a7910ae4b8ade5ada60ae5b08fe5ada60ae585b6e5ae83,'');
INSERT INTO mhs_users_profile_setting VALUES (0x636f6d70616e79,'1','0','0',0xe585ace58fb8,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6f636375706174696f6e,'1','0','0',0xe8818ce4b89a,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x706f736974696f6e,'1','0','0',0xe8818ce4bd8d,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x726576656e7565,'1','1','0',0xe5b9b4e694b6e585a5,0xe58d95e4bd8d20e58583,'0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x616666656374697665737461747573,'1','1','0',0xe68385e6849fe78ab6e68081,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6c6f6f6b696e67666f72,'1','0','0',0xe4baa4e58f8be79baee79a84,0xe5b88ce69c9be59ca8e7bd91e7ab99e689bee588b0e4bb80e4b988e6a0b7e79a84e69c8be58f8b,'0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x626c6f6f6474797065,'1','1','0',0xe8a180e59e8b,'','0','0','0','0','0','0','0',0x73656c656374,'0',0x410a420a41420a4f0ae585b6e5ae83,'');
INSERT INTO mhs_users_profile_setting VALUES (0x686569676874,'0','1','0',0xe8baabe9ab98,0xe58d95e4bd8d20636d,'0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x776569676874,'0','1','0',0xe4bd93e9878d,0xe58d95e4bd8d206b67,'0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x616c69706179,'1','1','0',0xe694afe4bb98e5ae9d,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x696371,'0','1','0',0x494351,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x7171,'1','1','0',0x5151,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x7961686f6f,'0','1','0',0x5941484f4fe5b890e58fb7,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6d736e,'1','1','0',0x4d534e,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x74616f62616f,'1','1','0',0xe998bfe9878ce697bae697ba,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x73697465,'1','0','0',0xe4b8aae4babae4b8bbe9a1b5,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x62696f,'1','1','0',0xe887aae68891e4bb8be7bb8d,'','0','0','0','0','0','0','0',0x7465787461726561,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x696e746572657374,'1','0','0',0xe585b4e8b6a3e788b1e5a5bd,'','0','0','0','0','0','0','0',0x7465787461726561,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6669656c6431,'0','1','0',0xe887aae5ae9ae4b989e5ad97e6aeb531,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6669656c6432,'0','1','0',0xe887aae5ae9ae4b989e5ad97e6aeb532,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6669656c6433,'0','1','0',0xe887aae5ae9ae4b989e5ad97e6aeb533,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6669656c6434,'0','1','0',0xe887aae5ae9ae4b989e5ad97e6aeb534,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6669656c6435,'0','1','0',0xe887aae5ae9ae4b989e5ad97e6aeb535,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6669656c6436,'0','1','0',0xe887aae5ae9ae4b989e5ad97e6aeb536,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6669656c6437,'0','1','0',0xe887aae5ae9ae4b989e5ad97e6aeb537,'','0','0','0','0','0','0','0',0x74657874,'0','','');
INSERT INTO mhs_users_profile_setting VALUES (0x6669656c6438,'0','1','0',0xe887aae5ae9ae4b989e5ad97e6aeb538,'','0','0','0','0','0','0','0',0x74657874,'0','','');
