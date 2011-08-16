<?php
/*
**************************************************************************

Plugin Name:  WordPress Admin Bar Improved
Plugin URI:   http://www.electriceasel.com/wpabi
Description:  A set of custom tweaks to the WordPress Admin Bar that was introduced in WP3.1
Version:      3.3
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
	private $version = '3.3';
	private $css_file;
	private $js_file;
	private $editing_file;
	private $scrollto;
	private $show_form;
	private $do_ajax;
	private $load_js;
	private $reg_link;
	private $toggleme;
	private $custom_menu;
	private $options;
	
	function WPAdminBarImproved()
	{
		$this->__construct();
	}
	
	public function __construct()
	{
		add_action('admin_init', array( &$this, 'options' ));
		add_filter( 'show_admin_bar', '__return_true' );
		
		$this->options = get_option('wpabi_options');
		
		$this->show_form = ($this->options['show_form'] === 'yes') ? true : false;
		$this->do_ajax = ($this->options['ajax_search'] === 'yes' || $this->options['ajax_login'] === 'yes') ? true : false;
		$this->reg_link = ($this->options['reg_link'] === 'yes') ? true : false;
		$this->toggleme = ($this->options['hide_admin_bar'] === 'yes') ? true : false;
		$this->custom_menu = ($this->options['custom_menu'] === 'yes') ? true : false;

		/* todo - somehow make this work to see if javascript needs loaded
		 * Something like this below, but other things depend on JS as well
		 * ($this->options['ajax_search'] === 'yes') ? true : false;
		 */
		$this->load_js = true;
		
		$this->css_file  = dirname(__FILE__) . '/wpabi.css';
		$this->js_file  = dirname(__FILE__) . '/wpabi.js';
		
		$this->scrollto = isset($_REQUEST['scrollto']) ? (int) $_REQUEST['scrollto'] : 0;
		
		if($this->show_form || $this->toggleme)
		{
			add_action('wp_before_admin_bar_render', array( &$this, 'before' ));
			add_action('wp_after_admin_bar_render', array( &$this, 'after' ));
		}
		
		if($this->load_js)
		{
			wp_enqueue_script('wpabi_js', plugins_url('wpabi.js', __FILE__), array('jquery'), '1.0');
		}
		
		wp_enqueue_style('wpabi_css', plugins_url('wpabi.css', __FILE__), '', '2.0', 'all');
		
		if($this->custom_menu)
		{
			$this->add_custom_menu();
		}
		
		$this->admin_page();
	}
	
	public function options()
	{
		register_setting( 'wpabi_options', 'wpabi_options', array( &$this, 'wpabi_options_validate') );
	}
	
	public function add_custom_menu()
	{
		if(!current_theme_supports('menus'))
		{
			add_theme_support('menus');
		}
		register_nav_menu('wpabi_menu', 'Admin Bar Improved');
		add_action('admin_bar_menu',  array( &$this, 'build_menu'), 9999);
	}
	
	public function build_menu($wp_admin_bar)
	{
		$locations = get_nav_menu_locations();
		$menu = wp_get_nav_menu_object($locations['wpabi_menu']);
		$menu_items = (array) wp_get_nav_menu_items($menu->term_id);
		
		foreach($menu_items as $menu_item) {
			$args = array(
						  'id' => 'wpabi_'.$menu_item->ID,
						  'title' => $menu_item->title,
						  'href' => $menu_item->url
						);
			
			if(!empty($menu_item->menu_item_parent))
			{
				$args['parent'] = 'wpabi_'.$menu_item->menu_item_parent;
			}
			
			$wp_admin_bar->add_menu($args);
		}
	}
	
	public function before()
	{
		if(!is_user_logged_in() || $this->toggleme) {
			ob_start();
		}
	}

	public function after()
	{
		$html = ob_get_clean();
		$loginform = 'id="wpadminbar">';
		if($this->toggleme)
		{
			$loginform = 'class="toggleme" '.$loginform;
		}
		if(!is_user_logged_in()) {
			$loginform .= '<div class="loginform">
				<form action="'.wp_login_url().'" method="post" id="adminbarlogin">
					<input class="adminbar-input" name="log" id="adminbarlogin-log" type="text" value="'.__('Username').'" />
					<input class="adminbar-input" name="pwd" id="adminbarlogin-pwd" type="password" value="'.__('Password').'" />
					<input type="submit" class="adminbar-button" value="'.__('Login').'"/>
					<span class="adminbar-loginmeta">
						<input type="checkbox" checked="checked" tabindex="3" value="forever" id="rememberme" name="rememberme">
						<label for="rememberme">'.__('Remember me').'</label>
						<a href="'.wp_login_url().'?action=lostpassword">'.__('Lost your password?').'</a>';
			if($this->reg_link)
			{
				$loginform .= '<a href="'.wp_login_url().'?action=register">'.__('Register').'</a>';
			}
			$loginform .= '</span></form></div>';
		}
		$html = str_replace('id="wpadminbar">', $loginform, $html);
		echo $html;
	}
	
	public function ajax_search()
	{
		if(isset($_GET['s']) && isset($_GET['wpabi_ajax']))
		{
			if(!$this->do_ajax)
			{
				die();
			}
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
	
	public function ajax_login()
	{
		if(isset($_POST['wpabi_ajax']) && isset($_POST['log']) && isset($_POST['pwd']))
		{
			if(!$this->do_ajax)
			{
				die();
			}
			
			$user = wp_signon();
			if(is_wp_error($user))
			{
				echo 'error';
				die();
			}
			global $current_user, $user_identity;
			$current_user = $user;
			$user_identity = $current_user->data->display_name;
			wp_admin_bar_render();
			die();
		}
	}
	
	private function which_file()
	{
		if(isset($_GET['wpabi_edit']))
		{
			switch($_GET['wpabi_edit'])
			{
				case 'css':
					$this->editing_file = $this->css_file;
					break;
				case 'js':
					$this->editing_file = $this->js_file;
					break;
			}
		}
	}
	
	private function write_file()
	{
		$scrollto = $this->scrollto;
		$newcontent = stripslashes($_POST['newcontent']);
		if ( is_writeable($this->editing_file) )
		{
			$f = fopen($this->editing_file, 'w+');
			fwrite($f, $newcontent);
			fclose($f);
			wp_redirect( self_admin_url("options-general.php?page=wpabi&wpabi_edit=".$_GET['wpabi_edit']."&a=te&scrollto=$scrollto") );
		}
	}
	
	private function admin_page()
	{
		$this->which_file();
		
		if(isset($_POST['newcontent']))
		{
			$this->write_file();
		}
		wp_enqueue_style('theme-editor');
		$hook = (version_compare($wp_version, '3.1', '>=') && is_multisite()) ? 'network_admin_menu' : 'admin_menu' ;
		add_action($hook, array( &$this, 'admin_menu' ));
	}
	
	public function admin_menu()
	{
		add_options_page('WPABI', 'WPABI', 'manage_options', 'wpabi', array(&$this, 'admin_page_render'));
	}
	
	public function admin_page_render()
	{
		echo '<div class="wrap">';
		$this->nav();
		
		switch($_GET['wpabi_edit'])
		{
			case 'css':
			case 'js':
				$this->edit_page();
				break;
			default:
				$this->main_page();
				break;
		}
		echo '</div>';
	}
	
	private function main_page()
	{
		?>
        <br />
        <form id="template" method="post" action="options.php">
        <?php settings_fields('wpabi_options'); ?>
        	<ul>
        		<li>
            		<label>Show Login Form?</label>
                	<input type="radio" name="wpabi_options[show_form]" value="yes" <?php checked($this->options['show_form'], 'yes') ?>/><span>Yes</span>
                	<input type="radio" name="wpabi_options[show_form]" value="no" <?php checked($this->options['show_form'], 'no') ?>/><span>No</span>
            	</li>
        		<li>
            		<label>Ajax Search</label>
                	<input type="radio" name="wpabi_options[ajax_search]" value="yes" <?php checked($this->options['ajax_search'], 'yes') ?>/><span>Enabled</span>
                	<input type="radio" name="wpabi_options[ajax_search]" value="no" <?php checked($this->options['ajax_search'], 'no') ?>/><span>Disabled</span>
            	</li>
        		<li>
            		<label>Ajax Login</label>
                	<input type="radio" name="wpabi_options[ajax_login]" value="yes" <?php checked($this->options['ajax_login'], 'yes') ?>/><span>Enabled</span>
                	<input type="radio" name="wpabi_options[ajax_login]" value="no" <?php checked($this->options['ajax_login'], 'no') ?>/><span>Disabled</span>
            	</li>
        		<li>
            		<label>Show/Hide Button</label>
                	<input type="radio" name="wpabi_options[hide_admin_bar]" value="yes" <?php checked($this->options['hide_admin_bar'], 'yes') ?>/><span>Enabled</span>
                	<input type="radio" name="wpabi_options[hide_admin_bar]" value="no" <?php checked($this->options['hide_admin_bar'], 'no') ?>/><span>Disabled</span>
            	</li>
        		<li>
            		<label>Show Registration Link in Form?</label>
                	<input type="radio" name="wpabi_options[reg_link]" value="yes" <?php checked($this->options['reg_link'], 'yes') ?>/><span>Enabled</span>
                	<input type="radio" name="wpabi_options[reg_link]" value="no" <?php checked($this->options['reg_link'], 'no') ?>/><span>Disabled</span>
            	</li>
        		<li>
            		<label>Enabled Custom Admin Bar Menu?</label>
                	<input type="radio" name="wpabi_options[custom_menu]" value="yes" <?php checked($this->options['custom_menu'], 'yes') ?>/><span>Enabled</span>
                	<input type="radio" name="wpabi_options[custom_menu]" value="no" <?php checked($this->options['custom_menu'], 'no') ?>/><span>Disabled</span>
            	</li>
            	<li>
                	&nbsp;
            	</li>
            	<li>
            		<label>&nbsp;</label>
                	<?php submit_button( __( 'Save Options' ), 'primary', 'submit', false, array( 'tabindex' => '2' ) ); ?>
            	</li>
        	</ul>
        </form>
        <?php
	}
	
	private function edit_page()
	{
		$content = '';
		if(file_exists($this->editing_file))
		{
			$content = esc_textarea(file_get_contents( $this->editing_file ));
		}
		?>
		<p><?php _e("Edit the CSS/JS for the Admin Bar and the Ajax Search below.") ?></p>
        
		<form id="template" method="post" action="">
			<input type="hidden" name="scrollto" id="scrollto" value="<?php echo $this->scrollto; ?>" />
			<textarea cols="70" rows="25" name="newcontent" id="newcontent" tabindex="1"><?php echo $content ?></textarea>
			<?php wp_nonce_field('wpabi_referrer','wpabi_referrer_nonce'); ?>
			<?php submit_button( __( 'Update File' ), 'primary', 'submit', false, array( 'tabindex' => '2' ) ); ?>
		</form>
        
		<script type="text/javascript">
		/* <![CDATA[ */
		jQuery(document).ready(function($){
			$('#template').submit(function(){ $('#scrollto').val( $('#newcontent').scrollTop() ); });
			$('#newcontent').scrollTop( $('#scrollto').val() );
		});
		/* ]]> */
		</script>
        <?php
	}
	
	private function nav()
	{
		screen_icon('options-general'); ?>
        
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo admin_url( 'options-general.php?page=wpabi'); ?>" class="nav-tab<?php 
				echo (isset($_GET['wpabi_edit'])) ? '' : ' nav-tab-active' ; 
			?>">WordPress Admin Bar Improved</a>
            
			<a href="<?php echo admin_url( 'options-general.php?page=wpabi&wpabi_edit=css'); ?>" class="nav-tab<?php
				echo ($_GET['wpabi_edit'] == 'css') ? ' nav-tab-active' : '' ; 
			?>">CSS Editor</a>
            
			<a href="<?php echo admin_url( 'options-general.php?page=wpabi&wpabi_edit=js'); ?>" class="nav-tab<?php
				echo ($_GET['wpabi_edit'] == 'js') ? ' nav-tab-active' : '' ; 
			?>">JS Editor</a>
	    </h2>
        
		<?php
		if (isset($_GET['a']) && isset($_GET['wpabi_edit'])) {
			echo '<div id="message" class="updated"><p>'.__('File edited successfully.').'</p></div>';
        };
	}
	
	public function wpabi_options_validate($input)
	{
		return $input;
	}
}

// Start this plugin once all other files and plugins are fully loaded
add_action( 'plugins_loaded', create_function( '', 'global $WPAdminBarImproved; $WPAdminBarImproved = new WPAdminBarImproved();' ), 15 );
add_action( 'wp_loaded', create_function( '', 'global $WPAdminBarImproved; $WPAdminBarImproved->ajax_search(); $WPAdminBarImproved->ajax_login();' ), 15 );
