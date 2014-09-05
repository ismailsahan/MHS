var Tool = function() {

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

	$(document).ajaxStop($.unblockUI);

	return {

		initclearcache: function() {
			$(".panel form").submit(function() {
				showloading();
				$.post("{U tool/clearcache?inajax=1}", {}, function(data) {
					modalAlert(data.msg);
				}, "JSON");
				return false;
			});
		}
	};

}();