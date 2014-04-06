(function($) {
	function set(grp, idx, txt) {
		$.fn.markdown.defaults.buttons[0][grp].data[idx].title = txt;
		if($.fn.markdown.defaults.buttons[0][grp].data[idx].btnText)
		$.fn.markdown.defaults.buttons[0][grp].data[idx].btnText = txt;
	}

	if($.fn.markdown.defaults.buttons) {
		$.each([
			{grp:0, idx:0, txt:"粗体"},
			{grp:0, idx:1, txt:"斜体"},
			{grp:0, idx:2, txt:"标题"},
			{grp:1, idx:0, txt:"超链接"},
			{grp:1, idx:1, txt:"图像"},
			{grp:2, idx:0, txt:"列表"},
			{grp:3, idx:0, txt:"预览"}
		], function(k, v) {
			set(v.grp, v.idx, v.txt);
		});
	}
})(jQuery)