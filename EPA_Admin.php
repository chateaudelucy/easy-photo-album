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
 * This class displays the options screen on admin side.
 *
 * @author TV Productions
 * @package EasyPhotoAlbum
 *
 */
class EPA_Admin {

	public function __construct() {
		add_action ( 'admin_init', array (
				&$this,
				'register_settings'
		) );
	}

	/**
	 * Add the settings to the media options screen
	 */
	public function register_settings() {
		// Add the settings to the media options screen
		register_setting ( 'media', 'EasyPhotoAlbum', array (
				&$this,
				'validate_settings'
		) );
		add_settings_section ( 'epa-section', __ ( 'Easy Photo Album default options', 'epa' ), array (
				&$this,
				'display_settings_section'
		), 'media' );
		add_settings_field ( 'linkto', __ ( 'Link image to', 'epa' ), array (
				&$this,
				'display_linkto_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'displaycolumns', __ ( 'Columns', 'epa' ), array (
				&$this,
				'display_displaycolumns_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'displaysize', __ ( 'Image size', 'epa' ), array (
				&$this,
				'display_displaysize_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'showcaption', __ ( 'Title', 'epa' ), array (
				&$this,
				'display_showcaption_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'showalbumlabel', __ ( 'Show the album label under the lightbox', 'epa' ), array (
				&$this,
				'display_showalbumlabel_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'albumlabel', __ ( 'Album label', 'epa' ), array (
				&$this,
				'display_albumlabel_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'wraparound', __ ( 'Wrap around', 'epa' ), array (
				&$this,
				'display_wraparound_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'numimageswhennotsingle', __ ( 'Number of images for excerpt', 'epa' ), array (
				&$this,
				'display_numimageswhennotsingle_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'showcaptionintable', __ ( 'Show the caption', 'epa' ), array (
				&$this,
				'display_showcaptionintable_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'inmainloop', __ ( 'Photo albums on blog page', 'epa' ), array (
				&$this,
				'display_inmainloop_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'showallimagesinlightbox', __ ( 'Show all images in the lighbox', 'epa' ), array (
				&$this,
				'display_showallimagesinlightbox_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'override', __ ( 'Override album specific options', 'epa' ), array (
				&$this,
				'display_override_field'
		), 'media', 'epa-section' );
	}

	/**
	 * Validates the options.
	 * This function is called by the Settings API.
	 *
	 * @param array $input
	 * @return array
	 *
	 *
	 */
	public function validate_settings($input) {
		$valid = EasyPhotoAlbum::get_instance ()->getOptions ();
		$valid ['linkto'] = (in_array ( $input ['linkto'], array (
				'file',
				'attachment',
				'lightbox'
		) ) ? $input ['linkto'] : $valid ['linkto']);
		$valid ['displaycolumns'] = is_numeric ( $input ['displaycolumns'] ) && intval ( $input ['displaycolumns'] ) >= 1 ? intval ( $input ['displaycolumns'] ) : $valid ['displaycolumns'];
		$valid ['displaysize'] = in_array ( $input ['displaysize'], get_intermediate_image_sizes () ) ? $input ['displaysize'] : $valid ['displaysize'];
		$valid ['showcaption'] = (isset ( $input ['showcaption'] ) && $input ['showcaption'] == 'true' ? true : false);
		$valid ['showalbumlabel'] = (isset ( $input ['showalbumlabel'] ) && $input ['showalbumlabel'] == 'true' ? true : false);
		$valid ['albumlabel'] = (isset ( $input ['albumlabel'] ) && ! empty ( $input ['albumlabel'] ) ? $input ['albumlabel'] : $valid ['albumlabel']);
		$valid ['wraparound'] = (isset ( $input ['wraparound'] ) && $input ['wraparound'] == 'true' ? true : false);
		$valid ['numimageswhennotsingle'] = (is_numeric ( $input ['numimageswhennotsingle'] ) ? $input ['numimageswhennotsingle'] : $valid ['numimageswhennotsingle']);
		$valid ['showcaption'] = (isset ( $input ['showcaption'] ) && $input ['showcaption'] == 'true' ? true : false);
		$valid ['inmainloop'] = (isset ( $input ['inmainloop'] ) && $input ['inmainloop'] == 'true' ? true : false);
		$valid ['showallimagesinlightbox'] = (isset ( $input ['showallimagesinlightbox'] ) && $input ['showallimagesinlightbox'] == 'true' ? true : false);

		if (isset ( $input ['override'] ) && $input ['override'] == 'true') {

			$albums = get_posts ( array (
					'posts_per_page' => - 1,
					'post_type' => EPA_PostType::POSTTYPE_NAME,
					'post_status' => 'any'
			) );
			foreach ( $albums as $album ) {
				$data = get_post_meta ( $album->ID, EPA_PostType::SETTINGS_NAME, true );
				$data ['options'] = EasyPhotoAlbum::get_instance ()->get_default_display_options ( $valid );
				update_post_meta ( $album->ID, EPA_PostType::SETTINGS_NAME, $data );
			}
		}
		return $valid;
	}

	public function display_settings_section() {
		printf ( '<p>%1$s</p><p>%2$s</p>', __ ( 'Options that changes the appreance of the photo albums. They can be overriden for each specific album.', 'epa' ), sprintf ( __ ( 'Do you like this plugin? Please write a review or rate the plugin at %1$swordpress.org%2$s.', 'epa' ), '<a href="http://wordpress.org/support/view/plugin-reviews/easy-photo-album" target="_blank">', '</a>' ) );
	}

	public function display_linkto_field() {
		?>
<select name="EasyPhotoAlbum[linkto]">
	<option value="file"
		<?php selected(EasyPhotoAlbum::get_instance()->linkto, 'file', true);?>><?php _e('The image file', 'epa');?></option>
	<option value="attachment"
		<?php selected(EasyPhotoAlbum::get_instance()->linkto, 'attachment', true);?>><?php _e('The attachment page', 'epa');?></option>
	<option value="lightbox"
		<?php selected(EasyPhotoAlbum::get_instance()->linkto, 'lightbox', true);?>><?php _e('Lightbox display', 'epa');?></option>
</select>
<strong>*</strong>
<?php
	}

	public function display_displaycolumns_field() {
		$this->show_input_field ( 'displaycolumns', EasyPhotoAlbum::get_instance ()->displaycolumns, 'number', '<strong>*</strong>', array (
				'step' => 1,
				'class' => 'small-text',
				'min' => 1,
				'max' => 15
		) );
		// 'max' => 15 means Minimum size of the images is then 5%
		$this->show_description ( __ ( 'The number of columns of the album', 'epa' ) );
	}

	public function display_displaysize_field() {
		echo '<select name="EasyPhotoAlbum[displaysize]">';
		// Using the same filter as in wp-admin/includes/media.php for the function
		// image_size_input_fields. Other plugins can use this filter to add their image size.
		$size_names = apply_filters ( 'image_size_names_choose', array (
				'thumbnail' => __ ( 'Thumbnail' ),
				'medium' => __ ( 'Medium' ),
				'large' => __ ( 'Large' ),
				'full' => __ ( 'Full Size' )
		) );
		foreach ( $size_names as $size => $displayname ) {
			$selected = selected ( EasyPhotoAlbum::get_instance ()->displaysize, $size, false );
			echo <<<HTML
			<option value="{$size}" {$selected}>{$displayname}</option>
HTML;
		}
		echo '</select><strong>*</strong>';
	}

	public function display_showcaption_field() {
		$attr = array (
				'id' => 'stwt'
		);
		if (EasyPhotoAlbum::get_instance ()->showcaption)
			$attr += array (
					'checked' => 'checked'
			);
		$this->show_input_field ( 'showcaption', 'true', 'checkbox', sprintf ( ' <label for="stwt">%s</label><strong>*</strong>', __ ( 'Show title underneath the photo.', 'epa' ) ), $attr );
	}

	public function display_showalbumlabel_field() {
		$attr = array (
				'id' => 'sal'
		);
		if (EasyPhotoAlbum::get_instance ()->showalbumlabel) {
			$attr += array (
					'checked' => 'checked'
			);
		}
		$this->show_input_field ( 'showalbumlabel', 'true', 'checkbox', sprintf ( '<label for="sal">%s</label>', __ ( 'Display a message like "Image x of y" (see next option)', 'epa' ) ), $attr );
	}

	public function display_albumlabel_field() {
		$this->show_input_field ( 'albumlabel', EasyPhotoAlbum::get_instance ()->albumlabel );
		$this->show_description ( __ ( 'You can translate or change the text. {0} will be replaced with the current image number, {1} with the total number of images.', 'epa' ) );
	}

	public function display_wraparound_field() {
		$attr = array (
				'id' => 'wa'
		);
		if (EasyPhotoAlbum::get_instance ()->wraparound) {
			$attr += array (
					'checked' => 'checked'
			);
		}
		$this->show_input_field ( 'wraparound', 'true', 'checkbox', sprintf ( '<label for="wa">%s</label>', __ ( 'Wrap the images in the lightbox, i.e. when you reach the last image in the album and you click on the right arrow, the first image will be displayed', 'epa' ) ), $attr );
	}

	public function display_numimageswhennotsingle_field() {
		$this->show_input_field ( 'numimageswhennotsingle', EasyPhotoAlbum::get_instance ()->numimageswhennotsingle, 'number', __ ( 'images', 'epa' ) . '<strong>*</strong> ', array (
				'step' => 1,
				'class' => 'small-text',
				'min' => 0
		) );
		$this->show_description ( __ ( "The number of photo's showed when the album is shown in an archive. Set to 0 to display all photo's", 'epa' ) );
	}

	public function display_showcaptionintable_field() {
		$attr = array (
				'id' => 'sc'
		);
		if (EasyPhotoAlbum::get_instance ()->showcaptionintable) {
			$attr += array (
					'checked' => 'checked'
			);
		}
		$this->show_input_field ( 'showcaptionintable', 'true', 'checkbox', sprintf ( '<label for="sc">%s</label>', __ ( 'Show the caption in the album edit screen', 'epa' ) ), $attr );
	}

	public function display_inmainloop_field() {
		$attr = array (
				'id' => 'iml'
		);
		if (EasyPhotoAlbum::get_instance ()->inmainloop) {
			$attr += array (
					'checked' => 'checked'
			);
		}
		$this->show_input_field ( 'inmainloop', 'true', 'checkbox', sprintf ( '<label for="iml">%s</label>', __ ( 'Show Photo Albums on the blog page', 'epa' ) ), $attr );
	}

	public function display_showallimagesinlightbox_field() {
		$attr = array (
				'id' => 'saiil'
		);
		if (EasyPhotoAlbum::get_instance ()->showallimagesinlightbox) {
			$attr += array (
					'checked' => 'checked'
			);
		}
		$this->show_input_field ( 'showallimagesinlightbox', 'true', 'checkbox', sprintf ( '<label for="saiil">%s</label>', __ ( "Show all the photo's in the lightbox, also when the album is shown in an archive.", 'epa' ) ), $attr );
	}

	public function display_override_field() {
		$attr = array (
				'id' => 'epa-override'
		);

		$this->show_input_field ( 'override', 'true', 'checkbox', sprintf ( '<label for="epa-override">%s</label>', __ ( 'Override the display options of each album with those default ones. (Only the options with a * can be set for each album)', 'epa' ) ), $attr );
	}

	/**
	 * Prints settings description
	 *
	 * @param string $description
	 */
	private function show_description($description) {
		printf ( '<span class="description">%s</span>', esc_html ( $description ) );
	}

	/**
	 * Displays an HMTL input box.
	 *
	 * @param string $epa_name
	 *        	name of the input field (without <code>EasyPhotoAlbum[...]</code>. Only what
	 *        	should be on the ellipsis.)
	 * @param string $value
	 *        	[optional] The value of the input field, default empty.
	 * @param string $type
	 *        	[optional] The HTML type of the input, default text
	 * @param string $after
	 *        	[optional] The text after the input, default nothing.
	 * @param array $attrs
	 *        	[optional] An array with some extra attributes, like <code>array ('size' =>
	 *        	5);</code>, default none.
	 */
	private function show_input_field($epa_name, $value = '', $type = 'text', $after = '', $attrs = array()) {
		$html = '';
		foreach ( $attrs as $attr => $val ) {
			$html .= $attr . '="' . $val . '" ';
		}
		printf ( '<input type="%1$s" name="EasyPhotoAlbum[%2$s]" value="%3$s" %4$s/>%5$s', $type, $epa_name, $value, $html, $after );
	}
}