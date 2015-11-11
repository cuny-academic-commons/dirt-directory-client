=== DiRT Directory Client ===
Contributors: boonebgorges, cuny-academic-commons
Tags: dirt, digital, tools, buddypress
Requires at least: 4.0
Tested up to: 4.3.1
Stable tag: 1.0.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Integrate the DiRT Directory http://dirtdirectory.org into your WordPress and BuddyPress site.

== Description ==

The DiRT Directory http://dirtdirectory.org is a directory of digital research tools for scholarly use. The DiRT Directory Client plugin interacts with DiRT's API, allowing users on your BuddyPress site to browse DiRT tools and add them to their profiles.

When activated, group administrators have the option of enabling a Digital Research Tools tab for the group. Group members can then search the DiRT Directory, adding tools to their profile by clicking the 'I use this' button next to an individual tool. Tools that are used by site members have profiles on your WordPress site, where local users are listed.

User-facing features of this plugin require BuddyPress. The plugin contains a standalone client for the DiRT API, which developers can use as a basis for building more general WordPress integration tools.

The plugin is designed for themes that use BuddyPress's theme compatibility system. If your theme does not use theme compatibility (usually this means it's a derivative of bp-default), you'll need to do some manual modification to your theme. Create a directory in your theme called 'dirt', and copy the plugin file `dirt-directory-client/templates/dirt/non-theme-compat-index.php` to the new directory.

Development of this tool was sponsored by a grant from the Andrew W. Mellon Foundation.

== Installation ==

1. Install the plugin through the Dashboard interface.
1. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How do I contribute to this plugin? =

Development of the plugin is ongoing. Visit https://github.com/cuny-academic-commons/dirt-directory-client. Note that you will need a Sass compiler to contribute CSS.

== Changelog ==

= 1.0.2 =
* Ensure that plugin translations are loaded

= 1.0.1 =
* Fix URL path for tool logos
* Fix fatal error when running with BuddyPress disabled

= 1.0 =
* Initial release
