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
 * This class handles the upgrades of the plugin for the versions.
 * To add a version upgrade, add first the method check_{version} that will return true when it
 * needs to be
 * upgraded. Second, add a method called upgrade_{version} that will perform the upgrade. It should
 * return true on succes.
 *
 * @author TV productions
 * @package EasyPhotoAlbum
 * @since 1.3
 */
class EPA_Upgrade {
	/**
	 * This class
	 *
	 * @var EPA_Upgrade
	 */
	private static $instance = null;
	/**
	 * The methods that need to run to upgrade.
	 *
	 * @var Array
	 */
	private $upgrade_methods = array ();
	/**
	 * The methods that check for upgrades.
	 *
	 * @var Array
	 */
	private $check_methods = array ();

	/**
	 * Performs the needed task to upgrade the settings
	 *
	 * @since 1.3
	 *
	 * @return <boolean, int>
	 *         Returns false on failure, -1 when there was no upgrade required and true on succes.
	 */
	public static function do_upgrade() {
		if (version_compare ( get_option ( 'EasyPhotoAlbumVersion', '1.0' ), EasyPhotoAlbum::$version, '<' )) {
			if (! self::$instance instanceof self)
				self::$instance = new self ();

			if (empty ( self::$instance->upgrade_methods )) {
				// set current version number
				update_option ( 'EasyPhotoAlbumVersion', EasyPhotoAlbum::$version );
				self::$instance->log ( 'No upgrades available.' );
				return - 1;
			}

			$run = false;
			foreach ( self::$instance->upgrade_methods as $method ) {
				$run = call_user_func ( array (
						self::$instance,
						$method
				), EasyPhotoAlbum::$version );
				self::$instance->log ( 'Upgrade "' . $method . '" executed. Result: ' . print_r ( $run, true ) );
			}

			update_option ( 'EasyPhotoAlbumVersion', EasyPhotoAlbum::$version );
			return $run;
		}
		return - 1;
	}

	/**
	 * Sets everything up to upgrade if needed
	 *
	 * @since 1.3
	 */
	private function __construct() {
		// is an upgrade needed?
		if (false !== get_option ( 'EasyPhotoAlbum', false )) {
			// Populate the checks
			$methods = get_class_methods ( __CLASS__ );
			$this->check_methods = array ();
			foreach ( $methods as $method ) {
				if (strpos ( $method, 'check_' ) === 0)
					$this->check_methods [] = $method;
			}
			natcasesort ( $this->check_methods );

			// Run checks
			foreach ( $this->check_methods as $index => $check ) {
				// run check
				$result = call_user_func ( array (
						$this,
						$check
				) );
				$this->log ( 'Check "' . $check . '" executed. Result: ' . print_r ( $result, true ) );
				if (true == $result) {
					// add all the version upgrades from here.
					$remained_checks = array_slice ( $this->check_methods, $index );
					foreach ( $remained_checks as $r_check ) {
						$this->upgrade_methods [] = 'upgrade_' . str_replace ( 'check_', '', $r_check );
					}
					natcasesort ( $this->upgrade_methods );
					break;
				}
			}
		}
	}

	/**
	 * If the old version of epa is =< 1.2
	 *
	 * @return boolean
	 * @since 1.3
	 */
	private function check_1_2_and_lower() {
		return get_option ( 'EasyPhotoAlbumVersion', false ) == false;
	}

	/**
	 * Upgrades to 1.3
	 *
	 * @param string $curr_version
	 * @return boolean
	 * @since 1.3
	 */
	private function upgrade_1_2_and_lower($curr_version) {
		// the upgrader already checked if the options are available, so use array as default
		$old_options = get_option ( 'EasyPhotoAlbum', array () );
		$new_options = array ();

		$new_options ['viewmode'] = $old_options ['linkto'];
		$new_options ['displaycolumns'] = $old_options ['displaycolumns'];
		$new_options ['showcaption'] = $old_options ['showcaption'];
		$new_options ['displaysize'] = $old_options ['displaysize'];
		$new_options ['excerptnumber'] = $old_options ['numimageswhennotsingle'];
		// lightbox settings
		$new_options ['showimagenumber'] = $old_options ['showalbumlabel'];
		$new_options ['imagenumberformat'] = $old_options ['albumlabel'];
		$new_options ['wraparound'] = $old_options ['wraparound'];
		$new_options ['scalelightbox'] = $old_options ['scalelightbox'];
		// Miscellaneous settings
		$new_options ['showtitleintable'] = $old_options ['showtitleintable'];
		$new_options ['inmainloop'] = $old_options ['inmainloop'];

		return update_option ( 'EasyPhotoAlbum', $new_options );
	}

	/**
	 * Logs the message to a log file.
	 * Logs only if <code>WP_DEBUG</code> is set.
	 *
	 * @param string $message
	 * @since 1.3
	 */
	private function log($message) {
		if (defined ( 'WP_DEBUG' )) {
			$log = date ( '[d/M/Y H:i:s] ' ) . $message . PHP_EOL;
			$file = dirname ( __FILE__ ) . '/epa-upgrade.log';
			if (! file_exists ( $file ))
				file_put_contents ( $file, $log, LOCK_EX );
			else
				file_put_contents ( $file, $log, FILE_APPEND | LOCK_EX );
		}
	}
}