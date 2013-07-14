<?php

class EPA_Admin {

	public function __construct() {
		add_action ( 'admin_init', array (
				&$this,
				'register_settings'
		) );
	}

	public function register_settings() {
		// Add the settings to the media options screen
		register_setting ( 'media', 'EasyPhotoAlbum', array (
				&$this,
				'validate_settings'
		) );
		add_settings_section ( 'epa-section', __ ( 'Easy Photo Album Settings', 'epa' ), array (
				&$this,
				'display_settings_section'
		), 'media' );
		add_settings_field ( 'linkto', __ ( 'Link image to', 'epa' ), array (
				&$this,
				'display_linkto_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'thumbnailwidth', __ ( 'Thumbnail width', 'epa' ), array (
				&$this,
				'display_thumbnailwidth_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'thumbnailheight', __ ( 'Thumbnail height', 'epa' ), array (
				&$this,
				'display_thumbnailheight_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'showtitlewiththumbnail', __ ( 'Title', 'epa' ), array (
				&$this,
				'display_showtitlewiththumbnail_field'
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

		add_settings_field ( 'showcaption', __ ( 'Show the caption', 'epa' ), array (
				&$this,
				'display_showcaption_field'
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
		$valid ['thumbnailwidth'] = (is_numeric ( $input ['thumbnailwidth'] ) ? $input ['thumbnailwidth'] : $valid ['thumbnailwidth']);
		$valid ['thumbnailheight'] = (is_numeric ( $input ['thumbnailheight'] ) ? $input ['thumbnailheight'] : $valid ['thumbnailheight']);
		$valid ['showtitlewiththumbnail'] = (isset ( $input ['showtitlewiththumbnail'] ) && $input ['showtitlewiththumbnail'] == 'true' ? true : false);
		$valid ['showalbumlabel'] = (isset ( $input ['showalbumlabel'] ) && $input ['showalbumlabel'] == 'true' ? true : false);
		$valid ['albumlabel'] = (isset ( $input ['albumlabel'] ) && ! empty ( $input ['albumlabel'] ) ? $input ['albumlabel'] : $valid ['albumlabel']);
		$valid ['wraparound'] = (isset ( $input ['wraparound'] ) && $input ['wraparound'] == 'true' ? true : false);
		$valid ['numimageswhennotsingle'] = (is_numeric ( $input ['numimageswhennotsingle'] ) ? $input ['numimageswhennotsingle'] : $valid ['numimageswhennotsingle']);
		$valid ['showcaption'] = (isset ( $input ['showcaption'] ) && $input ['showcaption'] == 'true' ? true : false);

		return $valid;
	}

	public function display_settings_section() {
		printf ( '<p>%1$s <i>%2$s</i></p><p>%3$s</p>', __ ( 'Settings that changes the appreance of the photo albums.', 'epa' ), __ ( 'Note: when you use the lightbox, you have to regenerate the images, in order to make them the desired size.', 'epa' ), sprintf ( __ ( 'Do you like this plugin? Please write a review or rate the plugin at %1$swordpress.org%2$s.', 'epa' ), '<a href="http://wordpress.org/support/view/plugin-reviews/easy-photo-album" target="_blank">', '</a>' ) );
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
<?php
	}

	public function display_thumbnailwidth_field() {
		$this->display_input_field ( 'thumbnailwidth', EasyPhotoAlbum::get_instance ()->thumbnailwidth, 'number', 'px ', array (
				'step' => 1,
				'class' => 'small-text',
				'min' => 0
		) );
		$this->display_description ( __ ( 'The display width of the thumbnails.', 'epa' ) );
	}

	public function display_thumbnailheight_field() {
		$this->display_input_field ( 'thumbnailheight', EasyPhotoAlbum::get_instance ()->thumbnailheight, 'number', 'px ', array (
				'step' => 1,
				'class' => 'small-text',
				'min' => 0
		) );
		$this->display_description ( __ ( 'The display height of the thumbnails.', 'epa' ) );
	}

	public function display_showtitlewiththumbnail_field() {
		$attr = array (
				'id' => 'stwt'
		);
		if (EasyPhotoAlbum::get_instance ()->showtitlewiththumbnail)
			$attr += array (
					'checked' => 'checked'
			);
		$this->display_input_field ( 'showtitlewiththumbnail', 'true', 'checkbox', sprintf ( ' <label for="stwt">%s</label>', __ ( 'The title will be displayed under the thumbnail.', 'epa' ) ), $attr );
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
		$this->display_input_field ( 'showalbumlabel', 'true', 'checkbox', sprintf ( '<label for="sal">%s</label>', __ ( 'Display a message like "Image x of y" (see next option)', 'epa' ) ), $attr );
	}

	public function display_albumlabel_field() {
		$this->display_input_field ( 'albumlabel', EasyPhotoAlbum::get_instance ()->albumlabel );
		$this->display_description ( __ ( 'You can translate or change the text. {0} will be replaced with the current image number, {1} with the total number of images.', 'epa' ) );
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
		$this->display_input_field ( 'wraparound', 'true', 'checkbox', sprintf ( '<label for="wa">%s</label>', __ ( 'Wrap the images in the lightbox, i.e. when you reach the last image in the album and you click on the right arrow, the first image will be displayed', 'epa' ) ), $attr );
	}

	public function display_numimageswhennotsingle_field() {
		$this->display_input_field ( 'numimageswhennotsingle', EasyPhotoAlbum::get_instance ()->numimageswhennotsingle, 'number', __ ( 'images', 'epa' ) . ' ', array (
				'step' => 1,
				'class' => 'small-text',
				'min' => 0
		) );
		$this->display_description ( __ ( 'The number of images showed when the album is not on a single page. Set to 0 for all', 'epa' ) );
	}

	public function display_showcaption_field() {
		$attr = array (
				'id' => 'sc'
		);
		if (EasyPhotoAlbum::get_instance ()->showcaption) {
			$attr += array (
					'checked' => 'checked'
			);
		}
		$this->display_input_field ( 'showcaption', 'true', 'checkbox', sprintf ( '<label for="sc">%s</label>', __ ( 'Show the caption in the album edit screen', 'epa' ) ), $attr );
	}

	/**
	 * Prints settings description
	 *
	 * @param string $description
	 */
	private function display_description($description) {
		printf ( '<span class="description">%s</span>', esc_html ( $description ) );
	}

	/**
	 * Displays an HMTL input box.
	 *
	 * @param string $epa_name
	 *        	name of the input field (without <code>EasyPhotoAlbum[...]</code>. Only what
	 *        	should be on the ellipsis.
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
	private function display_input_field($epa_name, $value = '', $type = 'text', $after = '', $attrs = array()) {
		$html = '';
		foreach ( $attrs as $attr => $val ) {
			$html .= $attr . '="' . $val . '" ';
		}
		printf ( '<input type="%1$s" name="EasyPhotoAlbum[%2$s]" value="%3$s" %4$s/>%5$s', $type, $epa_name, $value, $html, $after );
	}
}