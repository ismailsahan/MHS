<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>系统发生错误<?php echo isset($_G['setting']['sitename']) ? ' - '.$_G['setting']['sitename'] : '' ?></title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<style type="text/css">
*{
	transition:color .2s ease,background .2s ease,border .2s ease;
	-webkit-transition:color .2s ease,background .2s ease,border .2s ease;
	-moz-transition:color .2s ease,background .2s ease,border .2s ease;
	-o-transition:color .2s ease,background .2s ease,border .2s ease;
	-ms-transition:color .2s ease,background .2s ease,border .2s ease;
}
html{
	font-family: '\5FAE\8F6F\96C5\9ED1','Microsoft Yahei', Verdana, arial, sans-serif;
	font-size:14px;
}
a{text-decoration:none;color:#174B73;}
a:hover{ text-decoration:none;color:#FF6600;}
h2{
	border-bottom:1px solid #DDD;
	padding:8px 0;
	font-size:25px;
}
.title{
	margin:4px 0;
	color:#F60;
	font-weight:bold;
}
.message, #trace{
	padding:1em;
	border:solid 1px #000;
	margin:10px 0;
	background:#FFD;
	line-height:150%;
}
.message{
	background:#FFD;
	color:#2E2E2E;
	border:1px solid #E0E0E0;
}
#trace{
	background:#E7F7FF;
	border:1px solid #E0E0E0;
	color:#535353;
}
#trace ol {
	margin: 0 0 0 -1em;
}
.notice{
	padding:10px;
	margin:5px;
	color:#666;
	background:#FCFCFC;
	border:1px solid #E0E0E0;
}
.red{
	color:red;
	font-weight:bold;
}
</style>
</head>
<body>
	<div class="notice">
		<h2>系统发生错误 </h2>
		<div>您可以选择 [ <a href="<?php echo($_SERVER['PHP_SELF'])?>">重试</a> ] [ <a href="javascript:history.back()">返回</a> ] 或者 [ <a href="<?php global $_G; echo $_G['siteroot']; ?>">回到首页</a> ]</div>
<?php if(!empty($last)) { ?>
		<p><strong>错误位置:</strong>　FILE: <span class="red"><?php echo $last['file'] ;?></span>　LINE: <span class="red"><?php echo $last['line'];?></span></p>
<?php }?>
		<p class="title">[ 错误信息 ]</p>
		<div class="message"><?php echo $errormsg;?></div>
<?php if(!empty($phpmsg)) {?>
		<p class="title">[ TRACE ]</p>
		<div id="trace">
<?php if(is_array($phpmsg)){
	echo '<ol>';
	foreach($phpmsg as $trace) {
		echo "<li>{$trace['function']}<br />{$trace['file']}:{$trace['line']}</li>\n";
	}
	echo '</ol>';
} else {
	echo $phpmsg;
} ?>
		</div>
<?php }?>
		<p><a href="<?php echo $_G['siteroot']; ?>"><?php echo $_SERVER['HTTP_HOST']; ?></a> 已经将此出错信息详细记录, 由此给您带来的访问不便我们深感歉意</p>
	</div>
</body>
</html>