<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>系统发生错误<?php echo isset($_G['setting']['sitename']) ? ' - '.$_G['setting']['sitename'] : '' ?></title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
<style>
*{
	transition:all .2s ease;
	-webkit-transition:all .2s ease;
	-moz-transition:all .2s ease;
	-o-transition:all .2s ease;
	-ms-transition:all .2s ease;
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
.message,#trace{
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
<?php if(isset($e['file'])) {?>
<p><strong>错误位置:</strong>　FILE: <span class="red"><?php echo $e['file'] ;?></span>　LINE: <span class="red"><?php echo $e['line'];?></span></p>
<?php }?>
<p class="title">[ 错误信息 ]</p>
<p class="message"><?php echo $e['message'];?></p>
<?php if(isset($e['trace'])) {?>
<p class="title">[ TRACE ]</p>
<p id="trace">
<?php echo nl2br($e['trace']);?>
</p>
<?php }?>
</div>
</div>
</body>
</html>