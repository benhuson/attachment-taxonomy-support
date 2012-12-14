=== Attachment Taxonomy Support ===
Contributors: husobj
Tags: taxonmies, taxonomy, attachment, attachments, media, categories, category, tag, tags, image, images
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 1.2

Improved taxonomy support for media and attachments in versions of WordPress prior to 3.5.

== Description ==

The Attachment Taxonomy Support provides better support for media and attachments in WordPress versions before WordPress 3.5+.

In versions of WordPress subsequent to 3.5 it just registers 'attachment_category' and 'attachment_tag' taxonomies.

== Installation ==

Upload the Attachment Taxonomy Support plugin to your WordPress plugins folder (/wp-content/plugins) or install it via your WordPress admin. Then activate it from the plugin admin page.

== Screenshots ==

1. Edit Media (pre WordPress 3.5)
2. Media Popup (pre WordPress 3.5)

== Changelog ==

= 1.2 =

* Disable admin functionality in WordPress 3.5+ as taxonomies are now supported natively and media workflow has changed.

= 1.1.2 =

* Ensure admin.js is loaded on media pages.
* Added text domain to translation strings in setup_taxonomies().

= 1.1.1 =

* Add language support.
* Enqueue scripts properly and only when required.
* Remove unnecessary menu redirect.

= 1.1 =

* Added media taxonomy menu items.
* Added links to manage taxonomies.
* Fix attachment taxonomies not saving if all checkboxes deselected.
* Fix saving when editing multiple attachment in post popup.
* Allow default taxonomies to be removed via 'attachmenttaxsupp_taxonomies' filter.
* Added support for hierarchical taxonomies.

= 1.0 =

* First version.
