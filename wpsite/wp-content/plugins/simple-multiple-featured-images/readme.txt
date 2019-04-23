=== Simple Multiple Featured Images ===
Tags: multiple featured image, multiple post thumbnail, featured image, post thumbnail, dynamic featured image, dynamic post thumbnail
Requires at least: 4.9
Tested up to: 5.1
Requires PHP: 7.1
Stable tag: 1.0.6
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Allows adding multiple featured images.

== Description ==
If you create posts or pages on Wordpress, you usually have the option of attaching an image as a so-called »Featured Image«. If the featured image is supported by a Wordpress theme, it will be displayed on the website. It is a very popular and easy-to-use feature. However, it often happens that one wishes to add more featured images than one. This plugin provides a remedy. The plugin allows you to add multiple featured images. It integrates seamlessly into the wordpress design and acts as if it were an integral part of Wordpress.

You can find more information and
a comprehensive documentation about the plugin [here](https://roman-bauer-web.de/wordpress-plugin-smfi).

== Installation ==
1. Upload the plugins `simple-multiple-featured-images` directory to the `/wp-content/plugins/` directory or download the plugin directly via wordpress.
1. Activate the plugin through the \'Plugins\' menu in WordPress.
1. Now you can attach multiple featured images to posts and pages. To visualize the images on the website, a theme must support it! Currently, there are no shortcodes that allow the integration of the images into the website via wordpress editor.

== Changelog ==

= 1.0.6 =
* Added new hook to turn off the drag and drop.
* Added new hook to turn off the shortcodes.

= 1.0.5 =
* Fixed a bug that prevented the images from being showed correctly in the gutenberg editor on initial page load.
* Fixed a bug that prevented the images from being showed correctly if the initial collapsed metabox was expanded by user.
* Fixed a bug that prevented the move icon from being showed on new added images.
* Fixed a bug that caused an error as soon as the position of the images was recalculated while the metabox was empty.
* Updated some code documentation.
* Optimized arrow icon for web usage.

= 1.0.4 =
* Fixed a bug that prevented the images from being saved in the correct order.

= 1.0.3 =
* Fixed a bug which produced an error during saving a post. The error occured if no featured images were added.

= 1.0.2 =
* Drag and drop support added.
* Introduced shortcodes.

= 1.0.1 =
* Fixed a problem with firefox.
* Added more code documentation.

== Upgrade Notice ==

= 1.0.6 =
Update the plugin to get two new hooks which allows you to turn off the drag and drop and the shortcodes.

= 1.0.5 =
Update the plugin to make if work with gutenberg and get some bug fixes.

= 1.0.4 =
Update the plugin to get an important bug fix in context of image sorting.

= 1.0.3 =
Update the plugin to get an important bug fix.

= 1.0.2 =
Update the plugin to get drag and drop and shortcode support.

= 1.0.1 =
Update the plugin to make it work properly in Firefox.
