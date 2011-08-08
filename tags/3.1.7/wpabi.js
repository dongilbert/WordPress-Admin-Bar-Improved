/******
JS File for the WordPress Admin Bar Improved Plugin
http://www.electriceasel.com/wpabi
******/
jQuery(document).ready(function($){
	/* if you would like to reserve the admin bar hide for logged in users only, replace #wpadminbar below with body.logged-in #wpadminbar */
	$('#wpadminbar.toggleme').append('<span id="wpabi_min">Hide</span>');
	$('#wpabi_min').click(function(){
		var ctp = parseInt( $('#wpadminbar').css("top") );
		if(ctp >= 0)
		{
			$('#wpadminbar').animate({'top': '-=28px'}, 'slow');
			$('body').animate({'margin-top': '-=28px'}, 'slow');
			$('#wpabi_min').text('Show');
		}
		else
		{
			$('#wpadminbar').animate({'top': '+=28px'}, 'slow');
			$('body').animate({'margin-top': '+=28px'}, 'slow');
			$('#wpabi_min').text('Hide');
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
	
	$('#adminbarsearch').append('<div id="wpabi_ajax"><span id="wpabi_close">close</span><div id="wpabi_results"></div></div>');
	$('#adminbarsearch input[name="s"]').attr({'autocomplete': 'off'});
	$('#adminbarsearch input[name="s"]').keyup(function(){
		var s = $(this).val();
		if(s.length > 2)
		{
			$.get('?wpabi_ajax=true&s=' + s, function(results){
				if(results.length > 0)
				{
					$('#wpabi_results').html('<span class="h3">Quick Links</span>' + results);
					$('#wpabi_ajax').fadeIn(300);
				}
			});
		}
	});
	$('#wpabi_close').click(function(){
		$('#wpabi_ajax').fadeOut(300);
	});
});