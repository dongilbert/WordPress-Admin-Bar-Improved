<?php
/*
**************************************************************************

Plugin Name:  WordPress Admin Bar Improved
Plugin URI:   http://www.electriceasel.com/wpabi
Description:  A set of custom tweaks to the WordPress Admin Bar that was introduced in WP3.1
Version:      3.3.2
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
	private static $version = '3.3.2';
	private $textdomain = 'wpabi';
	private $css_file;
	private $js_file;
	private $editing_file;
	private $scrollto;
	private $load_js;
	private $options;
	
	function WPAdminBarImproved()
	{
		$this->__construct();
	}
	
	public function __construct()
	{
		load_plugin_textdomain('wpabi', false, dirname(plugin_basename(__FILE__)) . '/lang/');
		
		add_action('admin_init', array( &$this, 'options' ));
		$this->options = get_option('wpabi_options');
		
		$this->version_check();
		
		$this->css_file = dirname(__FILE__) . '/wpabi.css';
		$this->js_file  = dirname(__FILE__) . '/wpabi.js';
		
		$this->scrollto = isset($_REQUEST['scrollto']) ? (int) $_REQUEST['scrollto'] : 0;
		
		if($this->options['show_bar'])
		{
			add_filter( 'show_admin_bar', '__return_true' );
		}
		
		if( (!is_user_logged_in() && $this->options['show_form']) || $this->options['toggleme'])
		{
			add_action('wp_before_admin_bar_render', array( &$this, 'before_admin_bar_render' ), 99);
			add_action('wp_after_admin_bar_render', array( &$this, 'after_admin_bar_render' ), 99);
		}
		
		if($this->options['toggleme'] || $this->options['ajax_login'] || $this->options['ajax_search'])
		{
			$this->load_js = true;
		}
		
		if($this->load_js)
		{
			wp_enqueue_script('wpabi_js', plugins_url('wpabi.js', __FILE__), array('jquery'), '1.0');
		}
		
		wp_enqueue_style('wpabi_css', plugins_url('wpabi.css', __FILE__), '', '2.0', 'all');
		
		add_action('admin_bar_menu', array( &$this, 'manage_menu_items' ), 9998);
		
		$this->admin_page();
	}
	
	private function version_check()
	{
		if(version_compare($this->options['version'], '3.3.1', 'lt'))
		{
			$this->deactivation_hook();
			$this->activation_hook();
		}
	}
	
	public function activation_hook()
	{
		$options = get_option('wpabi_options');
		if(!$options)
		{
			$defaults = array(
				'version' => self::$version,
				'show_bar' => 1,
				'show_form' => 1,
				'ajax_login' => 1,
				'ajax_search' => 1,
				'toggleme' => 1,
				'reg_link' => 0,
				'custom_menu' => 0,
				'default_items' => array(
					'my-account-with-avatar' => 1,
					'view-site' => 1,
					'dashboard' => 1,
					'my-blogs' => 1,
					'new-content' => 1,
					'comments' => 1,
					'appearance' => 1,
					'edit' => 1,
					'get-shortlink' => 1,
					'updates' => 1
				)
			);
			add_option('wpabi_options', $defaults);
		}
	}
	
	public function deactivation_hook()
	{
		delete_option('wpabi_options');
	}
	
	public function options()
	{
		register_setting( 'wpabi_options', 'wpabi_options', array( &$this, 'wpabi_options_validate') );
	}
	
	public function wpabi_options_validate($input)
	{
		return $input;
	}
	
	public function manage_menu_items($wp_admin_bar)
	{		
		foreach( (array) $this->options['default_items'] as $menu => $enabled)
		{
			if(!$enabled)
			{
				$wp_admin_bar->remove_menu($menu);
			}
			
			if(($menu === 'my-account-with-avatar') && !$enabled)
			{
				$wp_admin_bar->remove_menu('my-account');
			}
		}
		
		if($this->options['custom_menu'])
		{
			$this->add_custom_menu();
		}
	}
	
	private function add_custom_menu()
	{
		if(!current_theme_supports('menus'))
		{
			add_theme_support('menus');
		}
		register_nav_menu('wpabi_menu', __('Admin Bar Improved', $this->textdomain));
		add_action('admin_bar_menu',  array( &$this, 'build_custom_menu'), 9999);
	}
	
	public function build_custom_menu($wp_admin_bar)
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
	
	public function before_admin_bar_render()
	{
		ob_start();
	}

	public function after_admin_bar_render()
	{
		$html = ob_get_clean();
		$loginform = 'id="wpadminbar" class="nojq">';
		if($this->options['toggleme'])
		{
			$loginform = str_replace('class="', 'class="toggleme ', $loginform);
		}
		if($this->options['ajax_login'])
		{
			$loginform = str_replace('class="', 'class="ajax_login ', $loginform);
		}
		if(!is_user_logged_in() && $this->options['show_form']) {
			$loginform .= '<div class="loginform">
				<form action="'.wp_login_url().'" method="post" id="adminbarlogin">
					<input class="adminbar-input" name="log" id="adminbarlogin-log" type="text" value="'.__('Username', $this->textdomain).'" />
					<input class="adminbar-input" name="pwd" id="adminbarlogin-pwd" type="password" value="'.__('Password', $this->textdomain).'" />
					<input type="submit" class="adminbar-button" value="'.__('Login').'"/>
					<span class="adminbar-loginmeta">
						<input type="checkbox" checked="checked" tabindex="3" value="forever" id="rememberme" name="rememberme">
						<label for="rememberme">'.__('Remember me', $this->textdomain).'</label>
						<a href="'.wp_login_url().'?action=lostpassword">'.__('Lost your password?', $this->textdomain).'</a>';
			if($this->options['reg_link'])
			{
				$loginform .= '<a href="'.wp_login_url().'?action=register">'.__('Register', $this->textdomain).'</a>';
			}
			$loginform .= '</span></form></div>';
		}
		$html = str_replace('id="wpadminbar" class="nojq">', $loginform, $html);
		echo $html;
	}
	
	public function ajax_search()
	{
		if(isset($_GET['s']) && isset($_GET['wpabi_ajax']))
		{
			if(!$this->options['ajax_search'])
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
				$return .= '<li><a><span class="wpabi_title">'.__('no results found', $this->textdomain).'</span><span class="wpabi_excerpt">'.__('Please enter another search term.', $this->textdomain).'</span></a></li>';
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
			if(!$this->options['ajax_login'])
			{
				die();
			}
			
			$user = wp_signon();
			if(is_wp_error($user))
			{
				echo $user->get_error_code();
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
		global $wp_version;
		
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
		$options_page = add_options_page(__('WordPress Admin Bar Improved', $this->textdomain), __('Admin Bar Improved', $this->textdomain), 'manage_options', 'wpabi', array(&$this, 'admin_page_render'));
		add_action("admin_print_scripts-$options_page", array( &$this, 'admin_scripts' ));
		add_action("admin_print_styles-$options_page", array( &$this, 'admin_styles' ));
	}
	
	public function admin_scripts()
	{
		wp_enqueue_script('wpabi_admin_js', plugins_url('wpabi-admin.js', __FILE__), array('jquery'), '1.0');
	}
	
	public function admin_styles()
	{
		wp_enqueue_style('wpabi_admin_css', plugins_url('wpabi-admin.css', __FILE__), '', '2.0', 'all');
	}
	
	public function admin_page_render()
	{
		echo '<div class="wrap">';
		echo '<div id="wpabi">';
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
		echo '</div>';
	}
	
	private function main_page()
	{
		?>
        <br />
<div class="form-table">
	<form id="template" method="post" action="options.php">
		<input type="hidden" name="wpabi_options[version]" value="<?php echo self::$version; ?>" />
        <?php settings_fields('wpabi_options'); ?>
        <ul>
        	<li class="show_bar">
            	<label for="show_bar"><?php _e('Admin Bar for logged out users', $this->textdomain); ?>:</label>
            	<select name="wpabi_options[show_bar]" id="show_bar">
            		<option value="1" <?php selected($this->options['show_bar'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['show_bar'], '0') ?>>Disabled</option>
            	</select>
           	</li>
           	
        	<li class="show_form">
            	<label for="show_form"><?php _e('Login Form in Admin Bar', $this->textdomain); ?>:</label>
            	<select name="wpabi_options[show_form]" id="show_form">
            		<option value="1" <?php selected($this->options['show_form'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['show_form'], '0') ?>>Disabled</option>
            	</select>
           	</li>
           	
        	<li class="ajax_login">
            	<label for="ajax_login"><?php _e('Ajax Login', $this->textdomain); ?>:</label>
            	<select name="wpabi_options[ajax_login]" id="ajax_login">
            		<option value="1" <?php selected($this->options['ajax_login'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['ajax_login'], '0') ?>>Disabled</option>
            	</select>
           	</li>
           	
        	<li class="ajax_search">
            	<label for="ajax_search"><?php _e('Ajax Search', $this->textdomain); ?>:</label>
            	<select name="wpabi_options[ajax_search]" id="ajax_search">
            		<option value="1" <?php selected($this->options['ajax_search'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['ajax_search'], '0') ?>>Disabled</option>
            	</select>
           	</li>
           	
        	<li class="toggleme">
            	<label for="toggleme"><?php _e('Show/Hide Button', $this->textdomain); ?>:</label>
            	<select name="wpabi_options[toggleme]" id="toggleme">
            		<option value="1" <?php selected($this->options['toggleme'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['toggleme'], '0') ?>>Disabled</option>
            	</select>
           	</li>
           	
        	<li class="reg_link">
            	<label for="reg_link"><?php _e('Registration Link in Form', $this->textdomain); ?>:</label>
            	<select name="wpabi_options[reg_link]" id="reg_link">
            		<option value="1" <?php selected($this->options['reg_link'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['reg_link'], '0') ?>>Disabled</option>
            	</select>
			</li>
           	
        	<li class="custom_menu">
            	<label for="custom_menu"><?php _e('Custom Menu Items', $this->textdomain); ?>:</label>
            	<select name="wpabi_options[custom_menu]" id="custom_menu">
            		<option value="1" <?php selected($this->options['custom_menu'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['custom_menu'], '0') ?>>Disabled</option>
            	</select>
            	<p><em><?php _e('When you enable this option, you will get a new location added to your "Theme Locations" box on your Appearance -> Menus admin page called "Admin Bar Improved." Use this as you would any other menu location for your site. Create a new menu on the Menus admin page, and name it whatever you\'d like, just keep it easy to identify. Add whatever items to that menu that you want. Nested menu items will work as expected. When you\'re finished, select the menu you created from the select list under the "Admin Bar Improved" theme location. Once completed, (and once you refresh the page, or navigate to another page) you will see that the items you added are now in your admin bar. Good job!', $this->textdomain); ?></em></p>
           	</li>
           	
           	<li><h3>Default Menu Items:</h3></li>
           	<li class="my-account-with-avatar">
           		<label for="my-account-with-avatar"><?php _e('My Account', $this->textdomain); ?>:</label>
           		<select name="wpabi_options[default_items][my-account-with-avatar]" id="my-account-with-avatar">
            		<option value="1" <?php selected($this->options['default_items']['my-account-with-avatar'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['default_items']['my-account-with-avatar'], '0') ?>>Disabled</option>
           		</select>
           	</li>
           	
           	<li class="view-site">
           		<label for="view-site"><?php _e('Visit Site (backend only)', $this->textdomain); ?>:</label>
           		<select name="wpabi_options[default_items][view-site]" id="view-site">
            		<option value="1" <?php selected($this->options['default_items']['view-site'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['default_items']['view-site'], '0') ?>>Disabled</option>
           		</select>
           	</li>
           	
           	<li class="dashboard">
           		<label for="dashboard"><?php _e('Dashboard (frontend only)', $this->textdomain); ?>:</label>
           		<select name="wpabi_options[default_items][dashboard]" id="dashboard">
            		<option value="1" <?php selected($this->options['default_items']['dashboard'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['default_items']['dashboard'], '0') ?>>Disabled</option>
           		</select>
           	</li>
           	
           	<li class="my-blogs">
           		<label for="my-blogs"><?php _e('My Sites', $this->textdomain); ?>:</label>
           		<select name="wpabi_options[default_items][my-blogs]" id="my-blogs">
            		<option value="1" <?php selected($this->options['default_items']['my-blogs'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['default_items']['my-blogs'], '0') ?>>Disabled</option>
           		</select>
           	</li>
           	
           	<li class="new-content">
           		<label for="new-content"><?php _e('Add New', $this->textdomain); ?>:</label>
           		<select name="wpabi_options[default_items][new-content]" id="new-content">
            		<option value="1" <?php selected($this->options['default_items']['new-content'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['default_items']['new-content'], '0') ?>>Disabled</option>
           		</select>
           	</li>
           	
           	<li class="comments">
           		<label for="comments"><?php _e('Comments', $this->textdomain); ?>:</label>
           		<select name="wpabi_options[default_items][comments]" id="comments">
            		<option value="1" <?php selected($this->options['default_items']['comments'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['default_items']['comments'], '0') ?>>Disabled</option>
           		</select>
           	</li>
           	
           	<li class="appearance">
           		<label for="appearance"><?php _e('Appearance', $this->textdomain); ?>:</label>
           		<select name="wpabi_options[default_items][appearance]" id="appearance">
            		<option value="1" <?php selected($this->options['default_items']['appearance'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['default_items']['appearance'], '0') ?>>Disabled</option>
           		</select>
           	</li>
           	
           	<li class="edit">
           		<label for="edit"><?php _e('Edit', $this->textdomain); ?>:</label>
           		<select name="wpabi_options[default_items][edit]" id="edit">
            		<option value="1" <?php selected($this->options['default_items']['edit'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['default_items']['edit'], '0') ?>>Disabled</option>
           		</select>
           	</li>
           	
           	<li class="get-shortlink">
           		<label for="get-shortlink"><?php _e('Shortlink', $this->textdomain); ?>:</label>
           		<select name="wpabi_options[default_items][get-shortlink]" id="get-shortlink">
            		<option value="1" <?php selected($this->options['default_items']['get-shortlink'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['default_items']['get-shortlink'], '0') ?>>Disabled</option>
           		</select>
           	</li>
           	
           	<li class="updates">
           		<label for="updates"><?php _e('Updates', $this->textdomain); ?>:</label>
           		<select name="wpabi_options[default_items][updates]" id="updates">
            		<option value="1" <?php selected($this->options['default_items']['updates'], '1') ?>>Enabled</option>
            		<option value="0" <?php selected($this->options['default_items']['updates'], '0') ?>>Disabled</option>
           		</select>
           	</li>
           	
        	<li class="submit">
            	<label>&nbsp;</label>
                <?php submit_button( __('Save Options', $this->textdomain), 'primary', 'submit', false, array( 'tabindex' => '2' ) ); ?>
           	</li>
        </ul>
	</form>
</div>
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
		<p><?php _e("Edit the CSS/JS for the Admin Bar and the Ajax Search below.", $this->textdomain); ?></p>
        
		<form id="template" method="post" action="">
			<input type="hidden" name="scrollto" id="scrollto" value="<?php echo $this->scrollto; ?>" />
			<textarea cols="70" rows="25" name="newcontent" id="newcontent" tabindex="1"><?php echo $content ?></textarea>
			<?php wp_nonce_field('wpabi_referrer','wpabi_referrer_nonce'); ?>
			<?php submit_button( __('Update File', $this->textdomain), 'primary', 'submit', false, array( 'tabindex' => '2' ) ); ?>
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
			echo '<div id="message" class="updated"><p>'.__('File edited successfully.', $this->textdomain).'</p></div>';
        };
	}
}

register_activation_hook(__FILE__, array('WPAdminBarImproved', 'activation_hook'));
register_deactivation_hook(__FILE__, array('WPAdminBarImproved', 'deactivation_hook'));

// Start this plugin once all other files and plugins are fully loaded
add_action( 'plugins_loaded', create_function( '', 'global $WPAdminBarImproved; $WPAdminBarImproved = new WPAdminBarImproved();' ), 15 );
add_action( 'wp_loaded', create_function( '', 'global $WPAdminBarImproved; $WPAdminBarImproved->ajax_search(); $WPAdminBarImproved->ajax_login();' ), 15 );
