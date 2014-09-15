$(document).ready(function() {
	var e = $("li.dropdown-user ul.dropdown-menu a:has(i.icon-lock)");
	if (e.length > 0) {
		var t = e.prop("href");
		e.prop("href", t + (t.indexOf("?") > -1 ? "&" : "?") + "referer=" + urlencode(window.location.href));
	}

	var badges = [],
		badges_detail = {},
		badges_tips = {
			"profile/pm": "您有 %d 条新短消息",
			"global/info": "全局 - 站点信息 需要更新",
			"members/verifyuser": "您有 %d 个新的用户需要审核",
			"manhour/applylog": "您有 %d 个工时申报记录需要审核",
			"manhour/checklog": "您有 %d 个工时复查记录需要审核",
		};
	$('.page-sidebar li span.badge').each(function() {
	    var idx = $(this).html(),
			type = $(this).prop("class").substr(12);
		badges.push({
			name: "badge[]",
			value: idx
		});
		badges_detail[idx] = {
			"type": type.substr(0, type.indexOf(" ")),
			"icon": $(this).closest("li:has(i.fa)").find("i.fa").prop("class").substr(6)
		};
	});
	if (badges.length) {
		$.get("index.php?action=api&operation=badge", badges, function(data) {
			$('.page-sidebar li span.badge').each(function() {
				var idx = $(this).html();
				if (data[idx] && (data[idx].constructor==Object ? data[idx].num : data[idx]) != "0") $(this).html(data[idx].constructor==Object ? data[idx].num : data[idx]).removeClass("hidden");
				else $(this).remove();
			});
			for (var badge in badges_detail) {
				if (data[badge] && (data[badge].constructor==Object ? data[badge].num : data[badge]) != "0") $("#header_notification_bar .dropdown-menu-list").append('<li><a href="#"><span class="label label-icon label-' + badges_detail[badge].type + '"><i class="fa fa-' + badges_detail[badge].icon + '"></i></span>' + badges_tips[badge].replace("%d", data[badge].constructor==Object ? data[badge].num : data[badge]) + (data[badge].constructor==Object&&data[badge].time ? ' <span class="time"> ' + data[badge].time + '&nbsp;</span>' : '') + '</a></li>');
			}
			var notinum = $("#header_notification_bar .dropdown-menu-list li").size();
			$("#header_notification_bar span:lt(2)").html(notinum);
			if(!notinum) {
				$("#header_notification_bar span:lt(2)").removeClass("badge-default");
				$("#header_notification_bar").hide();
			}
			$('.page-sidebar li:has(span.arrow)').each(function() {
				var num = 0;
				$(this).find("span.badge").each(function() {
					//num += parseInt($(this).html());
					num++;
				});
				if (num) $(this).find("a:has(span.arrow)").append('<span class="badge badge-warning">' + num + '</span>');
			});

			$(document).ready(function() {
				if ($("#header_notification_bar .dropdown-menu-list li").size()) setTimeout(function() {
					$.extend($.gritter.options, {
						position: 'top-left'
					});

					var unique_id = $.gritter.add({
						// (string | mandatory) the heading of the notification
						title: '系统提示',
						// (string | mandatory) the text inside the notification
						text: '你有 ' + $("#header_notification_bar .dropdown-menu-list li").size() + ' 个新提醒！',
						// (string | optional) the image to display on the left
						image1: './assets/global/img/image1.jpg',
						// (bool | optional) if you want it to fade out on its own or just sit there
						sticky: false,
						// (int | optional) the time you want it to be alive for before fading out
						time: 4000,
						// (string | optional) the class name you want to apply to that specific message
						class_name: 'my-sticky-class'
					});

					$.extend($.gritter.options, {
						position: 'top-right'
					});

					$('#header_notification_bar').pulsate({
						color: "#bb3319",
						repeat: 8
					});

				}, 1000);
			});
		}, "json");
	}

	if (typeof $.validator != "undefined") $.extend($.validator.messages, {
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
		require_from_group: $.validator.format("Please fill at least {0} of these fields."),
		skip_or_fill_minimum: $.validator.format("Please either skip these fields or fill at least {0} of them."),
		accept: $.validator.format("Please enter a value with a valid mimetype."),
		extension: $.validator.format("Please enter a value with a valid extension.")
	});

	if (typeof $.fn.dataTable != "undefined") $.extend(true, $.fn.dataTable.defaults.language, {
		"emptyTable":     "表中数据为空",
		"info":           "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
		"infoEmpty":      "没有数据可供显示",
		"infoFiltered":   "(由 _MAX_ 项结果过滤)",
		"infoPostFix":    "",
		"thousands":      ",",
		"lengthMenu":     "显示 _MENU_ 项结果",
		"loadingRecords": "加载中...",
		"processing":     "处理中...",
		"search":         "搜索:",
		"zeroRecords":    "没有匹配结果",
		"paginate": {
			"first":      "首页",
			"last":       "末页",
			"next":       "下页",
			"previous":   "上页"
		},
		"aria": {
			"sortAscending":  ": 以升序排列此列",
			"sortDescending": ": 以降序排列此列"
		}
	});

	if (typeof $.fn.dataTable != "undefined") $.fn.dataTable.ext.order['dom-time'] = function(settings, col) {
		var t= this.api().column(col, {order:'index'}).nodes().map(function(td, i) {
			var t = $(td).data("time");
			return t===undefined ? $(td).html() : t*1;
		});
		return t;
	};
});

$.ajaxSetup({
	contentType: "application/x-www-form-urlencoded; charset=utf-8"
});
/*$(window).ajaxStart(function() {
	if(window.Pace) Pace.restart();
});*/

$(document).ajaxError(function(event, jqxhr, settings, exception) {
	var msg = jqxhr.responseText.substr(0, 1) == "{" ? $.parseJSON(jqxhr.responseText).msg : (jqxhr.responseText.indexOf('<!DOCTYPE') > -1 ? "" : (jqxhr.responseText.indexOf("\n") > -1 ? "<pre>" + jqxhr.responseText + "</pre>" : jqxhr.responseText));
	var alertMethod = typeof modalAlert == "function" ? modalAlert : alert;
	alertMethod(msg ? msg : "向服务器请求数据时发生了错误，请稍候再试");
});

var hitokoto = {};

hitokoto.check = function() {
	if ($(".hitokoto").size() == 0) return $(window).unbind('scroll', hitokoto.check);
	if (!(($(window).scrollTop() > ($(".hitokoto").offset().top + $(".hitokoto").outerHeight())) || (($(window).scrollTop() + $(window).height()) < $(".hitokoto").offset().top))) {
		hitokoto.show();
		//hitokoto.alternative();
		$(window).unbind('scroll', hitokoto.check);
	}
}

hitokoto.handler = function(data) {
	$(".hitokoto").html('<a href="http://hitokoto.us/view/' + data.id + '.html" title="分类 ' + data.catname + (data.source ? '\n出自 ' + data.source : '') + '\n喜欢 ' + data.like + '\n投稿 ' + data.author + ' @ ' + data.date + '" target="_blank">' + data.hitokoto + '</a>').find('a').tooltip();
}

hitokoto.show = function() {
	$.getJSON("http://api.hitokoto.us/rand?encode=jsc&fun=?", function(data) {
		hitokoto.handler(data);
	});
}

hitokoto.alternative = function() {
	$.getJSON("hitokoto.php?fun=?", function(data) {
		hitokoto.handler(data);
	});
}

$(window).bind("scroll", hitokoto.check);
hitokoto.check();

function addreferer(obj, prop) {
	var url = prop ? $(obj).attr(prop) : obj;
	if (url) {
		var i = url.indexOf("referer=");
		if (i > -1) {
			var t = $("form").prop("action");
			$("form").prop("action", t + (t.indexOf("?") > -1 ? "&" : "?") + window.location.search.substr(i));
		}
	}
}

function stripslashes(str) {
	return (str + '').replace(/\\(.?)/g, function(s, n1) {
		switch (n1) {
			case '\\':
				return '\\';
			case '0':
				return '\u0000';
			case '':
				return '';
			default:
				return n1;
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
			format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
		}
	}
	return format;
}

function urlencode(str) {
	str = (str + '').toString();
	return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
}

function modalAlert(msg) {
	if ($("#alert-modal").size() == 0) {
		var html = "";
		html += '<div class="modal fade modal-scroll" id="alert-modal" tabindex="-1" data-backdrop="static" data-keyboard="false">';
		html += '<div class="modal-header">';
		html += '<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>';
		html += '<h4 class="modal-title">系统提示</h4>';
		html += '</div>';
		html += '<div class="modal-body"></div>';
		html += '<div class="modal-footer">';
		html += '<button type="button" class="btn blue" data-dismiss="modal">确定</button>';
		html += '</div>';
		html += '</div>';
		$(".page-content").append(html);
	}
	$("#alert-modal .modal-body").html(msg);
	$("#alert-modal").modal("show");
}

function showann(id) {
	if (!$.fn.modal) {
		return $.getScript("assets/plugins/bootstrap-modal/js/bootstrap-modal.js", function() {
			showann(id);
		});
	}
	if ($("#ann-modal").size() == 0) {
		var html = '';
		html += '<div id="ann-modal" class="modal fade modal-scroll" tabindex="-1" data-backdrop="static" data-keyboard="false" data-width="760">';
		html += '<div class="modal-header">';
		html += '<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>';
		html += '<h4 class="modal-title">公告详情</h4>';
		html += '</div>';
		html += '<div class="modal-body"></div>';
		html += '<div class="modal-footer">';
		html += '<button type="button" data-dismiss="modal" class="btn blue">确定</button>';
		html += '</div>';
		html += '</div>';
		$(".page-content").append(html);
	}
	$.get("index.php?action=api&operation=getann", {
		"id": id
	}, function(data) {
		var msg = '<dl class="dl-horizontal">';
		if (data.subject) {
			msg += "<dt>主题</dt>";
			msg += "<dd>" + data.subject + "</dd>";
		} else {
			msg = "获取公告失败";
		}
		if (data.author) {
			msg += "<dt>作者</dt>";
			msg += "<dd>" + data.author + "</dd>";
		}
		if (data.starttime) {
			msg += "<dt>开始时间</dt>";
			msg += "<dd>" + data.starttime + "</dd>";
		}
		if (data.endtime) {
			msg += "<dt>结束时间</dt>";
			msg += "<dd>" + data.endtime + "</dd>";
		}
		if (data.message) {
			msg += "<dt>内容</dt>";
			msg += "<dd>" + (data.type==1 ? '<a href="' + data.message + '" target="_blank">' + data.message + '</a>' : nl2br(data.message)) + "</dd>";
		}
		if (data.subject) {
			msg += "</dl>";
		}
		$("#ann-modal .modal-body").html(msg);
		$("#ann-modal").modal("show");
	}, "JSON");
}

function AC_FL_RunContent() {
	var str = '';
	var ret = AC_GetArgs(arguments, "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000", "application/x-shockwave-flash");
	if ($.browser.msie && !$.browser.opera) {
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
	for (var i = 0; i < args.length; i = i + 2) {
		var currArg = args[i].toLowerCase();
		switch (currArg) {
			case "classid":
				break;
			case "pluginspage":
				ret.embedAttrs[args[i]] = 'http://www.macromedia.com/go/getflashplayer';
				break;
			case "src":
				ret.embedAttrs[args[i]] = args[i + 1];
				ret.params["movie"] = args[i + 1];
				break;
			case "codebase":
				ret.objAttrs[args[i]] = 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0';
				break;
			case "onafterupdate":
			case "onbeforeupdate":
			case "onblur":
			case "oncellchange":
			case "onclick":
			case "ondblclick":
			case "ondrag":
			case "ondragend":
			case "ondragenter":
			case "ondragleave":
			case "ondragover":
			case "ondrop":
			case "onfinish":
			case "onfocus":
			case "onhelp":
			case "onmousedown":
			case "onmouseup":
			case "onmouseover":
			case "onmousemove":
			case "onmouseout":
			case "onkeypress":
			case "onkeydown":
			case "onkeyup":
			case "onload":
			case "onlosecapture":
			case "onpropertychange":
			case "onreadystatechange":
			case "onrowsdelete":
			case "onrowenter":
			case "onrowexit":
			case "onrowsinserted":
			case "onstart":
			case "onscroll":
			case "onbeforeeditfocus":
			case "onactivate":
			case "onbeforedeactivate":
			case "ondeactivate":
			case "type":
			case "id":
				ret.objAttrs[args[i]] = args[i + 1];
				break;
			case "width":
			case "height":
			case "align":
			case "vspace":
			case "hspace":
			case "class":
			case "title":
			case "accesskey":
			case "name":
			case "tabindex":
				ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i + 1];
				break;
			default:
				ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i + 1];
		}
	}
	ret.objAttrs["classid"] = classid;
	if (mimeType) {
		ret.embedAttrs["type"] = mimeType;
	}
	return ret;
}