var Manhour = function() {

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

	var status = [
		["danger", "无效"],
		["success", "有效"],
		["info", "审核中"],
		["primary", "复查中"],
		["danger", "未通过审核"],
		["danger", "未通过复查"],
		["danger", "未知错误"]
	];

	function statusLabel(id) {
		id = parseInt(id);
		switch (id) {
			default: id = 6;
			case 0:
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
				return '<span class="label label-sm label-' + status[id][0] + '" data-status="' + id + '">' + status[id][1] + '</span>';
		}
	}

	function gmdate(timestamp, format) {
		var t = new Date;
		t.setTime(timestamp + "000");
		return t.format(format);
	}

	function getDate(time) {
		if (!time || time == "0") return "-";
		return gmdate(time, "yyyy年MM月dd日");
	}

	function getTime(time) {
		if (!time || time == "0") return "-";
		return gmdate(time, "yyyy年MM月dd日 hh:mm:ss");
	}

	function detail(type, id) {
		showloading();
		if (type == "manhour") {
			$.post("{U api/manhour}", {
				"id": id
			}, function(data) {
				if (!data.id) return window.location.reload();
				$("#mh-modal p.col-md-9").each(function() {
					var mh = $(this).data("mh");
					if (mh == "time") {
						data.time = getDate(data.time);
					} else if (mh == "applytime" || mh == "verifytime") {
						data[mh] = getTime(data[mh]);
					} else if (mh == "status") {
						data.status = statusLabel(data.status);
					} else if (mh == "remark" || mh == "verifytext") {
						data[mh] = data[mh] ? nl2br(stripslashes(data[mh])) : "-";
					}
					$(this).html(data[mh]);
				});
				$("#mh-modal").modal("show");
			}, 'json');
		} else if (type == "activity") {
			$.post("{U api/activity}", {
				"id": id
			}, function(data) {
				if (!data.id) return modalAlert("活动数据获取失败 该活动不存在或已删除");
				$("#act-modal p.col-md-9").each(function() {
					var act = $(this).data("act");
					if (act == "starttime" || act == "endtime") {
						data[act] = getTime(data[act]);
					} else if (!data[act]) {
						data[act] = "-";
					}
					$(this).html(data[act]);
				});
				$("#act-modal").modal("show");
			}, 'json');
		}
	}

	function nthchild(id, columns) {
		return " td:nth-child(" + (columns[id] + 1) + ")";
	}

	function initDT(columns, noorder) {
		var utimes = [];
		if(columns["applytime"]) utimes.push(columns["applytime"]);
		if(columns["verifytime"]) utimes.push(columns["verifytime"]);
		var table = $('#manhours').dataTable({
			"order": noorder ? [] : [[columns["applytime"], 'asc']],
			"lengthMenu": [
				[10, 25, 50],// 每页显示数目，-1表示显示全部
				[10, 25, 50] // 对应的文字
			],
			"pageLength": 10,
			"columnDefs": [
				{ orderable: false, searchable: false, targets: [columns["checkbox"], columns["extra"], columns["avatar"]] },
				{ orderDataType: "dom-time", targets: utimes }
			]
		});
		var tt = new $.fn.dataTable.TableTools(table, {
			"sSwfPath": "assets/global/plugins/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf",
			"aButtons": [{
				"sExtends": "csv",
				"sButtonText": "导出 Excel",
				"bBomInc": true
			}]
		});
		$(tt.fnContainer()).insertBefore('#manhours_wrapper');
		$(".DTTT_container").addClass("btn-group margin-bottom-10");
		$(".DTTT_container a").addClass("btn btn-default");

		$('#column_toggler :checkbox').change(function(){
			var oTable = $('#manhours').dataTable();
			var iCol = columns[$(this).data("column")];
			var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
			oTable.fnSetColumnVis(iCol, (bVis ? false : true));
		});

		$('#manhours .group-checkable').change(function () {
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

		$('#manhours_wrapper .dataTables_filter input').addClass("form-control input-small");
		$('#manhours_wrapper .dataTables_length select').addClass("form-control input-xsmall");
		$('#manhours_wrapper .dataTables_length select').select2({minimumResultsForSearch:-1});
	}

	//$(document).ajaxStart($.blockUI);
	$(document).ajaxStop($.unblockUI);

	return {

		init: function() {

			$('#manhours tr:gt(0) td:nth-child(5)').each(function() {
				var t = $(this).data("status");
				if (t == "2" || t == "3") $(this).closest('tr').find('td:first :checkbox').prop("disabled", true).uniform.update();
				$(this).html(statusLabel(t));
			});

			/*$('#manhours tr:gt(0) td:nth-child(4)').each(function() {
				$(this).html(getDate($(this).data("time")));
			});*/

			$('#manhours td:nth-child(6) a.btn').click(function() {
				detail("manhour", $(this).closest('tr').find("td:first :checkbox").val());
			});

			$('#manhours td:nth-child(3) a').click(function() {
				detail("activity", $(this).data("aid"));
			});

			$("#applymh-button").click(function() {
				if (!$(this).data("inited")) {
					$('.date-picker').datepicker({
						rtl: Metronic.isRTL(),
						language: "zh-CN",
						keyboardNavigation: true,
						forceParse: true,
						autoclose: true,
						todayHighlight: true
					}).on("hide", function() {
						$("body").addClass("modal-open-noscroll");
						$('#applymh form').validate().element($(this));
					});

					$.get("{U api/activity}", {}, function(data) {
						$('#aid').append($.map(data, function(v) {
							return $('<option>', {
								val: v.id,
								text: v.name
							}).data("place", v.place)
							.data("starttime", v.starttime)
							.data("endtime", v.endtime)
							.data("academy", v.academy)
							.data("sponsor", v.sponsor)
							.data("undertaker", v.undertaker)
							.data("intro", v.intro);
						}));
					}, 'json');

					$("#aid").select2({
						formatResult: function(state) {
							var markup = "<table class='select-table'><tbody>",
								e = $(state.element);
							markup += '<tr><td colspan="2"><h4>' + state.text + '</h4></td></tr>';
							markup += "<tr><td>活动社团</td><td>" + e.data("academy") + "</td></tr>";
							if (e.data("place")) markup += "<tr><td>活动地点</td><td>" + e.data("place") + "</td></tr>";
							if (e.data("starttime") != "0") markup += "<tr><td>开始时间</td><td>" + getTime(e.data("starttime")) + "</td></tr>";
							if (e.data("endtime") != "0") markup += "<tr><td>结束时间</td><td>" + getTime(e.data("endtime")) + "</td></tr>";
							if (e.data("sponsor")) markup += "<tr><td>主办者</td><td>" + e.data("sponsor") + "</td></tr>";
							if (e.data("undertaker")) markup += "<tr><td>承办者</td><td>" + e.data("undertaker") + "</td></tr>";
							if (e.data("intro")) markup += "<tr><td>活动介绍</td><td>" + nl2br(e.data("intro")) + "</td></tr>";
							markup += "</tbody></table>";
							return markup;
						},
						dropdownCssClass: "bigdrop",
						escapeMarkup: function(m) {
							return m;
						}
					}).change(function() {
						$('#applymh form').validate().element($(this));
					});

					$('#applymh form').validate({
						errorElement: 'span', //default input error message container
						errorClass: 'help-block', // default input error message class
						focusInvalid: false, // do not focus the last invalid input
						ignore: "",
						rules: {
							aid: {
								required: true
							},
							manhour: {
								required: true,
								digits: true
							},
							time: {
								required: true
							}
						},

						messages: {
							aid: {
								required: "请选择一个活动！"
							},
							manhour: {
								required: "请输入您申请的工时数",
								digits: "工时只能是数字！"
							},
							time: {
								required: "请选择您所申请的工时对应的日期"
							}
						},

						highlight: function(element) { // hightlight error inputs
							$(element).closest('.form-group').removeClass('has-success').addClass('has-error');
						},

						unhighlight: function(element) { // revert the change done by hightlight
							$(element).closest('.form-group').removeClass('has-error'); // set error class to the control group
						},

						success: function(label) {
							label.addClass('valid').closest('.form-group').removeClass('has-error').addClass('has-success');
						},

						errorPlacement: function(error, element) {
							if (element.attr("name") == "time") return error.insertAfter(element.parent());
							error.insertAfter(element);
						},

						submitHandler: function(form) {
							var url = $(form).attr("action");
							showloading();
							$.post(url + (url.indexOf("?") > -1 ? "&inajax=1" : "/inajax/1"), $(form).serialize(), function(data) {
								modalAlert(data.msg);
								if (data.errno == 0) {
									$("#applymh").modal("hide");
									/*$('#manhours').dataTable().fnAddData([
										'<input type="checkbox" class="checkboxes" name="id[]" value="' + data.id + '" />',
										$(form).find("input[name='manhour']").val(),
										'<a href="javascript:;" data-aid="' + data.aid + '" onclick="detail(\'manhour\', ' + data.aid + ')">' + data.actname + '</a>',
										getDate(data.time),
										statusLabel(2),
										'<a href="javascript:;" class="btn btn-xs default blue-stripe" onclick="detail(\'manhour\', ' + data.id + ')">详情</a>'
									]);*/
									$("#aid").select2("val", null);
									$(form).find("input,textarea").val("");
									$(form).validate().resetForm();
								}
							});
						}
					});

					$("#applymh .modal-footer .blue").click(function() {
						if ($("#applymh form").valid() == false) return false;
						$("#applymh form").submit();
					});

					$(this).data("inited", true);
				}
			});

			$("#checkmh-button").click(function() {
				if (!$(this).data("inited")) {
					$('#checkmh form').validate({
						errorElement: 'span', //default input error message container
						errorClass: 'help-block', // default input error message class
						focusInvalid: false, // do not focus the last invalid input
						ignore: "",
						rules: {
							remark: {
								required: true
							}
						},

						messages: {
							remark: {
								required: "请输入申请复查的理由！"
							}
						},

						highlight: function(element) { // hightlight error inputs
							$(element).closest('.form-group').removeClass('has-success').addClass('has-error');
						},

						unhighlight: function(element) { // revert the change done by hightlight
							$(element).closest('.form-group').removeClass('has-error'); // set error class to the control group
						},

						success: function(label) {
							label.addClass('valid').closest('.form-group').removeClass('has-error').addClass('has-success');
						},

						errorPlacement: function(error, element) {
							if (element.attr("name") == "time") return error.insertAfter(element.parent());
							error.insertAfter(element);
						},

						submitHandler: function(form) {
							showloading();
							var url = $(form).attr("action"),
								dts = $('td:first-child :checkbox:enabled:checked', $('#manhours').dataTable().fnGetNodes()).serializeArray();
							dts = dts.concat($(form).serializeArray());
							$.post(url + (url.indexOf("?") > -1 ? "&inajax=1" : "/inajax/1"), dts, function(data) {
								modalAlert(data.msg);
								if (data.errno == 0) {
									$("#checkmh").modal("hide");
									$(form).find("input,textarea").val("");
									$(form).validate().resetForm();
									$('td:first-child :checkbox:checked', $('#manhours').dataTable().fnGetNodes()).closest("tr").each(function() {
										$('#manhours').dataTable().fnUpdate(statusLabel(3), this, 4);
									});
									$('td:first-child :checkbox:checked', $('#manhours').dataTable().fnGetNodes()).prop("checked", false).prop("disabled", true).uniform.update();
									$('#manhours th:first :checkbox').prop("checked", false).uniform.update();
								}
							});
						}
					});

					$("#checkmh .modal-footer .blue").click(function() {
						if ($("#checkmh form").valid() == false) return false;
						$("#checkmh form").submit();
					});

					$(this).data("inited", true);
				}

				if ($('td:first-child :checkbox:enabled:checked', $('#manhours').dataTable().fnGetNodes()).size() == 0) {
					return modalAlert("请先勾选需要复查的工时所对应的复选框！");
				}
				$("#checkmh").modal("show");
			});

			var table = $('#manhours').dataTable({
				"order": [],
				"lengthMenu": [
					[10, 25, 50], // 每页显示数目，-1表示显示全部
					[10, 25, 50] // 对应的文字
				],
				"pageLength": 10,
				"columnDefs": [{
					'orderable': false,
					'targets': [0, 5]
				}, {
					"searchable": false,
					"targets": [0, 5]
				}]
			});
			var tt = new $.fn.dataTable.TableTools(table, {
				"sSwfPath": "assets/global/plugins/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf",
				"aButtons": [{
					"sExtends": "csv",
					"sButtonText": "导出 Excel",
					"bBomInc": true
				}]
			});
			$(tt.fnContainer()).insertBefore('#manhours_wrapper');
			$(".DTTT_container").addClass("btn-group margin-bottom-10");
			$(".DTTT_container a").addClass("btn btn-default");

			$('#manhours .group-checkable').change(function() {
				var set = $(this).attr("data-set");
				var checked = $(this).is(":checked");
				$(set).each(function() {
					if (checked) {
						$(this).attr("checked", true);
					} else {
						$(this).attr("checked", false);
					}
				});
				$.uniform.update(set);
			});

			$('#manhours_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
			$('#manhours_wrapper .dataTables_length select').addClass("form-control input-xsmall"); // modify table per page dropdown
			$('#manhours_wrapper .dataTables_length select').select2({
				minimumResultsForSearch: -1
			}); // initialize select2 dropdown

		},

		applylog: function() {
			var columns = {
				"checkbox"  : 0,
				"avatar"    : 1,
				"username"  : 2,
				"realname"  : 3,
				"gender"    : 4,
				"actname"   : 5,
				"time"      : 6,
				"manhour"   : 7,
				"applytime" : 8,
				"status"    : 9,
				"remark"    : 10
			};

			/*$("#manhours tr:gt(0)" + nthchild("time", columns) + ", #manhours tr:gt(0)" + nthchild("applytime", columns)).each(function() {
				$(this).text(getTime($(this).data("time")));
			});*/

			$('#manhours tr:gt(0)' + nthchild("status", columns)).each(function() {
				var t = $(this).data("status");
				$(this).html(statusLabel(t));
			});

			$('#manhours tr:gt(0)' + nthchild("actname", columns) + " a").click(function() {
				detail("activity", $(this).data("aid"));
			});

			initDT(columns, 1);

			$("#showall").change(function() {
				window.location.replace("{U manhour/applylog}&showall=" + ($(this).prop("checked") ? "1" : "0"));
			});

			$("#passmh-button, #rejectmh-button").click(function() {
				var e = $('td:first-child :checkbox:enabled:checked', $('#manhours').dataTable().fnGetNodes());
				if(e.size() == 0) {
					return modalAlert("请至少选择一个可选的申报记录");
				}
				var id = [];
				e.each(function () {
					id.push($(this).val());
				});
				$("#verifymh input[name='type']").val($(this).prop("id").substr(0, 4)=="pass" ? "pass" : "reject");
				$("#verifymh input[name='ids']").val(id.join(","));
				$("#verifymh").modal("show");
			});
			$("#editmh-button").click(function() {
				modalAlert("出于对用户的考虑和负责，不允许管理员直接编辑用户的数据<br/>你可以拒绝或删除此记录，然后联系用户重新申请");
			});
			$("#delmh-button").click(function() {
				var e = $('td:first-child :checkbox:enabled:checked', $('#manhours').dataTable().fnGetNodes());
				if(e.size() == 0) {
					return modalAlert("请至少选择一个可选的申报记录");
				}
				$("#delmh").modal("show");
			});

			$("#verifymh .modal-footer button.blue").click(function () {
				var statusid = 0;
				if($("#verifymh input[name='type']").val() == "pass") {
					statusid = 1;
				} else {
					statusid = 4;
					if($("#verifymh textarea").val() == "")
						return modalAlert("请填写拒绝理由");
				}

				showloading();
				$.post("{U manhour/applylog?inajax=1}", $("#verifymh form").serialize(), function (data) {
					modalAlert(data.msg);
					if(!data.errno) {
						var e = $('td:first-child :checkbox:checked', $('#manhours').dataTable().fnGetNodes());
						if(statusid) e.closest("tr").each(function() {
							$('#manhours').dataTable().fnUpdate(statusLabel(statusid, status), this, columns["status"]);
						});
						e.prop("checked", false).prop("disabled", true).uniform.update();
						$('#manhours th:first :checkbox').prop("checked", false).uniform.update();
						$("#verifymh").modal("hide");
					}
				}, "json");
			});
			$("#delmh .modal-footer button.red").click(function () {
				showloading();
				var id = [];
				$('td:first-child :checkbox:enabled:checked', $('#manhours').dataTable().fnGetNodes()).each(function () {
					id.push($(this).val());
				});
				$.post("{U manhour/applylog?inajax=1}", {type:"del",ids:id.join(",")}, function (data) {
					modalAlert(data.msg);
					if(!data.errno) {
						var e = $('td:first-child :checkbox:checked', $('#manhours').dataTable().fnGetNodes());
						e.closest("tr").each(function() {
							$('#manhours').DataTable().row(this).remove();
						});
						$('#manhours').DataTable().draw();
						$('#manhours th:first :checkbox').prop("checked", false).uniform.update();
						$("#delmh").modal("hide");
					}
				}, "json");
			});
		},

		checklog: function() {
			var columns = {
				"checkbox"  : 0,
				"avatar"    : 1,
				"username"  : 2,
				"realname"  : 3,
				"gender"    : 4,
				"actname"   : 5,
				"time"      : 6,
				"manhour"   : 7,
				"applytime" : 8,
				"status"    : 9,
				"remark"    : 10
			};

			/*$("#manhours tr:gt(0)" + nthchild("time", columns) + ", #manhours tr:gt(0)" + nthchild("applytime", columns)).each(function() {
				$(this).text(getTime($(this).data("time")));
			});*/

			$('#manhours tr:gt(0)' + nthchild("status", columns)).each(function() {
				var t = $(this).data("status");
				$(this).html(statusLabel(t));
			});

			$('#manhours tr:gt(0)' + nthchild("actname", columns) + " a").click(function() {
				detail("activity", $(this).data("aid"));
			});

			initDT(columns, 1);

			$("#showall").change(function() {
				window.location.replace("{U manhour/checklog}&showall=" + ($(this).prop("checked") ? "1" : "0"));
			});

			$("#passmh-button, #rejectmh-button").click(function() {
				var e = $('td:first-child :checkbox:enabled:checked', $('#manhours').dataTable().fnGetNodes());
				if(e.size() == 0) {
					return modalAlert("请至少选择一个可选的申报记录");
				}
				var id = [];
				e.each(function () {
					id.push($(this).val());
				});
				$("#verifymh input[name='type']").val($(this).prop("id").substr(0, 4)=="pass" ? "pass" : "reject");
				$("#verifymh input[name='ids']").val(id.join(","));
				$("#verifymh").modal("show");
			});
			$("#editmh-button").click(function() {
				modalAlert("出于对用户的考虑和负责，不允许管理员直接编辑用户的数据<br/>你可以拒绝或删除此记录，然后联系用户重新申请");
			});
			$("#delmh-button").click(function() {
				var e = $('td:first-child :checkbox:enabled:checked', $('#manhours').dataTable().fnGetNodes());
				if(e.size() == 0) {
					return modalAlert("请至少选择一个可选的申报记录");
				}
				$("#delmh").modal("show");
			});

			$("#verifymh .modal-footer button.blue").click(function () {
				var statusid = 0;
				if($("#verifymh input[name='type']").val() == "pass") {
					statusid = 1;
				} else {
					statusid = 5;
					if($("#verifymh textarea").val() == "")
						return modalAlert("请填写拒绝理由");
				}

				showloading();
				$.post("{U manhour/checklog?inajax=1}", $("#verifymh form").serialize(), function (data) {
					modalAlert(data.msg);
					if(!data.errno) {
						var e = $('td:first-child :checkbox:checked', $('#manhours').dataTable().fnGetNodes());
						if(statusid) e.closest("tr").each(function() {
							$('#manhours').dataTable().fnUpdate(statusLabel(statusid, status), this, columns["status"]);
						});
						e.prop("checked", false).prop("disabled", true).uniform.update();
						$('#manhours th:first :checkbox').prop("checked", false).uniform.update();
						$("#verifymh").modal("hide");
					}
				}, "json");
			});
			$("#delmh .modal-footer button.red").click(function () {
				showloading();
				var id = [];
				$('td:first-child :checkbox:enabled:checked', $('#manhours').dataTable().fnGetNodes()).each(function () {
					id.push($(this).val());
				});
				$.post("{U manhour/checklog?inajax=1}", {type:"del",ids:id.join(",")}, function (data) {
					modalAlert(data.msg);
					if(!data.errno) {
						var e = $('td:first-child :checkbox:checked', $('#manhours').dataTable().fnGetNodes());
						e.closest("tr").each(function() {
							$('#manhours').DataTable().row(this).remove();
						});
						$('#manhours').DataTable().draw();
						$('#manhours th:first :checkbox').prop("checked", false).uniform.update();
						$("#delmh").modal("hide");
					}
				}, "json");
			});
		},

		manage: function() {
			var columns = {
				"checkbox"  : 0,
				"avatar"    : 1,
				"username"  : 2,
				"realname"  : 3,
				"gender"    : 4,
				"sno"       : 5,
				"zybj"      : 6,
				"actname"   : 7,
				"time"      : 8,
				"manhour"   : 9,
				"applytime" : 10,
				"status"    : 11,
				"remark"    : 12,
				"operator"  : 13,
				"verifytime": 14,
				"verifytext": 15
			};

			/*$("#manhours tr:gt(0)" + nthchild("time", columns) + ", #manhours tr:gt(0)" + nthchild("applytime", columns) + ", #manhours tr:gt(0)" + nthchild("verifytime", columns)).each(function() {
				$(this).text(getTime($(this).data("time")));
			});*/

			$('#manhours tr:gt(0)' + nthchild("status", columns)).each(function() {
				var t = $(this).data("status");
				$(this).html(statusLabel(t));
			});

			$('#manhours tr:gt(0)' + nthchild("actname", columns) + " a").click(function() {
				detail("activity", $(this).data("aid"));
			});

			initDT(columns, 1);

			$("#passmh-button, #rejectmh-button").click(function() {
				var e = $('td:first-child :checkbox:enabled:checked', $('#manhours').dataTable().fnGetNodes());
				if(e.size() === 0) {
					return modalAlert("请至少选择一个工时记录");
				}
				var id = [], type = $(this).prop("id").substr(0, 4)=="pass" ? "pass" : "reject";
				e.each(function () {
					id.push($(this).val());
				});
				$("#verifymh input[name='type']").val(type);
				$("#verifymh input[name='ids']").val(id.join(","));
				if(type == "pass") {
					$("#verifymh .radio-list").closest(".form-group").hide();
				} else {
					$("#verifymh .radio-list").closest(".form-group").show();
				}
				$("#verifymh").modal("show");
			});
			$("#delmh-button").click(function() {
				var e = $('td:first-child :checkbox:enabled:checked', $('#manhours').dataTable().fnGetNodes());
				if(e.size() === 0) {
					return modalAlert("请至少选择一个工时记录");
				}
				$("#delmh").modal("show");
			});

			$("#verifymh form").submit(function () {
				var statusid = 0;
				if($("#verifymh input[name='type']").val() == "pass") {
					statusid = 1;
				} else {
					statusid = $("#verifymh .radio-list :radio:checked").val()=="0" ? 4 : 5;
					if($("#verifymh textarea").val() === "") {
						modalAlert("请填写拒绝理由");
						return false;
					}
				}

				showloading();
				$.post("{U manhour/manage?inajax=1}", $("#verifymh form").serialize(), function (data) {
					modalAlert(data.msg);
					if(!data.errno) {
						var e = $('td:first-child :checkbox:checked', $('#manhours').dataTable().fnGetNodes());
						if(statusid) e.closest("tr").each(function() {
							$('#manhours').dataTable().fnUpdate(statusLabel(statusid, status), this, columns["status"]);
						});
						e.prop("checked", false).prop("disabled", true).uniform.update();
						$('#manhours th:first :checkbox').prop("checked", false).uniform.update();
						$("#verifymh").modal("hide");
					}
				}, "json");

				return false;
			});
			$("#delmh .modal-footer button.red").click(function () {
				showloading();
				var id = [];
				$('td:first-child :checkbox:enabled:checked', $('#manhours').dataTable().fnGetNodes()).each(function () {
					id.push($(this).val());
				});
				$.post("{U manhour/manage?inajax=1}", {type:"del",ids:id.join(",")}, function (data) {
					modalAlert(data.msg);
					if(!data.errno) {
						var e = $('td:first-child :checkbox:checked', $('#manhours').dataTable().fnGetNodes());
						e.closest("tr").each(function() {
							$('#manhours').DataTable().row(this).remove();
						});
						$('#manhours').DataTable().draw();
						$('#manhours th:first :checkbox').prop("checked", false).uniform.update();
						$("#delmh").modal("hide");
					}
				}, "json");
			});
		},

		initimportmh: function() {
			showloading();

			$('.date-picker').datepicker({
				rtl: Metronic.isRTL(),
				language: "zh-CN",
				keyboardNavigation: true,
				forceParse: true,
				autoclose: true,
				todayHighlight: true
			}).on("hide", function() {
				$('#importmh form').validate().element($(this));
			});

			$.get("{U api/activity}", {}, function(data) {
				$('#aid').append($.map(data, function(v) {
					return $('<option>', {
						val: v.id,
						text: v.name
					}).data("place", v.place)
					.data("starttime", v.starttime)
					.data("endtime", v.endtime)
					.data("academy", v.academy)
					.data("sponsor", v.sponsor)
					.data("undertaker", v.undertaker)
					.data("intro", v.intro);
				}));
			}, 'json');

			$("#aid").select2({
				formatResult: function(state) {
					var markup = "<table class='select-table'><tbody>",
						e = $(state.element);
					markup += '<tr><td colspan="2"><h4>' + state.text + '</h4></td></tr>';
					markup += "<tr><td>活动社团</td><td>" + e.data("academy") + "</td></tr>";
					if (e.data("place")) markup += "<tr><td>活动地点</td><td>" + e.data("place") + "</td></tr>";
					if (e.data("starttime") != "0") markup += "<tr><td>开始时间</td><td>" + getTime(e.data("starttime")) + "</td></tr>";
					if (e.data("endtime") != "0") markup += "<tr><td>结束时间</td><td>" + getTime(e.data("endtime")) + "</td></tr>";
					if (e.data("sponsor")) markup += "<tr><td>主办者</td><td>" + e.data("sponsor") + "</td></tr>";
					if (e.data("undertaker")) markup += "<tr><td>承办者</td><td>" + e.data("undertaker") + "</td></tr>";
					if (e.data("intro")) markup += "<tr><td>活动介绍</td><td>" + nl2br(e.data("intro")) + "</td></tr>";
					markup += "</tbody></table>";
					return markup;
				},
				dropdownCssClass: "bigdrop",
				escapeMarkup: function(m) {
					return m;
				}
			}).change(function() {
				$('#importmh form').validate().element($(this));
			});

			$('#importmh form').validate({
				errorElement: 'span', //default input error message container
				errorClass: 'help-block', // default input error message class
				focusInvalid: false, // do not focus the last invalid input
				ignore: "",
				rules: {
					aid: {
						required: true
					},
					time: {
						required: true
					},
					mh_excel: {
						required: true,
						accept: "application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
					}
				},

				messages: {
					aid: {
						required: "请选择一个活动！"
					},
					time: {
						required: "请选择您所添加工时对应的日期"
					},
					mh_excel: {
						required: "请选择需要导入的电子表格文件",
						accept: "所选文件类型不被支持，请重新选择"
					}
				},

				highlight: function(element) { // hightlight error inputs
					$(element).closest('.form-group').removeClass('has-success').addClass('has-error');
				},

				unhighlight: function(element) { // revert the change done by hightlight
					$(element).closest('.form-group').removeClass('has-error'); // set error class to the control group
				},

				success: function(label) {
					label.addClass('valid').closest('.form-group').removeClass('has-error').addClass('has-success');
				},

				errorPlacement: function(error, element) {
					if (element.attr("name") == "time") return error.insertAfter(element.parent());
					error.insertAfter(element);
				},

				submitHandler: function(form) {
					showloading();
					$(form).ajaxSubmit({
						type: "post",
						dataType: "json",
						success: function(data) {
							$.unblockUI();
							modalAlert(data.msg);
						}
					});
				}
			});
		}

	};

}();