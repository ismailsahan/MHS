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

	var initSeccheck = function () {

		function secpanel(val, speed) {
			speed = speed || "normal";
			switch(val) {
				case "0":
				case "1":
					$("#secpicsize,#secpicprop").show(speed);
					break;
				case "2":
					$("#secpicsize").show(speed);
					$("#secpicprop").hide(speed);
					break;
				case "3":
				case "4":
					$("#secpicsize,#secpicprop").hide(speed);
					$("input[name='seclength']").val(4);
			}
		}

		var secpreview = function () {
			var sec = $("#verifycode").val();
			if(sec) {
				var e = $("#verifycode").closest(".form-group");
				e.removeClass("has-error").removeClass("has-success").find("i.fa").removeClass("fa-check").removeClass("fa-times").addClass("fa-remove");
				$("#verifycode").addClass("spinner");
				$.ajax({
					async: true,
					cache: false,
					data : {"seccode": sec},
					dataType: "json",
					global: false,
					success: function(data) {
						$("#verifycode").removeClass("spinner");
						console.log("Seccode Test: ", data);
						e.addClass(data.result ? "has-success" : "has-error");
						e.find("i.fa").removeClass("fa-remove").removeClass("fa-check").removeClass("fa-times").addClass(data.result ? "fa-check" : "fa-times");
					},
					type: "POST",
					url: "{U global/chksec}"
				});
			}
		};
		secpreview.key = function(e) {
			if(e.which == 13) {
				e.preventDefault();
				return secpreview();
			}
			if($("#verifycode").val().length >= parseInt($("input[name='seclength']").val())) secpreview();
		}

		$("input[name='sectype']").change(function() {
			secpanel($(this).val());
		});
		$("input[name='seclength']").change(function() {
			$("#verifycode").prop("maxlength", $(this).val());
		});

		$("#secform").validate({
			doNotHideMessage: true,
			errorElement: 'span',
			errorClass: 'help-block',
			focusInvalid: false,
			rules: {
				"secopn[]": {},
				sectype: {
					required: true
				},
				seclength: {
					required: true,
					range: [2, 8],
					digits: true
				},
				secwidth: {
					required: true,
					range: [100, 200],
					digits: true
				},
				secheight: {
					required: true,
					range: [30, 80],
					digits: true
				},
				secscatter: {
					required: true,
					min: 0,
					digits: true
				},
				secbackground: {
					required: true
				},
				secadulterate: {
					required: true
				},
				secttf: {
					required: true
				},
				secangle: {
					required: true
				},
				secwarping: {
					required: true
				},
				seccolor: {
					required: true
				},
				secsize: {
					required: true
				},
				secshadow: {
					required: true
				},
				secanimator: {
					required: true
				}
			},

			messages: {
				
			},

			errorPlacement: function (error, element) {
				if (element.prop("type")=="radio") {
					error.insertAfter(element.closest('.radio-list'));
				} else if (element.prop("type")=="checkbox") {
					error.insertAfter(element.closest('.checkbox-list'));
				} else {
					error.insertAfter(element);
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit
				//App.scrollTo(error, -200);
			},

			highlight: function (element) { // hightlight error inputs
				$(element).closest('.form-group').addClass('has-error'); // set error class to the control group
			},

			unhighlight: function (element) { // revert the change done by hightlight
				$(element).closest('.form-group').removeClass('has-error'); // set error class to the control group
			},

			success: function (label) {
			},

			submitHandler: function (form) {
				$.post("{U global/seccheck?inajax=1}", $(form).serialize(), function(data) {
					modalAlert(data.msg);
				}, "JSON");
			}

		});

		secpanel($("input[name='sectype']:checked").val());
		$("#verifycode").blur(secpreview).keypress(secpreview.key).focus(function() {
			$(this).closest(".form-group").removeClass("has-error").removeClass("has-success");
		}).seccode();
	};

	var initDatatime = function () {

		$("#timeoffset").select2({
			minimumResultsForSearch:-1,
			allowClear: false,
			data:[
				{id:"",  text:"选择公共时区"},
				{id:-12, text:"(GMT -12:00) 埃尼威托克岛, 夸贾林.."},
				{id:-11, text:"(GMT -11:00) 中途岛, 萨摩亚群岛"},
				{id:-10, text:"(GMT -10:00) 夏威夷"},
				{id:-9,  text:"(GMT -09:00) 阿拉斯加"},
				{id:-8,  text:"(GMT -08:00) 太平洋时间(美国和加拿.."},
				{id:-7,  text:"(GMT -07:00) 山区时间(美国和加拿大.."},
				{id:-6,  text:"(GMT -06:00) 中部时间(美国和加拿大.."},
				{id:-5,  text:"(GMT -05:00) 东部时间(美国和加拿大.."},
				{id:-4,  text:"(GMT -04:00) 大西洋时间(加拿大), .."},
				{id:-3.5,text:"(GMT -03:30) 纽芬兰"},
				{id:-3,  text:"(GMT -03:00) 巴西利亚, 布宜诺斯艾.."},
				{id:-2,  text:"(GMT -02:00) 中大西洋, 阿森松群岛,.."},
				{id:-1,  text:"(GMT -01:00) 亚速群岛, 佛得角群岛 .."},
				{id:0,   text:"(GMT) 卡萨布兰卡, 都柏林, 爱丁堡, .."},
				{id:1,   text:"(GMT +01:00) 柏林, 布鲁塞尔, 哥本.."},
				{id:2,   text:"(GMT +02:00) 赫尔辛基, 加里宁格勒,.."},
				{id:3,   text:"(GMT +03:00) 巴格达, 利雅得, 莫斯.."},
				{id:3.5, text:"(GMT +03:30) 德黑兰"},
				{id:4,   text:"(GMT +04:00) 阿布扎比, 巴库, 马斯.."},
				{id:4.5, text:"(GMT +04:30) 坎布尔"},
				{id:5,   text:"(GMT +05:00) 叶卡特琳堡, 伊斯兰堡,.."},
				{id:5.5, text:"(GMT +05:30) 孟买, 加尔各答, 马德.."},
				{id:5.75,text:"(GMT +05:45) 加德满都"},
				{id:6,   text:"(GMT +06:00) 阿拉木图, 科伦坡, 达.."},
				{id:6.5, text:"(GMT +06:30) 仰光"},
				{id:7,   text:"(GMT +07:00) 曼谷, 河内, 雅加达"},
				{id:8,   text:"(GMT +08:00) 北京, 香港, 帕斯, 新.."},
				{id:9,   text:"(GMT +09:00) 大阪, 札幌, 首尔, 东.."},
				{id:9.5, text:"(GMT +09:30) 阿德莱德, 达尔文"},
				{id:10,  text:"(GMT +10:00) 堪培拉, 关岛, 墨尔本,.."},
				{id:11,  text:"(GMT +11:00) 马加丹, 新喀里多尼亚,.."},
				{id:12,  text:"(GMT +12:00) 奥克兰, 惠灵顿, 斐济,.."}
			]
		});
	};

	return {
		initBaseInfo: function () {
			initBaseInfo();
		},
		initAccessCtrl: function() {
			initAccessCtrl();
		},
		initSeccheck: function() {
			initSeccheck();
		},
		initDatatime: function() {
			initDatatime();
		}
	};

}();