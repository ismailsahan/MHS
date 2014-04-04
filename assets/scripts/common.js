$(document).ready(function() {
	if(typeof $.validator != "undefined") $.extend($.validator.messages, {
		required: "这一项不能为空！",
		remote: "请修正该字段",
		email: "请输入有效的电子邮箱(Email)地址",
		url: "请输入合法的网址",
		date: "请输入合法的日期",
		dateISO: "请输入合法的日期 (ISO)",
		number: "请输入合法的数字",
		digits: "只能输入整数",
		creditcard: "请输入合法的信用卡号",
		equalTo: "请再次输入相同的数据",
		maxlength: $.validator.format("请输入一个长度最多是 {0} 的字符串"),
		minlength: $.validator.format("请输入一个长度最少是 {0} 的字符串"),
		rangelength: $.validator.format("请输入一个长度介于 {0} 和 {1} 之间的字符串"),
		range: $.validator.format("请输入一个介于 {0} 和 {1} 之间的值"),
		max: $.validator.format("请输入一个最大为 {0} 的数"),
		min: $.validator.format("请输入一个最小为 {0} 的数"),
		maxWords: $.validator.format("Please enter {0} words or less."),
		minWords: $.validator.format("Please enter at least {0} words."),
		rangeWords: $.validator.format("Please enter between {0} and {1} words."),
		letterswithbasicpunc: "Letters or punctuation only please",
		alphanumeric: "Letters, numbers, and underscores only please",
		lettersonly: "Letters only please",
		nowhitespace: "No white space please",
		ziprange: "Your ZIP-code must be in the range 902xx-xxxx to 905-xx-xxxx",
		zipcodeUS: "The specified US ZIP Code is invalid",
		integer: "A positive or negative non-decimal number please",
		vinUS: "The specified vehicle identification number (VIN) is invalid.",
		dateITA: "Please enter a correct date",
		iban: "Please specify a valid IBAN",
		dateNL: "Please enter a correct date",
		phoneNL: "Please specify a valid phone number.",
		mobileNL: "Please specify a valid mobile number",
		postalcodeNL: "Please specify a valid postal code",
		bankaccountNL: "Please specify a valid bank account number",
		giroaccountNL: "Please specify a valid giro account number",
		bankorgiroaccountNL: "Please specify a valid bank or giro account number",
		time: "Please enter a valid time, between 00:00 and 23:59",
		time12h: "Please enter a valid time in 12-hour am/pm format",
		phoneUS: "Please specify a valid phone number",
		phoneUK: "Please specify a valid phone number",
		mobileUK: "Please specify a valid mobile number",
		phonesUK: "Please specify a valid uk phone number",
		postcodeUK: "Please specify a valid UK postcode",
		strippedminlength: $.validator.format("Please enter at least {0} characters"),
		email2: "Please enter a valid email address.",
		url2: "Please enter a valid URL.",
		creditcardtypes: "Please enter a valid credit card number.",
		ipv4: "请输入一个合法的 IPv4 地址",
		ipv6: "请输入一个合法的 IPv6 地址",
		pattern: "格式不正确",
		require_from_group: $.format("Please fill at least {0} of these fields."),
		skip_or_fill_minimum: $.format("Please either skip these fields or fill at least {0} of them."),
		accept: $.format("Please enter a value with a valid mimetype."),
		extension: $.format("Please enter a value with a valid extension.")
	});
});

var hitokoto = {};

hitokoto.check = function() {
	if($(".hitokoto").size() == 0) return $(window).unbind('scroll', hitokoto.check);
	if(!(($(window).scrollTop()>($(".hitokoto").offset().top+$(".hitokoto").outerHeight()))||(($(window).scrollTop()+$(window).height())<$(".hitokoto").offset().top))) {
		hitokoto.show();
		//hitokoto.alternative();
		$(window).unbind('scroll', hitokoto.check);
	}
}

hitokoto.handler = function(data) {
	$(".hitokoto").html('<a href="http://hitokoto.us/view/'+data.id+'.html" title="分类 '+data.catname+(data.source ? '\n出自 '+data.source : '')+'\n喜欢 '+data.like+'\n投稿 '+data.author+' @ '+data.date+'" target="_blank">'+data.hitokoto+'</a>').find('a').tooltip();
}

hitokoto.show = function() {
	$.getJSON("http://api.hitokoto.us/rand?encode=jsc&fun=?", function(data){
		hitokoto.handler(data);
	});
}

hitokoto.alternative = function() {
	$.getJSON("hitokoto.php?fun=?", function(data){
		hitokoto.handler(data);
	});
}

$(window).bind("scroll", hitokoto.check);
hitokoto.check();

try {
	if (window.console && window.console.log) {
		console.log("\n%c", 'font-size:0;margin-top:20px;line-height:17px;padding-top:17px;padding-left:130px;background-color:#3d3d3d;background-image:url("data:image/png;base64,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");background-repeat:no-repeat;');
		console.log("咦！这么巧你也喜欢研究html源代码!\n又这么巧我们急需像你这样的专业前端人士!\n%c来自强社吧，和我们一起改变自强社！", "color:#F00");
		//console.log("\u54a6\uff01\u8fd9\u4e48\u5de7\u4f60\u4e5f\u559c\u6b22\u7814\u7a76html\u6e90\u4ee3\u7801!\n\u53c8\u8fd9\u4e48\u5de7\u6211\u4eec\u6025\u9700\u50cf\u4f60\u8fd9\u6837\u7684\u4e13\u4e1a\u524d\u7aef\u4eba\u58eb!\n\u6765\u767e\u5ea6\u4e91\u5427\uff0c\u548c\u6211\u4eec\u4e00\u8d77\u6539\u53d8\u767e\u5ea6\u4e91\uff01\u6539\u53d8\u4e91\u5b58\u50a8\uff01\n\u804c\u4f4d\u4ecb\u7ecd\uff1ahttp://dwz.cn/eMWdq");
		//console.log("\r\u8bf7\u5c06\u7b80\u5386\u53d1\u9001\u81f3 %c spacehr@baidu.com\uff08 \u90ae\u4ef6\u6807\u9898\u8bf7\u4ee5\u201c\u59d3\u540d-\u5e94\u8058XX\u804c\u4f4d-\u6765\u81eaconsole\u201d\u547d\u540d\uff09", "color:red");
	}
} catch (e) {}

function addreferer(obj, prop) {
	var url = prop ? $(obj).attr(prop) : obj;
	if(url) {
		var i = url.indexOf("referer=");
		if(i > -1) {
			var t = $("form").prop("action");
			$("form").prop("action", t + (t.indexOf("?")>-1 ? "&" : "?") + window.location.search.substr(i));
		}
	}
}

function stripslashes(str) {
	return (str + '').replace(/\\(.?)/g, function (s, n1) {
		switch (n1) {
			case '\\': return '\\';
			case '0' : return '\u0000';
			case ''  : return '';
			default  : return n1;
		}
	});
}

function nl2br(str, is_xhtml) {
	var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>';
	return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

Date.prototype.format = function(format) {
	var date = {
		"M+": this.getMonth() + 1,
		"d+": this.getDate(),
		"h+": this.getHours(),
		"m+": this.getMinutes(),
		"s+": this.getSeconds(),
		"q+": Math.floor((this.getMonth() + 3) / 3),
		"S+": this.getMilliseconds()
	};
	if (/(y+)/i.test(format)) {
		format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
	}
	for (var k in date) {
		if (new RegExp("(" + k + ")").test(format)) {
			format = format.replace(RegExp.$1, RegExp.$1.length == 1
				? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
		}
	}
	return format;
}

function announcement() {
	function $(id) {
		return document.getElementById(id);
	}
	var ann = {};
	ann.anndelay = 3000;
	ann.annst = 0;
	ann.annstop = 0;
	ann.annrowcount = 0;
	ann.anncount = 0;
	ann.annlis = $('annbody').getElementsByTagName("li");
	ann.annrows = [];
	ann.announcementScroll = function () {
		if(this.annstop) {
			this.annst = setTimeout(function () {
				ann.announcementScroll();
			}, this.anndelay);
			return;
		}
		if(!this.annst) {
			var lasttop = -1;
			for(i = 0;i < this.annlis.length;i++) {
				if(lasttop != this.annlis[i].offsetTop) {
					if(lasttop == -1) lasttop = 0;
					this.annrows[this.annrowcount] = this.annlis[i].offsetTop - lasttop;this.annrowcount++;
				}
				lasttop = this.annlis[i].offsetTop;
			}
			if(this.annrows.length == 1) {
				$('ann').onmouseover = $('ann').onmouseout = null;
			} else {
				this.annrows[this.annrowcount] = this.annrows[1];
				$('annbodylis').innerHTML += $('annbodylis').innerHTML;
				this.annst = setTimeout(function () {
					ann.announcementScroll();
				}, this.anndelay);
				$('ann').onmouseover = function () {
					ann.annstop = 1;
				};
				$('ann').onmouseout = function () {
					ann.annstop = 0;
				};
			}
			this.annrowcount = 1;
			return;
		}
		if(this.annrowcount >= this.annrows.length) {
			$('annbody').scrollTop = 0;
			this.annrowcount = 1;
			this.annst = setTimeout(function () {
				ann.announcementScroll();
			}, this.anndelay);
		} else {
			this.anncount = 0;
			this.announcementScrollnext(this.annrows[this.annrowcount]);
		}
	};
	ann.announcementScrollnext = function (time) {
		$('annbody').scrollTop++;
		this.anncount++;
		if(this.anncount != time) {
			this.annst = setTimeout(function () {
				ann.announcementScrollnext(time);
			}, 10);
		} else {
			this.annrowcount++;
			this.annst = setTimeout(function () {
				ann.announcementScroll();
			}, this.anndelay);
		}
	};
	ann.announcementScroll();
}

function AC_FL_RunContent() {
	var str = '';
	var ret = AC_GetArgs(arguments, "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000", "application/x-shockwave-flash");
	if($.browser.msie && !$.browser.opera) {
		str += '<object ';
		for (var i in ret.objAttrs) {
			str += i + '="' + ret.objAttrs[i] + '" ';
		}
		str += '>';
		for (var i in ret.params) {
			str += '<param name="' + i + '" value="' + ret.params[i] + '" /> ';
		}
		str += '</object>';
	} else {
		str += '<embed ';
		for (var i in ret.embedAttrs) {
			str += i + '="' + ret.embedAttrs[i] + '" ';
		}
		str += '></embed>';
	}
	return str;
}

function AC_GetArgs(args, classid, mimeType) {
	var ret = new Object();
	ret.embedAttrs = new Object();
	ret.params = new Object();
	ret.objAttrs = new Object();
	for (var i = 0; i < args.length; i = i + 2){
		var currArg = args[i].toLowerCase();
		switch (currArg){
			case "classid":break;
			case "pluginspage":ret.embedAttrs[args[i]] = 'http://www.macromedia.com/go/getflashplayer';break;
			case "src":ret.embedAttrs[args[i]] = args[i+1];ret.params["movie"] = args[i+1];break;
			case "codebase":ret.objAttrs[args[i]] = 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0';break;
			case "onafterupdate":case "onbeforeupdate":case "onblur":case "oncellchange":case "onclick":case "ondblclick":case "ondrag":case "ondragend":
			case "ondragenter":case "ondragleave":case "ondragover":case "ondrop":case "onfinish":case "onfocus":case "onhelp":case "onmousedown":
			case "onmouseup":case "onmouseover":case "onmousemove":case "onmouseout":case "onkeypress":case "onkeydown":case "onkeyup":case "onload":
			case "onlosecapture":case "onpropertychange":case "onreadystatechange":case "onrowsdelete":case "onrowenter":case "onrowexit":case "onrowsinserted":case "onstart":
			case "onscroll":case "onbeforeeditfocus":case "onactivate":case "onbeforedeactivate":case "ondeactivate":case "type":
			case "id":ret.objAttrs[args[i]] = args[i+1];break;
			case "width":case "height":case "align":case "vspace": case "hspace":case "class":case "title":case "accesskey":case "name":
			case "tabindex":ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i+1];break;
			default:ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i+1];
		}
	}
	ret.objAttrs["classid"] = classid;
	if(mimeType) {
		ret.embedAttrs["type"] = mimeType;
	}
	return ret;
}