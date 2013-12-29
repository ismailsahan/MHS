$(document).ready(function(){
	$(".input-size").keyup(function(e){
		if(/[^0-9]+/.test(this.value)) this.value=this.value.replace(/[^0-9]+/g, "");
		if(e.which == 13){
			changeSize($(".input-width").val(), $(".input-height").val());
		}
	}).focus(function(){
		$(this).select();
	});
	$(".input-url").keyup(function(e){
		if(e.which == 13){
			$("iframe").attr("src", $(this).val());
		}
	});
	$(".header .nav li a").each(function(){
		switch($(this).data("device")){
			case "desktop":$(this).click(function(){changeSize();$(this).parent().addClass('active');});break;
			case "laptop":$(this).click(function(){changeSize();$(this).parent().addClass('active');});break;
			case "mobile":$(this).click(function(){changeSize(340,510);$(this).parent().addClass('active');});break;
			case "tablet":$(this).click(function(){changeSize(768,1024);$(this).parent().addClass('active');});break;
			case "mobile-reverse":$(this).click(function(){changeSize(510,340);$(this).parent().addClass('active');});break;
			case "tablet-reverse":$(this).click(function(){changeSize(1024,768);$(this).parent().addClass('active');});break;
		}
	});
});

function changeSize(width, height) {
	$(".input-width").val(width ? width : "100%");
	$(".input-height").val(height ? height : "100%");
	width = width ? width+"px" : "100%";
	height = height ? height+"px" : "100%";
	//height = "280px";
	$("table div").css({
		width: width,
		height:height
	});
	$(".header .nav li:gt(1)").removeClass('active');
}
