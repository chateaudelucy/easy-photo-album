<?php
/*
 * Plugin Name: Easy Photo Album
 * Version: 1.3
 * Author: TV productions
 * Author URI: http://tv-productions.org/
 * Description: This plugin makes it very easy to create and manage photo albums. The albums are responsive and display in a lightbox. You can help by submit bugs and request new features at the plugin page at wordpress.org.
 * Licence: GPL3
 * Text Domain:   epa
 * Domain Path:   /lang/
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
require_once 'EPA_Insert_Album.php';
require_once 'EPA_Renderer.php';
require_once 'EPA_Upgrade.php';

if (is_admin ()) {
	require_once 'EPA_List_Table.php';
	require_once 'EPA_Admin.php';
}

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
	private $admin = null;
	private $insert_album = null;
	public static $version = '1.3';

	private function __construct() {
		load_plugin_textdomain ( 'epa', false, basename ( dirname ( __FILE__ ) ) . '/lang' );

		// Do upgrade before the options are initialized.
		EPA_Upgrade::do_upgrade ();

		$this->options_init ();
		$this->post_type = new EPA_PostType ();
		$this->insert_album = new EPA_Insert_Album ();
		if (is_admin ()) {
			$this->admin = new EPA_Admin ();
		}

		register_activation_hook ( __FILE__, array (
				$this,
				'on_activation'
		) );
		register_deactivation_hook ( __FILE__, array (
				$this,
				'remove_capabilities'
		) );
		register_uninstall_hook ( __FILE__, array (
				__CLASS__,
				'uninstall'
		) );

		add_filter ( "plugin_action_links_" . plugin_basename ( __FILE__ ), array (
				$this,
				'add_plugin_links'
		), 10, 1 );

		// Rerender the albums every time the settings are updated.
		add_action ( 'update_option_EasyPhotoAlbum', array (
				$this,
				'rerender_photos'
		), 11, 2 );
		add_action ( 'add_option_EasyPhotoAlbum', array (
				$this,
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
		flush_rewrite_rules ();
		// regenerate the albums, if any options are changed by plugin update.
		$this->rerender_photos ( null, $this->options );
		// Redirect to about page after activation
		add_option ( 'epa_redirect_' . get_current_user_id (), true );
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
		flush_rewrite_rules ();
	}

	/**
	 * Rerenders all the albums.
	 */
	public function rerender_photos($oldval, $newval) {
		// Set the $options to the newvalue
		$this->options = $newval;
		$albums = get_posts ( array (
				'posts_per_page' => - 1,
				'numberposts' => '',
				'post_type' => EPA_PostType::POSTTYPE_NAME
		) );

		foreach ( $albums as $album ) {
			// Render each album
			$renderer = new EPA_Renderer ( $album );
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
		// Remove the option for all users
		foreach ( get_users ( array (
				'who' => 'autors'
		) ) as $user ) {
			delete_option ( 'epa_redirect_' . $user->id );
		}
		delete_option ( 'epa_update_fields' );
		delete_option ( 'EasyPhotoAlbumVersion' );
	}

	/**
	 * Adds a link to the plugin settings on the plugin page.
	 *
	 * @param array $links
	 * @return array
	 */
	public function add_plugin_links($links) {
		$links [] = sprintf ( '<a href="%1$s">%2$s</a>', admin_url ( 'options-general.php?page=epa-settings' ), __ ( 'Settings', 'epa' ) );
		$links [] = sprintf ( '<a href="%1$s">%2$s</a>', admin_url ( 'index.php?page=epa-about' ), __ ( 'About', 'epa' ) );
		return $links;
	}

	public function __get($name) {
		if ('version' == $name)
			return self::$version;
		if ('helpurl' == $name)
			return 'http://tv-productions.org/plugins/easy-photo-album/support/story.html';
		if ('forumurl' == $name)
			return 'http' . (is_ssl () ? 's' : '') . '://wordpress.org/support/plugin/easy-photo-album';
		if ('changelogurl' == $name)
			return 'http:' . (is_ssl () ? 's' : '') . '//wordpress.org/plugins/easy-photo-album/changelog/';
		if (isset ( $this->options [$name] ))
			return $this->options [$name];
		else
			throw new Exception ( sprintf ( __ ( 'Property not found Exception in EasyPhotoAlbum: property "%s" isn&#39;t valid.', 'epa' ), $name ), 101 );
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
	 * Returns the options inclusive their default values.
	 *
	 * @return Array options
	 * @since 1.3
	 */
	private function get_default_options() {
		return array (
				// Display settings
				'viewmode' => 'lightbox',
				'displaycolumns' => 3,
				'showcaption' => true,
				'displaysize' => 'thumbnail',
				'excerptnumber' => 3,
				// lightbox settings
				'showimagenumber' => true,
				'imagenumberformat' => _x ( 'Image {0} of {1}', 'Example: Image 4 of 6, so {0} is the current image number and {1} is the total number of images.', 'epa' ),
				'wraparound' => false,
				'scalelightbox' => true,
				'lightboxsize' => 'large',
				// Miscellaneous settings
				'showtitleintable' => false,
				'inmainloop' => true
		);
	}

	/**
	 * Loads existing options, or loads the defaults.
	 */
	private function options_init() {
		$defaults = $this->get_default_options ();
		$from_db = get_option ( 'EasyPhotoAlbum', false );
		if (false == $from_db) {
			// Store the default options
			add_option ( 'EasyPhotoAlbum', $defaults );
			$from_db = array ();
		}
		$this->options = $this->add_defaults ( $from_db, $defaults, true );
		update_option('EasyPhotoAlbum', $this->options);
	}

	/**
	 * Returns the general settings for displaying a photo album, set by the user on the options
	 * screen.
	 * This options are used for the display options of each photo album.
	 *
	 * @return array
	 */
	public function get_default_display_options($options = array()) {
		return array (
				'columns' => isset ( $options ['displaycolumns'] ) ? ( int ) $options ['displaycolumns'] : ( int ) $this->displaycolumns,
				'excerpt_number' => isset ( $options ['excerptnumber'] ) ? $options ['excerptnumber'] : $this->excerptnumber,
				'show_caption' => isset ( $options ['showcaption'] ) ? $options ['showcaption'] : $this->showcaption,
				'display_size' => isset ( $options ['displaysize'] ) ? $options ['displaysize'] : $this->displaysize
		);
	}

	/**
	 * Makes sure the defaults are in the array.
	 * When strict is true, only the defaults are in the array.
	 *
	 * @param array $array
	 * @param array $defaults
	 * @param bool $strict
	 * @return array
	 * @since 1.3
	 */
	public function add_defaults($array, $defaults, $strict = true) {
		if ($strict)
			$res = array ();
		else
			$res = $array;
		foreach ( $defaults as $index => $value ) {
			if (! isset ( $array [$index] ))
				$array [$index] = $value;
			$res [$index] = $array [$index];
		}

		return $res;
	}

	/**
	 * Generates attributes from an array
	 *
	 * @param array $array
	 *        	the attribute names and values
	 * @return string
	 *
	 * @since 1.3
	 */
	public function generate_attributes($array) {
		$out = '';
		foreach ( $array as $name => $value ) {
			if (is_array ( $value ))
				$value = implode ( ' ', $value );
				// Check if there is a underscore in the name. If so, ignore
			if (false === strpos ( $name, '_' ))
				$out .= $name . '="' . esc_attr ( $value ) . '" ';
		}
		return $out;
	}

	/**
	 * Get the number of downloads of this plugin from the WordPress repository.
	 *
	 * @param bool $force_update
	 *        	get the most actual value?
	 * @return number string
	 */
	public function get_download_count($force_update = false) {
		if ($force_update || false === ($downloaded = get_transient ( 'epa_download_count' ))) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$data = plugins_api ( 'plugin_information', array (
					'slug' => 'easy-photo-album',
					'fields' => array (
							'downloaded' => true
					)
			) );
			if (! is_wp_error ( $data ) && isset ( $data->downloaded )) {
				set_transient ( 'epa_download_count', $data->downloaded, 5 * MINUTE_IN_SECONDS );
				return number_format_i18n ( $data->downloaded );
			} else {
				// The number by release (rounded)
				return '~' . number_format_i18n ( 14000 );
			}
		}
		return number_format_i18n ( $downloaded );
	}
}

// Create a new instance: startup plugin
EasyPhotoAlbum::get_instance ();
return;

// Some strings for translation of the plugin description
_n_noop ( 'This plugin makes it very easy to create and manage photo albums. The albums are responsive and display in a lightbox. You can help by submit bugs and request new features at the plugin page at wordpress.org.', 'This plugin makes it very easy to create and manage photo albums. The albums are responsive and display in a lightbox. You can help by submit bugs and request new features at the plugin page at wordpress.org.', 'epa' );