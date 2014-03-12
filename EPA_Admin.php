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

require_once 'EPA_Help.php';

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
	private $help = null;

	public function __construct() {
		add_action ( 'admin_init', array (
				$this,
				'admin_init'
		) );
		add_action ( 'admin_menu', array (
				$this,
				'add_pages'
		) );
		add_action ( 'network_admin_menu', array (
				$this,
				'add_about_page'
		) );
		add_action ( 'admin_head', array (
				$this,
				'admin_head'
		) );
		add_action ( 'load-index.php', array (
				$this,
				'load_about_page'
		) );
		add_action ( 'load-plugins.php', array (
				$this,
				'load_about_page'
		) );
		add_action ( 'admin_enqueue_scripts', array (
				$this,
				'admin_enqueue_scripts'
		) );

		$this->help = new EPA_Help ();
	}

	/**
	 * Add settings and about pages
	 *
	 * @since 1.2
	 */
	public function add_pages() {
		$this->admin_page = add_options_page ( _x ( 'Easy Photo Album Settings', 'Page title of settings page', 'epa' ), _x ( 'Easy Photo Album', 'Menu title for the Easy Photo Album settings page.', 'epa' ), 'manage_options', 'epa-settings', array (
				$this,
				'render_admin_page'
		) );

		$this->add_about_page ();
	}

	/**
	 * Adds the EPA about page to the dashboard page.
	 */
	public function add_about_page() {
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
				wp_redirect ( is_network_admin () ? network_admin_url ( 'index.php?page=epa-about' ) : admin_url ( 'index.php?page=epa-about' ) );
				exit ();
			}
		}
	}

	/**
	 * Do some styling for the menu and load css if needed
	 *
	 * @since 1.2
	 */
	public function admin_head() {
		// Remove the menu item for the about page.
		remove_submenu_page ( 'index.php', 'epa-about' );
		if (get_current_screen ()->id === $this->admin_page) {
			wp_enqueue_style ( 'epa-settings-css', plugin_dir_url ( __FILE__ ) . 'css/easy-photo-album-settings' . (defined ( 'WP_DEBUG' ) ? '' : '.min') . '.css', false, EasyPhotoAlbum::$version );
			wp_enqueue_script ( 'epa-settings-js', plugin_dir_url ( __FILE__ ) . 'js/easy-photo-album-settings' . (defined ( 'WP_DEBUG' ) ? '' : '.min') . '.js', array (
					'jquery'
			), EasyPhotoAlbum::$version, true );
		}
	}

	/**
	 * Add the settings to the media options screen
	 */
	public function admin_init() {
		register_setting ( 'EasyPhotoAlbumSettings', 'EasyPhotoAlbum', array (
				$this,
				'validate_settings'
		) );
		// Display settings
		add_settings_section ( 'epa-section-display', __ ( 'Display Settings', 'epa' ), false, $this->admin_page );
		add_settings_field ( 'viewmode', __ ( 'Image view mode', 'epa' ), array (
				$this,
				'display_viewmode_field'
		), $this->admin_page, 'epa-section-display', array (
				'name' => 'displaysize'
		) );
		add_settings_field ( 'displaycolumns', __ ( 'Columns', 'epa' ), array (
				$this,
				'display_numeric_field'
		), $this->admin_page, 'epa-section-display', array (
				'name' => 'displaycolumns',
				'min' => 1,
				'step' => 1
		) );
		add_settings_field ( 'showcaption', __ ( 'Show caption', 'epa' ), array (
				$this,
				'display_checkbox_field'
		), $this->admin_page, 'epa-section-display', array (
				'name' => 'showcaption',
				'label_for' => 'epa-showcaption'
		) );
		add_settings_field ( 'displaysize', __ ( 'Image size', 'epa' ), array (
				$this,
				'display_imagesize_field'
		), $this->admin_page, 'epa-section-display', array (
				'name' => 'displaysize'
		) );
		add_settings_field ( 'excerptnumber', __ ( 'Number of images for excerpt', 'epa' ), array (
				$this,
				'display_numeric_field'
		), $this->admin_page, 'epa-section-display', array (
				'name' => 'excerptnumber',
				'min' => 0,
				'step' => 1
		) );

		// LIGHTBOX SECTION
		add_settings_section ( 'epa-section-lightbox', __ ( 'Lightbox settings', 'epa' ), false, $this->admin_page );
		add_settings_field ( 'showimagenumber', __ ( 'Show image number', 'epa' ), array (
				$this,
				'display_checkbox_field'
		), $this->admin_page, 'epa-section-lightbox', array (
				'name' => 'showimagenumber',
				'label_for' => 'epa-showimagenumber'
		) );
		add_settings_field ( 'imagenumberformat', __ ( 'Image number format', 'epa' ), array (
				$this,
				'display_text_field'
		), $this->admin_page, 'epa-section-lightbox', array (
				'name' => 'imagenumberformat'
		) );
		add_settings_field ( 'wraparound', __ ( 'Wrap around', 'epa' ), array (
				$this,
				'display_checkbox_field'
		), $this->admin_page, 'epa-section-lightbox', array (
				'name' => 'wraparound',
				'label_for' => 'epa-wraparound'
		) );
		add_settings_field ( 'scalelightbox', __ ( 'Scale to fit', 'epa' ), array (
				$this,
				'display_checkbox_field'
		), $this->admin_page, 'epa-section-lightbox', array (
				'name' => 'scalelightbox',
				'label_for' => 'epa-scalelightbox'
		) );
		add_settings_field ( 'lightboxsize', __ ( 'Imagesize', 'epa' ), array (
				$this,
				'display_imagesize_field'
		), $this->admin_page, 'epa-section-lightbox', array (
				'name' => 'lightboxsize'
		) );
		add_settings_section ( 'epa-section-miscellaneous', __ ( 'Miscellaneous settings', 'epa' ), false, $this->admin_page );
		add_settings_field ( 'showtitleintable', __ ( 'Show title field', 'epa' ), array (
				$this,
				'display_checkbox_field'
		), $this->admin_page, 'epa-section-miscellaneous', array (
				'name' => 'showtitleintable',
				'label_for' => 'epa-showtitleintable'
		) );
		add_settings_field ( 'inmainloop', __ ( 'Show albums on blog page', 'epa' ), array (
				$this,
				'display_checkbox_field'
		), $this->admin_page, 'epa-section-miscellaneous', array (
				'name' => 'inmainloop',
				'label_for' => 'epa-inmainloop'
		) );
	}

	/**
	 * Validates the settings values.
	 *
	 * @param array $input
	 * @return array
	 */
	public function validate_settings($input) {
		require_once 'EPA_Validator.php';

		$validator = new EPA_Validator ();
		$valid = get_option ( 'EasyPhotoAlbum', array () );
		// DISPLAY SETTINGS
		$valid ['viewmode'] = $validator->get_if_set ( $input, 'viewmode', $valid ['viewmode'] );
		$valid ['displaycolumns'] = $validator->validate_nummeric ( $validator->get_if_set ( $input, 'displaycolumns', $valid ['displaycolumns'] ), 1, $valid ['displaycolumns'] );
		$valid ['showcaption'] = $validator->validate_checkbox ( $input, 'showcaption' );
		// No validation on displaysize?
		$valid ['displaysize'] = $validator->get_if_set ( $input, 'displaysize', $valid ['displaysize'] );
		$valid ['excerptnumber'] = $validator->validate_nummeric ( $validator->get_if_set ( $input, 'excerptnumber', $valid ['excerptnumber'] ), 0, $valid ['excerptnumber'] );

		// LIGHTBOX SETTINGS
		$valid ['showimagenumber'] = $validator->validate_checkbox ( $input, 'showimagenumber' );
		$valid ['imagenumberformat'] = $validator->get_if_set ( $input, 'imagenumberformat', $valid ['imagenumberformat'] );
		$valid ['wraparound'] = $validator->validate_checkbox ( $input, 'wraparound' );
		$valid ['scalelightbox'] = $validator->validate_checkbox ( $input, 'scalelightbox' );
		// Again: no validation on image sizes?
		$valid ['lightboxsize'] = $validator->get_if_set ( $input, 'lightboxsize', $valid ['lightboxsize'] );

		// MISCELLANEOUS SETTINGS
		$valid ['showtitleintable'] = $validator->validate_checkbox ( $input, 'showtitleintable' );
		$valid ['inmainloop'] = $validator->validate_checkbox ( $input, 'inmainloop' );

		// Set for all albums the display settings?
		if (isset ( $input ['setforallalbums'] )) {
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

		// Force a rerender
		if ($this->array_equal_values ( get_option ( 'EasyPhotoAlbum', array () ), $valid )) {
			// The new and the old settings are the same, so there won't be a rerender
			// add a difference
			if (isset ( $valid ['none'] )) {
				$valid ['none'] = 1 - $valid ['none']; // $valid['none'] is 0 or 1.
			} else {
				$valid ['none'] = 1;
			}
		}

		return $valid;
	}

	/**
	 * Renders the viewmode options for the albums
	 */
	public function display_viewmode_field() {
		?>
<span class="block"><span class="block"><input type="radio"
		name="EasyPhotoAlbum[viewmode]" value="file" id="epa-viewmode-file"
		<?php checked(EasyPhotoAlbum::get_instance()->viewmode, 'file', true);?> />
		<label for="epa-viewmode-file"><?php _e('Imagefile', 'epa');?></label>
		<br /> <input type="radio" name="EasyPhotoAlbum[viewmode]"
		value="attachment" id="epa-viewmode-attachment"
		<?php checked(EasyPhotoAlbum::get_instance()->viewmode, 'attachment', true);?> />
		<label for="epa-viewmode-attachment"><?php _e('Attachment', 'epa');?></label>
		<br /> <input type="radio" name="EasyPhotoAlbum[viewmode]"
		value="lightbox" id="epa-viewmode-lightbox"
		<?php checked(EasyPhotoAlbum::get_instance()->viewmode, 'lightbox', true);?> />
		<label for="epa-viewmode-lightbox"><?php _e('Lightbox', 'epa');?></label></span>
	<?php $this->help->render_tooltip('viewmode');?>
</span>
<?php
	}

	/**
	 * Renders an HTML input element of the type text.
	 *
	 * @param array $args
	 */
	public function display_text_field($args) {
		$name = $args ['name'];
		echo '<input type="text" value="' . esc_attr ( EasyPhotoAlbum::get_instance ()->$name ) . '" name="EasyPhotoAlbum[' . $name . ']"/>';
		$this->help->render_tooltip ( $name );
	}

	/**
	 * Renders an HTML input element of the type number.
	 *
	 * @param array $args
	 */
	public function display_numeric_field($args) {
		$name = $args ['name'];
		unset ( $args ['name'] );
		$args ['size'] = 5;
		if (isset ( $args ['class'] )) {
			if (is_array ( $args ['class'] )) {
				$args ['class'] [] = 'small-text';
			} else {
				$args ['class'] = array (
						$args ['class'],
						'small-text'
				);
			}
		} else {
			$args ['class'] = 'small-text';
		}
		echo '<input type="number" value="' . esc_attr ( EasyPhotoAlbum::get_instance ()->$name ) . '" name="EasyPhotoAlbum[' . $name . ']" ' . EasyPhotoAlbum::get_instance ()->generate_attributes ( $args ) . '/>';
		$this->help->render_tooltip ( $name );
	}

	/**
	 * Renders an HTML input element of the type checkbox
	 *
	 * @param array $args
	 */
	public function display_checkbox_field($args) {
		$name = $args ['name'];
		unset ( $args ['name'] );
		if (isset ( $args ['label_for'] ))
			$args ['id'] = $args ['label_for'];

		echo '<input type="checkbox" value="true" name="EasyPhotoAlbum[' . $name . ']" ' . checked ( EasyPhotoAlbum::get_instance ()->$name, true, false ) . ' ' . EasyPhotoAlbum::get_instance ()->generate_attributes ( $args ) . '/>';
		$this->help->render_tooltip ( $name );
	}

	/**
	 * Renders an HTML select element with the image sizes
	 *
	 * @param array $args
	 */
	public function display_imagesize_field($args) {
		$name = $args ['name'];
		echo '<select name="EasyPhotoAlbum[' . $name . ']">';
		// Using the same filter as in wp-admin/includes/media.php for the function
		// image_size_input_fields. Other plugins can use this filter to add their image size.
		$size_names = apply_filters ( 'image_size_names_choose', array (
				'thumbnail' => __ ( 'Thumbnail' ),
				'medium' => __ ( 'Medium' ),
				'large' => __ ( 'Large' ),
				'full' => __ ( 'Full Size' )
		) );
		foreach ( $size_names as $size => $displayname ) {
			$selected = selected ( EasyPhotoAlbum::get_instance ()->$name, $size, false );
			echo <<<HTML
                        <option value="{$size}" {$selected}>{$displayname}</option>
HTML;
		}
		echo '</select>';
		$this->help->render_tooltip ( $name );
	}

	public function render_admin_page() {
		?>
<div class="wrap">
	<?php
		screen_icon (); // deprecated since wp 3.8
		?>
		<h2><?php _ex('Easy Photo Album Settings', 'Page title of settings page', 'epa');?></h2>
		<?php
		settings_errors ( 'EasyPhotoAlbumSettings' );
		?>
	<form method="post" action="options.php">
		<?php
		settings_fields ( 'EasyPhotoAlbumSettings' );
		?>
		<div class="epa-settings-block">
			<p><?php printf( __('The following settings affect the display and behaviour of the photo album.%1$sFor help, check the help text (click on the quesion icon) or visit the %2$ssupport forums%3$s.', 'epa'), '<br/>', '<a href="'.EasyPhotoAlbum::get_instance()->forumurl.'" target="_blank">', '</a>');?></p>
			<p><?php printf( __('Support the development of this plugin by donating a %1$scup of coffee%2$s.','epa'), '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=KNZYFDLJHV756" target="_blank">','</a>');?></p>
		</div>

		<div class="epa-settings-block" id="epa-display-settings">
			<h3><?php _e('Display settings', 'epa')?></h3>
			<table class="form-table">
				<?php
		do_settings_fields ( $this->admin_page, 'epa-section-display' );
		?>
			</table>
			<?php
		submit_button ( __ ( 'Apply to all albums', 'epa' ), 'secondary large', 'EasyPhotoAlbum[setforallalbums]', false );
		$this->help->render_tooltip ( 'setforallalbums' );
		?>
		</div>

		<div class="epa-settings-block" id="epa-lightbox-settings">
			<h3><?php _e('Lightbox settings', 'epa');?></h3>
			<table class="form-table">
		<?php
		do_settings_fields ( $this->admin_page, 'epa-section-lightbox' );
		?>
		</table>
		</div>

		<div class="epa-settings-block" id="epa-miscellaneous-settings">
			<h3><?php _e('Miscellaneous settings', 'epa'); ?></h3>
			<table class="form-table">
			<?php
		do_settings_fields ( $this->admin_page, 'epa-section-miscellaneous' );
		?>
			</table>
		</div>
		<?php
		submit_button ();
		?>
	</form>
</div>
<?php
	}

	/**
	 * Hooked to admin_enqueue_scripts
	 */
	public function admin_enqueue_scripts() {
		// Remove the autosave function, becuase it will only save the title
		// and the content (that doesn't exists for epa). Will it be better?
		if (EPA_PostType::POSTTYPE_NAME == get_post_type ())
			wp_dequeue_script ( 'autosave' );
	}

	/**
	 * Determens if two arrays are the same
	 *
	 * @link http://stackoverflow.com/a/17638939/1167959
	 * @param array $a
	 * @param array $b
	 * @param bool $strict
	 * @param bool $allow_duplicate_values
	 * @return boolean
	 */
	public function array_equal_values(array $a, array $b, $strict = FALSE, $allow_duplicate_values = TRUE) {
		$add = ( int ) ! $allow_duplicate_values;

		if ($add and count ( $a ) !== count ( $b )) {
			return FALSE;
		}

		$table = array ();
		return $this->array_equal_values_count ( $a, $table, $add, $strict ) == $this->array_equal_values_count ( $b, $table, $add, $strict );
	}

	private function array_equal_values_count(array $array, &$table, $add, $strict) {
		$exit = ( bool ) $table;
		$result = array ();
		foreach ( $array as $value ) {
			$key = array_search ( $value, $table, $strict );

			if (FALSE !== $key) {
				if (! isset ( $result [$key] )) {
					$result [$key] = 1;
				} else {
					$result [$key] += $add;
				}
				continue;
			}

			if ($exit) {
				break;
			}

			$key = count ( $table );
			$table [$key] = $value;
			$result [$key] = 1;
		}
		return $result;
	}
}