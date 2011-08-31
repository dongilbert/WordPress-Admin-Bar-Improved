/******
JS File for the WordPress Admin Bar Improved Plugin
http://www.electriceasel.com/wpabi
******/
jQuery(document).ready(function($){
	/* if you would like to reserve the admin bar hide for logged in users only, replace #wpadminbar below with body.logged-in #wpadminbar */
	$('#wpadminbar.toggleme').append('<span id="wpabi_min">Hide</span>');
	if($.cookie('wpabi_show') == 0)
	{
			$('#wpadminbar').css('top', '-28px');
			$('body').css('margin-top', '-28px');
			$('#wpabi_min').text('Show');
	}
	$('#wpabi_min').click(function(){
		var ctp = parseInt( $('#wpadminbar').css("top") );
		if(ctp >= 0)
		{
			$('#wpadminbar').animate({'top': '-=28px'}, 'slow');
			$('body').animate({'margin-top': '-=28px'}, 'slow');
			$('#wpabi_min').text('Show');
			$.cookie('wpabi_show', 0, { expires: 7, path: '/', domain: location.hostname, secure: false });
		}
		else
		{
			$('#wpadminbar').animate({'top': '+=28px'}, 'slow');
			$('body').animate({'margin-top': '+=28px'}, 'slow');
			$('#wpabi_min').text('Hide');
			$.cookie('wpabi_show', 1, { expires: 7, path: '/', domain: location.hostname, secure: false });
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
	
	$('.ajax_login #adminbarlogin').submit(function(event){
		
		event.preventDefault();
		
		var wpabi_log = $('#adminbarlogin-log').val();
		var wpabi_pwd = $('#adminbarlogin-pwd').val();
		var wpabi_rememberme = $('#rememberme').val();
		
		$.post('index.php',{
				log: wpabi_log,
				pwd: wpabi_pwd,
				rememberme: wpabi_rememberme,
				wpabi_ajax: 'true'
			},
			function(data){
				switch(data)
				{
					case 'incorrect_password':
						alert('The password you entered is incorrect.');
						break;
					case 'invalid_username':
						alert('The username you entered is invalid.');
						break;
					default:
						$('#wpadminbar').html(data);
				}
			}
		);
	});
});

if(jQuery().cookie){}else{
	jQuery.cookie = function (key, value, options) {
		// key and at least value given, set cookie...
		if (arguments.length > 1 && String(value) !== "[object Object]") {
			options = jQuery.extend({}, options);
	
			if (value === null || value === undefined) {
				options.expires = -1;
			}
	
			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setDate(t.getDate() + days);
			}
			
			value = String(value);
			
			return (document.cookie = [
				encodeURIComponent(key), '=',
				options.raw ? value : encodeURIComponent(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path ? '; path=' + options.path : '',
				options.domain ? '; domain=' + options.domain : '',
				options.secure ? '; secure' : ''
			].join(''));
		}
	
		// key and possibly options given, get cookie...
		options = value || {};
		var result, decode = options.raw ? function (s) { return s; } : decodeURIComponent;
		return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
	};
}