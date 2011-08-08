=== WordPress Admin Bar Improved ===
Contributors: dilbert4life, electriceasel
Tags: admin bar, top, login form, 3.1+, ajax, search
Requires at least: 3.1
Tested up to: 3.2 beta1
Stable tag: 3.1.3

A set of custom tweaks to the WordPress Admin Bar that was introduced in WP3.1.

== Description ==

This plugin has been completely re-written to interface with the WP3.1 admin bar, instead of what it used to be, which was an enhancement of the now defunct [WordPress Admin Bar](http://wordpress.org/extend/plugins/wordpress-admin-bar/).

Check the post on this plugin over at our site, [Electric Easel](http://www.electriceasel.com/plugins/wordpress-admin-bar-improved), for instructions, updates, and other news.

**Features**
* Displays a Login form on the front end of your site in the WP Admin Bar.
* Ajax Search Popup in from Admin Bar Search Form
* Ability to Show or Hide the admin bar by clicking the Show/Hide Box that appears below the top left corner of the admin bar
* More to come...



== Installation ==

Use the built in WordPress Plugin Installer via Plugins -> Add New, searching for  "WordPress Admin Bar Improved"

OR

Extract all files from the ZIP file, making sure to keep the file structure intact, and then upload the plugin's folder to `/wp-content/plugins/`.

This should result in the following file structure:

`- wp-content
    - plugins
        - wordpress-admin-bar-improved
            | readme.txt
            | wpabi.css
            | wpabi.js
            | wpabi.php`

Then just visit your admin area and activate the plugin.

== Frequently Asked Questions ==

= It's not working! =

Do you have WP3.1 installed?
Have you opted to NOT show the admin bar within your user profile settings?
Is jQuery loading properly?
Did you try to hover over the top left corner of your site?


== ChangeLog ==

= 3.1.3 =
* Added ajax search to search form
* fixed bug in IE

= 3.1.2 =
* Added ability to show or hide the admin bar

= 2.0 =
*Completely re-written to interface with WP3.1 admin bar.
*Dumped previous version
