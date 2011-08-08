<?php
/*
**************************************************************************

Plugin Name:  WordPress Admin Bar Improved
Plugin URI:   http://www.electriceasel.com/wpabi
Description:  A set of custom tweaks to the WordPress Admin Bar that was introduced in WP3.1
Version:      3.1.2
Author:       dilbert4life, electriceasel
Author URI:   http://www.electriceasel.com/team-member/don-gilbert

**************************************************************************

Copyright (C) 2011 Don Gilbert

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************/

class WPAdminBarImproved {
	var $version = '3.1.2';
	
	function WPAdminBarImproved()
	{
		$this->__construct();
	}
	
	public function __construct()
	{
		add_filter( 'show_admin_bar', '__return_true' );
		add_action('wp_before_admin_bar_render', array( &$this, 'before' ));
		add_action('wp_after_admin_bar_render', array( &$this, 'after' ));
		wp_enqueue_style('wpabi', plugins_url('wpabi.css', __FILE__), '', '2.0', 'all');
		wp_enqueue_script('wpabi', plugins_url('wpabi.js', __FILE__), array('jquery'), '1.0');
	}
	
	public function before() {
		if(!is_user_logged_in()) {
			ob_start();
		}
	}

	public function after() {
		if(!is_user_logged_in()) {
			$html = ob_get_clean();
			$loginform = 'id="wpadminbar"><div class="loginform">
				<form action="/wp-login.php" method="post" id="adminbarlogin">
					<input class="adminbar-input" name="log" id="adminbarlogin-log" type="text" value="Username" />
					<input class="adminbar-input" name="pwd" id="adminbarlogin-pwd" type="password" value="Password" />
					<input type="submit" class="adminbar-button" value="'.__('Login').'"/>
				</form></div>';
			$html = str_replace('id="wpadminbar">', $loginform, $html);
			echo $html;
		}
	}
	
}

// Start this plugin once all other files and plugins are fully loaded
add_action( 'plugins_loaded', create_function( '', 'global $WPAdminBarImproved; $WPAdminBarImproved = new WPAdminBarImproved();' ), 15 );

