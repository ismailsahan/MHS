var Mhdict = function () {

	function showloading() {
		$.blockUI({
			message: '<img src="assets/global/img/ajax-loading.gif" />',
			css: {
				//top: '10%',
				border: 'none',
				//padding: '2px',
				backgroundColor: 'none'
			},
			overlayCSS: {
				backgroundColor: '#000',
				opacity: 0.2,
				cursor: 'wait'
			},
			baseZ: 11000
		});
	}

	function nthchild(id, columns) {
		return " td:nth-child(" + (columns[id] + 1) + ")";
	}

	function gmdate(timestamp, format) {
		var t = new Date;
		t.setTime(timestamp + "000");
		return t.format(format);
	}

	function getTime(time) {
		if (!time || time == "0") return "";
		return gmdate(time, "yyyy-MM-dd hh:mm");
	}

	$(document).ajaxStop($.unblockUI);
	$(document).ajaxError(function() {
		modalAlert("向服务器请求数据时发生了错误，请稍候再试");
	});

	return {

		initann: function () {
			$("#anns .group-checkable").change(function() {
				$("#anns .checkboxes:enabled").prop("checked", $(this).prop("checked"));
				$.uniform.update($("#anns .checkboxes:enabled"));
			});

			$('#anndetail form').validate({
				errorElement: 'span',
				errorClass: 'help-block',
				focusInvalid: false,
				ignore: "",
				rules: {
					subject: {
						required: true
					},
					starttime: {},
					endtime: {},
					type: {
						required: true
					},
					academy: {
						required: true
					},
					message: {
						required: true
					}
				},

				messages: {
					subject: {
						required: "公告标题不能为空"
					},
					starttime: {},
					endtime: {},
					type: {
						required: "公告类型必选"
					},
					academy: {
						required: "公告作用范围必选"
					},
					message: {
						required: "公告内容不能为空"
					}
				},

				highlight: function(element) {
					$(element).closest('.form-group').removeClass('has-success').addClass('has-error');
				},

				unhighlight: function(element) {
					$(element).closest('.form-group').removeClass('has-error');
				},

				success: function(label) {
					label.addClass('valid').closest('.form-group').removeClass('has-error').addClass('has-success');
				},

				errorPlacement: function(error, element) {
					error.insertAfter(element.is(":radio") ? element.closest(".radio-list") : element);
				},

				submitHandler: function(form) {
					showloading();
					$.post($(form).attr("action"), $(form).serialize(), function(data) {
						modalAlert(data.msg);
						if (!data.errno) {
							$("#anndetail").modal("hide");
							$("#alert-modal").on("hide.bs.modal", function() {
								window.location.reload();
							});
						}
					});
				}
			});

			$("#anndetail .form-control-inline").datetimepicker({
				language: "zh-CN",
				autoclose: true,
				todayBtn: true,
				format: "yyyy-mm-dd hh:ii"
			});

			$("#anndetail .form-control-inline, #anndetail :radio").change(function() {
				$('#anndetail form').validate().element($(this));
			});

			$("#addann-button").click(function() {
				$('#anndetail form').get(0).reset();
				$('#anndetail form').validate().resetForm();
				$('#act-modal .form-group').removeClass('has-error').removeClass('has-success');
				$('#anndetail form').find("[name='id']").val("");
				$('#anndetail form').find(":radio").uniform.update();
				$("#anndetail").modal("show");
			});

			$("#anns tr > td:nth-child(10) > a").click(function() {
				showloading();
				var form = $('#anndetail form'), annid = $(this).data("id");
				form.validate().resetForm();
				$('#act-modal .form-group').removeClass('has-error').removeClass('has-success');
				$.post("{U api/getann}", {id:annid}, function(data) {
					form.find("[name='id']").val(annid);
					form.find("[name='subject']").val(data.subject);
					form.find("[name='starttime']").val(data.starttime ? data.starttime : undefined);
					form.find("[name='endtime']").val(data.endtime ? data.endtime : undefined);
					form.find("[name='type']").prop("checked", false).filter("[value='" + data.type + "']").prop("checked", true);
					form.find(":radio").uniform.update();
					form.find("[name='message']").val(data.message);
					$("#anndetail").modal("show");
				});
			});

			$(".portlet-body > form").submit(function() {
				if($("#anns .checkboxes:checked:enabled").size() && !confirm("您选择删除 " + $("#anns .checkboxes:checked:enabled").size() + " 个公告，确认要继续吗？")) return false;
				showloading();
				$.post("{U mhdict/ann?inajax=1}", $(this).serialize(), function(data) {
					modalAlert(data.msg);
					if (!data.errno) {
						$("#anndetail").modal("hide");
						$("#alert-modal").on("hide.bs.modal", function() {
							window.location.reload();
						});
					}
				});
				return false;
			})
		},

		initactivity: function() {
			var columns = {
				"checkbox"   : 0,
				"name"       : 1,
				"place"      : 2,
				"starttime"  : 3,
				"endtime"    : 4,
				"sponsor"    : 5,
				"undertaker" : 6,
				"intro"      : 7,
				"available"  : 8,
				"academy"    : 9,
				"extra"      : 10
			};

			$('#act-modal form').validate({
				errorElement: 'span',
				errorClass: 'help-block',
				focusInvalid: false,
				ignore: "",
				rules: {
					name: {
						required: true
					},
					place: {},
					starttime: {},
					endtime: {},
					sponsor: {},
					undertaker: {},
					available: {
						required: true
					},
					academy: {
						required: true
					},
					intro: {
						required: true
					}
				},

				messages: {
					name: {
						required: "活动名称不能为空"
					},
					place: {},
					starttime: {},
					endtime: {},
					sponsor: {},
					undertaker: {},
					available: {
						required: "工时申请选项不能为空"
					},
					academy: {
						required: "社团选项必选"
					},
					intro: {
						required: "活动介绍不能为空"
					}
				},

				highlight: function(element) {
					$(element).closest('.form-group').removeClass('has-success').addClass('has-error');
				},

				unhighlight: function(element) {
					$(element).closest('.form-group').removeClass('has-error');
				},

				success: function(label) {
					label.addClass('valid').closest('.form-group').removeClass('has-error').addClass('has-success');
				},

				errorPlacement: function(error, element) {
					error.insertAfter(element.is(":radio") ? element.closest(".radio-list") : element);
				},

				submitHandler: function(form) {
					showloading();
					$.post($(form).attr("action"), $(form).serialize(), function(data) {
						modalAlert(data.msg);
						if (!data.errno) {
							$("#activities").modal("hide");
							$("#alert-modal").on("hide.bs.modal", function() {
								window.location.reload();
							});
						}
					});
				}
			});

			$("#act-modal .form-control-inline").datetimepicker({
				language: "zh-CN",
				autoclose: true,
				todayBtn: true,
				format: "yyyy-mm-dd hh:ii"
			});

			$("#act-modal .form-control-inline, #act-modal :radio").change(function() {
				$('#act-modal form').validate().element($(this));
			});

			$("#addact-button").click(function() {
				$('#act-modal form').get(0).reset();
				$('#act-modal form').validate().resetForm();
				$('#act-modal .form-group').removeClass('has-error').removeClass('has-success');
				$("#act-modal input[name='id']").val("");
				$('#act-modal form').find(":radio").uniform.update();
				$("#act-modal").modal("show");
			});
			$("#activities tr:gt(0)" + nthchild("extra", columns) + " > a").click(function() {
				showloading();
				var form = $('#act-modal form'), actid = $(this).data("id");
				form.validate().resetForm();
				$('#act-modal .form-group').removeClass('has-error').removeClass('has-success');
				$.post("{U api/activity}", {id:actid}, function(data) {
					form.find("[name='id']").val(actid);
					form.find("[name='name']").val(data.name);
					form.find("[name='place']").val(data.place);
					form.find("[name='starttime']").val(getTime(data.starttime));
					form.find("[name='endtime']").val(getTime(data.endtime));
					form.find("[name='sponsor']").val(data.sponsor);
					form.find("[name='undertaker']").val(data.undertaker);
					form.find("[name='available']").prop("checked", false).filter("[value='" + data.available + "']").prop("checked", true);
					form.find("[name='academy']").prop("checked", false).filter("[value='" + data.academy + "']").prop("checked", true);
					form.find(":radio").uniform.update();
					form.find("[name='intro']").val(data.intro);
					$("#act-modal").modal("show");
				});
			});
			$("#delact-button").click(function() {
				var e = $('td:first-child :checkbox:enabled:checked', $('#activities').dataTable().fnGetNodes());
				if(e.size() == 0) {
					return modalAlert("请至少选择一个活动");
				}
				$("#delact").modal("show");
			});

			$('#activities').dataTable({
				"order": [],
				"lengthMenu": [
					[10, 25, 50],// 每页显示数目，-1表示显示全部
					[10, 25, 50] // 对应的文字
				],
				"pageLength": 10,
				"columnDefs": [{
					'orderable': false,
					'targets': [columns["checkbox"], columns["extra"]]
				}, {
					"searchable": false,
					"targets": [columns["checkbox"], columns["extra"]]
				}]
			});

			$('#column_toggler :checkbox').change(function(){
				var oTable = $('#activities').dataTable();
				var iCol = columns[$(this).data("column")];
				var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
				oTable.fnSetColumnVis(iCol, (bVis ? false : true));
			});

			$('#activities .group-checkable').change(function () {
				var set = $(this).attr("data-set");
				var checked = $(this).is(":checked");
				$(set).each(function () {
					if (checked) {
						$(this).attr("checked", true);
					} else {
						$(this).attr("checked", false);
					}
				});
				$.uniform.update(set);
			});

			$('#activities_wrapper .dataTables_filter input').addClass("form-control input-small");
			$('#activities_wrapper .dataTables_length select').addClass("form-control input-xsmall");
			$('#activities_wrapper .dataTables_length select').select2({minimumResultsForSearch:-1});

			$("#delact .modal-footer button.red").click(function () {
				showloading();
				var id = [];
				$('td:first-child :checkbox:enabled:checked', $('#activities').dataTable().fnGetNodes()).each(function () {
					id.push($(this).val());
				});
				$.post("{U mhdict/activity?inajax=1}", {ids:id.join(",")}, function (data) {
					modalAlert(data.msg);
					if(!data.errno) {
						$("#delmh").modal("hide");
						$("#alert-modal").on("hide.bs.modal", function() {
							window.location.reload();
						});
					}
				}, "json");
			});
		}

	};

}();