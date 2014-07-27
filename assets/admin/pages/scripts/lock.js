var Lock = function () {

	function showerr(msg) {
		$(".page-lock-info span.locked").html('<p class="text-danger">' + msg + '</p>');
	}

	return {
		//main function to initiate the module
		init: function () {

			$(".page-lock-info form").submit(function() {
				if($("input[name='password']").val() == "") {
					showerr("密码不能为空");
					return false;
				}else if($("input[name='password']").val().length < 6) {
					showerr("密码不能少于6个字符");
					return false;
				}
				if($("#verifycode").val()=="") {
					showerr("验证码不能为空");
					return false;
				}
			});

			$.backstretch([
				"assets/admin/pages/media/bg/1.jpg",
				"assets/admin/pages/media/bg/2.jpg",
				"assets/admin/pages/media/bg/3.jpg",
				"assets/admin/pages/media/bg/4.jpg"
			], {
				fade: 1000,
				duration: 8000
			});
		}

	};

}();