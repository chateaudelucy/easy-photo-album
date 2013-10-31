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
	private $admin_page = '';
	private $about_page = '';

	public function __construct() {
		add_action ( 'admin_menu', array (
				&$this,
				'add_pages'
		) );
		add_action ( 'admin_init', array (
				&$this,
				'admin_init'
		) );
		add_action ( 'admin_head', array (
				&$this,
				'admin_head'
		) );
		add_action ( 'load-index.php', array (
				&$this,
				'load_about_page'
		) );
		add_action ( 'load-plugins.php', array (
				&$this,
				'load_about_page'
		) );
	}

	/**
	 * Add settings and about pages
	 *
	 * @since 1.2
	 */
	public function add_pages() {
		$this->admin_page = add_options_page ( _x ( 'Easy Photo Album Settings', 'Page title of settings page', 'epa' ), _x ( 'Easy Photo Album', 'Menu title for the Easy Photo Album settings page.', 'epa' ), 'manage_options', 'epa-settings', array (
				&$this,
				'render_admin_page'
		) );
		// The menu link is removed in admin_head
		$this->about_page = add_dashboard_page ( __ ( 'About Easy Photo Album', 'epa' ), 'About epa', 'manage_options', 'epa-about', create_function ( '', "require_once 'EPA_about.php';" ) );
	}

	/**
	 * Loads the about page if the plugin is activated in a bulk action
	 *
	 * @since 1.2
	 */
	public function load_about_page() {
		// Redirect to about page if the plugin is just activated
		if (get_option ( 'epa_redirect_' . get_current_user_id (), false )) {
			if (! isset ( $_GET ['activate-multi'] )) {
				// only delete the option before a redirect
				delete_option ( 'epa_redirect_' . get_current_user_id () );
				wp_redirect ( 'index.php?page=epa-about' );
			}
		}
	}

	/**
	 * Do some styling for the menu
	 *
	 * @since 1.2
	 */
	public function admin_head() {
		// Remove the menu item for the about page.
		remove_submenu_page ( 'index.php', 'epa-about' );
	}

	/**
	 * Add the settings to the media options screen
	 */
	public function admin_init() {
		// Add the settings to the media options screen
		register_setting ( 'EasyPhotoAlbumSettings', 'EasyPhotoAlbum', array (
				&$this,
				'validate_settings'
		) );
		add_settings_section ( 'epa-section-general', __ ( 'General options', 'epa' ), array (
				&$this,
				'display_general_settings_section'
		), $this->admin_page );
		add_settings_field ( 'linkto', __ ( 'Link image to', 'epa' ), array (
				&$this,
				'display_linkto_field'
		), $this->admin_page, 'epa-section-general' );
		add_settings_field ( 'displaycolumns', __ ( 'Columns', 'epa' ), array (
				&$this,
				'display_displaycolumns_field'
		), $this->admin_page, 'epa-section-general' );
		add_settings_field ( 'displaysize', __ ( 'Image size', 'epa' ), array (
				&$this,
				'display_displaysize_field'
		), $this->admin_page, 'epa-section-general' );
		add_settings_field ( 'showcaption', __ ( 'Show caption', 'epa' ), array (
				&$this,
				'display_showcaption_field'
		), $this->admin_page, 'epa-section-general' );
		add_settings_field ( 'numimageswhennotsingle', __ ( 'Number of images for excerpt', 'epa' ), array (
				&$this,
				'display_numimageswhennotsingle_field'
		), $this->admin_page, 'epa-section-general' );
		add_settings_field ( 'showtitleintable', __ ( 'Show the title', 'epa' ), array (
				&$this,
				'display_showtitleintable_field'
		), $this->admin_page, 'epa-section-general' );
		add_settings_field ( 'inmainloop', __ ( 'Photo albums on blog page', 'epa' ), array (
				&$this,
				'display_inmainloop_field'
		), $this->admin_page, 'epa-section-general' );

		// LIGHTBOX SECTION
		add_settings_section ( 'epa-section-lightbox', __ ( 'Lightbox settings', 'epa' ), array (
				&$this,
				'display_lightbox_settings_section'
		), $this->admin_page );
		add_settings_field ( 'showalbumlabel', __ ( 'Show the album label under the lightbox', 'epa' ), array (
				&$this,
				'display_showalbumlabel_field'
		), $this->admin_page, 'epa-section-lightbox' );
		add_settings_field ( 'albumlabel', __ ( 'Album label', 'epa' ), array (
				&$this,
				'display_albumlabel_field'
		), $this->admin_page, 'epa-section-lightbox' );
		add_settings_field ( 'wraparound', __ ( 'Wrap around', 'epa' ), array (
				&$this,
				'display_wraparound_field'
		), $this->admin_page, 'epa-section-lightbox' );
		add_settings_field ( 'scalelightbox', __ ( 'Scale images in the lightbox', 'epa' ), array (
				&$this,
				'display_scalelightbox_field'
		), $this->admin_page, 'epa-section-lightbox' );
		add_settings_field ( 'showallimagesinlightbox', __ ( 'Show all images in the lighbox', 'epa' ), array (
				&$this,
				'display_showallimagesinlightbox_field'
		), $this->admin_page, 'epa-section-lightbox' );

		// OVERRIDE SECTION
		add_settings_section ( 'epa-section-override', __ ( 'Override options', 'epa' ), null, $this->admin_page );
		add_settings_field ( 'override', __ ( 'Override album specific options', 'epa' ), array (
				&$this,
				'display_override_field'
		), $this->admin_page, 'epa-section-override' );
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
		$valid ['scalelightbox'] = (isset ( $input ['scalelightbox'] ) && $input ['scalelightbox'] == 'true' ? true : false);
		$valid ['numimageswhennotsingle'] = (is_numeric ( $input ['numimageswhennotsingle'] ) ? $input ['numimageswhennotsingle'] : $valid ['numimageswhennotsingle']);
		$valid ['showtitleintable'] = (isset ( $input ['showtitleintable'] ) && $input ['showtitleintable'] == 'true' ? true : false);
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

	public function display_general_settings_section() {
		printf ( '<p>%1$s</p><p>%2$s</p>', __ ( 'With those options you can change the display of the photo albums.', 'epa' ), sprintf ( __ ( 'Do you like this plugin? Please write a review or rate the plugin at %1$swordpress.org%2$s.', 'epa' ), '<a href="http://wordpress.org/support/view/plugin-reviews/easy-photo-album" target="_blank">', '</a>' ) );
	}

	public function display_linkto_field() {
		?>
<select name="EasyPhotoAlbum[linkto]">
	<option value="file"
		<?php selected(EasyPhotoAlbum::get_instance()->linkto, 'file', true);?>><?php _e('The image file', 'epa');?></option>
	<option value="attachment"
		<?php selected(EasyPhotoAlbum::get_instance()->linkto, 'attachment', true);?>><?php _e('The attachment page', 'epa');?></option>
	<option value="lightbox"
		<?php selected(EasyPhotoAlbum::get_instance()->linkto, 'lightbox', true);?>><?php _e('Lightbox', 'epa');?></option>
</select>
<strong>*</strong>
<?php
		$this->show_description ( __ ( "i.e. what will happen when the user clicks on an image?", 'epa' ) );
	}

	public function display_displaycolumns_field() {
		$this->show_input_field ( 'displaycolumns', EasyPhotoAlbum::get_instance ()->displaycolumns, 'number', '<strong>*</strong>', array (
				'step' => 1,
				'class' => 'small-text',
				'min' => 1,
				'max' => 15
		) );
		// 'max' => 15 means Minimum size of the images is then 5%
		$this->show_description ( __ ( 'How many collumns the album will have.', 'epa' ) );
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
		$this->show_description ( __ ( 'The image size that will be used when for the display of the album.', 'epa' ) );
	}

	public function display_showcaption_field() {
		$attr = array (
				'id' => 'stwt'
		);
		if (EasyPhotoAlbum::get_instance ()->showcaption)
			$attr += array (
					'checked' => 'checked'
			);
		$this->show_input_field ( 'showcaption', 'true', 'checkbox', sprintf ( ' <label for="stwt">&nbsp;%s</label><strong>*</strong>', __ ( 'Show the caption underneath the photo when the album is displayed.', 'epa' ) ), $attr );
	}

	public function display_lightbox_settings_section() {
		printf ( '<p id="epa-lightbox-settings">%s</p>', __ ( 'Those settings will only have effect if the "link image to" option is set to lightbox.', 'epa' ) );
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
		$this->show_input_field ( 'showalbumlabel', 'true', 'checkbox', sprintf ( '<label for="sal">&nbsp;%s</label>', __ ( 'Display a message like "Image x of y" underneath the lightbox (see next option)', 'epa' ) ), $attr );
	}

	public function display_albumlabel_field() {
		$this->show_input_field ( 'albumlabel', EasyPhotoAlbum::get_instance ()->albumlabel );
		$this->show_description ( __ ( 'You can translate or change the text that can be displayed underneath the lightbox (see the option above). {0} will be replaced with the current image number, {1} with the total number of images.', 'epa' ) );
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
		$this->show_input_field ( 'wraparound', 'true', 'checkbox', sprintf ( '<label for="wa">&nbsp;%s</label>', __ ( 'Wrap the images in the lightbox, i.e. when you reach the last image in the album and you click on the right arrow, the first image will be displayed', 'epa' ) ), $attr );
	}

	function display_scalelightbox_field() {
		$attr = array (
				'id' => 'sl'
		);
		if (EasyPhotoAlbum::get_instance ()->scalelightbox) {
			$attr += array (
					'checked' => 'checked'
			);
		}
		$this->show_input_field ( 'scalelightbox', 'true', 'checkbox', sprintf ( '<label for="sl">&nbsp;%s</label>', __ ( 'Scale the lightbox to the viewport, so every image fits nice on the screen.', 'epa' ) ), $attr );
	}

	public function display_numimageswhennotsingle_field() {
		$this->show_input_field ( 'numimageswhennotsingle', EasyPhotoAlbum::get_instance ()->numimageswhennotsingle, 'number', __ ( 'images', 'epa' ) . '<strong>*</strong> ', array (
				'step' => 1,
				'class' => 'small-text',
				'min' => 0
		) );
		$this->show_description ( __ ( "The number of photos shown when you view the album in an archive. Set to 0 to display all photos", 'epa' ) );
	}

	public function display_showtitleintable_field() {
		$attr = array (
				'id' => 'st'
		);
		if (EasyPhotoAlbum::get_instance ()->showtitleintable) {
			$attr += array (
					'checked' => 'checked'
			);
		}
		$this->show_input_field ( 'showtitleintable', 'true', 'checkbox', sprintf ( '<label for="st">&nbsp;%s</label>', __ ( 'Show the title field in the album edit screen', 'epa' ) ), $attr );
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
		$this->show_input_field ( 'inmainloop', 'true', 'checkbox', sprintf ( '<label for="iml">&nbsp;%s</label>', __ ( 'Show photo albums on the blog page (they will be included in the main loop).', 'epa' ) ), $attr );
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
		$this->show_input_field ( 'showallimagesinlightbox', 'true', 'checkbox', sprintf ( '<label for="saiil">&nbsp;%s</label>', __ ( "When an user watches some images in a lightbox from archive view, should the lightbox display all the images (also the ones that aren't shown in the archive view)?", 'epa' ) ), $attr );
	}

	public function display_override_field() {
		$attr = array (
				'id' => 'epa-override'
		);

		$this->show_input_field ( 'override', 'true', 'checkbox', sprintf ( '<label for="epa-override">&nbsp;%s</label>', __ ( 'Override the display options of each album with the default ones.', 'epa' ) ), $attr );
		$this->show_description ( sprintf ( esc_html ( __ ( 'You can change some display options for each album seperatly. The default options for new albums are the ones you have set here. This only affects the options marked with a %s.', 'epa' ) ), '<strong>*</strong>' ), false );
	}

	/**
	 * Renders and displays the admin settings page.
	 */
	public function render_admin_page() {
		// Explanation for the javascript:
		// Disable lightbox options when the lightbox isn't used
		// Enable those options before submit, else they wont be submitted.
		?>
<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php _ex('Easy Photo Album Settings', 'Page title of settings page', 'epa');?></h2>
		<?php settings_errors('EasyPhotoAlbumSettings')?>
		<style type="text/css">
table tr td strong {
	font-size: 150%;
	color: red;
}
</style>
	<form method="post" action="options.php">
		<?php
		settings_fields ( 'EasyPhotoAlbumSettings' );
		do_settings_sections ( $this->admin_page );
		submit_button ();
		?>
		</form>
	<script>
		jQuery(document).ready(function($){
			$('select[name="EasyPhotoAlbum[linkto]"]').change(function(){
				var $lightbox_settings = $('#epa-lightbox-settings').next('table');
				$('input', $lightbox_settings).prop('disabled', $(this).val() != 'lightbox');
			});
			$('#submit').click(function(e){
				$('input:disabled').prop('disabled', false);
			})
			$('select[name="EasyPhotoAlbum[linkto]"]').change();
		});
		</script>
</div>
<?php
	}

	/**
	 * Prints settings description
	 *
	 * @param string $description
	 * @param bool $escape
	 *        	[optional] Escape the content? Default true
	 */
	private function show_description($description, $escape = true) {
		echo ($escape ? esc_html ( $description ) : $description );
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