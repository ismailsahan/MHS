var Activate = function () {
	var handleActivate = function() {
		function format(state) {
			if (!state.id) return state.text; // optgroup
			return "<img class='flag' src='assets/img/flags/" + state.id.toLowerCase() + ".png'/>&nbsp;&nbsp;" + state.text;
		}

		$("#gender").select2({
			placeholder: '{lang gender}',
			allowClear: false
		});
		$("#grade").select2({
			placeholder: '{lang grade}',
			allowClear: false
		});
		$("#academy").select2({
			placeholder: '{lang academy}',
			allowClear: false
		});
		$("#specialty").select2({
			placeholder: '{lang specialty}',
			allowClear: false
		});
		$("#class").select2({
			placeholder: '{lang class}',
			allowClear: false
		});
		 $('#league').select2({
            placeholder: "{lang league_organization}",
            allowClear: true
        });
		$("#department").select2({
			placeholder: '{lang department}',
			allowClear: true
		});

		$('.form-group select').change(function () {
			$('.activation-form').validate().element($(this)); //revalidate the chosen dropdown value and show error or success message for the input
		});

		$('.activate-form').validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block', // default input error message class
			focusInvalid: true, // do not focus the last invalid input
			rules: {
				studentid: {
					required: true,
				},
				password: {
					required: true
				},
				remember: {
					required: false
				}
			},

			messages: {
				studentid: {
					required: "Username is required."
				},
				password: {
					required: "Password is required."
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit   
				$('.activate-form .alert-danger').show();
			},

			highlight: function (element) { // hightlight error inputs
				$(element).closest('.form-group').addClass('has-error'); // set error class to the control group
			},

			success: function (label) {
				label.closest('.form-group').removeClass('has-error');
				label.remove();
			},

			errorPlacement: function (error, element) {
				error.insertAfter(element.closest('.input-icon'));
			},

			submitHandler: function (form) {
				form.submit();
			}
		});

		$('.activate-form input').keypress(function (e) {
			if (e.which == 13) {
				if ($('.activate-form').validate().form()) {
					$('.activate-form').submit();
				}
				return false;
			}
		});
	}
	
	return {
		init: function () {
			handleActivate();
			$(".verifycode a img").seccode();

			$.backstretch([
				"assets/img/bg/1.jpg",
				"assets/img/bg/2.jpg",
				"assets/img/bg/3.jpg",
				"assets/img/bg/4.jpg"
			], {
				fade: 1000,
				duration: 7000
			});
		}

	};

}();