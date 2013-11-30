jQuery.easing.def = "easeOutSine";
var Login = function () {
	var handleLogin = function() {
		$('.login-form').validate({
			errorElement: 'label', //default input error message container
			errorClass: 'help-block', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			rules: {
				username: {
					required: true
				},
				password: {
					required: true,
					minlength: 6
				},
				verifycode: {
					required: true,
					minlength: 4
				}
			},

			messages: {
				username: {
					required: "用户名不能为空！"
				},
				password: {
					required: "密码不能为空！",
					minlength: "密码不能少于6个字符！"
				},
				verifycode: {
					required: "验证码不能为空！",
					minlength: "验证码的长度不正确！"
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit   
				$('.alert-error', $('.login-form')).show();
			},

			highlight: function (element) { // hightlight error inputs
				$(element).closest('.control-group').addClass('error'); // set error class to the control group
			},

			success: function (label) {
				label.closest('.control-group').removeClass('error');
				label.remove();
			},

			errorPlacement: function (error, element) {
				error.addClass('help-small no-left-padding').insertAfter(element.closest('.input-icon'));
			},

			submitHandler: function (form) {
				form.submit();
			}
		});

		$('.login-form input').keypress(function (e) {
			if(e.which == 13) {
				if($('.login-form').validate().form()) {
					$('.login-form').submit();
				}
				return false;
			}
		});
	}

	var handleForgetPassword = function () {
		$('.forget-form').validate({
			errorElement: 'label', //default input error message container
			errorClass: 'help-block', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",
			rules: {
				username: {
					required: true
				},
				qq: {
					required: true,
					range: [10000, 9999999999]
				}
			},

			messages: {
				username: {
					required: "用户名不能为空！"
				},
				qq: {
					required: "QQ号不能为空！",
					range: "QQ号格式不正确！"
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit   

			},

			highlight: function (element) { // hightlight error inputs
				$(element).closest('.control-group').addClass('error'); // set error class to the control group
			},

			success: function (label) {
				label.closest('.control-group').removeClass('error');
				label.remove();
			},

			errorPlacement: function (error, element) {
				error.addClass('help-small no-left-padding').insertAfter(element.closest('.input-icon'));
			},

			submitHandler: function (form) {
				form.submit();
			}
		});

		$('.forget-form input').keypress(function (e) {
			if (e.which == 13) {
				if ($('.forget-form').validate().form()) {
					$('.forget-form').submit();
				}
				return false;
			}
		});

		jQuery('#forget-password').click(function () {
			jQuery('.login-form').hide();
			jQuery('.forget-form').show();
		});

		jQuery('#back-btn').click(function () {
			jQuery('.login-form').show();
			jQuery('.forget-form').hide();
		});

	}

	var handleRegister = function () {

		function format(state) {
			if (!state.id) return state.text; // optgroup
			return "<img class='flag' src='assets/img/flags/" + state.id.toLowerCase() + ".png'/>&nbsp;&nbsp;" + state.text;
		}

		$("#select2_sample4").select2({
		  	placeholder: '<i class="icon-map-marker"></i>&nbsp;Select a Country',
			allowClear: true,
			formatResult: format,
			formatSelection: format,
			escapeMarkup: function (m) {
				return m;
			}
		});

		$('#select2_sample4').change(function () {
			$('.register-form').validate().element($(this)); //revalidate the chosen dropdown value and show error or success message for the input
		});

		$('.register-form').validate({
			errorElement: 'label', //default input error message container
			errorClass: 'help-inline', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",
			rules: {
				username: {
					required: true
				},
				email: {
					required: true,
					email: true
				},
				password: {
					required: true,
					minlength: 6
				},
				rpassword: {
					required: true,
					equalTo: "#register_password"
				},
				tnc: {
					required: true
				}
			},

			messages: { // custom messages for radio buttons and checkboxes
				username: {
					required: "用户名不能为空！"
				},
				password: {
					required: "密码不能为空！",
					minlength: "密码不能少于6个字符！"
				},
				rpassword: {
					required: "确认密码不能为空！",
					equalTo: "两个密码不一致！"
				},
				tnc: {
					required: "请先接受网站服务条款！"
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit

			},

			highlight: function (element) { // hightlight error inputs
				$(element).closest('.control-group').addClass('error'); // set error class to the control group
			},

			success: function (label) {
				label.closest('.control-group').removeClass('error');
				label.remove();
			},

			errorPlacement: function (error, element) {
				if (element.attr("name") == "tnc") { // insert checkbox errors after the container
					error.addClass('help-small no-left-padding').insertAfter($('#register_tnc_error'));
				} else if (element.closest('.input-icon').size() === 1) {
					error.addClass('help-small no-left-padding').insertAfter(element.closest('.input-icon'));
				} else {
					error.addClass('help-small no-left-padding').insertAfter(element);
				}
			},

			submitHandler: function (form) {
				form.submit();
			}
		});

		$('.register-form input').keypress(function (e) {
			if (e.which == 13) {
				if ($('.register-form').validate().form()) {
					$('.register-form').submit();
				}
				return false;
			}
		});

		jQuery('#register-btn').click(function () {
			jQuery('.login-form').hide();
			jQuery('.register-form').show();
		});

		jQuery('#register-back-btn').click(function () {
			jQuery('.login-form').show();
			jQuery('.register-form').hide();
		});
	}
	
	return {
		//main function to initiate the module
		init: function () {
			handleLogin();
			handleForgetPassword();
			handleRegister();
			$(".verifycode a img").seccode();
		}
	};
}();