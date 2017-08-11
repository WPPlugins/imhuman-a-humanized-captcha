=== ImHuman - a humanized captcha ===
Contributors: Yuksel Kurtbas
Donate link: http://elxsy.com/donate/
Tags: plugin, captcha, AJAX, post, posts, admin, comments, spam, security, security check, user interface
Requires at least: 2.5.x
Tested up to: 2.8.x
Stable tag: 0.0.9

ImHuman provides more humane, fun, easy, secure and mostly user friendly captcha (are you really a human?) detection for posting comments.

== Description ==

ImHuman is a new way of asking "are you a human?" question to your website visitors. It is designed to be easy, fun, secure and user friendly than most of the other captcha systems. Security and fun grows a little bit more everyday because it uses the ImHuman globalized API and you do not need to worry about anything. Supports AJAX comments, multilanguage, customizations and eliminates security drawbacks from browser cache.


Features:

* **Automatically** insert ImHuman security check to any page
* AJAX support for comments without any hack or modification
* Uses global wordpress errors in a fancy way instead of die
* Option to skip check for registered members
* Recreation of humanizer grid at every failed attempt
* AJAXed comments will function even though grid is not displayed
* Multilanguage Support
* Cache and Back key support
* Works seemlesly with mobile browsers. Specially Iphone.
* Plugin will only activate itself if the current url is a page or post 
* It acts within the boundaries of "is comments allowed"
* You can tweak your API user and keys from administration page
* Tweaks to show how many rows, columns and selection options are configured from plugin settings page also
* Requires PHP5 and JQuery to be existant
* Security checks are done within the boundaries of wordpress, no extra connection required
* Detailed error and debug reporting in the event of an error instead of blocking the whole wordpress execution

* [Support](http://www.elxsy.com/imhuman/)
* [Version History](http://www.elxsy.com/imhuman/version/)
* [FAQ](http://www.elxsy.com/imhuman/faq/)
* [Installation](http://www.elxsy.com/imhuman/#comment-34)

== Installation ==

1. Upload "imhuman-a-humanized-captcha" directory to the "/wp-content/plugins/" directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure your API key and plugin settings from "Plugins"->"ImHuman Settings" menu.


== Frequently Asked Questions ==

= Do I need to get API user and key from elxsy website? =

Yes, for security measures your plugin requires API user and key values to run. All the API information is public and free.

= Do I need to use jquery? =

Yes, if you do not want to use wordpress included or an external jquery. You can always write your simple Javascript equivelant functions.

= Can I change my settings? =

Yes, you can under "Plugins"->"ImHuman Settings"

= I receive some error codes, what do they mean? =

ImHuman API returns detailed error codes and error strings in the event of an error. All the information are provided in plain english and easy to understand. If you are still unsure about the error and reasons you are having them, you can visit support pages anytime and seek for help.

= I would like to change display, acting format of the humanizers, can I do that? =

Yes, you can. Browse to folder "wp-content/plugins/imhuman/css/" and edit imhuman.css to edit your display style.

= Do I need Javascript? =

With 0.0.8 yes. below 0.0.8 no.

= Any external Javascript libraries required ? =

With and above 0.0.8 Yes, JQuery. Below 0.0.8 No.

== Screenshots ==

1. Options Screen.
2. Sample Explanations of Input values.
3. Sample Output
4. Sample Error