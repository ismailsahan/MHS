var Members = function () {

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

	function statusLabel(id, status) {
		id = parseInt(id);
		if(!status[id]) id = status.length-1;
		return '<span class="label label-sm label-'+status[id][0]+'" data-status="'+id+'">'+status[id][1]+'</span>';
	}

	function gmdate(timestamp, format) {
		var t = new Date;
		t.setTime(timestamp + "000");
		return t.format(format);
	}

	function getDate(time) {
		if(!time || time == "0") return "-";
		return gmdate(time, "yyyy年MM月dd日");
	}

	function getTime(time) {
		if(!time || time == "0") return "-";
		return gmdate(time, "yyyy年MM月dd日 hh:mm:ss");
	}

	function detail(type, id) {
		showloading();
		if(type == "manhour") {
			$.post("{U api/manhour}", {"id":id}, function(data){
				if(!data.id) return window.location.reload();
				$("#mh-modal p.col-md-9").each(function() {
					var mh = $(this).data("mh");
					if(mh=="time") {
						data.time = getDate(data.time);
					}else if(mh=="applytime" || mh=="verifytime") {
						data[mh] = getTime(data[mh]);
					}else if(mh == "status") {
						data.status = statusLabel(data.status);
					}else if(mh=="remark" || mh=="verifytext") {
						data[mh] = data[mh] ? nl2br(stripslashes(data[mh])) : "-";
					}
					$(this).html(data[mh]);
				});
				$("#mh-modal").modal("show");
			}, 'json');
		} else if(type == "activity") {
			$.post("{U api/activity}", {"id":id}, function(data){
				if(!data.id) return window.location.reload();
				$("#act-modal p.col-md-9").each(function() {
					var act = $(this).data("act");
					if(act=="starttime" || act=="endtime") {
						data[act] = getTime(data[act]);
					}else if(!data[act]) {
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

	function initData(columns) {

		return false;
		$("#users tr:gt(0)" + nthchild("academy", columns)).each(function() {
			$.ajax({url:"{U api/getnamebyid?type=academy}",data:{id:$(this).data("academy")},type:"GET",async:false,cache:true,context:this,dataType:"json",success:function(data) {
				$(this).text(data.name ? data.name : "-");
			}});
		});

	}

	function initDT(columns) {
		var table = $('#users').dataTable({
			"order": [[columns["uid"], 'asc']],
			"lengthMenu": [
				[10, 25, 50],// 每页显示数目，-1表示显示全部
				[10, 25, 50] // 对应的文字
			],
			"pageLength": 10,
			"columnDefs": [{
				'orderable': false,
				'targets': [columns["checkbox"], columns["extra"], columns["avatar"]]
			}, {
				"searchable": false,
				"targets": [columns["checkbox"], columns["extra"], columns["avatar"]]
			}]
		});
		var tt = new $.fn.dataTable.TableTools(table, {
			"sSwfPath": "assets/global/plugins/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf",
			"aButtons": [
				{
					"sExtends": "csv",
					"sButtonText": "导出 Excel"
				}
			]
		});
		$(tt.fnContainer()).insertBefore('#users_wrapper');
		$(".DTTT_container").addClass("btn-group margin-bottom-10");
		$(".DTTT_container a").addClass("btn btn-default");

		$('#column_toggler :checkbox').change(function(){
			var oTable = $('#users').dataTable();
			var iCol = columns[$(this).data("column")];
			var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
			oTable.fnSetColumnVis(iCol, (bVis ? false : true));
		});

		$('#users .group-checkable').change(function () {
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

		$('#users_wrapper .dataTables_filter input').addClass("form-control input-small");
		$('#users_wrapper .dataTables_length select').addClass("form-control input-xsmall");
		$('#users_wrapper .dataTables_length select').select2({minimumResultsForSearch:-1});
	}

	$(document).ajaxStop($.unblockUI);

	return {

		init: function () {

			/*$('#users tr:gt(0)'+nthchild("gender", columns)).each(function() {
				var t = $(this).data("status");
				if(t=="2" || t=="3") $(this).closest('tr').find('td:first :checkbox').prop("disabled", true).uniform.update();
				$(this).html(statusLabel(t));
			});*/

			//$('#users tr:gt(0)'+nthchild("grade", columns)).each(function() {
			//	$.ajax({url:"{U api/getnamebyid}",data:{type:"grade"},type:"GET",async:false,cache:true,context:this,dataType:"json",global:false,success:function(data) {
			//		$(this).text(data[$(this).data("grade")]);
			//	}});
			//});

			var columns = {
				"checkbox"	: 0,
				"avatar"	: 1,
				"uid"		: 2,
				"username"	: 3,
				"realname"	: 4,
				"gender"	: 5,
				"qq"		: 6,
				"email"		: 7,
				"manhour"	: 8,
				"studentid"	: 9,
				"grade"		: 10,
				"academy"	: 11,
				"specialty"	: 12,
				"class"		: 13,
				"league"	: 14,
				"department": 15,
				"mobile"	: 16,
				"lastlogin"	: 17,
				"extra"		: 18
			};

			initData(columns);

			/*$("#users tr:gt(0)" + nthchild("lastlogin", columns)).each(function() {
				$(this).text(getTime($(this).data("time")));
			});*/

			$("#users tr:gt(0)" + nthchild("extra", columns)).each(function() {
				$(this).find(".blue-stripe").click(function() {
					return modalAlert("出于对用户的考虑，不允许管理员直接修改其他用户的资料<br/>你可以联系用户，通知其自行修改");
					$("#user-modal");
					$("#user-modal").modal("show");
				});
				$(this).find(".red-stripe").click(function() {
					$("#deluser-confirm :checkbox").prop("checked", false);
					$("#deluser-confirm em").text($(this).closest("tr").find(nthchild("username", columns)).html() + "(" + $(this).closest("tr").find(nthchild("realname", columns)).html() + ")").data("uid", $(this).closest("tr").find(":checkbox:first").val());
					$("#deluser-confirm").modal("show");
				});
			});

			$("#deluser-confirm .modal-footer .red").click(function() {
				showloading();
				$.post("{U members/user?inajax=1}", {type:"deluser", uid:$("#deluser-confirm em").data("uid"), deluc:$("#deluser-confirm :checkbox").prop("checked")?1:0}, function(data) {
					modalAlert(data.msg);
				}, 'JSON');
			});
			$("#user-modal .modal-footer .red").click(function() {
				showloading();
				$.post("{U members/user?inajax=1}", {type:"edituser", uid:$("#user-modal").data("uid")}, function(data) {
					modalAlert(data.msg);
				}, 'JSON');
			});

			$("#addmh-button").click(function() {
				if (!$(this).data("inited")) {
					showloading();

					$('.date-picker').datepicker({
						rtl: Metronic.isRTL(),
						language: "zh-CN",
						keyboardNavigation: true,
						forceParse: true,
						autoclose: true,
						todayHighlight: true
					}).on("hide", function() {
						$("body").addClass("modal-open-noscroll");
						$('#addmh form').validate().element($(this));
					});

					$.get("{U api/activity}", {}, function(data) {
						$('#aid').append($.map(data, function(v) {
							return $('<option>', {
								val: v.id,
								text: v.name
							}).data("place", v.place)
							.data("starttime", v.starttime)
							.data("endtime", v.endtime)
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
						$('#addmh form').validate().element($(this));
					});

					$('#addmh form').validate({
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
								required: "请输入工时数",
								digits: "工时只能是数字！"
							},
							time: {
								required: "请选择您所添加工时对应的日期"
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
							$.post($(form).attr("action"), $(form).serialize(), function(data) {
								modalAlert(data.msg);
								if (data.errno == 0) {
									$("#addmh").modal("hide");
									$("#aid").select2("val", null);
									$(form).find("input,textarea").val("");
									$(form).validate().resetForm();
								}
							});
						}
					});

					$(this).data("inited", true);
				}

				var e = $('td:first-child :checkbox:enabled:checked', $('#users').dataTable().fnGetNodes());
				if(e.size() == 0) {
					return modalAlert("请至少选择一个用户");
				}
				var ls  = [];
				var uid = [];
				e.each(function () {
					uid.push($(this).closest("tr").find(nthchild("uid", columns)).html());
					ls.push($(this).closest("tr").find(nthchild("realname", columns)).html());
				});
				$("#addmh input[name='uid']").val(uid.join(","));
				$("#addmh .form-control-static").text(ls.join(", "));
				$("#addmh").modal("show");
			});

			initDT(columns);

		},

		initverify: function () {
			var columns = {
				"checkbox"	: 0,
				"avatar"	: 1,
				"uid"		: 2,
				"username"	: 3,
				"realname"	: 4,
				"gender"	: 5,
				"qq"		: 6,
				"email"		: 7,
				"status"	: 8,
				"studentid"	: 9,
				"grade"		: 10,
				"academy"	: 11,
				"specialty"	: 12,
				"class"		: 13,
				"league"	: 14,
				"department": 15,
				"applytime"	: 16,
				"remark"	: 17,
				"operator"	: 18,
				"verifytime": 19,
				"verifytext": 20
			};
			var status = [["primary","等待审核"], ["success","已通过审核"], ["danger","未通过审核"], ["danger","未知错误"]];

			initData(columns);

			$("#users tr:gt(0)" + nthchild("status", columns)).each(function() {
				$(this).html(statusLabel($(this).data("status"), status));
			});

			/*$("#users tr:gt(0)" + nthchild("applytime", columns) + ", #users tr:gt(0)" + nthchild("verifytime", columns)).each(function() {
				$(this).text(getTime($(this).data("time")));
			});*/

			initDT(columns);

			$("#showall").change(function() {
				window.location.replace("{U members/verifyuser}&showall=" + ($(this).prop("checked") ? "1" : "0"));
			});

			$("#passuser-button, #rejectuser-button").click(function() {
				var e = $('td:first-child :checkbox:enabled:checked', $('#users').dataTable().fnGetNodes());
				if(e.size() == 0) {
					return modalAlert("请至少选择一个有效的申请项");
				}
				var ls = [];
				var uid= [];
				e.each(function () {
					uid.push($(this).closest("tr").find(nthchild("uid", columns)).html());
					ls.push($(this).closest("tr").find(nthchild("realname", columns)).html());
				});
				$("#verifyuser input[name='type']").val($(this).prop("id").substr(0, 4)=="pass" ? "pass" : "reject");
				$("#verifyuser input[name='uids']").val(uid.join(","));
				$("#verifyuser .form-control-static").text(ls.join(", "));
				$("#verifyuser").modal("show");
			});
			$("#edituser-button").click(function() {
				modalAlert("出于对用户的考虑，不允许管理员直接编辑用户的数据<br/>你可以联系用户重新申请");
				//$("#edituser").modal("show");
			});
			$("#delog-button").click(function() {
				var e = $('td:first-child :checkbox:enabled:checked', $('#users').dataTable().fnGetNodes());
				if(e.size() == 0) {
					return modalAlert("请至少选择一个有效的申请条目");
				}
				var ls = [];
				e.each(function () {
					ls.push($(this).closest("tr").find(nthchild("realname", columns)).html());
				});
				$("#ready2del").text(ls.join(", "));
				$("#delog").modal("show");
			});

			$("#verifyuser .modal-footer button.blue").click(function () {
				var statusid = 0;
				if($("#verifyuser input[name='type']").val() == "pass") {
					statusid = 1;
				} else {
					statusid = 2;
					if($("#verifyuser textarea").val() == "")
						return modalAlert("请填写拒绝理由");
				}

				showloading();
				$.post("{U members/verifyuser?inajax=1}", $("#verifyuser form").serialize(), function (data) {
					modalAlert(data.msg);
					if(!data.errno) {
						var e = $('td:first-child :checkbox:checked', $('#users').dataTable().fnGetNodes());
						if(statusid) e.closest("tr").each(function() {
							$('#users').dataTable().fnUpdate(statusLabel(statusid, status), this, columns["status"]);
						});
						e.prop("checked", false).prop("disabled", true).uniform.update();
						$('#users th:first :checkbox').prop("checked", false).uniform.update();
						$("#verifyuser").modal("hide");
					}
				}, "json");
			});
			$("#delog .modal-footer button.red").click(function () {
				showloading();
				var uid = [];
				$('td:first-child :checkbox:enabled:checked', $('#users').dataTable().fnGetNodes()).each(function () {
					uid.push($(this).closest("tr").find(nthchild("uid", columns)).html());
				});
				$.post("{U members/verifyuser?inajax=1}", {type:"del",uids:uid.join(",")}, function (data) {
					modalAlert(data.msg);
					if(!data.errno) {
						var e = $('td:first-child :checkbox:checked', $('#users').dataTable().fnGetNodes());
						e.closest("tr").each(function() {
							$('#users').DataTable().row(this).remove();
						});
						$('#users').DataTable().draw();
						$('#users th:first :checkbox').prop("checked", false).uniform.update();
						$("#delog").modal("hide");
					}
				}, "json");
			});
		},

		initadmingrp: function () {
			var columns = {
				"checkbox"	: 0,
				"uid"		: 1,
				"name"		: 2,
				"parent"	: 3,
				"note"		: 4,
				"extra"		: 5
			};

			$("#addgrp-button").click(function() {
				$('#grpdetail form').get(0).reset();
				$('#grpdetail form').validate().resetForm();
				$('#grpdetail .form-group').removeClass('has-error').removeClass('has-success');
				$("#grpdetail [name='id']").val("");
				$('#grpdetail form').find(":checkbox").uniform.update();
				$("#grpdetail").modal("show");
			});
			$("#users tr:gt(0)" + nthchild("extra", columns) + " > a:nth-child(1)").click(function() {
				showloading();
				var agid = $(this).data("gid");
				$.post("{U members/admingroup?inajax=1}", {"agid":agid}, function(data) {
					if(data.errno) return modalAlert(data.msg);
					$('#grpdetail form').validate().resetForm();
					$('#grpdetail .form-group').removeClass('has-error').removeClass('has-success');
					$("#grpdetail [name='id']").val(data.gid);
					$("#grpdetail [name='name']").val(data.name);
					$("#grpdetail [name='note']").val(data.note);
					$("#grpdetail [name='permit[]']").each(function() {
						$(this).prop("checked", $.inArray($(this).val(), data.permit) > -1 ? true : false);
					});
					$("#grpdetail [name='formula']").val(data.formula);
					$("#grpdetail [name='parent']").val(data.parent);
					$('#grpdetail .select2').trigger("change");
					$("#grpdetail").modal("show");
				}, "json");
			});
			$("#users tr:gt(0)" + nthchild("extra", columns) + " > a:nth-child(2)").click(function() {
				showloading();
				$('#grpmem table').DataTable().ajax.url("{U members/admingroup?inajax=1&agrpmem=1&gid=}" + $(this).data("gid")).load();
				$("#grpmem-user [name='gid']").val($(this).data("gid"));
				$("#grpmem").modal("show");
			});
			$("#grpmem button.blue, #grpmem button.red").click(function() {
				$('#grpmem-user form').validate().resetForm();
				$('#grpmem-user .form-group').removeClass('has-error').removeClass('has-success');
				$("#grpmem-user [name='uid']").val("");
				$("#grpmem-user [name='opmethod']").val($(this).is(".blue") ? "add" : "remove");
				$("#grpmem-user").modal("show");
			})
			$('#grpmem-user form').validate({
				errorElement: 'span',
				errorClass: 'help-block',
				focusInvalid: false,
				ignore: "",
				rules: {
					uid: {
						required: true,
						digits: true,
						min: 1
					}
				},
				messages: {
					uid: {
						required: "UID 不能为空",
						digits: "UID 只能是数字",
						min: "UID 大于或等于 1"
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
					error.insertAfter(element);
				},
				submitHandler: function(form) {
					showloading();
					$.post($(form).attr("action"), $(form).serialize(), function(data) {
						modalAlert(data.msg);
						if (!data.errno) {
							$("#grpmem-user").modal("hide");
							$("#alert-modal").on("hide.bs.modal", function() {
								$('#grpmem table').DataTable().ajax.reload();
								$("#alert-modal").off("hide.bs.modal");
							});
						}
					});
				}
			});

			var grps = {};
			$("#users tr:gt(0)" + nthchild("name", columns)).each(function() {
				var t = $(this), id = t.data("gid"), v = t.html();
				if(id!="0" && !grps[id]) grps[id] = v;
			});
			$('#grpdetail .select2').append($.map(grps, function(v, k) {
				return $('<option>', {
					val: k,
					text: v
				});
			})).change(function() {
				var gid = $(this).val();
				if(gid == "") return;
				showloading();
				$.post("{U members/admingroup?inajax=1}", {"agid":gid}, function(data) {
					if(data.errno) return;
					$("#grpdetail [name='permit[]']").each(function() {
						if($.inArray($(this).val(), data.permit) == -1) {
							$(this).prop("checked", false).prop("disabled", true);
						} else {
							$(this).prop("disabled", false);
						}
					});
					$("#grpdetail form :checkbox").uniform.update();
				}, "json");
			})/*.select2({
				minimumResultsForSearch: -1,
				allowClear: false
			})*/;

			initDT(columns);

			$.validator.addMethod("notEqualTo", function(value, element, param) {
				return this.optional(element) || value != $(param).val();
			}, "");
			$('#grpdetail form').validate({
				errorElement: 'span',
				errorClass: 'help-block',
				focusInvalid: false,
				ignore: "",
				rules: {
					name: {
						required: true
					},
					parent: {
						required: true,
						notEqualTo: "#grpdetail [name='id']"
					},
					note: {},
					formula: {},
					"permit[]": {
						required: true
					}
				},

				messages: {
					name: {
						required: "组头衔不能为空"
					},
					parent: {
						required: "直属上级不能为空",
						notEqualTo: "直属上级不能是自身管理组"
					},
					note: {},
					formula: {},
					"permit[]": {
						required: "访问权限不能为空"
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
					error.insertAfter(element.is(":checkbox") ? element.closest(".checkbox-list") : element);
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

			$("#delgrp-button").click(function() {
				var e = $('td:first-child :checkbox:enabled:checked', $('#users').dataTable().fnGetNodes());
				if(e.size() == 0) {
					return modalAlert("请至少选择一个组");
				}
				$("#delgrp").modal("show");
			});
			$("#delgrp .modal-footer button.red").click(function () {
				showloading();
				var id = [];
				$('td:first-child :checkbox:enabled:checked', $('#users').dataTable().fnGetNodes()).each(function () {
					id.push($(this).val());
				});
				$.post("{U members/admingroup?inajax=1}", {ids:id.join(",")}, function (data) {
					modalAlert(data.msg);
					if(!data.errno) {
						$("#delmh").modal("hide");
						$("#alert-modal").on("hide.bs.modal", function() {
							window.location.reload();
						});
					}
				}, "json");
			});

			$('#showgraph-button').click(function() {
				if(!$(this).data('inited')) {
					showloading();
					return $.post("{U members/agrp}", {}, function(data) {
						$("#agrp-tree").jstree({
							'core' : {
								'data' : data
							}
						});
						$("#showgraph-button").data('inited', 1);
						$("#agrp-tree-modal").modal("show");
					}, "JSON");
				}
				$("#agrp-tree-modal").modal("show");
			});

		}

	};

}();