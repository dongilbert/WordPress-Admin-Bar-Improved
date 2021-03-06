<?php
/*
**************************************************************************

Plugin Name:  WordPress Admin Bar Improved
Plugin URI:   http://mactimize.com/?p=263
Description:  Creates an admin bar inspired by the one at <a href="http://wordpress.com/">WordPress.com</a>. Credits for the base of this plugin goes to Viper007Bond.
Version:      1.0.7.1
Author:       IFBDesign - Don Gilbert
Author URI:   http://ifbdesign.com

**************************************************************************

Copyright (C) 2009 IFBDesign

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
	var $version = '1.0.7.1';
	var $settings = array();
	var $defaults = array();
	var $themes = array();
	var $menu = array();
	var $submenu = array();
	var $folder;

	// Plugin initialization
	function WPAdminBarImproved() {
		// This version only supports WP 2.7+
		if ( !function_exists('wp_list_comments') )
			return;

		// Don't do anything within the media upload iframe
		if ( 'media-upload.php' == basename( $_SERVER['PHP_SELF'] ) )
			return;

		// Load up the localization file if we're using WordPress in a different language
		// Place it in this plugin's folder and name it "wordpress-admin-bar-improved-[value in wp-config].mo"
		load_plugin_textdomain( 'wordpress-admin-bar-improved', FALSE, '/wordpress-admin-bar-improved/localization' );

		// Register the admin settings page hooks
		add_action( 'admin_menu', array(&$this, 'AddAdminMenu') );
		//add_filter( 'plugin_action_links', array(&$this, 'AddPluginActionLink'), 10, 2 );
		add_action( 'load-tools_page_wordpress-admin-bar-improved', array(&$this, 'SettingsPageInit') );
		add_action( 'admin_head-tools_page_wordpress-admin-bar-improved', array(&$this, 'AdminCSS') );
		add_action( 'admin_post_wordpress-admin-bar-improved', array(&$this, 'HandleFormPOST') );

		// Modify the menu array a little to make it better
		add_filter( 'wpabarimp_menuitems', array(&$this, 'AddSingleEditLink') );
		add_filter( 'wpabarimp_menuitems', array(&$this, 'CommentsAwaitingModCount') );
		add_filter( 'wpabarimp_menuitems', array(&$this, 'PluginsUpdateCount') );

		$this->folder = plugins_url('wordpress-admin-bar-improved');

		// Create the list of default themes
		// Theme authors: use wpabarimp_register_theme() instead of the "wpabarimp_themes" filter
		$this->themes = apply_filters( 'wpabarimp_themes', array(
			'wordpress_grey' => array(
				'name' => __( 'WordPress Grey', 'wordpress-admin-bar-improved' ),
				'css' => $this->folder . '/themes/grey/grey.css?ver=' . $this->version,
			),
			'wordpress_blue' => array(
				'name' => __( 'WordPress Blue', 'wordpress-admin-bar-improved' ),
				'css' => $this->folder . '/themes/blue/blue.css?ver=' . $this->version,
			),
			'wordpress_green' => array(
				'name' => __( 'WordPress Green', 'wordpress-admin-bar-improved' ),
				'css' => $this->folder . '/themes/green/green.css?ver=' . $this->version,
			),
			'wordpress_orange' => array(
				'name' => __( 'WordPress Orange', 'wordpress-admin-bar-improved' ),
				'css' => $this->folder . '/themes/orange/orange.css?ver=' . $this->version,
			),
			'wordpress_purple' => array(
				'name' => __( 'WordPress Purple', 'wordpress-admin-bar-improved' ),
				'css' => $this->folder . '/themes/purple/purple.css?ver=' . $this->version,
			),
			'wordpress_red' => array(
				'name' => __( 'WordPress Red', 'wordpress-admin-bar-improved' ),
				'css' => $this->folder . '/themes/red/red.css?ver=' . $this->version,
			),
			'wordpress_yellow' => array(
				'name' => __( 'WordPress Yellow', 'wordpress-admin-bar-improved' ),
				'css' => $this->folder . '/themes/yellow/yellow.css?ver=' . $this->version,
			),
			'wordpress_classic' => array(
				'name' => __( 'WordPress Classic', 'wordpress-admin-bar-improved' ),
				'css' => $this->folder . '/themes/classic/classic.css?ver=' . $this->version,
			),
		) );

		// Stop here until the theme is loaded (so it can add any admin bar themes)
		add_action( 'init', array(&$this, 'InitializationPart2'), 1 );
	}


	// The rest of the plugin's initialization
	function InitializationPart2() {
		$themekeys = array_keys($this->themes);

		// Create the default settings
		$this->defaults = apply_filters( 'wpabarimp_defaults', array(
			'show_site'  => '1',
			'show_admin' => '1',
			'theme'      => $themekeys[0],
			'hide'       => array(),
		) );

		// Load the settings
		global $user_ID;
		$settings = get_option( 'wordpress-admin-bar-improved' );
		if ( !is_array($settings) )
			$settings = array();
		if ( empty($settings[$user_ID]) )
			$settings[$user_ID] = $this->defaults;
		$this->settings = $settings[$user_ID]; // For coding ease
		foreach ( $this->defaults as $setting => $value ) {
			if ( !isset($this->settings[$setting]) && !is_null($this->settings[$setting]) )
				$this->settings[$setting] = $value;
		}

		// Make sure a valid theme is requested
		if ( !array_key_exists( $this->settings['theme'], $this->themes ) )
			$this->settings['theme'] = $themekeys[0];

		// Register the hooks to display the admin bar
		if ( 1 == $this->settings['show_site'] ) {
			add_action( 'wp_head', array(&$this, 'OutputCSS') );
			add_action( 'wp_footer', array(&$this, 'OutputMenuBar') );
		}
		if ( 1 == $this->settings['show_admin'] ) {
			add_action( 'admin_head', array(&$this, 'OutputCSS') );
			add_action( 'admin_footer', array(&$this, 'OutputMenuBar') );
		}

		// Load the little JS file for this plugin
		wp_enqueue_script( 'wordpress-admin-bar-improved', $this->folder . '/wordpress-admin-bar-improved.js', array(), $this->version );
	}


	// Register the settings page
	function AddAdminMenu() {
		add_submenu_page( 'tools.php', __('WordPress Admin Bar', 'wordpress-admin-bar-improved'), __('Admin Bar', 'wordpress-admin-bar-improved'), 'read', 'wordpress-admin-bar-improved', array(&$this, 'SettingsPage') );
	}


	// Add a link to the settings page to the plugins list
	function AddPluginActionLink( $links, $file ) {
		static $this_plugin;
		
		if( empty($this_plugin) ) $this_plugin = plugin_basename(__FILE__);

		if ( $file == $this_plugin ) {
			$settings_link = '<a href="options-general.php?page=wordpress-admin-bar-improved">' . __('Settings') . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}


	// Register the checkboxes script
	function SettingsPageInit() {
		wp_enqueue_script( 'jquery-checkboxes', $this->folder . '/jquery.checkboxes.pack.js', array('jquery'), '2.1' );
	}


	// Setup the menu arrays
	function SetupMenu() {
		if ( !empty($this->menu) ) return;

		global $wpdb, $menu, $submenu, $_wp_submenu_nopriv, $wp_taxonomies;

		require_once( ABSPATH . 'wp-admin/includes/admin.php' );
		require_once( ABSPATH . 'wp-admin/menu.php' );

		// The top-level menus
		foreach( $menu as $id => $menuitem ) {
			if ( empty($menuitem[2]) || false !== strpos($menuitem[4], 'wp-menu-separator') ) continue;
			$custom = ( !empty($menuitem[3]) ) ? TRUE : FALSE;
			$this->menu[$menuitem[2]] = array( 0 => array( 'id' => $id, 'title' => $menuitem[0], 'custom' => $custom ) );
		}

		// All children menus
		foreach( $submenu as $parent => $submenuitem ) {
			foreach( $submenuitem as $id => $item ) {
				$custom = ( isset($item[3]) ) ? TRUE : FALSE;
				if ( $parent == $item[2] )
					$custom = FALSE;
				$this->menu[$parent][$item[2]] = array( 'id' => $id, 'title' => $item[0], 'custom' => $custom );
			}
		}

		$this->menu = apply_filters( 'wpabarimp_menuitems', $this->menu );
	}


	// Handle settings page POST
	function HandleFormPOST() {
		global $user_ID;

		check_admin_referer( 'wordpress-admin-bar-improved' );

		// Start with a full menu and remove items that were checked. We'll be left with items to hide.
		$this->SetupMenu();
		$hide = $this->menu;
		foreach ( $this->menu as $topfilename => $menuitem ) {
			foreach( $menuitem as $submenustub => $submenutitle ) {
				if ( isset( $_POST['wpabarimp_items'][$topfilename][$submenustub] ) )
					unset( $hide[$topfilename][$submenustub] );
				else
					$hide[$topfilename][$submenustub] = TRUE; // Reduce array size
			}
		}
		foreach ( $hide as $topfilename => $menuitem ) {
			if ( empty($menuitem) )
				unset( $hide[$topfilename] );
		}

		$settings = get_option( 'wordpress-admin-bar-improved' );
		if ( !is_array($settings) )
			$settings = array();

		$settings[$user_ID] = array(
			'show_site'  => $_POST['wpabarimp_site'],
			'show_admin' => $_POST['wpabarimp_admin'],
			'theme'      => $_POST['wpabarimp_theme'],
			'hide'       => $hide,
		);

		update_option( 'wordpress-admin-bar-improved', $settings );

		wp_safe_redirect( add_query_arg( 'updated', 'true', wp_get_referer() ) );
	}


	// Some CSS for the settings page
	function AdminCSS() { ?>
	<style type="text/css">
		#wpabarimplist ul {
			margin: 5px 0 0 25px;
		}
	</style>
<?php
	}


	// The settings page for this plugin
	function SettingsPage() {
		global $user_ID;

		$this->SetupMenu();

		/*
		echo '<pre>';
		print_r( $this->settings );
		echo '</pre>';
		*/

		?>

<script type="text/javascript">
	//<![CDATA[
		// My Javascript skills, especially when it comes to jQuery, are kinda poor so this code may be ugly as hell
		jQuery(document).ready(function() {
			jQuery("#wpabarimplist").children("li").children("label").children("input[type=checkbox]").bind("change", function() {
				if ( true == jQuery(this).attr("checked") ) {
					jQuery(this).parent("label").parent("li").checkCheckboxes();
				} else {
					jQuery(this).parent("label").parent("li").unCheckCheckboxes();
				}
			});
			jQuery("#wpabarimplist").children("li").children("ul").children("li").children("label").children("input[type=checkbox]").bind("click", function() {
				if ( true == jQuery(this).attr("checked") ) {
					jQuery(this).parents(".wpabarimp_toplevel").children("label").children("input[type=checkbox]").attr("checked","checked");
				}
			});
		});
	//]]>
</script>

<div class="wrap">
	<h2><?php _e( 'WordPress Admin Bar', 'wordpress-admin-bar-improved' ); ?></h2>

	<form method="post" action="admin-post.php">
<?php wp_nonce_field('wordpress-admin-bar-improved'); ?>

	<p><?php _e( 'Everything here applies to your account only.', 'wordpress-admin-bar-improved' ); ?></p>

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e( 'Show the admin bar', 'wordpress-admin-bar-improved' ); ?></th>
			<td>
				<label for="wpabarimp_site"><input name="wpabarimp_site" type="checkbox" id="wpabarimp_site" value="1"<?php checked( 1, $this->settings['show_site'] ); ?> /> <?php _e( 'On the site', 'wordpress-admin-bar-improved' ); ?></label><br />
				<label for="wpabarimp_admin"><input name="wpabarimp_admin" type="checkbox" id="wpabarimp_admin" value="1"<?php checked( 1, $this->settings['show_admin'] ); ?> /> <?php _e( 'In the admin area', 'wordpress-admin-bar-improved' ); ?></label>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="wpabarimp_theme"><?php _e( 'Theme', 'wordpress-admin-bar-improved' ); ?></label></th>
			<td>
				<select name="wpabarimp_theme" id="wpabarimp_theme">
<?php foreach( $this->themes as $stub => $details ) : ?>
					<option value="<?php echo $stub; ?>"<?php selected( $stub, $this->settings['theme'] ); ?>><?php echo htmlspecialchars( $details['name'] ); ?></option>
<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e( 'Show the following items', 'wordpress-admin-bar-improved' ); ?></th>
			<td>
				<ul id="wpabarimplist">
<?php
		foreach ( $this->menu as $topfilename => $menuitem ) {
			echo '					<li class="wpabarimp_toplevel"><label><input type="checkbox" name="wpabarimp_items[' . $topfilename . '][0]"';
			if ( empty( $this->settings['hide'][$topfilename][0] ) ) echo ' checked="checked"';
			echo ' /> ' . strip_tags($menuitem[0]['title']) . '</label>';

			if ( count($menuitem) > 1 ) {
				echo "\n						<ul>\n";
				foreach( $menuitem as $submenustub => $submenu ) {
					if ( 0 === $submenustub ) continue;
					echo '							<li><label><input type="checkbox" name="wpabarimp_items[' . $topfilename . '][' . $submenustub . ']"';
					if ( empty( $this->settings['hide'][$topfilename][$submenustub] ) ) echo ' checked="checked"';
					echo ' /> ' . strip_tags($submenu['title']) . "</label></li>\n";
				}
				echo "						</ul>\n					";
			}

			echo "</li>\n";
		}
?>
				</ul>
			</td>
		</tr>
	</table>


	<p class="submit">
		<input type="hidden" name="action" value="wordpress-admin-bar-improved" />
		<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
	</p>

	</form>
</div>

<?php
	}


	// Output the needed CSS for the plugin
	function OutputCSS() { ?>
	<link rel="stylesheet" href="<?php echo $this->themes[$this->settings['theme']]['css']; ?>" type="text/css" />
	<!--[if lt IE 7]><style type="text/css">#wpabarimp { position: absolute; } #wpabarimp .wpabarimp-menupop li a { width: 100%; }</style><![endif]-->
<?php
		if ( is_admin() ) {
			echo '	<style type="text/css">#wpabarimplist ul { margin: 5px 0 0 25px; }</style>' . "\n";
		}
	}


	// Generate and output the HTML for the admin menu
	function OutputMenuBar() {
		$this->SetupMenu();
?>

<!-- Start WordPress Admin Bar -->
<div id="wpabarimp">
	<div id="wpabarimp-leftside">
		<ul>
			<!-- Begin IFBDesign Customization -->
			<?php if ( !is_user_logged_in() ) { ?>
            <li><form style="margin:2px 0pt 0pt 10px" name="loginform" id="loginform" action="/wp-login.php" method="post">
			<li><input type="text" id="user_login" name="log" tabindex="1" value="Username"onblur="if (this.value == '') {this.value = 'Username';}" onfocus="if (this.value == 'Username') {this.value = '';}" value="username here"/>
			<input type="password" id="user_pass" name="pwd" tabindex="2" value="Password" onblur="if (this.value == '') {this.value = 'Password';}" onfocus="if (this.value == 'Password') {this.value = '';}"/>
			<button type="submit" id="wp-submit" name="wp-submit" value="true" tabindex="4">Log In</button>
            <input type="checkbox" id="rememberme" name="rememberme" value="forever" tabindex="3" /><span style="color:#FFFFFF;">Remember Me</span>
			</form>
			<?php } else { ?>
<?php

			$first = TRUE;
			$switched = FALSE;

			foreach( $this->menu as $topstub => $menu ) {
				if ( FALSE === $switched && 39 < $menu[0]['id'] ) {
					echo "		</ul>\n	</div>\n	<div id=\"wpabarimp-rightside\">\n		<ul>\n";
					$switched = TRUE;
				}

				if ( isset($this->settings['hide'][$topstub][0]) && ( 'options-general.php' !== $topstub || !is_admin() ) ) continue;

				if ( 1 == count($menu) ) {
					echo '			<li class="wpabarimp-menu_';
					if ( TRUE === $menu[0]['custom'] ) echo 'admin-php_';
					echo str_replace( '.', '-', $topstub );

					if ( TRUE == $first ) {
						echo ' wpabarimp-menu-first';
						$first = FALSe;
					}

					echo '"><a href="' . admin_url( $topstub ) . '">' . $menu[0]['title'] . "</a></li>\n";
				} else {
					echo '			<li class="wpabarimp-menu_';
					if ( TRUE === $menu[0]['custom'] ) echo 'admin-php_';
					echo str_replace( '.', '-', $topstub );

					if ( TRUE == $first ) {
						echo ' wpabarimp-menu-first';
						$first = FALSe;
					}

					$url = ( TRUE === $menu[0]['custom'] ) ? 'admin.php?page=' . $topstub : $topstub;

					echo ' wpabarimp-menupop" onmouseover="showNav(this)" onmouseout="hideNav(this)">' . "\n" . '				<a href="' . admin_url( $url ) . '"><span class="wpabarimp-dropdown">' . $menu[0]['title'] . "</span></a>\n				<ul>\n";

					foreach( $menu as $submenustub => $submenu ) {
						if ( 0 === $submenustub || ( !empty($this->settings['hide'][$topstub]) && TRUE === $this->settings['hide'][$topstub][$submenustub] && ( 'wordpress-admin-bar-improved' !== $submenustub || !is_admin() ) ) )
							continue;

						$parent = ( !empty($menu[0]['custom']) && TRUE === $menu[0]['custom'] ) ? 'admin.php' : $topstub;
						$url = ( !empty($submenu['custom']) && TRUE === $submenu['custom'] ) ? $parent . '?page=' . $submenustub : $submenustub;

						echo '					<li><a href="' . admin_url( $url ) . '">' . $submenu['title'] . "</a></li>\n";
					}

					echo "				</ul>\n			</li>\n";
				}
			}
		}

?>
            <?php if ( !is_user_logged_in() ) { ?>
            </ul></div><div id="wpabar-rightside"><ul>
		<div style="margin:-2px 0px;">
            <?php } ?>
            <li><?php wp_loginout(); ?></li>
            <?php if ( !is_user_logged_in() ) { ?>
            </div>
            <?php } ?>
		</ul>
	</div>
</div>

<?php
	}

	// If viewing a single post or page, filter in a link to edit it
	function AddSingleEditLink( $menu ) {
		global $posts;

		if ( empty($posts) )
			return $menu;

		$post_ID = $posts[0]->ID;

		if ( ( is_single() && current_user_can( 'edit_post', $post_ID ) ) || ( is_page() && current_user_can( 'edit_page', $post_ID ) ) ) {
			if ( function_exists('get_edit_post_link') )
				$editlink = str_replace( get_bloginfo('wpurl') . '/wp-admin/', '', get_edit_post_link( $post_ID ) );
			else
				$editlink = 'post.php?action=edit&post=' . $post_ID;

			$editarray = array( $editlink => array( 'id' => 1, 'title' => __('Edit This') ) );

			$page = ( is_page() ) ? 'edit-pages.php' : 'edit.php';

			$menu[$page] = $editarray + $menu[$page];
		}

		return $menu;
	}


	// Tweak the comments awaiting moderation count
	function CommentsAwaitingModCount( $menu ) {
		if ( !isset($menu['edit-comments.php'][0]['title']) || stristr( $menu['edit-comments.php'][0]['title'], 'count-0' ) )
			return $menu;

		$menu['edit-comments.php'][0]['title'] = str_replace(
			array( "<span id='awaiting-mod' class='count-", '</span></span>' ),
			array( "(<span title='" . __( 'Number of comments in the moderation queue', 'wordpress-admin-bar-improved' ) . "' class='awaiting-mod count-", '</span></span>)'),
			$menu['edit-comments.php'][0]['title']
		);
		
		return $menu;
	}


	// Tweak the plugins needing updating count
	function PluginsUpdateCount( $menu ) {
		if ( !isset($menu['plugins.php'][0]['title']) || stristr( $menu['plugins.php'][0]['title'], 'count-0' ) )
			return $menu;

		$menu['plugins.php'][0]['title'] = str_replace(
			array( "<span class='update-plugins", '</span></span>' ),
			array( "(<span title='" . __( 'Number of plugins needing upgrading', 'wordpress-admin-bar-improved' ) . "' class='update-plugins", '</span></span>)'),
			$menu['plugins.php'][0]['title']
		);
		
		return $menu;
	}
}


// Start this plugin once all other files and plugins are fully loaded
add_action( 'plugins_loaded', create_function( '', 'global $WPAdminBarImproved; $WPAdminBarImproved = new WPAdminBarImproved();' ), 15 );


// Call this from your theme's functions.php to easily add a new bar style for your theme
function wpabarimp_register_theme( $name, $stub, $filename ) {
	global $WPAdminBarImproved;
	$newtheme = array( $stub => array( 'name' => $name, 'css' => get_bloginfo('wpurl') . '/wp-content/themes/' . get_template() . '/' . $filename ) );
	$WPAdminBarImproved->themes = $newtheme + $WPAdminBarImproved->themes; // Add it as the first one so it'll be the default
}

?>
