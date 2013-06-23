<?php
/*
 * Plugin Name: Easy Photo Album
 * Version: 1.0.2
 * Author: TV productions
 * Author URI: http://tv-productions.org/
 * Description: This plugin makes it very easy to create and manage photo albums. You can help by submit bugs and request new features at the plugin page at wordpress.org.
 * Licence: GPL3
 */

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

require_once 'EPA_PostType.php';
require_once 'EPA_Renderer.php';

/**
 * Class that keeps track of the options and the version.
 *
 * @author TV productions
 * @package EasyPhotoAlbum
 */
class EasyPhotoAlbum {
	private static $instance = null;
	private $options = array ();
	private $post_type = null;
	public static $version = '1.0.2';

	private function __construct() {
		$this->options_init ();
		$this->post_type = new EPA_PostType ();

		load_plugin_textdomain ( 'epa', false, basename ( dirname ( __FILE__ ) ) . '/lang' );
		register_activation_hook ( __FILE__, array (
				&$this,
				'on_activation'
		) );
		register_deactivation_hook ( __FILE__, array (
				&$this,
				'remove_capabilities'
		) );
		register_uninstall_hook ( __FILE__, array (
				__CLASS__,
				'uninstall'
		) );

		add_action ( 'admin_init', array (
				&$this,
				'admin_init'
		) );
		add_filter ( "plugin_action_links_" . plugin_basename ( __FILE__ ), array (
				&$this,
				'add_plugin_settings_link'
		), 10, 1 );

		// Rerender the albums every time the settings are updated.
		add_action ( 'update_option_EasyPhotoAlbum', array (
				&$this,
				'rerender_photos'
		), 11, 2 );
		add_action ( 'add_option_EasyPhotoAlbum', array (
				&$this,
				'rerender_photos'
		), 11, 2 );
	}

	/**
	 * When the plugin is activated
	 */
	public function on_activation() {
		// First, make shure the post type is registered
		$this->post_type->add_album_posttype ();
		// Second, add the caps to make shure the user(s) see the menu item
		$this->assign_capabilities ();
		// And flush the rewrite rules, so that the permalinks work
		flush_rewrite_rules();
	}

	/**
	 * Assigns capabilities to the current roles.
	 * Called on plugin activation
	 */
	public function assign_capabilities() {
		global $wp_roles;
		if (! isset ( $wp_roles ))
			$wp_roles = new WP_Roles ();

		$posttype_epa = get_post_type_object ( EPA_PostType::POSTTYPE_NAME );
		// Add epa caps according to the current caps
		// Example: role has edit_post (and is granted), then the role edit_epa_album is added.
		foreach ( $wp_roles->role_objects as $name => $role ) {
			foreach ( $role->capabilities as $cap => $grand ) {
				if (isset ( $posttype_epa->cap->$cap ) && $grand) {
					$role->add_cap ( $posttype_epa->cap->$cap );
				}
			}
		}
	}

	/**
	 * Removes the capabilities from the roles
	 * Called on plugin deactivation
	 */
	public function remove_capabilities() {
		global $wp_roles;
		if (! isset ( $wp_roles ))
			$wp_roles = new WP_Roles ();
		$posttype_epa = get_post_type_object ( EPA_PostType::POSTTYPE_NAME );
		$epa_caps = array_values ( get_object_vars ( ($posttype_epa->cap) ) );
		// remove epa caps
		foreach ( $wp_roles->role_objects as $role ) {
			foreach ( $epa_caps as $cap ) {
				if ('read' != $cap) // make shure that the read cap isn't removed.
					$role->remove_cap ( $cap );
			}
		}

		// And flush the rewrite rules, so that the permalinks work
		flush_rewrite_rules();
	}

	/**
	 * On admin init
	 */
	public function admin_init() {
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
		add_settings_field ( 'showtitlewiththumbnail', __ ( 'Title' ), array (
				&$this,
				'display_showtitlewiththumbnail_field'
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
		$valid = $this->options;
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

		return $valid;
	}

	public function display_settings_section() {
		printf ( '<p>%1$s <i>%2$s</i></p>', __ ( 'Settings that changes the appreance of the photo albums.', 'epa' ), __ ( 'Note: when you use the lightbox, you have to regenerate the images, in order to make them the desired size.', 'epa' ) );
	}

	public function display_linkto_field() {
		?>
<select name="EasyPhotoAlbum[linkto]">
	<option value="file" <?php selected($this->linkto, 'file', true);?>><?php _e('The image file', 'epa');?></option>
	<option value="attachment"
		<?php selected($this->linkto, 'attachment', true);?>><?php _e('The attachment page', 'epa');?></option>
	<option value="lightbox"
		<?php selected($this->linkto, 'lightbox', true);?>><?php _e('Lightbox display', 'epa');?></option>
</select>
<?php
	}

	public function display_thumbnailwidth_field() {
		$this->display_input_field ( 'thumbnailwidth', $this->thumbnailwidth, 'number', 'px ', array (
				'size' => 5
		) );
		$this->display_description ( __ ( 'The display width of the thumbnails.', 'epa' ) );
	}

	public function display_thumbnailheight_field() {
		$this->display_input_field ( 'thumbnailheight', $this->thumbnailheight, 'number', 'px ', array (
				'size' => 5
		) );
		$this->display_description ( __ ( 'The display height of the thumbnails.', 'epa' ) );
	}

	public function display_displaywidth_field() {
		$this->display_input_field ( 'displaywidth', $this->displaywidth, 'number', 'px ', array (
				'size' => 5
		) );
		$this->display_description ( __ ( 'The maximum width of the image when showed in a lightbox.', 'epa' ) );
	}

	public function display_displayheight_field() {
		$this->display_input_field ( 'displayheight', $this->displayheight, 'number', 'px ', array (
				'size' => 5
		) );
		$this->display_description ( __ ( 'The maximum height of the image when showed in a lightbox.', 'epa' ) );
	}

	public function display_showtitlewiththumbnail_field() {
		$attr = array (
				'id' => 'stwt'
		);
		if ($this->showtitlewiththumbnail)
			$attr += array (
					'checked' => 'checked'
			);
		$this->display_input_field ( 'showtitlewiththumbnail', 'true', 'checkbox', sprintf ( ' <label for="stwt">%s</label>', __ ( 'The title will be displayed under the thumbnail.', 'epa' ) ), $attr );
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
	 *        	The value of the input field, default empty.
	 * @param string $type
	 *        	The HTML type of the input, default text
	 * @param string $after
	 *        	The text after the input, default nothing.
	 * @param array $attrs
	 *        	An array with some extra attributes, like <code>array ('size' => 5);</code>
	 */
	private function display_input_field($epa_name, $value = '', $type = 'text', $after = '', $attrs = array()) {
		$html = '';
		foreach ( $attrs as $attr => $val ) {
			$html .= $attr . '="' . $val . '" ';
		}
		printf ( '<input type="%1$s" name="EasyPhotoAlbum[%2$s]" value="%3$s" %4$s/>%5$s', $type, $epa_name, $value, $html, $after );
	}

	/**
	 * Rerenders all the albums.
	 */
	public function rerender_photos($oldval, $newval) {
		// Set the $options to the newvalue
		$this->options = $newval;
		$albums = get_posts ( array (
				'posts_per_page' => '',
				'numberposts' => '',
				'post_type' => EPA_PostType::POSTTYPE_NAME
		) );

		foreach ( $albums as $album ) {
			// Render each album
			$renderer = new EPA_Renderer ( get_post_meta ( $album->ID, EPA_PostType::SETTINGS_NAME, true ), get_the_title ( $album->ID ) );
			wp_update_post ( array (
					'ID' => $album->ID,
					'post_content' => $renderer->render ( false )
			) );
		}
	}

	/**
	 * Removes the options on deinstallation.
	 */
	public static function uninstall() {
		// Remove options
		delete_option ( 'EasyPhotoAlbum' );
	}

	/**
	 * Adds a link to the plugin settings on the plugin page.
	 *
	 * @param array $links
	 * @return array
	 */
	public function add_plugin_settings_link($links) {
		$links [] = sprintf ( '<a href="%1$s">%2$s</a>', admin_url ( 'options-media.php' ), __ ( 'Settings' ) );
		return $links;
	}

	public function __get($name) {
		if ('version' == $name)
			return self::$version;
		if (isset ( $this->options [$name] ))
			return $this->options [$name];
		else
			throw new Exception ( sprintf ( "Property not found Exception in EasyPhotoAlbum: property '%s' isn't valid.", $name ), 101 );
	}

	/**
	 * Returns the single instance of this class.
	 *
	 * @return EasyPhotoAlbum
	 */
	public static function get_instance() {
		return (self::$instance instanceof self ? self::$instance : self::$instance = new self ());
	}

	/**
	 * Loads existing options, or loads the defaults.
	 */
	private function options_init() {
		$this->options = array (
				'linkto' => 'lightbox',
				'thumbnailwidth' => 150,
				'thumbnailheight' => 150,
				'displaywidth' => 600,
				'displayheight' => 600,
				'showtitlewiththumbnail' => true
		);
		$this->options = get_option ( 'EasyPhotoAlbum', $this->options );
	}
}

// Create a new instance: startup plugin
EasyPhotoAlbum::get_instance ();