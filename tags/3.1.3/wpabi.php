<?php
/*
**************************************************************************

Plugin Name:  WordPress Admin Bar Improved
Plugin URI:   http://www.electriceasel.com/wpabi
Description:  A set of custom tweaks to the WordPress Admin Bar that was introduced in WP3.1
Version:      3.1.3
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
	protected $version = '3.1.2';
	protected $wpdb;
	
	function WPAdminBarImproved()
	{
		$this->__construct();
	}
	
	public function __construct()
	{		$this->wpdb = $wpdb;
		
		add_filter( 'show_admin_bar', '__return_true' );
		add_action('wp_before_admin_bar_render', array( &$this, 'before' ));
		add_action('wp_after_admin_bar_render', array( &$this, 'after' ));
		wp_enqueue_style('wpabi', plugins_url('wpabi.css', __FILE__), '', '2.0', 'all');
		wp_enqueue_script('wpabi', plugins_url('wpabi.js', __FILE__), array('jquery'), '1.0');
	}
	
	public function before()
	{
		if(!is_user_logged_in()) {
			ob_start();
		}
	}

	public function after()
	{
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
	
	public function ajax_search()
	{
		if(isset($_GET['s']) && isset($_GET['wpabi_ajax']))
		{
			global $wpdb;

			$s = $wpdb->escape($_GET['s']);
			$p = $wpdb->posts;
			
			$sql = "SELECT * FROM $p wp WHERE wp.post_status = 'publish'
					AND wp.post_type != 'nav_menu_item' 
					AND ((wp.post_title LIKE '%$s%') OR (wp.post_content LIKE '%$s%')) 
					ORDER BY  wp.post_date DESC LIMIT 5";
			$results = $wpdb->get_results($sql);
			$return = '<ul>';
			if(count($results))
			{
				$i = 1;
				
				$return = '<ul>';
				foreach($results as $result)
				{
					$return .= '<li class="';
					$return .= ($i&1) ? 'odd' : 'even' ;
					$return .= '"><a href="';
					$return .= get_permalink($result->ID);
					$return .= '">';
					$return .= '<span class="wpabi_title">'.$result->post_title.'</span>';
					$return .= '<span class="wpabi_excerpt">'.substr(strip_tags($result->post_content), 0, 50).'</span>';
					$return .= '</a></li>';
					
					$i++;
				}
			}
			else
			{
				$return .= '<li><a><span class="wpabi_title">'.__('no results found').'</span><span class="wpabi_excerpt">'.__('Please enter another search term.').'</span></a></li>';
			}
			$return .= '</ul>';
			
			echo $return;
			die();
		}
	}
}

// Start this plugin once all other files and plugins are fully loaded
add_action( 'plugins_loaded', create_function( '', 'global $WPAdminBarImproved; $WPAdminBarImproved = new WPAdminBarImproved();' ), 15 );
add_action( 'wp_loaded', create_function( '', 'global $WPAdminBarImproved; $WPAdminBarImproved->ajax_search();' ), 15 );

