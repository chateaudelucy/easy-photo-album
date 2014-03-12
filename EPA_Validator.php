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
 * Class that holds some validation functions.
 * Mostly for EPA settings.
 *
 * @author TV productions
 * @package EasyPhotoAlbum
 * @since 1.3
 *
 */
class EPA_Validator {

	/**
	 * Returns the nummeric value if there is any in $var.
	 * Else $default will be returned.
	 *
	 * @param mixed $var
	 * @param number $min
	 * @param number $default
	 * @return number
	 *
	 * @since 1.3
	 */
	public function validate_nummeric($var, $min = 0, $default = 0) {
		if (is_numeric ( $var ) && intval ( $var ) >= $min)
			return intval ( $var );
		return $default;
	}

	/**
	 * Checks for a key in the array and if it exists, it should contain the value
	 * <code>'true'</code>
	 *
	 * @param array $array
	 * @param mixed $key
	 * @return boolean
	 *
	 * @since 1.3
	 */
	public function validate_checkbox(&$array, $key) {
		if (isset ( $array [$key] )) {
			return $array [$key] == 'true';
		}
		return false;
	}

	/**
	 * Returns the value of the key of an array or $default if not set.
	 *
	 * @param array $array
	 * @param mixed $key
	 * @param mixed $default
	 * @return unknown $default when the key isn't set.
	 *
	 * @since 1.3
	 */
	public function get_if_set(&$array, $key, $default = '') {
		if (isset ( $array [$key] )) {
			return $array [$key];
		}
		return $default;
	}
}