<?php
/*
 Easy Photo Album Wordpress plugin.

Copyright (C) 2013  TV productions

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

/**
 * Class to handle the help tabs.
 *
 * @author TV productions
 * @package EasyPhotoAlbum
 */
class EPA_Help {

	public function __construct() {
		add_action ( 'admin_head', array (
				$this,
				'add_help'
		) );
	}

	public function add_help() {
		// settings help
		$this->add_tab ( 'settings_page_epa-settings', 'epa-settings', __ ( "Easy Photo Album Settings", 'epa' ), '<p>' . sprintf ( __ ( 'Click on the question mark next to each setting for a short description of it. If you still have questions, please ask them at the %1$ssupport forums%2$s.', 'epa' ), '<a href="http://wordpress.org/support/plugin/easy-photo-album" target="_blank">', '</a>' ) . '</p><p><strong><a href="' . EasyPhotoAlbum::get_instance ()->helpurl . '" target="_blank">' . __ ( 'View the video documentation', 'epa' ) . '</a></strong></p>' );
		// photo album edit screen
		// $this->add_tab(EPA_PostType::POSTTYPE_NAME, 'epa-edit-help', __($text), $content);
	}

	private function add_tab($screen_id, $tab_id, $title, $content) {
		$screen = get_current_screen ();

		if ($screen->id != $screen_id)
			return;

		$screen->add_help_tab ( array (
				'id' => $tab_id,
				'title' => $title,
				'content' => $content
		) );
	}

	/**
	 * Renders a tooltip with help contents for each setting, if available
	 *
	 * @param string $setting
	 *
	 * @since 1.3
	 */
	public function render_tooltip($setting) {
		$helptexts = array (
				'viewmode' => sprintf ( '%1$s:<ul><li><strong>%2$s:</strong> %3$s</li><li><strong>%4$s:</strong> %5$s</li><li><strong>%6$s:</strong> %7$s</li></ul>', __ ( 'This setting determines what happens when you click on a photo', 'epa' ), __ ( 'Imagefile', 'epa' ), __ ( 'with this option, you will see the photo file in your browser. This isn\'t recognisable as your website.', 'epa' ), __ ( 'Attachment', 'epa' ), __ ( 'with this option, you will the attachment page of that photo, this is recognisable as your website.', 'epa' ), __ ( 'Lightbox', 'epa' ), __ ( 'with this option, you will stay on the same page, but a lightbox will show the photo.', 'epa' ) ),
				'displaycolumns' => __ ( 'The number of columns of an album.', 'epa' ),
				'showcaption' => __ ( 'Show the caption below the photo.', 'epa' ),
				'displaysize' => __ ( 'The size of the images. When you have a few columns, you may want to choose a larger size or there will be a lot of space between the photos.', 'epa' ),
				'excerptnumber' => __ ( 'The number of images to display when the excerpt of an album is shown. Set to 0 to dispaly all the images.', 'epa' ),
				'setforallalbums' => __ ( 'Apply the current display settings to every photo album. This overrides the current display setting of each indiviual photo album.', 'epa' ),
				// Lightbox settings
				'showimagenumber' => __ ( 'Show something like "Image 2 of 6" below the lightbox.', 'epa' ),
				'imagenumberformat' => sprintf ( __ ( 'Customize your image number text. %1$s will be replaced with the current photo number and %2$s with the total number of photos.', 'epa' ), '<code>{0}</code>', '<code>{1}</code>' ),
				'wraparound' => __ ( 'Wrap the images around in the lightbox, i.e. when you look at the last image of the album and press on the right arrow, the first image will appear.', 'epa' ),
				'scalelightbox' => __ ( 'Scale the image in the lightbox to the largest possible size of the screen and the image itselve.', 'epa' ),
				'lightboxsize' => sprintf ( __ ( 'Recommended to set to %1$s or %2$s. When you have very large photos, it might be usefull to choose %1$s to reduce the download time for each photo.', 'epa' ), '<strong>' . __ ( 'Large' ) . '</strong>', '<strong>' . __ ( 'Full Size' ) . '</strong>' ),
				// Miscellaneous settings
				'showtitleintable' => __ ( "Show the title field on the photo album edit screen. The title isn't used by Easy Photo Album.", 'epa' ),
				'inmainloop' => __ ( 'Show the albums on your blog page, like posts.', 'epa' )
		);
		if (isset ( $helptexts [$setting] )) {
			echo '<span class="epa-dashicons-question epa-help" data-helpid="epa-help-' . $setting . '"></span><div class="epa-help-content" id="epa-help-' . $setting . '">' . $helptexts [$setting] . '</div>';
		} else {
			echo '<!-- no help content available for "' . $setting . '" -->';
		}
	}
}