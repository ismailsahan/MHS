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

function hitokoto() {
	$.getJSON("http://api.hitokoto.us/rand?encode=jsc&fun=?", function(data){
		$(".hitokoto").html('<a href="http://hitokoto.us/view/'+data.id+'" title="分类 '+data.catname+(data.source ? '\n出自 '+data.source : '')+'\n喜欢 '+data.like+'\n投稿 '+data.author+' @ '+data.date+'" target="_blank">'+data.hitokoto+'</a>').find('a').tooltip();
	});
}

hitokoto.show = function() {
	if($(".hitokoto").size() == 0) return $(window).unbind('scroll', hitokoto.show);
	if(!(($(window).scrollTop()>($(".hitokoto").offset().top+$(".hitokoto").outerHeight()))||(($(window).scrollTop()+$(window).height())<$(".hitokoto").offset().top))) {
		hitokoto();//setTimeout(hitokoto.alternative, 1000);
		$(window).unbind('scroll', hitokoto.show);
	}
}

hitokoto.alternative = function() {
	$.getJSON("hitokoto.php?fun=?", function(data){
		$(".hitokoto").html('<a href="http://hitokoto.us/view/'+data.id+'" title="分类 '+data.catname+(data.source ? '\n出自 '+data.source : '')+'\n喜欢 '+data.like+'\n投稿 '+data.author+' @ '+data.date+'" target="_blank">'+data.hitokoto+'</a>').find('a').tooltip();
	});
}

$(window).bind("scroll", hitokoto.show);
hitokoto.show();

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