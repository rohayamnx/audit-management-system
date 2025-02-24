$(document).ready(function() {
		
	$("#menu li").click(function(){
		
		$("#menu li").each(function() {
			$(this).removeClass();
		});
		
		$(this).addClass('active');

	});

}); //end doc ready

