<?php if(!$this || is_object($this) === false){exit('Hacking!');} extract($this->templates_assign);?><?php ($phpnewtpl = $this->display('header')) && include($phpnewtpl);?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="cache/static_7ef5c25f3d51145b903903d35b7c885a.css" />
<!-- END PAGE LEVEL SCRIPTS -->
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="login">
	<!-- BEGIN LOGO -->
	<div class="logo"><img src="<?php echo $_G['setting']['logopath']?>" alt="<?php echo $_G['setting']['sitename']?>" /></div>
	<!-- END LOGO -->
	<!-- BEGIN LOGIN -->
	<div class="content">
		<!-- BEGIN LOGIN FORM -->
		<form class="form-vertical login-form" action="<?php echo $_G['basefilename']?>?action=logging" method="post">
			<?php echo $LoginToken?>
			<h3 class="form-title">请登录</h3>
			<div class="alert alert-block alert-error fade-in<?php if(!$errmsg){ ?> hide<?php } ?>">
				<button type="button" class="close" data-dismiss="alert"></button>
				<h4 class="alert-heading"><strong>登录失败！</strong></h4><span><?php echo $errmsg?></span>
			</div>
			<?php if(!empty($logintip)){ ?>
			<div class="alert alert-block alert-info fade-in">
				<button type="button" class="close" data-dismiss="alert"></button>
				<h4 class="alert-heading"><strong>登录小贴士</strong></h4><span><?php echo $logintip?></span>
			</div>
			<?php } ?>
			<div class="control-group">
				<!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
				<label class="control-label visible-ie8 visible-ie9">用户名</label>
				<div class="controls">
					<div class="input-icon left">
						<i class="icon-user"></i>
						<input class="m-wrap placeholder-no-fix inputtext" type="text" autocomplete="off" placeholder="用户名" name="username" value="<?php if(isset($_POST['username'])){ ?><?php echo $_POST['username']?><?php } ?>" required />
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label visible-ie8 visible-ie9">密码</label>
				<div class="controls">
					<div class="input-icon left">
						<i class="icon-lock"></i>
						<input class="m-wrap placeholder-no-fix inputpassword" type="password" autocomplete="off" placeholder="密码" name="password" required />
					</div>
				</div>
			</div>
			<?php if(need_seccode('Login')){ ?>
			<div class="control-group">
				<label class="control-label visible-ie8 visible-ie9">验证码</label>
				<div class="controls">
					<div class="input-icon left verifycode">
						<i class="icon-picture"></i>
						<input class="m-wrap placeholder-no-fix inputtext" type="text" maxlength="4" autocomplete="off" placeholder="验证码" name="verifycode" required />
						<a href="javascript:;" style="right"><img src="<?php echo $_G['basefilename']?>?action=seccode&tag=<?php echo $LoginTokenHash?>&width=80&height=26" width="80" height="26" alt="单击此处更换验证码" /></a>
					</div>
				</div>
			</div>
			<?php } ?>
			<div class="form-actions">
				<button type="submit" class="btn green pull-right">
					登录 <i class="m-icon-swapright m-icon-white"></i>
				</button>            
			</div>
			<div class="forget-password">
				<h4>忘记密码？</h4>
				<p><a href="javascript:;" id="forget-password">您可以单击这里来重置您的密码</a></p>
			</div>
			<div class="create-account">
				<p>还没有账号？&nbsp;<a href="javascript:;" id="register-btn">单击这里注册一个账号</a></p>
			</div>
		</form>
		<!-- END LOGIN FORM -->        
		<!-- BEGIN FORGOT PASSWORD FORM -->
		<form class="form-vertical forget-form" action="<?php echo $_G['basefilename']?>?action=logging" method="post">
			<?php echo $ForgotPwdToken?>
			<h3>忘记密码？</h3>
			<p>请填写以下信息</p>
			<div class="control-group">
				<label class="control-label visible-ie8 visible-ie9">用户名</label>
				<div class="controls">
					<div class="input-icon left">
						<i class="icon-user"></i>
						<input class="m-wrap placeholder-no-fix inputtext" type="text" autocomplete="off" placeholder="用户名" name="username" value="<?php if(isset($_POST['username'])){ ?><?php echo $_POST['username']?><?php } ?>" required />
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label visible-ie8 visible-ie9">QQ号</label>
				<div class="controls">
					<div class="input-icon left">
						<i class="icon-user"></i>
						<input class="m-wrap placeholder-no-fix inputtext" type="text" maxlength="10" autocomplete="off" placeholder="QQ号" name="qq" required />
					</div>
				</div>
			</div>
			<?php if(need_seccode('ForgotPwd')){ ?>
			<div class="control-group">
				<label class="control-label visible-ie8 visible-ie9">验证码</label>
				<div class="controls">
					<div class="input-icon left verifycode">
						<i class="icon-picture"></i>
						<input class="m-wrap placeholder-no-fix inputtext" type="text" maxlength="4" autocomplete="off" placeholder="验证码" name="verifycode" required />
						<a href="javascript:;" style="right"><img src="<?php echo $_G['basefilename']?>?action=seccode&tag=<?php echo $ForgotPwdTokenHash?>&width=80&height=26" width="80" height="26" alt="单击此处更换验证码" /></a>
					</div>
				</div>
			</div>
			<?php } ?>
			<div class="form-actions">
				<button type="button" id="back-btn" class="btn">
				<i class="m-icon-swapleft"></i> 返回
				</button>
				<button type="submit" class="btn green pull-right">
				提交 <i class="m-icon-swapright m-icon-white"></i>
				</button>            
			</div>
		</form>
		<!-- END FORGOT PASSWORD FORM -->
		<!-- BEGIN REGISTRATION FORM -->
		<form class="form-vertical register-form" action="<?php echo $_G['basefilename']?>?action=logging" method="post">
			<?php echo $RegisterToken?>
			<h3>注册</h3>
			<?php if($_G['setting']['reg']){ ?>
			<div class="control-group">
				<label class="control-label visible-ie8 visible-ie9">用户名</label>
				<div class="controls">
					<div class="input-icon left">
						<i class="icon-user"></i>
						<input class="m-wrap placeholder-no-fix inputtext" type="text" autocomplete="off" placeholder="用户名" name="username" value="<?php if(isset($_POST['username'])){ ?><?php echo $_POST['username']?><?php } ?>" required />
					</div>
				</div>
			</div>
			<div class="control-group">
				<!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
				<label class="control-label visible-ie8 visible-ie9">Email</label>
				<div class="controls">
					<div class="input-icon left">
						<i class="icon-envelope"></i>
						<input class="m-wrap placeholder-no-fix inputtext" type="text" placeholder="Email" name="email"/>
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label visible-ie8 visible-ie9">密码</label>
				<div class="controls">
					<div class="input-icon left">
						<i class="icon-lock"></i>
						<input class="m-wrap placeholder-no-fix" type="password" autocomplete="off" id="register_password" placeholder="密码" name="password" />
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label visible-ie8 visible-ie9">确认密码</label>
				<div class="controls">
					<div class="input-icon left">
						<i class="icon-ok"></i>
						<input class="m-wrap placeholder-no-fix inputpassword" type="password" autocomplete="off" placeholder="确认密码" name="rpassword" />
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label visible-ie8 visible-ie9">QQ号</label>
				<div class="controls">
					<div class="input-icon left">
						<i class="icon-user"></i>
						<input class="m-wrap placeholder-no-fix inputtext" type="text" maxlength="10" autocomplete="off" placeholder="QQ号" name="qq" required />
					</div>
				</div>
			</div>
			<?php if(need_seccode('Register')){ ?>
			<div class="control-group">
				<label class="control-label visible-ie8 visible-ie9">验证码</label>
				<div class="controls">
					<div class="input-icon left verifycode">
						<i class="icon-picture"></i>
						<input class="m-wrap placeholder-no-fix inputtext" type="text" maxlength="4" autocomplete="off" placeholder="验证码" name="verifycode" required />
						<a href="javascript:;" style="right"><img src="<?php echo $_G['basefilename']?>?action=seccode&tag=<?php echo $RegisterTokenHash?>&width=80&height=26" width="80" height="26" alt="单击此处更换验证码" /></a>
					</div>
				</div>
			</div>
			<?php } ?>
			<div class="control-group">
				<div class="controls">
					<label class="checkbox">
					<input class="inputcheckbox" type="checkbox" name="tnc"/> I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
					</label>  
					<div id="register_tnc_error"></div>
				</div>
			</div>
			<?php } else { ?>
			<div class="note note-error" style="margin-top:24px">
				<h4 class="block">当前关闭注册！</h4>
				<p><?php echo $_G['setting']['regclosed']?></p>
			</div>
			<?php } ?>
			<div class="form-actions">
				<button id="register-back-btn" type="button" class="btn">
				<i class="m-icon-swapleft"></i> 返回
				</button>
				<?php if($_G['setting']['reg']){ ?>
				<button type="submit" id="register-submit-btn" class="btn green pull-right">
				注册 <i class="m-icon-swapright m-icon-white"></i>
				</button>
				<?php } ?>
			</div>
		</form>
		<!-- END REGISTRATION FORM -->
	</div>
	<!-- END LOGIN -->
	<!-- BEGIN COPYRIGHT -->
	<div class="copyright">
		<p><script type="text/javascript">document.write((new Date()).getFullYear());</script> &copy; <?php echo $_G['setting']['copyright']?></p>
		<p><?php ($phpnewtpl = $this->display('debuginfo')) && include($phpnewtpl);?></p>
	</div>
	<!-- END COPYRIGHT -->
	<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
	<?php ($phpnewtpl = $this->display('corejs')) && include($phpnewtpl);?>
	<!-- BEGIN PAGE LEVEL PLUGINS -->
	<script src="cache/static_2375cefaa53c4d261d5670b6a6e3a593.js" type="text/javascript"></script>
	<script src="cache/static_4a7d17e45ff700e2afa66751742e8eb3.js" type="text/javascript"></script>
	<script src="cache/static_2b2a962124cda04724294d2555598098.js" type="text/javascript"></script>
	<!-- END PAGE LEVEL PLUGINS -->
	<!-- BEGIN PAGE LEVEL SCRIPTS -->
	<script src="cache/static_fe8f7ad2cd2d70e2f81d7f84a6c139b1.js" type="text/javascript"></script>
	<script src="cache/static_ec1b89791ea5dcecc364b652cc65902a.js" type="text/javascript"></script>
	<!-- END PAGE LEVEL SCRIPTS --> 
	<script type="text/javascript">
		$(document).ready(function() {     
		  App.init();
		  Login.init();
		});
	</script>
	<!-- END JAVASCRIPTS -->
<?php ($phpnewtpl = $this->display('footer')) && include($phpnewtpl);?>