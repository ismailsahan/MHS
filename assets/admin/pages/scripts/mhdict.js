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

	function initDT(columns) {
		$('#anns .group-checkable').change(function () {
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

		$('#anns_wrapper .dataTables_filter input').addClass("form-control input-small");
		$('#anns_wrapper .dataTables_length select').addClass("form-control input-xsmall");
		$('#anns_wrapper .dataTables_length select').select2({minimumResultsForSearch:-1});
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
				$('#anndetail form').find("[name='id']").val("");
				$('#anndetail form').find(":radio").uniform.update();
				$("#anndetail").modal("show");
			});

			$("#anns tr > td:nth-child(10) > a").click(function() {
				showloading();
				var form = $('#anndetail form'), annid = $(this).data("id");
				form.validate().resetForm();
				$.get("{U api/getann}", {id:annid}, function(data) {
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
		}

	};

}();