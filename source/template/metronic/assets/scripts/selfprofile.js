var SelfProfile = function () {

	var showloading = function () {
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
	};

	var modalAlert = function (msg){
		$("#alert-modal .modal-body .col-md-12").html(msg);
		$("#alert-modal").modal("show");
	}

	var initProfiles = function () {

		function updateSpecialty(initial){
			$('#specialty').editable('disable');
			$.get("{U api/profile}", {type:"specialty",grade:$('#grade').data("value"),academy:$('#academy').data("value")}, function(data){
				$('#specialty').editable('option', 'source', data);
				$('#specialty').editable('setValue', initial ? $('#specialty').data("value") : null);
				$('#specialty').editable('enable');
			}, 'json');
		}

		function updateClass(initial){
			$('#class').editable('disable');
			$.get("{U api/profile}", {type:"class",grade:$('#grade').data("value"),specialty:$('#specialty').data("value")}, function(data){
				$('#class').editable('option', 'source', data);
				$('#class').editable('setValue', initial ? $('#class').data("value") : null);
				$('#class').editable('enable');
			}, 'json');
		}

		function initLeague(element, callback){
			var ls = callback ? element.val().split(",") : $('#league').data("value").toString().split(",");
			$.get("{U api/profile}", {type:"league",academy:$('#academy').data("value")}, function(data){
				var res = [];
				$.each(data, function (_k, _v) {
					if(!_k) return;
					$.each(_v, function (k, v) {
						if($.inArray(k, ls) > -1) res.push(callback ? {id: k, text: v} : v);
					});
				});
				if(!callback) res = res.join(", ");
				return callback ? callback(res) : (res ? $('#league').removeClass("editable-empty").text(res) : $('#league').addClass("editable-empty").text($.fn.editable.defaults.emptytext));
			}, 'json');
		}

		function initDepartment(element, callback){
			var ls = callback ? element.val().split(",") : $('#department').data("value").toString().split(",");
			$.get("{U api/profile}", {type:"department",league:$('#league').data("value")}, function(data){
				var res = [];
				$.each(data, function (_k, _v) {
					if(!_k) return;
					$.each(_v, function (k, v) {
						if($.inArray(k, ls) > -1) res.push(callback ? {id: k, text: v} : v);
					});
				});
				if(!callback) res = res.join(", ");
				return callback ? callback(res) : (res ? $('#department').removeClass("editable-empty").text(res) : $('#department').addClass("editable-empty").text($.fn.editable.defaults.emptytext));
			}, 'json');
		}

		$.fn.editable.defaults.mode = 'inline';
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
						allowClear: $.inArray(editable.$element.prop("id"), ["specialty", "class"])>-1 ? true : false
					});
					break;
				case "checklist":
					App.initUniform();
			}
		});

		$('#email,#realname,#mobile,#qq,#studentid').editable();

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
			select2: {
				minimumResultsForSearch:-1,
				allowClear: false
			}
		});

		$('#grade').editable({
			success: function(response, newValue) {
				$(this).data("value", newValue);
				updateSpecialty();
			}
		});
		$('#academy').editable({
			success: function(response, newValue) {
				$(this).data("value", newValue);
				updateSpecialty();
				$('#league').editable('setValue', null);
			}
		});
		$('#specialty').on('init', function(e, editable) {
			updateSpecialty(1);
		});
		$('#specialty').editable({
			success: function(response, newValue) {
				$(this).data("value", newValue);
				updateClass();
			}
		});
		$('#class').on('init', function(e, editable) {
			updateClass(1);
		});
		$('#class').editable({
			success: function(response, newValue) {
				$(this).data("value", newValue);
				//updateClass();
			}
		});

		/*$('#league').on('shown', function(e, editable) {
			console.log(editable.input.$input.val());
			//updateLeague(1);
		});*/
		$('#league').on('init', function(e, editable) {
			initLeague();
		});
		$("#league").editable({
			success: function(response, newValue) {
				$(this).data("value", newValue.join(","));
				$('#department').editable('setValue', null);
			},
			select2: {
				allowClear: true,
				multiple: true,
				minimumResultsForSearch:-1,
				id: function (item) {
					//console.log(item);
					return item.id;
				},
				ajax: {
					url: "{U api/profile}",
					dataType: 'json',
					cache: true,
					data: function (term, page) {
						return {
							type: "league",
							academy: $('#academy').data("value")
						};
					},
					results: function (data, page) {
						var res = [];
						$.each(data, function (k, v) {
							if(!k || $.isEmptyObject(v)) return;
							var tmp = [];
							$.each(v, function (_k, _v) {
								tmp.push({
									id: _k,
									text: _v
								});
							});
							res.push({
								text: k,
								children: tmp
							});
						});
						return {more:false, results: res};
					}
				},
				initSelection : function (element, callback) {
					return initLeague(element, callback);
				}
			}
		});

		$('#department').on('init', function(e, editable) {
			initDepartment();
		});
		$("#department").editable({
			select2: {
				allowClear: true,
				multiple: true,
				minimumResultsForSearch:-1,
				ajax: {
					url: "{U api/profile}",
					dataType: 'json',
					cache: true,
					data: function (term, page) {
						return {
							type: "department",
							league: $('#league').data("value")
						};
					},
					results: function (data, page) {
						var res = [];
						$.each(data, function (k, v) {
							if(!k || $.isEmptyObject(v)) return;
							var tmp = [];
							$.each(v, function (_k, _v) {
								tmp.push({
									id: _k,
									text: _v
								});
							});
							res.push({
								text: k,
								children: tmp
							});
						});
						return {more:false, results: res};
					}
				},
				initSelection : function (element, callback) {
					return initDepartment(element, callback);
				}
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