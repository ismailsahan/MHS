var Activate = function() {

    return {
        //main function to initiate the module
        init: function() {
            if (!$().bootstrapWizard) {
                return;
            }

            function updateSpecialty() {
                $("#specialty").select2("val", null).html('<option value=""></option>');
                var p1 = $('#grade').select2("val"),
                    p2 = $('#academy').select2("val");
                if (p1 && p2)
                    $.get("{U api/profile}", {
                        type: "specialty",
                        grade: p1,
                        academy: p2
                    }, function(data) {
                        $('#specialty').append($.map(data, function(v, i) {
                            return $('<option>', {
                                val: i,
                                text: v
                            });
                        }));
                    }, 'json');
            }

            function updateClass() {
                $("#class").select2("val", null).html('<option value=""></option>');
                var p1 = $('#grade').select2("val"),
                    p2 = $('#specialty').select2("val");
                if (p1 && p2)
                    $.get("{U api/profile}", {
                        type: "class",
                        grade: p1,
                        specialty: p2
                    }, function(data) {
                        $('#class').append($.map(data, function(v, i) {
                            return $('<option>', {
                                val: i,
                                text: v
                            });
                        }));
                    }, 'json');
            }

            function updateLeague() {
                $("#league").select2("val", null).html('');
                var tmp = $('#academy').select2("val");
                if (tmp)
                    $.get("{U api/profile}", {
                        type: "league",
                        academy: tmp
                    }, function(data) {
                        var i, j, s;
                        for (i in data) {
                            s = '<optgroup label="' + i + '">';
                            for (j in data[i]) {
                                s += '<option value="' + j + '">' + data[i][j] + '</option>';
                            }
                            s += '</optgroup>';
                            $('#league').append(s);
                        }
                    }, 'json');
            }

            function updateDepartment() {
                $("#department").select2("val", null).html('');
                var tmp = $('#league').select2("val");
                if (tmp)
                    $.get("{U api/profile}", {
                        type: "department",
                        league: tmp.join(",")
                    }, function(data) {
                        var i, j, s;
                        for (i in data) {
                            s = '<optgroup label="' + i + '">';
                            for (j in data[i]) {
                                s += '<option value="' + j + '">' + data[i][j] + '</option>';
                            }
                            s += '</optgroup>';
                            $('#department').append(s);
                        }
                    }, 'json');
            }

            function modalAlert(msg) {
                $("#alert-modal .modal-body .col-md-12").html(msg);
                $("#alert-modal").modal("show");
            }

            /*function showloading() {
				$("body").modalmanager("loading");
				$(".modal-scrollable").unbind("click");
			}*/
            function showloading() {
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

            $("#gender").select2({
                placeholder: '{lang gender}',
                minimumResultsForSearch: -1,
                allowClear: false
            });

            $("#grade").select2({
                placeholder: '{lang grade}',
                /*initSelection: function(e, callback) {
					$.ajax("{$_G['basefilename']?action=api&operation=grade").done(function(data){
						callback({more:false, results:data});
					});
				},
				query: function(q){
					console.log(arguments);
					$.get("{$_G['basefilename']?action=api&operation=grade", function(data){
						var ret = {more:false, results:data};
						q.callback(ret);
					});
				},*/
                minimumResultsForSearch: -1,
                allowClear: false
            }).change(function() {
                updateSpecialty();
                updateClass();
            });
            $("#academy").select2({
                placeholder: '{lang academy}',
                minimumResultsForSearch: -1,
                allowClear: false
            }).change(function() {
                updateSpecialty();
                updateClass();
                updateLeague();
            });
            $("#specialty").select2({
                placeholder: '{lang specialty}',
                minimumResultsForSearch: -1,
                allowClear: false
            }).change(function() {
                updateClass();
            });
            $("#class").select2({
                placeholder: '{lang class}',
                allowClear: false
            });
            $("#league").select2({
                placeholder: '{lang league}',
                minimumResultsForSearch: -1,
                allowClear: false
            }).change(function() {
                updateDepartment();
            });
            $("#department").select2({
                placeholder: '{lang department}',
                minimumResultsForSearch: -1,
                allowClear: false
            });
            $('#submit_form select').change(function() {
                $('#submit_form').validate().element($(this));
            });
            $('#submit_form input:not(#verifycode)').keyup(function(e) {
                if (e.which == 13) $("#activate .button-next").click();
            });
            $('#verifycode').keyup(function(e) {
                if (e.which == 13) $("#activate .button-submit").click();
            });
            $('#agreement-link').on('click', function() {
                if ($('#agreement-modal').data("inited")) {
                    $("#agreement-modal").modal();
                } else {
                    showloading();
                    $.get("{U api/tos}", function(text) {
                        if (typeof markdown == 'object') {
                            text = markdown.toHTML(text);
                        } else if (typeof marked == 'function') {
                            text = marked(text);
                        } else {
                            text = nl2br(text);
                        }
                        $("#agreement-modal .modal-body .col-md-12").html(text);
                        $('#agreement-modal').data("inited", true);
                        $("#agreement-modal .modal-footer .red").click(function() {
                            $("#agreement-modal").modal("hide");
                            $("input[name='agreement']").prop("checked", false).uniform.update();
                        });
                        $("#agreement-modal .modal-footer .blue").click(function() {
                            $("#agreement-modal").modal("hide");
                            $("input[name='agreement']").prop("checked", true).uniform.update();
                        });
                        $.unblockUI();
                        $("#agreement-modal").modal();
                    });
                }
            });
            $("#report .blue").click(function() {
                if ($("#report textarea").val() == '') {
                    modalAlert("请写一点内容再反馈吧");
                    return;
                }
                $.post("{U api/report}", {
                    data: $("#report textarea").val()
                }, function(data) {
                    modalAlert(data);
                    $('#report').modal('hide');
                    $("#report textarea").val('');
                });
            });
            $('.report-link').on('click', function() {
                $("#report").modal();
            });
            $(".note-info a").click(function() {
                $("#verifycode").secUdt();
                $("#activate").slideDown();
                $(".note-info").slideUp();
            });

            //检查是否为数字账号的QQ邮箱，是则自动填写QQ
            var eml = $("[name='email']").val();
            if (/^[1-9]{1}[0-9]{4,10}@qq\.com$/i.test(eml) && !$("[name='qq']").val()) {
                $("[name='qq']").val(eml.match(/[0-9]+/)[0]);
            }
            $("#verifycode").seccode();

            $.get("{U api/profile}", {
                type: "grade"
            }, function(data) {
                $('#grade').append($.map(data, function(v, i) {
                    return $('<option>', {
                        val: i,
                        text: v
                    });
                }));
            }, 'json');
            $.get("{U api/profile}", {
                type: "academy"
            }, function(data) {
                $('#academy').append($.map(data, function(v, i) {
                    return $('<option>', {
                        val: i,
                        text: v
                    });
                }));
            }, 'json');

            var form = $('#submit_form');
            var error = $('.alert-danger', form);
            var success = $('.alert-success', form);

            form.validate({
                doNotHideMessage: true, //this option enables to show the error/success messages on tab switch.
                errorElement: 'span', //default input error message container
                errorClass: 'help-block', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                rules: {
                    realname: {
                        required: true
                    },
                    gender: {
                        required: true
                    },
                    qq: {
                        required: false,
                        rangelength: [5, 11],
                        digits: true
                    },
                    studentid: {
                        required: true,
                        minlength: 13,
                        maxlength: 13,
                        digits: true
                    },
                    grade: {
                        required: true
                    },
                    academy: {
                        required: true
                    },
                    specialty: {},
                    "class": {},
                    "league[]": {},
                    "department[]": {},
                    remarks: {},
                    agreement: {
                        required: true
                    },
                    allowemail: {},
                    verifycode: {
                        required: true,
                        minlength: {
                            $_G['setting']['seccodedata']['length']
                        }
                    }
                },

                messages: {
                    realname: {
                        required: "请输入你的真实姓名！"
                    },
                    gender: {
                        required: "你没有性别或者是未知吗？"
                    },
                    qq: {
                        required: "真的没有QQ号吗？",
                        rangelength: "你输入的QQ号的长度不对哦",
                        digits: "QQ号由数字构成，你只能输入数字"
                    },
                    studentid: {
                        required: "学号不能为空！",
                        minlength: "你输入的学号长度不正确！",
                        maxlength: "你输入的学号长度不正确！",
                        digits: "学号仅由数字组成！"
                    },
                    grade: {
                        required: "年级必填！"
                    },
                    academy: {
                        required: "请选择你的院系！这跟第三步的资料有关"
                    },
                    specialty: {},
                    "class": {},
                    "league[]": {
                        required: "请选择你所有加入的社团或组织！"
                    },
                    "department[]": {
                        required: "请选择你所有加入的社团或组织中的部门！"
                    },
                    remarks: {},
                    agreement: {
                        required: "你必须同意服务条款后才能使用本系统"
                    },
                    allowemail: {},
                    verifycode: {
                        required: "验证码不能为空！",
                        minlength: "你输入的验证码长度不正确！"
                    }
                },

                errorPlacement: function(error, element) { // render error placement for each input type
                    if (element.attr("name") == "gender") { // for uniform radio buttons, insert the after the given container
                        error.insertAfter("#form_gender_error");
                    } else if (element.attr("name") == "agreement" || element.attr("name") == "allowemail") { // for uniform radio buttons, insert the after the given container
                        error.insertAfter("#form_extra_error");
                    } else {
                        error.insertAfter(element); // for other inputs, just perform default behavior
                    }
                },

                invalidHandler: function(event, validator) { //display error alert on form submit   
                    success.hide();
                    error.show();
                    App.scrollTo(error, -200);
                },

                highlight: function(element) { // hightlight error inputs
                    $(element).closest('.form-group').removeClass('has-success').addClass('has-error'); // set error class to the control group
                },

                unhighlight: function(element) { // revert the change done by hightlight
                    $(element).closest('.form-group').removeClass('has-error'); // set error class to the control group
                },

                success: function(label) {
                    if (label.attr("for") == "gender" || label.attr("for") == "payment[]") { // for checkboxes and radio buttons, no need to show OK icon
                        label.closest('.form-group').removeClass('has-error').addClass('has-success');
                        label.remove(); // remove error label here
                    } else { // display success icon for other inputs
                        label.addClass('valid').closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                    }
                },

                submitHandler: function(form) {
                    success.show();
                    error.hide();
                }

            });

            var displayConfirm = function() {
                $('#tab5 .form-control-static', form).each(function() {
                    var input = $('[name="' + $(this).attr("data-display") + '"]', form);
                    if (input.is(":text") || input.is("textarea")) {
                        $(this).html(input.val().replace(/\n/g, "<br />"));
                    } else if (input.is("select")) {
                        var arr = [];
                        input.find('option:selected').each(function() {
                            arr.push($(this).text());
                        })
                        $(this).html(arr.join(" , "));
                    } else if (input.is(":radio") && input.is(":checked")) {
                        $(this).html(input.attr("data-title"));
                    } else if (input.is(":checkbox") && input.is(":checked")) {
                        $(this).html(input.attr("data-title"));
                    } else if ($(this).attr("data-display") == 'payment') {
                        var payment = [];
                        $('[name="payment[]"]').each(function() {
                            payment.push($(this).attr('data-title'));
                        });
                        $(this).html(payment.join("<br>"));
                    }
                });
            }

            var handleTitle = function(tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                // set wizard title
                $('.step-title', $('#activate')).text('第' + (index + 1) + '步(共' + total + '步)');
                // set done steps
                $('li', $('#activate')).removeClass("done");
                var li_list = navigation.find('li');
                for (var i = 0; i < index; i++) {
                    $(li_list[i]).addClass("done");
                }

                if (current == 1) {
                    $('#activate .button-previous').hide();
                } else {
                    $('#activate .button-previous').show();
                }

                if (current >= total) {
                    $('#activate .button-next').hide();
                    $('#activate .button-submit').show();
                    displayConfirm();
                } else {
                    $('#activate .button-next').show();
                    $('#activate .button-submit').hide();
                }
                App.scrollTo($('#activate'));
            }

            // default form wizard
            $('#activate').bootstrapWizard({
                'nextSelector': '.button-next',
                'previousSelector': '.button-previous',
                onTabClick: function(tab, navigation, index, clickedIndex) {
                    success.hide();
                    error.hide();
                    if (form.valid() == false) {
                        return false;
                    }
                    handleTitle(tab, navigation, clickedIndex);
                },
                onNext: function(tab, navigation, index) {
                    success.hide();
                    error.hide();

                    if (form.valid() == false) {
                        return false;
                    }

                    handleTitle(tab, navigation, index);
                },
                onPrevious: function(tab, navigation, index) {
                    success.hide();
                    error.hide();

                    handleTitle(tab, navigation, index);
                },
                onTabShow: function(tab, navigation, index) {
                    var total = navigation.find('li').length;
                    var current = index + 1;
                    var percent = (current / total) * 100;
                    $('#activate').find('.progress-bar').css({
                        width: percent + '%'
                    });
                }
            });

            $('#activate .button-previous').hide();
            $('#activate .button-submit').click(function() {
                if (form.validate().element($("#verifycode")) == false) return $("#verifycode").focus();
                var url = $("#submit_form").attr("action");
                showloading();
                $.post(url + (url.indexOf("?") > -1 ? "&inajax=1" : "/inajax/1"), $("#submit_form").serialize(), function(data) {
                    if (data.url) {
                        window.location.href(data.url);
                    } else if (data.msg) {
                        $.unblockUI();
                        modalAlert(data.msg);
                        if (data.errno) {
                            switch (data.errno) {
                                case 2:
                                    return $("#alert-modal").on("hide.bs.modal", function() {
                                        window.location.reload();
                                    }); //表单过期
                                case 1: //$(".button-previous").click();//验证码错误
                                default:
                                    return $("#verifycode").secUdt();
                            }
                        }
                        App.scrollTo($('.page-title'));
                        $(".note-danger, #activate").slideUp();
                        $(".note-info").slideDown();
                    }
                });
            }).hide();

            /*$.fn.modal.defaults.spinner = $.fn.modalmanager.defaults.spinner = 
				'<div class="loading-spinner" style="width: 200px; margin-left: -100px;">' +
					'<div class="progress progress-striped active">' +
						'<div class="progress-bar" style="width: 100%;"></div>' +
					'</div>' +
				'</div>';
			$.fn.modalmanager.defaults.resize = true;*/

        }

    };

}();