=== WordPress Admin Bar Improved ===
Contributors: dilbert4life, electriceasel
Tags: admin bar, top, login form, 3.1+, ajax, search
Requires at least: 3.1
Tested up to: 3.2.1
Stable tag: 3.3.1

A set of custom tweaks to the WordPress Admin Bar that was introduced in WP3.1.

== Description ==

This plugin has been completely re-written to interface with the WP3.1 admin bar, and adds some much requested / great features to the WP Admin Bar.

Check the post on this plugin over at our site, [Electric Easel](http://www.electriceasel.com/plugins/wordpress-admin-bar-improved), for instructions, updates, and other news.

Features:

* Easy Interface using WP3.0 Menus to add custom menu items to your Admin Bar.
<<<<<<< HEAD
* Displays a Login form on the front end of your site in the WP Admin Bar.
* Ajax Search Popup in from Admin Bar Search Form
* Manage default menu items.
* Ability to Show or Hide the admin bar by clicking the Show/Hide Box that appears below the top left corner of the admin bar
=======
* Displays a login form on the front end of your site in the WP Admin Bar.
* Ajax Login
* Ajax Search from form in admin bar
* Ability to show or hide the admin bar by clicking the Show/Hide box that appears below the center of the admin bar
>>>>>>> cb625136c24b83a4c23b49288bd751f3969e7d9e
* More to come...

ToDo:

* Make everything work with MultiSite
* Popup notification/error messages

== Installation ==

Use the built in WordPress Plugin Installer via Plugins -> Add New, searching for  "WordPress Admin Bar Improved"

OR

Extract all files from the ZIP file, making sure to keep the file structure intact, and then upload the plugin's folder to `/wp-content/plugins/` via FTP.

This should result in the following file structure:

`- wp-content
    - plugins
        - wordpress-admin-bar-improved
            | readme.txt
            | screenshot-1.png
            | wpab-admin.css
            | wpabi-admin.js
            | wpabi.css
            | wpabi.js
            | wpabi.php`

Then just visit your admin area and activate the plugin on the Plugins screen.

== Frequently Asked Questions ==

<<<<<<< HEAD
= HELP! Something got messed up! = 

You can reset the settings for WordPress Admin Bar Improved by simply deactivation and then re-activating the plugin through the Plugin Admin interface.

= How to add menu items = 
=======
= How to change the settings? =
>>>>>>> cb625136c24b83a4c23b49288bd751f3969e7d9e

In your WordPress admin dashboard, click the Settings -> WPABI menu item. (Taking suggestions for a better menu name.)

= How to add menu items? = 

In the admin area of your site, click "Menus" under the "Appearance" menu. Create a new menu named `WPABI` or something like that. Add whatever menu items you want to it, and save the menu. In the box titled "Theme Locations" on the "Menus" screen, select the menu you created in the `Admin Bar Improved` dropdown select list, and hit save. After that, your menu items will appear in your admin bar for both logged in and logged out users.!

= It's not working! =

Do you have WP3.1 installed?
Have you opted to NOT show the admin bar within your user profile settings?
Is jQuery loading properly?
Did you try to hover over the top center of your site to see if the Show/Hide button appears?


== ChangeLog ==

= 3.3.1 =
* Added ability to manage default menu items.
* Cleaned up the code / made it simpler to maintain.
* Added help text for custom menus.
* Implemented `register_activation_hook` to set default settings.
* Implemented `register_deactivation_hook` to clean up database upon deactivation.
* Bugfix: Fixed login form when ajax login is disabled.

= 3.3 =
* Added ajax login feature.

= 3.2.3 =
* Show/hide feature now persists across pages. Utilizes jQuery.cookie.

= 3.2 =
* Added easy integration for custom menu items using WP3.0 menu system.

= 3.1.7 =
* Added option to turn off the Show/Hide feature in admin panel

= 3.1.6 =
* Fixed issue that triggered a fatal error upon installation.

= 3.1.5 =
* Fixed multisite admin page
* Added ability to enable the register link next to the login form.

= 3.1.4 =
* Added admin screen for enabling/disabling the login form
* Added ability to edit CSS and JS for the file directly on the admin screen, instead of going through the plugin editor.

= 3.1.3 =
* Added ajax search to search form
* fixed bug in IE

= 3.1.2 =
* Added ability to show or hide the admin bar

= 2.0 =
*Completely re-written to interface with WP3.1 admin bar.
*Dumped previous version
