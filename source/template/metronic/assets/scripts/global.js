var Global = function () {

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

	var initBaseInfo = function () {

		$.fn.editable.defaults.mode = 'inline';
		$.fn.editable.defaults.inputclass = 'form-control';
		$.fn.editable.defaults.url = '{U global/info?inajax=1}';
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

		$('a[data-pk]').each(function() {
			if($(this).data("type") == "select2") {
				$(this).editable({
					inputclass: 'form-control',
					source: [{
							value: 1,
							text: '是'
						}, {
							value: 0,
							text: '否'
						}
					],
					select2: {
						minimumResultsForSearch:-1,
						allowClear: false
					}
				});
			}else{
				$(this).editable();
			}
		});
	};

	var initAccessCtrl = function () {

		$.get("{U api/tos}", function(data) {
			$("textarea[name='tos']").text(data);
		});
	};

	return {
		initBaseInfo: function () {
			initBaseInfo();
		},
		initAccessCtrl: function() {
			initAccessCtrl();
		}
	};

}();