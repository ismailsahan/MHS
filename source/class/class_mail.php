<?php

!defined('IN_APP_FRAMEWORK') && exit('Access Denied');

class Mail {

	private static $mail;
	private static $sitename;
	private static $template;

	public static function init($keepalive=false){
		static $inited = false;
		if($inited) return;

		global $_G;
		require_once libfile('vendor/PHPMailer/PHPMailerAutoload');

		$setting = &$_G['setting']['mail'];
		self::$sitename = &$_G['setting']['sitename'];
		self::$template = &$setting['template'];

		self::$mail = new PHPMailer(true);
		self::$mail->setLanguage('zh_cn');
		switch($setting['type']){
			case 'POP3':
				POP3::popBeforeSmtp($setting['pop3host'], $setting['pop3port'], 60, $setting['username'], $setting['password']);
			case 'SMTP':
				self::$mail->isSMTP();
				self::$mail->SMTPDebug  = 0;														// 调试模式 关
				self::$mail->CharSet    = 'utf-8';													// 字符集编码
				self::$mail->Host       = $setting['smtphost'];										// SMTP主机
				self::$mail->Port       = $setting['smtpport'];										// SMTP端口
				self::$mail->SMTPSecure = $setting['smtpsecure'] ? $setting['smtpsecure'] : 'none';	// 安全连接
				self::$mail->SMTPAuth   = $setting['smtpauth'] ? true : false;						// 需要验证
				self::$mail->Username   = $setting['username'];										// 用户名
				self::$mail->Password   = $setting['password'];										// 密码
				self::$mail->SMTPKeepAlive = $keepalive ? true : false;								// 保持连接
				break;
			case 'MAIL':
				self::$mail->isMail();
				break;
			case 'QMAIL':
				self::$mail->isQmail();
				break;
			case 'SENDMAIL':
				self::$mail->isSendmail();
				break;
		}
		self::$mail->From     = $setting['from'];
		self::$mail->FromName = $setting['fromname'];
		self::$mail->AltBody  = '要想正确查看此邮件，请务必在支持HTML的客户端中浏览';
		self::$mail->addReplyTo($setting['from'], $setting['fromname']);

		$inited = true;
	}

	public static function addAddress($email, $name=null) {
		if(is_array($email)) {
			foreach($email as $val) {
				if(is_array($val)) {
					self::$mail->addAddress($val['email'], $val['name']);
				} else {
					self::$mail->addAddress($val);
				}
			}
			return;
		}
		return self::$mail->addAddress($email, $name);
	}

	public static function setContent($content, $subject='') {
		self::$mail->Subject = empty($subject) ? self::$sitename : lang('template', $subject).' - '.self::$sitename;
		self::$mail->msgHTML($content, APP_FRAMEWORK_ROOT, true);
	}

	public static function setMsg($msg, $subject='') {
		global $template;
		static $attachmentAdded = false;
		$template->assign('emailmsg', $msg, true);
		if(!$attachmentAdded) {
			//self::$mail->addAttachment('static/images/logo.png', 'logo.png');
			$attachmentAdded = true;
		}
		self::setContent($template->fetch(self::$template), $subject);
	}

	public static function send() {
		try {
			self::$mail->send();
			self::$mail->clearAddresses();
			self::$mail->clearAttachments();
		} catch (phpmailerException $e) {
			return lang('error', 'send_mail_failed', array('msg'=>$e->errorMessage()));
		}
		return null;
	}

	public static function close() {
		self::$mail->smtpClose();
	}

}
