=== Easy Photo Album ===
Contributors: TV productions
Donate link:
Tags:  album, photo's, images
Requires at least: 3.5
Tested up to: 3.6
Stable tag: 1.1.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Easy Photo Album is a plugin that makes it easy for you to create and manage photo albums.

== Description ==

This plugin enables you to create and manage photo albums, just like posts.
You don't have to change any theme files, the album displays nice right away.
Photo's can be viewed by a **Lightbox**.
The size of the thumbnails is all yours. The Lightbox displays the most large possible size, resized to the current screen size.
You can manage the order of the photo's (very easy with drag and drop) and tell your story in pictures!

So go ahead, and try it out!


**Feature request, bugs, ideas are welcome!**
Report your feature request, bug or idea under the support tab.

Current language support:
-------------------------
* English (en)
* Dutch (nl_NL)

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory OR
download, upload and install .zip under Plugins > Add New > Upload.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to Settings > Media to review the default settings.

== Frequently asked questions ==

= Q: Where can I find the settings for this plugin? =

A: You can find the settings under Settings > Media.

= Q: I want to add a photo album to the menu. Where can I select the photo albums? =

A: You have to check `Photo Albums` in the Screen options box under Appearance > Menus

= Q: How can I display all the photo albums? =

A: You can display the photo album archive by visiting `example.com/albums/` (where `example.com` is your WordPress home URL).

== Screenshots ==

1. Example of a photo album on Twenty Thirteen
2. Reorder the photo's with drag and drop.
3. The settings for the photo albums.
4. Example of lightbox display.

== Changelog ==

**1.1.1**

* Small bugfixes for WordPress 3.6

**1.1.0**

* Updated lightbox to version 2.6
* Removed options: displaywidht and displayheight options are removed, because the lightbox now fits the image to the viewport.
* Added options: Options for the label under the lightbox and for displaying the caption column when you edit an album.
* Minor bugfixes

**1.0.7**

* Updated translations

**1.0.6**

* Fixed bug: option doesn't exists after updating to 1.0.5

**1.0.5**

* Updated: updated the Dutch translation.

**1.0.4**

* Added: excerpts show also some images (can be set by the user)

**1.0.3**

* Moved the settings functions from `EasyPhotoAlbum` to `EPA_Admin`.

**1.0.2**

* Fixed bug: Photo table uses pagination
* Fixed typo: EAP_List_Table => EPA_List_Table

**1.0.1**

* Fixed bug: the post type menu item isn't visible after activation.
* Fixed style error: dotted border around images in Firefox