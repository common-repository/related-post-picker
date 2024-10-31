=== Related Posts Picker ===
Contributors: thoughtlabllc, lellimecnar
Tags: related post, post, related
Requires at least: 2.0.2
Tested up to: 3.1
Stable tag: trunk

Don't rely on WordPress alone to decide what posts are related! Choose those which you know are related.

== Description ==

Choose your related posts! Most related posts plugins and widgets generate a list of related posts by fetching some posts from the database that have the same or similar tags or categories. Some use both. These related posts can be very innacurate. Related Posts Picker will give you a list of posts that it thinks are related, and you can select only those which you agree are related. You must tag your posts and save them before the Related Posts Picker will be able to get a list for you.

To include in your theme, use the function: `<?php rpp_related_posts(); ?>`

If you don't select any posts, the auto generated list will show instead. You are able to configure the number of posts that will show.

== Installation ==

1. Upload `related-posts-picker.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php rpp_related_posts(); ?>` in your templates

== Screenshots ==

1. This is similar to what you will see as you edit your posts. Check off the posts that are related to your post.

== Changelog ==

= 1.1 =
* Cleaned up some code, and fixed some errors.