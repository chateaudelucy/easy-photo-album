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

// This file is based on wp-includes/js/tinymce/langs/wp-langs.php

if (! defined ( 'ABSPATH' ))
	exit ();

if (! class_exists ( '_WP_Editors' ))
	require (ABSPATH . WPINC . '/class-wp-editor.php');

function easy_photo_album_insert_dialog_translation() {
	$strings = array (
			'dlg_title' => __ ( 'Insert a Photo Album', 'epa' )
	);
	$locale = _WP_Editors::$mce_locale;
	$translated = 'tinyMCE.addI18n("' . $locale . '.epa", ' . json_encode ( $strings ) . ");\n";

	return $translated;
}

$strings = easy_photo_album_insert_dialog_translation ();