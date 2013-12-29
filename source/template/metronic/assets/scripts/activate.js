var Activate = function () {


	return {
		//main function to initiate the module
		init: function () {
			if (!$().bootstrapWizard) {
				return;
			}

			function format(state) {
				if (!state.id) return state.text; // optgroup
				return "<img class='flag' src='assets/img/flags/" + state.id.toLowerCase() + ".png'/>&nbsp;&nbsp;" + state.text;
			}

			function updateSpecialty(){
				$("#specialty").select2("val",null).html('<option value=""></option>');
				var p1=$('#grade').select2("val"), p2=$('#academy').select2("val");
				if(p1 && p2)
					$.get("{$_G['basefilename']}?action=api&operation=profile", {type:"specialty",grade:p1,academy:p2}, function(data){
						$('#specialty').append($.map(data, function(v, i){ return $('<option>', { val: i, text: v }); }));
					}, 'json');
			}

			function updateClass(){
				$("#class").select2("val",null).html('<option value=""></option>');
				var p1=$('#grade').select2("val"), p2=$('#specialty').select2("val");
				if(p1 && p2)
					$.get("{$_G['basefilename']}?action=api&operation=profile", {type:"class",grade:p1,specialty:p2}, function(data){
						$('#class').append($.map(data, function(v, i){ return $('<option>', { val: i, text: v }); }));
					}, 'json');
			}

			function updateLeague(){
				$("#league").select2("val",null).html('');
				var tmp=$('#academy').select2("val");
				if(tmp)
					$.get("{$_G['basefilename']}?action=api&operation=profile", {type:"league",academy:tmp}, function(data){
						var i, j, s;
						for(i in data){
							s = '<optgroup label="'+i+'">';
							for(j in data[i]){
								s += '<option value="'+j+'">'+data[i][j]+'</option>';
							}
							s += '</optgroup>';
							$('#league').append(s);
						}
					}, 'json');
			}

			function updateDepartment(){
				$("#department").select2("val",null).html('');
				var tmp=$('#league').select2("val");
				if(tmp)
					$.get("{$_G['basefilename']}?action=api&operation=profile", {type:"department",league:tmp.join(",")}, function(data){
						var i, j, s;
						for(i in data){
							s = '<optgroup label="'+i+'">';
							for(j in data[i]){
								s += '<option value="'+j+'">'+data[i][j]+'</option>';
							}
							s += '</optgroup>';
							$('#department').append(s);
						}
					}, 'json');
			}

			$("#country_list").select2({
				placeholder: "Select",
				allowClear: true,
				formatResult: format,
				formatSelection: format,
				escapeMarkup: function (m) {
					return m;
				}
			});

			$("#gender").select2({
				placeholder: '{lang gender}',
				minimumResultsForSearch:-1,
				allowClear: false
			});

			$("#grade").select2({
				placeholder: '{lang grade}',
				/*initSelection: function(e, callback) {
					$.ajax("{$_G['basefilename']?action=api&operation=grade").done(function(data){
						callback({more:false, results:data});
					});
				},
				query: function(q){
					console.log(arguments);
					$.get("{$_G['basefilename']?action=api&operation=grade", function(data){
						var ret = {more:false, results:data};
						q.callback(ret);
					});
				},*/
				minimumResultsForSearch:-1,
				allowClear: false
			}).change(function(){
				updateSpecialty();
			});
			$("#academy").select2({
				placeholder: '{lang academy}',
				allowClear: false
			}).change(function(){
				updateSpecialty();
				updateLeague();
			});
			$("#specialty").select2({
				placeholder: '{lang specialty}',
				allowClear: false
			}).change(function(){
				updateClass();
			});
			$("#class").select2({
				placeholder: '{lang class}',
				allowClear: false
			});
			$("#league").select2({
				placeholder: '{lang league}',
				allowClear: false
			}).change(function(){
				updateDepartment();
			});
			$("#department").select2({
				placeholder: '{lang department}',
				allowClear: false
			});
			$('#submit_form select').change(function () {
				$('#submit_form').validate().element($(this));
			});
			$("#verifycode").seccode();

			$.get("{$_G['basefilename']}?action=api&operation=profile", {type:"grade"}, function(data){
				$('#grade').append($.map(data, function(v, i){
					return $('<option>', { val: i, text: v });
				}));
			}, 'json');
			$.get("{$_G['basefilename']}?action=api&operation=profile", {type:"academy"}, function(data){
				$('#academy').append($.map(data, function(v, i){
					return $('<option>', { val: i, text: v });
				}));
			}, 'json');

			var form = $('#submit_form');
			var error = $('.alert-danger', form);
			var success = $('.alert-success', form);

			form.validate({
				doNotHideMessage: true, //this option enables to show the error/success messages on tab switch.
				errorElement: 'span', //default input error message container
				errorClass: 'help-block', // default input error message class
				focusInvalid: false, // do not focus the last invalid input
				rules: {
					realname: {
						required: true
					},
					gender: {
						required: true
					},
					qq: {
						required: true,
						rangelength: [5, 11],
						digits: true
					},
					studentid: {
						required: true,
						minlength: 13,
						maxlength: 13,
						digits: true
					},
					grade: {
						required: true
					},
					academy: {
						required: true
					},
					specialty: {},
					"class": {},
					"league[]": {
						required: true
					},
					"department[]": {
						required: true
					},
					remarks: {},
					agreement: {
						required: true
					},
					allowemail: {},
					verifycode: {
						required: true,
						minlength: {$_G['setting']['seccodedata']['length']}
					}					
				},

				messages: {
					realname: {
						required: "请输入你的真实姓名！"
					},
					gender: {
						required: "你没有性别或者是未知吗？如果真是这样的，那你真的是太英雄无敌了"
					},
					qq: {
						required: "你难道没有QQ号吗？",
						rangelength: "你输入的QQ号的长度不对哦",
						digits: "QQ号由数字构成，你只能输入数字"
					},
					studentid: {
						required: "学号不能为空！",
						minlength: "你输入的学号长度不正确！",
						maxlength: "你输入的学号长度不正确！",
						digits: "学号仅由数字组成！"
					},
					grade: {
						required: "年级必填！"
					},
					academy: {
						required: "请选择你的院系！这跟第三步的资料有关"
					},
					specialty: {},
					"class": {},
					"league[]": {
						required: "请选择你所有加入的社团或组织！"
					},
					"department[]": {
						required: "请选择你所有加入的社团或组织中的部门！"
					},
					remarks: {},
					agreement: {
						required: "你必须同意服务条款后才能使用本系统"
					},
					allowemail: {},
					verifycode: {
						required: "验证码不能为空！",
						minlength: "你输入的验证码长度不正确！"
					}
				},

				errorPlacement: function (error, element) { // render error placement for each input type
					if (element.attr("name") == "gender") { // for uniform radio buttons, insert the after the given container
						error.insertAfter("#form_gender_error");
					} else if (element.attr("name") == "agreement" || element.attr("name") == "allowemail") { // for uniform radio buttons, insert the after the given container
						error.insertAfter("#form_extra_error");
					} else {
						error.insertAfter(element); // for other inputs, just perform default behavior
					}
				},

				invalidHandler: function (event, validator) { //display error alert on form submit   
					success.hide();
					error.show();
					App.scrollTo(error, -200);
				},

				highlight: function (element) { // hightlight error inputs
					$(element)
						.closest('.form-group').removeClass('has-success').addClass('has-error'); // set error class to the control group
				},

				unhighlight: function (element) { // revert the change done by hightlight
					$(element)
						.closest('.form-group').removeClass('has-error'); // set error class to the control group
				},

				success: function (label) {
					if (label.attr("for") == "gender" || label.attr("for") == "payment[]") { // for checkboxes and radio buttons, no need to show OK icon
						label
							.closest('.form-group').removeClass('has-error').addClass('has-success');
						label.remove(); // remove error label here
					} else { // display success icon for other inputs
						label
							.addClass('valid') // mark the current input as valid and display OK icon
						.closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
					}
				},

				submitHandler: function (form) {
					success.show();
					error.hide();
					//add here some ajax code to submit your form or just call form.submit() if you want to submit the form without ajax
				}

			});

			var displayConfirm = function() {
				$('#tab5 .form-control-static', form).each(function(){
					var input = $('[name="'+$(this).attr("data-display")+'"]', form);
					if (input.is(":text") || input.is("textarea")) {
						$(this).html(input.val().replace(/\n/g, "<br />"));
					} else if (input.is("select")) {
						var arr = [];
						input.find('option:selected').each(function(){
							arr.push($(this).text());
						})
						$(this).html(arr.join(" , "));
					} else if (input.is(":radio") && input.is(":checked")) {
						$(this).html(input.attr("data-title"));
					} else if (input.is(":checkbox") && input.is(":checked")) {
						$(this).html(input.attr("data-title"));
					} else if ($(this).attr("data-display") == 'payment') {
						var payment = [];
						$('[name="payment[]"]').each(function(){
							payment.push($(this).attr('data-title'));
						});
						$(this).html(payment.join("<br>"));
					}
				});
			}

			var handleTitle = function(tab, navigation, index) {
				var total = navigation.find('li').length;
				var current = index + 1;
				// set wizard title
				$('.step-title', $('#activate')).text('第' + (index + 1) + '步(共' + total + '步)');
				// set done steps
				$('li', $('#activate')).removeClass("done");
				var li_list = navigation.find('li');
				for (var i = 0; i < index; i++) {
					$(li_list[i]).addClass("done");
				}

				if (current == 1) {
					$('#activate').find('.button-previous').hide();
				} else {
					$('#activate').find('.button-previous').show();
				}

				if (current >= total) {
					$('#activate').find('.button-next').hide();
					$('#activate').find('.button-submit').show();
					displayConfirm();
				} else {
					$('#activate').find('.button-next').show();
					$('#activate').find('.button-submit').hide();
				}
				App.scrollTo($('.page-title'));
			}

			// default form wizard
			$('#activate').bootstrapWizard({
				'nextSelector': '.button-next',
				'previousSelector': '.button-previous',
				onTabClick: function (tab, navigation, index, clickedIndex) {
					success.hide();
					error.hide();
					if (form.valid() == false) {
						return false;
					}
					handleTitle(tab, navigation, clickedIndex);
				},
				onNext: function (tab, navigation, index) {
					success.hide();
					error.hide();

					if (form.valid() == false) {
						return false;
					}

					handleTitle(tab, navigation, index);
				},
				onPrevious: function (tab, navigation, index) {
					success.hide();
					error.hide();

					handleTitle(tab, navigation, index);
				},
				onTabShow: function (tab, navigation, index) {
					var total = navigation.find('li').length;
					var current = index + 1;
					var $percent = (current / total) * 100;
					$('#activate').find('.progress-bar').css({
						width: $percent + '%'
					});
				}
			});

			$('#activate').find('.button-previous').hide();
			$('#activate .button-submit').click(function () {
				$.post($("#submit_form").attr("action")+"&inajax=1", $("#submit_form").serialize(), function(data){
					alert("测试发送到服务器的数据：\n\n"+data);
				});
			}).hide();
		}

	};

}();