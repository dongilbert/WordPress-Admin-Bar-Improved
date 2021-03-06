/******
JS File for the WordPress Admin Bar Improved Plugin
http://www.electriceasel.com/wpabi
******/
jQuery(document).ready(function($){
	$('#wpadminbar').append('<span id="wpabi_min">Hide</span>');
	$('#wpabi_min').click(function(){
		var ctp = parseInt( $('#wpadminbar').css("top") );
		if(ctp >= 0)
		{
			$('#wpadminbar').animate({'top': '-=28px'}, 'slow');
			$('html').animate({'top': '-28px'}, 'slow');
			$('#wpabi_min').text('Show');
			
			$("html").css("cssText", "margin-top: 0px !important;");

		}
		else
		{
			$('#wpadminbar').animate({'top': '+=28px'}, 'slow');
			$('html').animate({'top': '0px'}, 'slow');
			$('#wpabi_min').text('Hide');
			
			$("html").css("cssText", "margin-top: 28px !important;");

		}
	});
	$('#adminbarlogin input').not('[type="submit"]').each(function(){
		var defval = this.value;
		$(this).focus(function(){
			if(this.value == defval)
			{
				this.value = '';
			}
		});
		$(this).blur(function(){
			if(this.value == '')
			{
				this.value = defval;
			}
		});
	});
});