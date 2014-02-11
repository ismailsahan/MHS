var Lock = function () {

	return {

		init: function () {
			if(window.location.search) {
				var i = window.location.search.indexOf("referer=");
				if(i > -1) {
					var t = $("form").prop("action");
					$("form").prop("action", t + (t.indexOf("?")>-1 ? "&" : "?") + window.location.search.substr(i));
				}
			}

			$.backstretch([
				"assets/img/bg/1.jpg",
				"assets/img/bg/2.jpg",
				"assets/img/bg/3.jpg",
				"assets/img/bg/4.jpg"
			], {
				fade: 1000,
				duration: 8000
			});

			$("input:last").focus();
        }
    };

}();