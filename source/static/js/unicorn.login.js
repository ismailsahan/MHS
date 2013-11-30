$(document).ready(function(){
	var usr = $('#usr');
	var uid = $('#uid');
	var speed = 400;
	usr.find('.pull-left .flip-link').click(function(){
		$(this).blur();usr.stop();uid.stop();
		usr.fadeTo(speed,0).css('z-index','100');
		uid.fadeTo(speed,1).css('z-index','200');
	});
	uid.find('.pull-left .flip-link').click(function(){
		$(this).blur();usr.stop();uid.stop();
		uid.fadeTo(speed,0).css('z-index','100');
		usr.fadeTo(speed,1).css('z-index','200');
	});
	if($.browser.msie == true && $.browser.version.slice(0,3) < 10) {
		$('input[placeholder]').each(function(){ 
			var input = $(this);	   
			$(input).val(input.attr('placeholder'));
			$(input).focus(function(){
				if(input.val() == input.attr('placeholder')) input.val('');
			});
			$(input).blur(function(){
				if(input.val() == '' || input.val() == input.attr('placeholder')) input.val(input.attr('placeholder'));
			});
		});
	}
});