var Members = function () {

	function modalAlert(msg) {
		$("#alert-modal .modal-body .col-md-12").html(msg);
		$("#alert-modal").modal("show");
	}

	function showloading() {
		$.blockUI({
			message: '<img src="assets/img/ajax-loading.gif" />',
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

		$("#users tr:gt(0)" + nthchild("gender", columns)).each(function() {
			var gender="未知";
			switch($(this).data("gender")){
				case 1: gender="男";break;
				case 2: gender="女";break;
			}
			$(this).text(gender);
		});

		$("#users tr:gt(0)" + nthchild("grade", columns)).each(function() {
			$.ajax({url:"{U api/getnamebyid?type=grade}",data:{id:$(this).data("grade")},type:"GET",async:false,cache:true,context:this,dataType:"json",success:function(data) {
				$(this).text(data.name ? data.name : "-");
			}});
		});

		$("#users tr:gt(0)" + nthchild("academy", columns)).each(function() {
			$.ajax({url:"{U api/getnamebyid?type=academy}",data:{id:$(this).data("academy")},type:"GET",async:false,cache:true,context:this,dataType:"json",success:function(data) {
				$(this).text(data.name ? data.name : "-");
			}});
		});

		$("#users tr:gt(0)" + nthchild("specialty", columns)).each(function() {
			$.ajax({url:"{U api/getnamebyid?type=specialty}",data:{id:$(this).data("specialty")},type:"GET",async:false,cache:true,context:this,dataType:"json",success:function(data) {
				$(this).text(data.name ? data.name : "-");
			}});
		});

		$("#users tr:gt(0)" + nthchild("class", columns)).each(function() {
			$.ajax({url:"{U api/getnamebyid?type=class}",data:{id:$(this).data("class")},type:"GET",async:false,cache:true,context:this,dataType:"json",success:function(data) {
				$(this).text(data.name ? data.name : "-");
			}});
		});

		$("#users tr:gt(0)" + nthchild("league", columns)).each(function() {
			$.ajax({url:"{U api/getnamebyid?type=league}",data:{id:$(this).data("league")},type:"GET",async:false,cache:true,context:this,dataType:"json",success:function(data) {
				var arr = [];
				$.each(data.name, function(k, v) {
					arr.push(v.name);
				});
				arr = arr.join(", ");
				$(this).text(arr ? arr : "-");
			}});
		});

		$("#users tr:gt(0)" + nthchild("department", columns)).each(function() {
			$.ajax({url:"{U api/getnamebyid?type=department}",data:{id:$(this).data("department")},type:"GET",async:false,cache:true,context:this,dataType:"json",success:function(data) {
				var arr = [];
				$.each(data.name, function(k, v) {
					arr.push(v.name);
				});
				arr = arr.join(", ");
				$(this).text(arr ? arr : "-");
			}});
		});

	}

	function initDT(columns) {
		$('#users').dataTable({
			"aLengthMenu": [
				[10, 25, 50],// 每页显示数目，-1表示显示全部
				[10, 25, 50] // 对应的文字
			],
			"aaSorting": [[columns["uid"], 'asc']],
			"iDisplayLength": 10,
			"sPaginationType": "bootstrap",
			//"aaSortingFixed": [[0,'desc']],
			"aoColumnDefs": [{
				'bSortable': false,
				'aTargets': [columns["checkbox"], columns["extra"], columns["avatar"]]
			}]
		});

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
	$(document).ajaxError(function() {
		modalAlert("向服务器请求数据时发生了错误，请稍候再试");
	});

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

			$("#users tr:gt(0)" + nthchild("lastlogin", columns)).each(function() {
				$(this).text(getTime($(this).data("time")));
			});

			initDT(columns);

			$('#users td:nth-child(6) a.btn').click(function() {
				detail("manhour", $(this).closest('tr').find("td:first :checkbox").val());
			});

			$('#users td:nth-child(3) a').click(function() {
				detail("activity", $(this).data("aid"));
			});

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

			$("#users tr:gt(0)" + nthchild("applytime", columns) + ", #users tr:gt(0)" + nthchild("verifytime", columns)).each(function() {
				$(this).text(getTime($(this).data("time")));
			});

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
				modalAlert("此功能暂未开放");
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
						var p = $('#users').dataTable().fnGetNodes();
						var e = $('td:first-child :checkbox:checked', p.reverse());
						p = $(p);
						e.closest("tr").each(function() {
							$('#users').dataTable().fnDeleteRow(p.index(this));
						});
						$('#users th:first :checkbox').prop("checked", false).uniform.update();
						$("#delog").modal("hide");
					}
				}, "json");
			});
		}

	};

}();