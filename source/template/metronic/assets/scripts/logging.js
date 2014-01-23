var Logging = function () {
	var handleLogin = function() {
		$('.login-form').validate({
			errorElement: 'span', //default input error message container
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
					minlength: {$_G['setting']['seccodedata']['length']}/*,
					pattern: /^[0-9A-Za-z]{{$_G['setting']['seccodedata']['length']}}$/*/
				}
			},
			messages: {
				username: {
					required: "{lang username_required}"
				},
				password: {
					required: "{lang password_required}",
					minlength: "{lang password_minlength}"
				},
				verifycode: {
					required: "{lang verifycode_required}",
					minlength: "{lang verifycode_length}",
					pattern: "{lang verifycode_invalid}"
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit
				$('.alert-danger p').html("{lang login_invalid}");
				$('.alert-danger').show();
			},

			highlight: function (element) { // hightlight error inputs
				$(element).closest('.form-group').addClass('has-error'); // set error class to the control group
			},

			success: function (label) {
				label.closest('.form-group').removeClass('has-error');
				label.remove();
			},

			errorPlacement: function (error, element) {
				error.insertAfter(element.closest('.input-icon'));
			},

			submitHandler: function (form) {
				form.submit(); // form validation success, call ajax form submit
			}
		});

		$('.login-form input').keypress(function (e) {
			if (e.which == 13) {
				if ($('.login-form').validate().form()) {
					$('.login-form').submit(); //form validation success, call ajax form submit
				}
				return false;
			}
		});
	}

	var handleForgetPassword = function () {
		$('.forget-form').validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",
			rules: {
				email: {
					required: true,
					email: true
				}
			},

			messages: {
				email: {
					required: "Email is required."
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit   

			},

			highlight: function (element) { // hightlight error inputs
				$(element).closest('.form-group').addClass('has-error'); // set error class to the control group
			},

			success: function (label) {
				label.closest('.form-group').removeClass('has-error');
				label.remove();
			},

			errorPlacement: function (error, element) {
				error.insertAfter(element.closest('.input-icon'));
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

	}

	var handleRegister = function () {

		$('.register-form').validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",
			rules: {
				username: {
					required: true
				},
				password: {
					required: true
				},
				rpassword: {
					equalTo: "#register_password"
				},
				email: {
					required: true,
					email: true
				},
				tnc: {
					required: true
				}
			},

			messages: { // custom messages for radio buttons and checkboxes
				tnc: {
					required: "Please accept TNC first."
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit   

			},

			highlight: function (element) { // hightlight error inputs
				$(element).closest('.form-group').addClass('has-error'); // set error class to the control group
			},

			success: function (label) {
				label.closest('.form-group').removeClass('has-error');
				label.remove();
			},

			errorPlacement: function (error, element) {
				if (element.attr("name") == "tnc") { // insert checkbox errors after the container				  
					error.insertAfter($('#register_tnc_error'));
				} else if (element.closest('.input-icon').size() === 1) {
					error.insertAfter(element.closest('.input-icon'));
				} else {
					error.insertAfter(element);
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
	}
	
	return {
		//main function to initiate the module
		init: function () {
			handleLogin();
			handleForgetPassword();
			handleRegister();
			$("#verifycode").seccode();
			//$(".verifycode a img").seccode();
		}

	};

}();