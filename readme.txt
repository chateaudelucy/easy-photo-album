=== Easy Photo Album ===
Contributors: TV productions
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AK8Y5W7ZSMRTA
Tags:  album, photo's, images, lightbox, responsive
Requires at least: 3.7
Tested up to: 3.8.1
Stable tag: 1.3
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Easy Photo Album makes it easy for you to create and manage photo albums.

== Description ==

This plugin enables you to create and manage photo albums, just like posts.
You don't have to change any theme files, the album displays nice right away.
Photo's can be viewed in a **Lightbox**.
The albums are **responsive** and you can choose how may columns of images you want.
The Lightbox displays the most large size possible, resized to the current screen size.
You can manage the order of the photo's (very easy with **drag and drop**) and tell your story in photo's!
You can now also **insert your album** in a post or page.

So go ahead, and try it out!

**[Get started with Easy Photo Album videos](http://tv-productions.org/plugins/easy-photo-album/support/story.html)**

**Feature request, bugs, ideas are welcome!**
Report your feature request, bug or idea under the support tab or the review tab.

Current language support:
-------------------------
* English - English (en)
* Dutch - Nederlands (nl_NL)
* French - Fran&#231;ais (fr_FR) (by [Dan](http://bp-fr.net))

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory OR
download, upload and install .zip under Plugins > Add New > Upload.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to Settings > Easy Photo Album to review the default settings.

== Frequently asked questions ==

**[Get started with Easy Photo Album videos](http://tv-productions.org/plugins/easy-photo-album/support/story.html)**

= Q: Where can I find the settings for this plugin? =

A: You can find the settings under Settings > Easy Photo Album.

= Q: I want to add a photo album to the menu. Where can I select the photo albums? =

A: You have to check `Photo Albums` in the Screen options box under Appearance > Menus

= Q: Can I add a description to a photo album? =

A: No, you can't. What you can do is include your album in a post or page with the `epa-album` shortcode. Look for the button with the Easy Photo Album icon in the editor.

= Q: What is the syntax of the EPA shortcode? =

A: Syntax: `[epa-album id="123" show_title="true" display="full"]`

* Here is `123` the id of the photo album (you can get the id by editing an album and check the url for `edit.php?post=123`)
* `show_title` can be `true` or `false`.
* `display` can be `full` or `excerpt`.

= Q: How can I display all the photo albums? =

A: *When you have enabled permalinks*: You can display the photo album archive by visiting `example.com/albums/` (where `example.com` is your WordPress home URL).

*When you have NOT enabled permalinks*: You can display the photo album archive by visiting `example.com/?post_type=easy-photo-album` (where `example.com` is your WordPress home URL).

= Q: The images are small; there is much more space, so how do I display larger images? =

A: The image size is default `thumbnail`. So choose an other image size (`medium` perhaps?) and it should display better. If not, you need to regenerate the images. (for example with [this plugin](http://wordpress.org/plugins/regenerate-thumbnails/ "Regenerate Thumbnails plugin on WordPress.org") )

== Screenshots ==

1. Example of a photo album on Twenty Fourteen
2. Reorder the photo's with drag and drop.
3. The settings page of the photo albums.
4. Example of lightbox display.

== Changelog ==

= 1.3 =

* **Video documentation**: [check it out](http://tv-productions.org/plugins/easy-photo-album/support/story.html)
* New feature: the revision screen is more human readable.
* New feature: Support for featured image ([#2107](https://plugins.trac.wordpress.org/ticket/2107]))
* New feature: Support for comments ([#2137](https://plugins.trac.wordpress.org/ticket/2137))
* New feature: Choose an image size for the ligtbox (reduce download time) ([#2138](https://plugins.trac.wordpress.org/ticket/2138]))
* Updated: settings page is now easy and clear.
* WordPress 3.8 compatible
* Fixed bug: Error after deleting last image of an album.
* Fixed bug: Included albums don't display right when viewed on blog page.
* Fixed bug: Preview changes button shows an empty photo album.
* Fixed bug: Lightbox width isn't correct resized ([#2014](https://plugins.trac.wordpress.org/ticket/2014))
* Fixed bug: Single album view shows excerpt album when view all images in lightbox is set. ([#2162](https://plugins.trac.wordpress.org/ticket/2162))
* Fixed bug: Error after activation on mutlisite installation ([#2100](https://plugins.trac.wordpress.org/ticket/2100))
* Fixed bug: Images disappear after a bulk action ([#2161](https://plugins.trac.wordpress.org/ticket/2161))


= 1.2 =

* New settings page: you can now find the settings under `Settings > Easy Photo Album`
* New about page: A quick overview of the update and some tips and tricks
* Added minfied versions of ccs and js files. This will cause faster load times
* WordPress 3.7 compatible
* Added French translation by [Dan](http://bp-fr.net)
* Fixed bug: The show caption option doesn't work.
* Fixed bug: The number of photo's is limited by the server.
* Fixed bug: The captions are not saved at the right place.
* Fixed bug: Included, not published albums are visible for logged in users
* Fixed bug: When you edit the caption in the media library, the caption in the album isn't updated

= 1.1.7 =

* Fixed bug: update label with the number of photo's (near the table) when there are photo's added or removed.
* Fixed bug: When there is a photo with the same id as a valid order, the script breaks.
* New option: You can now to choose to scale the images in the lightbox to the viewport (default on)

= 1.1.6 =

* Fixed bug: No message if there are no albums in tinymce editor insert album dialog.
* Security issue fixed: Display included album only if the album is published or the current user has enough rights.
* New option: Display all photo's in the lightbox (also if the album is displayed as excerpt).
* New feature: help content for the options.

= 1.1.5 =

* Fixed bug: 404 error on activation
* New feature: albums are responsive
* New feature: Edit the display settings for each specific album.

= 1.1.4 =

* Fixed bug: PHP notices (only if WP_DEBUG is true)

= 1.1.3 =

* Fixed bug: view more photo's link doesn't work when an album is included.

= 1.1.2 =

* Added option to show photo albums in the main query (i.e. the blog page).
* Added feature: insert a album in a post (or page).

= 1.1.1 =

* Small bugfixes for WordPress 3.6

= 1.1.0 =

* Updated lightbox to version 2.6
* Removed options: displaywidht and displayheight options are removed, because the lightbox now fits the image to the viewport.
* Added options: Options for the label under the lightbox and for displaying the caption column when you edit an album.
* Minor bugfixes

= 1.0.7 =

* Updated translations

= 1.0.6 =

* Fixed bug: option doesn't exists after updating to 1.0.5

= 1.0.5 =

* Updated: updated the Dutch translation.

= 1.0.4 =

* Added: excerpts show also some images (can be set by the user)

= 1.0.3 =

* Moved the settings functions from `EasyPhotoAlbum` to `EPA_Admin`.

= 1.0. =

* Fixed bug: Photo table uses pagination
* Fixed typo: EAP_List_Table => EPA_List_Table

= 1.0.1 =

* Fixed bug: the post type menu item isn't visible after activation.
* Fixed style error: dotted border around images in Firefox