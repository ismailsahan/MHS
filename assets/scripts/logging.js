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
					required: "{lang logging/username_required}"
				},
				password: {
					required: "{lang logging/password_required}",
					minlength: "{lang logging/password_minlength}"
				},
				verifycode: {
					required: "{lang logging/verifycode_required}",
					minlength: "{lang logging/verifycode_length}",
					pattern: "{lang logging/verifycode_invalid}"
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit
				$('.alert-danger p').html("{lang logging/login_invalid}");
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
				username: {
					required: true
				},
				email: {
					required: true,
					email: true
				}
			},

			messages: {
				username: {
					required: "{lang logging/username_required}"
				},
				email: {
					required: "{lang logging/email_required}",
					email: "{lang logging/email_illegal}"
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit   
				$('.alert-danger p').html("请正确填写以下资料后再尝试找回密码");
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
					required: true,
					minlength: 6
				},
				rpassword: {
					equalTo: "#register_password"
				},
				email: {
					required: true,
					email: true
				},
				verifycode: {
					required: true,
					minlength: {$_G['setting']['seccodedata']['length']}
				},
				tnc: {
					required: true
				}
			},

			messages: {
				username: {
					required: "{lang logging/username_required}"
				},
				password: {
					required: "{lang logging/password_required}",
					minlength: "{lang logging/password_minlength}"
				},
				rpassword: {
					equalTo: "确认密码与密码不一致！"
				},
				email: {
					required: "邮箱不能为空！",
					email: "您输入的电邮格式不正确"
				},
				verifycode: {
					required: "{lang logging/verifycode_required}",
					minlength: "{lang logging/verifycode_length}",
					pattern: "{lang logging/verifycode_invalid}"
				},
				tnc: {
					required: "你必须同意我们的服务条款才能使用系统"
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit   
				$('.alert-danger p').html("请正确填写以下资料后再尝试注册");
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

		$('#tnc-link').on('click', function(){
			if($('#tnc-modal').size() == 0){
				var html = "";
				html += '<div id="tnc-modal" class="modal container fade modal-scroll" tabindex="-1">';
				html += 	'<div class="modal-header">';
				html += 		'<h4 class="modal-title">服务条款</h4>';
				html += 	'</div>';
				html += 	'<div class="modal-body"></div>';
				html += 	'<div class="modal-footer">';
				html += 		'<button type="button" class="btn red">我拒绝</button>';
				html += 		'<button type="button" class="btn blue">我接受</button>';
				html += 	'</div>';
				html += '</div>';
				$("body").append(html);
			}
			if($('#tnc-modal').data("inited")){
				$("#tnc-modal").modal();
			}else{
				$.blockUI({
					message: '<img src="assets/img/ajax-loading.gif" />',
					css: {
						border: 'none',
						backgroundColor: 'none'
					},
					overlayCSS: {
						backgroundColor: '#000',
						opacity: 0.2,
						cursor: 'wait'
					},
					baseZ: 11000
				});
				$.get("{U api/tos}", function(text){
					if(typeof markdown == 'object') {
						text = markdown.toHTML(text);
					}else if(typeof marked == 'function') {
						text = marked(text);
					}else{
						text = nl2br(text);
					}
					$("#tnc-modal .modal-body").html(text);
					$('#tnc-modal').data("inited", true);
					$("#tnc-modal .modal-footer .red").click(function(){
						$("#tnc-modal").modal("hide");
						$("#tnc").prop("checked", false).uniform.update();
					});
					$("#tnc-modal .modal-footer .blue").click(function(){
						$("#tnc-modal").modal("hide");
						$("#tnc").prop("checked", true).uniform.update();
					});
					$.unblockUI();
					$("#tnc-modal").modal();
				});
			}
		});

		if($.fn.pwstrength) {
			/*$("#register_password").keydown(function () {
				if (initialized === false) {
					// set base options
					input.pwstrength({
						raisePower: 1.4,
						minChar: 8,
						verdicts: ["Weak", "Normal", "Medium", "Strong", "Very Strong"],
						scores: [17, 26, 40, 50, 60]
					});

					// add your own rule to calculate the password strength
					input.pwstrength("addRule", "demoRule", function (options, word, score) {
						return word.match(/[a-z].[0-9]/) && score;
					}, 10, true);

					// set as initialized 
					initialized = true;
				}
			});*/
			/*$("#register_password").pwstrength({
				raisePower: 1.4,
				minChar: 6
			})*//*.pwstrength("addRule", "demoRule", function (options, word, score) {
				return word.match(/[a-z].[0-9]/) && score;
			}, 10, true)*/;
		}

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
			$("input:visible:first").focus();
			//$(".verifycode a img").seccode();
		}

	};

}();