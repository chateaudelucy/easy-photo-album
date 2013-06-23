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
		add_settings_field ( 'displaywidth', __ ( 'Display width', 'epa' ), array (
				&$this,
				'display_displaywidth_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'displayheight', __ ( 'Display height', 'epa' ), array (
				&$this,
				'display_displayheight_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'showtitlewiththumbnail', __ ( 'Title', 'epa' ), array (
				&$this,
				'display_showtitlewiththumbnail_field'
		), 'media', 'epa-section' );
		add_settings_field ( 'numimageswhennotsingle', __ ( 'Number of images for excerpt', 'epa' ), array (
				&$this,
				'display_numimageswhennotsingle_field'
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
		$valid ['displaywidth'] = (is_numeric ( $input ['displaywidth'] ) ? $input ['displaywidth'] : $valid ['displaywidth']);
		$valid ['displayheight'] = (is_numeric ( $input ['displayheight'] ) ? $input ['displayheight'] : $valid ['displayheight']);
		$valid ['showtitlewiththumbnail'] = (isset ( $input ['showtitlewiththumbnail'] ) && $input ['showtitlewiththumbnail'] == 'true' ? true : false);
		$valid ['numimageswhennotsingle'] = (is_numeric ( $input ['numimageswhennotsingle'] ) ? $input ['numimageswhennotsingle'] : $valid ['numimageswhennotsingle']);

		return $valid;
	}

	public function display_settings_section() {
		printf ( '<p>%1$s <i>%2$s</i></p>', __ ( 'Settings that changes the appreance of the photo albums.', 'epa' ), __ ( 'Note: when you use the lightbox, you have to regenerate the images, in order to make them the desired size.', 'epa' ) );
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
				'size' => 5
		) );
		$this->display_description ( __ ( 'The display width of the thumbnails.', 'epa' ) );
	}

	public function display_thumbnailheight_field() {
		$this->display_input_field ( 'thumbnailheight', EasyPhotoAlbum::get_instance ()->thumbnailheight, 'number', 'px ', array (
				'size' => 5
		) );
		$this->display_description ( __ ( 'The display height of the thumbnails.', 'epa' ) );
	}

	public function display_displaywidth_field() {
		$this->display_input_field ( 'displaywidth', EasyPhotoAlbum::get_instance ()->displaywidth, 'number', 'px ', array (
				'size' => 5
		) );
		$this->display_description ( __ ( 'The maximum width of the image when showed in a lightbox.', 'epa' ) );
	}

	public function display_displayheight_field() {
		$this->display_input_field ( 'displayheight', EasyPhotoAlbum::get_instance ()->displayheight, 'number', 'px ', array (
				'size' => 5
		) );
		$this->display_description ( __ ( 'The maximum height of the image when showed in a lightbox.', 'epa' ) );
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

	public function display_numimageswhennotsingle_field() {
		$this->display_input_field('numimageswhennotsingle', EasyPhotoAlbum::get_instance()->numimageswhennotsingle,'number', 'images ' );
		$this->display_description(__('The number of images showed when the album is not on a single page. Set to 0 for all', 'epa'));
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
	 *        	[optional] An array with some extra attributes, like <code>array ('size' => 5);</code>, default none.
	 */
	private function display_input_field($epa_name, $value = '', $type = 'text', $after = '', $attrs = array()) {
		$html = '';
		foreach ( $attrs as $attr => $val ) {
			$html .= $attr . '="' . $val . '" ';
		}
		printf ( '<input type="%1$s" name="EasyPhotoAlbum[%2$s]" value="%3$s" %4$s/>%5$s', $type, $epa_name, $value, $html, $after );
	}
}