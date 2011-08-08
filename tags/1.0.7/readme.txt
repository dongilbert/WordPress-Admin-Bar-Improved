=== WordPress Admin Bar Improved===
Contributors: IFBDesign - Don Gilbert
Donate link: http://ifbdesign.com/donate/
Tags: admin, bar, top, login
Requires at least: 2.7
Tested up to: 2.9
Stable tag: 1.0.7

An upload-and-activate plugin that creates an admin bar at the top of your site like the one at WordPress.com. It allows users to log in from any page.

== Description ==

Based on the WordPress Admin Bar by Viper007Bond, the Wordpress Admin Bar Improved has added the ability to show up whether the user is logged in or not, and if not, offers a form to login.

Viper007Bond has asked that I include a link to his, as he is planning (sometime) to update his with new features that may not end up in this Plugin. Here's the link.
[WordPress Admin Bar](http://wordpress.org/extend/plugins/wordpress-admin-bar/).

This Plugin replicates all of the menu links in your normal admin area at the top of your main site for logged in users (i.e. you). You can go right to the "Write Post" or manage options pages in one click from anywhere on your blog. No more having to go to your dashboard first. You can even have it replace your admin area menus if you want.

It features a full options page where you can hide any of the menus or switch themes.

== Installation ==

###Upgrading From A Previous Version###

To upgrade from a previous version of this plugin, delete the entire folder and files from the previous version of the plugin and then follow the installation instructions below.

Alternatively, you can have WordPress upgrade it for you automatically via the Plugin Admin Page.

###Installing The Plugin###

For now, in order for the Login Form to work correctly, you need to have WordPress installed in the root directory, not a sub-directory. (i.e. - site name - example.com, but you have WordPress installed in the example.com/blog directory.) I am currently working on a fix for that.

Extract all files from the ZIP file, making sure to keep the file structure intact, and then upload the plugin's folder to `/wp-content/plugins/`.

This should result in the following file structure:

`- wp-content
    - plugins
        - wordpress-admin-bar-improved
            | jquery.checkboxes.pack.js
            | readme.txt
            | screenshot.png
            | wordpress-admin-bar-improved.js
            | wordpress-admin-bar-improved.php
            - themes
                - blue
                    | blue.css
                    - images
                    [...]`

Then just visit your admin area and activate the plugin.

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

###Installing The Plugin For WordPress MU###

Install normally, but move **only** the `wordpress-admin-bar-improved.php` to the `mu-plugins` folder. Leave the rest of the files in their normal place.

== Frequently Asked Questions ==

= It's not working! =

If it's not working for you, first try switching to the default WordPress theme. If that makes it show up, then you know it's an issue with your regular theme. Make sure your theme has `<?php wp_head(); ?>` inside it's `<head>` of it's `header.php` file and `<?php wp_footer(); ?>` somewhere in it's `footer.php` file, like before `</body>`.

== Screenshots ==

1. How the plugin appears for logged out Users
2. How the Plugin appears for logged in Users

== ChangeLog ==
**Version 1.0.7**

* Updated Compatibility with WordPress 2.9
* Bugfix - some users were saying that the plugin was not working as expected.
* CSS Enhancements / Refinements
* Version 2.0 ToDo
* Remove templating "feature" - would like to make the look more static and not customizable - just because by default most themes will kill the look of the login form.
* Various other fixes - please suggest - email me on it.
* Option to hide the default Wordpress Admin Bar (Like the Ozh Admin Dropdown Menu) - It saves on screen real-estate :)

**Version 1.0.6**

* Updated the readme.txt and some code. (Made it cleaner.)


**Version 1.0.3**

* Initial release.
