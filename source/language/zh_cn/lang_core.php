<?php

$lang = array(
	'date' => array(
		'before' => '前',
		'day' => '天',
		'yday' => '昨天',
		'byday' => '前天',
		'hour' => '小时',
		'half' => '半',
		'min' => '分钟',
		'sec' => '秒',
		'now' => '刚刚',
	),
	'yes' => '是',
	'no' => '否',
	'weeks' => array(
		1 => '周一',
		2 => '周二',
		3 => '周三',
		4 => '周四',
		5 => '周五',
		6 => '周六',
		7 => '周日',
	),
	'dot' => '、',

	'seccode_image_tips' => '输入下图中的字符<br />单击图片可以更换验证码<br />',
	'seccode_image_ani_tips' => '请输入下面动画图片中的字符<br />单击图片可以更换验证码<br />',
	'seccode_swf_tips' => '输入下图中的字符<br /><a href="javascript:;" onmouseenter="$.fn.seccode.list[\'{hash}\']=true" onclick="$(\'#{id}\').focus().select();$(\'#{id}\').seccodeHTML(1)">单击这里可以更换验证码</a><br />',
	'seccode_swf_ani_tips' => '请输入下面动画图片中的字符<br /><a href="javascript:;" onmouseenter="$.fn.seccode.list[\'{hash}\']=true" onclick="$(\'#{id}\').focus().select();$(\'#{id}\').seccodeHTML(1)">单击这里可以更换验证码</a><br />',
	'seccode_sound_tips' => '输入您听到的字符<br /><a href="javascript:;" onmouseenter="$.fn.seccode.list[\'{hash}\']=true" onclick="$(\'#{id}\').focus().select();$(\'#{id}\').seccodeHTML(1)">单击这里可以更换验证码</a><br />',
	'secqaa_tips' => '输入下面问题的答案<br />',
	'seccode' => '验证码',
	'seccode_update' => '换一个',
	'seccode_player' => '<span style="padding:2px" onclick="$.fn.seccode.list[\'{hash}\']=true"><img border="0" style="vertical-align:middle" src="static/seccode/seccodeplayer.gif" /> <a href="javascript:;" onclick="$(\'#{id}\').focus().select();$(\'[name=\\\'seccodeplayer_{hash}\\\']\')[0].SetVariable(\'isPlay\', 1)">播放验证码</a></span>',
	'secqaa' => '验证问答',
);