<?php
/*
 * Plugin Name: Easy Photo Album
 * Version: 1.0.5
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
	public static $version = '1.0.5';

	private function __construct() {
		$this->options_init ();
		$this->post_type = new EPA_PostType ();
		if (is_admin())
			$this->admin = new EPA_Admin();

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
		flush_rewrite_rules ();
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
				'showtitlewiththumbnail' => true,
				'numimageswhennotsingle' => 3
		);
		$this->options = get_option ( 'EasyPhotoAlbum', $this->options );
	}

	/**
	 * Returns the options.
	 *
	 * @internal THIS FUNCTION SHOULD BE CALLED FROM EPA_Admin ONLY!
	 *
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}
}

// Create a new instance: startup plugin
EasyPhotoAlbum::get_instance ();