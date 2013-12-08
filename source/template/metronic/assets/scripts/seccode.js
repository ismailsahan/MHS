$.fn.seccode = function(obj) {
	obj = obj ? $(obj) : $(this);
	obj.click(function() {
		//$.fn.seccode.update(obj);
		var s = $(this).prop("src"), p = s.lastIndexOf("&"), t = s.substr(p).indexOf("=")>-1, u = t ? s : s.substr(0, p);
		$(this).prop("src", u + "&" + new Date().getTime());
	});
};

$.fn.seccode.update = function(obj) {
	obj = $(obj);
	var s = obj.prop("src"), p = s.lastIndexOf("&"), t = s.substr(p).indexOf("=")>-1, u = t ? s : s.substr(0, p);
	obj.prop("src", u + "&" + new Date().getTime());
};