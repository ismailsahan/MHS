if(!$.fn.poshytip) document.write('\
<link rel="stylesheet" href="assets/global/plugins/poshytip-1.2/src/tip-yellow/tip-yellow.css" type="text/css" />\
<link rel="stylesheet" href="assets/global/plugins/poshytip-1.2/src/tip-violet/tip-violet.css" type="text/css" />\
<link rel="stylesheet" href="assets/global/plugins/poshytip-1.2/src/tip-darkgray/tip-darkgray.css" type="text/css" />\
<link rel="stylesheet" href="assets/global/plugins/poshytip-1.2/src/tip-skyblue/tip-skyblue.css" type="text/css" />\
<link rel="stylesheet" href="assets/global/plugins/poshytip-1.2/src/tip-yellowsimple/tip-yellowsimple.css" type="text/css" />\
<link rel="stylesheet" href="assets/global/plugins/poshytip-1.2/src/tip-twitter/tip-twitter.css" type="text/css" />\
<link rel="stylesheet" href="assets/global/plugins/poshytip-1.2/src/tip-green/tip-green.css" type="text/css" />\
<script type="text/javascript" src="assets/global/plugins/poshytip-1.2/src/jquery.poshytip.js"></script>');

$.fn.seccode = function() {
	switch($(this).prop('tagName')){
		case 'IMG':
			$(this).click(function() {
				//$.fn.seccode.update(obj);
				var s = $(this).prop("src"), p = s.lastIndexOf("&"), t = s.substr(p).indexOf("=")>-1, u = t ? s : s.substr(0, p);
				$(this).prop("src", u + "&" + new Date().getTime());
			});
			break;
		case 'INPUT':
			$(this).poshytip({
				content: function(updateCallback) { return $(this).seccodeHTML(updateCallback); },
				showTimeout: 1,
				showOn: 'none',
				//offsetY: 5,
				alignTo: 'target',
				alignX: 'center',
				//fade: false,
				slide: false
			}).focus(function(){
				$(this).poshytip('show');
				$.fn.seccode.list[$.fn.seccode.id($(this))] = true;
			}).blur(function(){$.fn.seccode.list[$.fn.seccode.id($(this))]
				//setTimeout('!$(".seccodeImg").is(":focus") && $("#'+$(this).prop("id")+'").poshytip("hideDelayed");', 1000);
				$.fn.seccode.list[$.fn.seccode.id($(this))] = false;
				setTimeout('$.fn.seccode.hide("'+$(this).prop("id")+'")', 1000);
			});
			$.fn.seccode.list[$.fn.seccode.id($(this))] = false;
			break;
		default:
			//console.log($(this).prop('tagName'));
	}
};

$.fn.secUdt = function() {
	switch($(this).prop('tagName')){
		case 'IMG': return $.fn.seccode.update(this);
		case 'INPUT': $(this).val(""); return $(this).seccodeHTML(1);
		default:
			//console.log($(this).prop('tagName'));
	}
};

$.fn.seccodeHTML = function(callback) {
	var id = $(this).prop("id"), imgurl = $(this).attr('imgurl');
	//$.fn.seccode.list[$.fn.seccode.id(this)] = true;
	if(callback){
		$.post("{U seccode/html}", {id:id, imgurl:imgurl, hash:id, tag:$(this).attr("tag")}, function(data) {
			if(/AC_FL_RunContent/.test(data)) data = data.replace(/AC_FL_RunContent\(.+\)/, function(s){return eval(s)});
			return typeof callback=="function" ? callback(data) : $("#"+id).poshytip('update', data);
		});
		callback==1 && $(this).focus().select();
		return 'Loading...';
	}
	return '<a href="javascript:;" class="seccodeImg" onmouseenter="$.fn.seccode.list[$.fn.seccode.id(\'#'+id+'\')]=true" onmouseout="$.fn.seccode.hideDelayed(\''+id+'\')"><img src="'+imgurl+'&'+new Date().getTime()+'" width="150" height="60" onclick="$(\'#'+id+'\').poshytip(\'update\', $(\'#'+id+'\').seccodeHTML())" onblur="!$(\'#'+id+'\').is(\':focus\')&&$(\'#'+id+'\').poshytip(\'hide\')" /></a>';
};

$.fn.seccode.update = function(obj) {
	obj = $(obj);
	var s = obj.prop("src"), p = s.lastIndexOf("&"), t = s.substr(p).indexOf("=")>-1, u = t ? s : s.substr(0, p);
	obj.prop("src", u + "&" + new Date().getTime());
};

$.fn.seccode.list = [];

$.fn.seccode.id = function(e) {
	if($(e).attr("tag")) return $(e).attr("tag");
	e = $(e).parents("form");
	if(e.length > 0){
		e = $($(this).parents("form")[0]);
	}
	return e.attr("class") + e.attr("name") + e.attr("id");
}

$.fn.seccode.hide = function(id){
	var e = $(id.substr(0,1)=="#" ? id : "#"+id);
	id = $.fn.seccode.id($(this));
	if(!$.fn.seccode.list[id] && !$(".seccodeImg").is(":focus") && !e.is(":focus")){
		e.poshytip("hideDelayed");
	}
}

$.fn.seccode.hideDelayed = function(id){
	$.fn.seccode.list[$.fn.seccode.id('#'+id)] = false;
	setTimeout("$.fn.seccode.hide('"+id+"')", 1000);
}