jQuery(document).ready(function($){
	if($('#show_form').val() == '0')
	{
		$('li.ajax_login').hide();
	}
	$('#show_form').change(function(){
		var ajl = $('li.ajax_login');
		if($(this).val() == '0')
		{
			ajl.slideUp(400);
		}
		else
		{
			ajl.slideDown(400);
		}
	});
	
	if($('#custom_menu').val() == '0')
	{
		$('li.custom_menu p').hide();
	}
	$('#custom_menu').change(function(){
		if($(this).val() == '0')
		{
			$('li.custom_menu p').slideUp(400);
		}
		else
		{
			$('li.custom_menu p').slideDown(400);
		}
	});
});