var Mhdict = function () {

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

			$("#addann-button").click(function() {
				$("#anndetail").modal("show");
			});

			$("#anns tr > td:nth-child(10) > a").click(function() {
				$("#anndetail").modal("show");
			});

			$(".portlet-body > form").submit(function() {
				if($("#anns .checkboxes:checked:enabled").size() && !confirm("您选择删除 " + $("#anns .checkboxes:checked:enabled").size() + " 个公告，确认要继续吗？")) return false;
				return false;
			})
		}

	};

}();