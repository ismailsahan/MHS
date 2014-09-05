var SelfProfile = function () {

	var showloading = function () {
		$.blockUI({
			message: '<img src="assets/global/img/ajax-loading.gif" />',
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
	};

	var modalAlert = function (msg){
		$("#alert-modal .modal-body .col-md-12").html(msg);
		$("#alert-modal").modal("show");
	}

	var initProfiles = function () {

		$.fn.editableContainer.defaults.placement = "bottom";
		//$.fn.editable.defaults.mode = 'inline';
		$.fn.editable.defaults.inputclass = 'form-control';
		$.fn.editable.defaults.url = '{U self/profile?inajax=1}';
		$.fn.editable.defaults.inputclass = 'form-control input-medium';
		$.fn.editable.defaults.emptytext = '(空值)';
		$.fn.editable.defaults.sourceError = '加载列表时发生了错误';
		$.fn.editable.defaults.success = function(response, newValue) {
			$(this).data("value", newValue);
		};

		$('#table-profile td:nth-child(2) a').on('shown', function(e, editable) {
			switch(editable.$element.data("type")) {
				case "select":
					editable.input.$input.select2({
						minimumResultsForSearch:-1,
						allowClear: false
					});
					break;
				case "checklist":
					Metronic.initUniform();
			}
		});

		$('#grade,#academy,#specialty,#class').editable();
		$('#email,#realname,#qq,#studentid').editable({
			disabled: true
		});
		$('#mobile').editable({
			placement: "top",
		});

		$('#gender').editable({
			inputclass: 'form-control',
			source: [{
					value: 1,
					text: '{lang male}'
				}, {
					value: 2,
					text: '{lang female}'
				}
			],
			disabled: true,
			select2: {
				minimumResultsForSearch:-1,
				allowClear: false
			}
		});

		$('#league,#department').editable({
			inputclass: 'form-control input-medium',
			source: [{
					value: 1,
					text: '{lang male}'
				}, {
					value: 2,
					text: '{lang female}'
				}
			],
			select2: {
				minimumResultsForSearch:-1,
				tags: [],
				tokenSeparators: [",", " "]
			}
		});

	};

	var initpwd = function () {
		$.validator.addMethod("notequalTo", function(value, element, param) {
			return this.optional(element) || value != $(param).val();
		}, "");

		$('#tab_pwd form').validate({
			errorElement: 'span',
			errorClass: 'help-block',
			focusInvalid: false,
			ignore: "",
			rules: {
				curpwd: {
					required: true,
					minlength: 6
				},
				newpwd: {
					required: true,
					minlength: 6,
					notequalTo: "#curpwd"
				},
				rpwd: {
					equalTo: "#newpwd"
				},
				verifycode: {
					required: true,
					minlength: {$_G['setting']['seccodedata']['length']}
				}
			},

			messages: {
				curpwd: {
					required: "当前密码不能为空",
					minlength: "密码不能短于6个字符"
				},
				newpwd: {
					required: "新密码不能为空",
					minlength: "密码不能短于6个字符",
					notequalTo: "新密码不能与原密码相同"
				},
				rpwd: {
					equalTo: "确认密码与新密码不一致"
				},
				verifycode: {
					required: "验证码不能为空",
					minlength: "验证码长度不正确"
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit   
				$('#tab_pwd .alert-danger p').html("请正确填写以下资料后再尝试修改密码");
				$('#tab_pwd .alert-danger').show();
			},

			highlight: function (element) {
				$(element).closest('.form-group').addClass('has-error');
			},

			success: function (label) {
				label.closest('.form-group').removeClass('has-error');
				label.remove();
			},

			errorPlacement: function (error, element) {
				error.insertAfter(element);
			},

			submitHandler: function (form) {
				var url = $(form).prop("action");
				showloading();
				$.post(url + (url.indexOf("?")>-1 ? "&inajax=1" : "/inajax/1"), $(form).serialize(), function(data){
					if(data.url) {
						window.location.href(data.url);
					}else if(data.msg){
						$.unblockUI();
						modalAlert(data.msg);
						$("#alert-modal").on("hide.bs.modal", function(){ window.location.reload(); });
					}
				});
			}
		});

		$("#verifycode").seccode();
	};

	return {
		init: function () {
			initProfiles();
			initpwd();
		}

	};

}();