=== Instagrate to WordPress ===
Contributors: polevaultweb
Donate link: http://www.polevaultweb.com/contact/
Plugin URI: http://www.polevaultweb.com/plugins/instagrate-to-wordpress/
Author URI: http://www.polevaultweb.com/
Tags: instagram, posts, integration, automatic, post, wordpress, posting, images
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 1.0.3

Integrate your Instagram images and your WordPress blog with automatic posting of new images into blog posts.

== Description ==

The Instagrate to WordPress plugin allows you to automatically integrate your Instagram account with your WordPress blog.  

No more manual embedding Instagram images into your posts, let this plugin take care of it all.

Install the plugin. Log in to Instagram, pick your default WordPress post settings, and you are done. Take a photo or lots on Instagram. The next time someone visits your site, a new post will be created with your each photo from Instagram. 

**Please note this plugin supersedes InstaPost Press, which has been discontinued because of a naming conflict. If you installed this plugin you will need to deactivate it before you can use this new plugin. Instagrate to WordPress has new features and will continue to be developed**

Full list of features:

* Simple connection to Instagram. Login securely to Instagram to authorise this plugin to access your image data. **This plugin does not ask or store your Instagram username and password, you only log into Instagram.**
* Helpful feed of images in the admin screen.
* Option to manually set the last image in the feed, so all later images will be posted.
* Configurable post settings:
	*	Post title - default as Instagram image title. Custom title text before Instagram title, or embed the Instagram title using %%title%%
	*	Post body text - default as Instagram image. Custom body text before Instagram image, or embed the Instagram image using %%image%%
	*	Image size
	*	Image CSS class
	*	Post Category (selected from dropdown of available categories)
	*	Post Author (selected from dropdown of available authors)
	* 	Plugin link at the end of the post body text. Can be turned off.
	

If you have any issues or feature requests please visit and use the [Support Forum](http://www.polevaultweb.com/support/forum/instagrate-to-wordpress-plugin/)

[Plugin Page](http://www.polevaultweb.com/plugins/instagrate-to-wordpress/) | [@polevaultweb](http://www.twitter.com/polevaultweb/) | [Donate by signing up to Dropbox - free space for you and me](https://www.dropbox.com/referrals/NTI4NjU1OTQ1OQ)

== Installation ==

This section describes how to install the plugin and get it working.

You can use the built in installer and upgrader, or you can install the plugin manually.

1. Delete any existing `instagrate-to-wordpress` folder from the `/wp-content/plugins/` directory
2. Upload `instagrate-to-wordpress` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the options panel under the 'Settings' menu and add your Instagram account details and set the rest of configuration you want.

If you have to upgrade manually simply repeat the installation steps and re-enable the plugin.

**Please note this plugin supersedes InstaPost Press, which has been discontinued because of a naming conflict. If you installed this plugin you will need to deactivate it before you can use this new plugin. Instagrate to WordPress has new features and will continue to be developed**

== Changelog ==

= 1.0.4 =

* Bug fix - resolved WordPress forcing a re-login after trying to authenticate plugin, and never fully activating Instagrate

= 1.0.3 =

* Bug fix - resolved multiple posts for one image.
* Bug fix - resolved issues for authenticating plugin with Instagram for blogs not in root directory, eg. /blog/
* Bug fix - resolved issues where users were receiving unhandled exceptions for the plugin on their blog
* Log out button to allow you to change which Instagram account the plugin uses.
* When a custom post title is added without the %%title%% text, it no longer adds the Instagram image title as well.
* You can now use the %%title%% text within the post body.

= 1.0.2 = 

* Category dropdown in WordPress post settings now shows all categories even if no posts exist for the category. Also order by name.

= 1.0.1 =

* Change to ensure on enable all images aren't posted.

= 1.0 =

* First release, bugs expected.

== Frequently Asked Questions ==

= I have an issue with the plugin =

Please visit the [Support Forum](http://www.polevaultweb.com/support/forum/instagrate-to-wordpress-plugin/) and see what has been raised before, if not raise a new topic.

= What about the InstaPost Press plugin? =

This is the newer version of that plugin. It has been discontinued because of a naming conflict. If you installed this plugin you will need to deactivate it before you can use this new plugin.

= Does the plugin support WordPress Multisite? =

No, currently the plugin does not support Multisite. It's on the development todo list.

= Can I use more than one Instagram account? =

No, not at the moment. The plugin only allows one Instagram account at a time.

= I have a feature request =

Please visit and add to the [Feature Requests topic](http://www.polevaultweb.com/support/topic/feature-requests/) on the support forum.

== Screenshots ==

1. Screenshot of the Instagram settings of manual last image.
2. Screenshot of the WordPress blog post settings.
3. Screenshot of the admin feed of images from Instagram.
4. Screenshot of the plugin link setting.

== Upgrade Notice ==

Please note this plugin supersedes InstaPost Press, which has been discontinued because of a naming conflict. If you installed this plugin you will need to deactivate it before you can use this new plugin. Instagrate to WordPress has new features and will continue to be developed.

== Disclaimer ==

This plugin uses the Instagram(tm) API and is not endorsed or certified by Instagram or Burbn, inc. All Instagram(tm) logoes and trademarks displayed on this website are property of Burbn, inc.