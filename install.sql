SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `conn_failedlogin` (
  `ip` char(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `username` char(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `count` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip`,`username`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `conn_setting` (
  `skey` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `svalue` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `conn_setting` (`skey`, `svalue`) VALUES
('closed', '0'),
('closedreason', ''),
('copyright', '武理工计科自强社服务队'),
('dateconvert', '1'),
('dateformat', 'Y-n-j'),
('debug', '1'),
('failedlogin', 'a:2:{s:5:"count";i:5;s:4:"time";i:1;}'),
('logintip', 'a:2:{s:5:"count";i:5;s:4:"time";i:1;}'),
('logopath', 'source/template/metronic/assets/img/logo-big.png'),
('reg', '1'),
('seccodedata', 'a:14:{s:4:"type";i:0;s:5:"width";i:80;s:6:"height";i:26;s:10:"background";i:1;s:10:"adulterate";i:1;s:3:"ttf";i:1;s:5:"angle";i:1;s:7:"warping";i:0;s:7:"scatter";i:0;s:5:"color";i:1;s:4:"size";i:1;s:6:"shadow";i:1;s:8:"animator";i:0;s:6:"length";i:4;}'),
('seccodestatus', 'a:3:{i:0;s:5:"Login";i:1;s:8:"Register";i:2;s:9:"ForgotPwd";}'),
('sitename', '武理工计科自强社通讯录登记系统'),
('template', 'metronic'),
('timeformat', 'H:i'),
('timeoffset', '8');

CREATE TABLE IF NOT EXISTS `conn_users` (
  `uid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `email` char(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `username` char(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` char(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `emailstatus` tinyint(1) NOT NULL DEFAULT '0',
  `avatarstatus` tinyint(1) NOT NULL DEFAULT '0',
  `videophotostatus` tinyint(1) NOT NULL DEFAULT '0',
  `adminid` tinyint(1) NOT NULL DEFAULT '0',
  `groupid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `groupexpiry` int(10) unsigned NOT NULL DEFAULT '0',
  `extgroupids` char(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `regdate` int(10) unsigned NOT NULL DEFAULT '0',
  `credits` int(10) NOT NULL DEFAULT '0',
  `notifysound` tinyint(1) NOT NULL DEFAULT '0',
  `timeoffset` char(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `newpm` smallint(6) unsigned NOT NULL DEFAULT '0',
  `newprompt` smallint(6) unsigned NOT NULL DEFAULT '0',
  `accessmasks` tinyint(1) NOT NULL DEFAULT '0',
  `allowadmincp` tinyint(1) NOT NULL DEFAULT '0',
  `onlyacceptfriendpm` tinyint(1) NOT NULL DEFAULT '0',
  `conisbind` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `groupid` (`groupid`),
  KEY `conisbind` (`conisbind`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

INSERT INTO `conn_users` (`uid`, `email`, `username`, `password`, `status`, `emailstatus`, `avatarstatus`, `videophotostatus`, `adminid`, `groupid`, `groupexpiry`, `extgroupids`, `regdate`, `credits`, `notifysound`, `timeoffset`, `newpm`, `newprompt`, `accessmasks`, `allowadmincp`, `onlyacceptfriendpm`, `conisbind`) VALUES
(1, 'demo@demo.com', 'demo', 'e10adc3949ba59abbe56e057f20f883e', 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, '', 0, 0, 0, 0, 0, 0);
