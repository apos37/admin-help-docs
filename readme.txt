=== Admin Help Docs ===
Contributors: apos37
Donate link: https://paypal.com/donate/?business=3XHJUEHGTMK3N
Tags: help, documentation, instructions, how-to, admin
Requires at least: 5.9.0
Tested up to: 6.4.1
Requires PHP: 7.4
Stable tag: 1.2.3
License: GPL v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Site developers and operators can easily create help documentation and notices for the admin area.

== Description ==
Site developers and operators can easily create help documentation and notices for the admin area. Include a help section with pages of instructions, or add a help box just about anywhere on the back-end (see site locations and page locations below).

* Supports custom post types
* Supports Gutenberg and Classic editors
* Completely customizeable - colors, admin menu name, icon, logo, etc
* Change the admin footer text to admin contact information or whatever you want
* Easily import settings from another site
* Import or auto-feed documents from another site
* Users can reset meta box and admin list column preferences from profile page
* Use it for notices and reminders, too!

= Site Locations =
* Main documentation page
* Admin bar
* Dashboard
* Post/page edit screen
* Post/page admin list screen
* All other pages that are listed on admin menu

= Page Locations =
* Contextual help (even on Gutenberg)
* Top
* Bottom
* Side

== Installation ==
1. Install the plugin from your website's plugin directory, or upload the plugin to your plugins folder. 
2. Activate it.
3. Go to Help Docs in your admin menu.

== Frequently Asked Questions ==
= Who can add a help section? =
Anyone that has the Administrator role, or other roles that you specify.

= Can I use the same documentation across multiple sites? =
Yes, you can choose to automatically feed documents or import them locally from a remote site with the same plugin.

= Where can I request features and get further support? =
Join my [WordPress Support Discord server](https://discord.gg/3HnzNEJVnR)

== Demo ==
https://youtu.be/-V_vyBe6lv0

== Screenshots ==
1. Main documentation page
2. Add a doc to the top of any page as a notification 
3. Bottom page placement
4. Classic contextual help and side meta box 
5. Gutenberg contextual help and side meta box
6. Dashboard meta box with custom colors
7. Manage the help docs like any other post
8. Import documents from another site
9. Settings page
10. Settings page with colors changed

== Changelog ==
= 1.2.3 =
* Fix: More than 5 files in a folder won't stay in the folder (props alex_p6577 for pointing this out)
* Tweak: Added an option to stop showing feedback form on deactivate; will automatically disable for certain choices

= 1.2.2 =
* Tweak: Changed import feeds icon to a newspaper
* Update: Added notice that import feeds cannot be added to folders

= 1.2.1 =
* Update: Added setting option to hide the created and last modified dates and authors (props chrismaclean for suggestion)
* Update: Added `[helpdocs_css]` shortcode for adding custom CSS to docs on the main doc page
* Update: Added setting option to select a default doc for the main doc page
* Tweak: Removed top border on first doc in main doc page and added borders to dragged doc instead
* Update: Added permalink and view button to top of edit screen if site location is main doc page
* Update: Added `[dont_do_shortcode]` shortcode to make it easier to share shortcodes without executing them

= 1.2.0 =
* Tweak: Drag and drop sorting - added icons, linked entire cells instead of just text, removed sorting cursor
* Tweak: Added 150 ms delay to drag and drop sorting to prevent accidental dragging
* Update: Added folders for main documentation page (props alex_p6577 for suggestion)
* Tweak: Moved doc editing JS to its own file
* Update: Added support for WP Version to still be used in footer
* Tweak: Changed order of deactivate feedback form options
* Tweak: Planned Updates on About tab
* Update: Added search bar on main documentation page (props alex_p6577 for suggestion)

= 1.1.5 =
* Fix: PHP warning about id variable not being found on every page load

= 1.1.4 =
* Fix: Sorting by order column not in order (props alex_p6577 for pointing this out)
* Fix: Documentation page ordering issue (props alex_p6577 for pointing this out)
* Fix: Attempt to read property "singular_name" on null (props alex_p6577 for pointing this out)

= 1.1.3 =
* Update: Added setting to change user capability
* Fix: Editors could view menu link and dashboard widget, but not view docs (props chrismaclean for pointing it out)

= 1.1.2 =
* Update: Added setting to disable curly quotes site-wide that make sharing code difficult
* Fix: Resize cursor showing up on doc list items

= 1.1.1 =
* Fix: Custom link fields not showing up for some people

= 1.1.0 =
* Tweak: Highlighted "Enable This Site" checkbox on imports when disabled
* Update: Added deactivation survey
* Update: Added support for importing custom urls with auto-updating domain
* Update: Added new site location for custom url

= 1.0.9 =
* Tweak: Updated Discord support link

= 1.0.8 =
* Tweak: Added icons to dashboard TOC
* Tweak: Added ability to add imports/feeds to dashboard TOC
* Fix: Hid "Add to Dashboard TOC" by default
* Fix: Replaced early escapes with sanitizers

= 1.0.7 =
* Update: Added dashboard table of contents (props chrismaclean for suggestion)
* Tweak: Updated changelog to use commonly used prefixes (Fix, Tweak, and Update)

= 1.0.6 =
* Update: Added optional setting for allowing the addition of missing user meta merge tags to Gravity Forms dropdowns
* Update: Added missing `index.php` to `/classes/` and `/js/` folders
* Fix: Excerpt meta box title changing on other post types

= 1.0.5 =
* Update: Added video to readme
* Tweak: When resetting settings, added a notice instead of attempting to refresh

= 1.0.4 =
* Fix: Nested ordered lists on main documentation page not showing proper list types
* Update: Added links to plugins list page

= 1.0.3 =
* Fix: Minor fixes

= 1.0.2 =
* Update: Added feedback form to About tab

= 1.0.1 =
* Initial release on WP.org January 23, 2023

= 1.0.0 =
* Created plugin on November 14, 2022