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
			'dlg_title' => __ ( 'Insert a Photo Album', 'epa' ),
			'select_album' => __('Select an album to insert', 'epa'),
			'show_title' => __('Show the title', 'epa'),
			'display_label' => _x('Display album', 'Like: Display album full OR Display album excerpt', 'epa'),
			'excerpt' => _x('Excerpt', 'Display album excerpt', 'epa'),
			'full' => _x('Full', 'Display album full', 'epa'),
			'insert' => _x('Insert', 'button text', 'epa'),
			'cancel' => _x('Cancel', 'button text', 'epa'),
			'title_loading' => _x('Loading...', 'Loading dialog title', 'epa'),
			'loading' => __('Loading albums ...', 'epa'),
			'nonce' => wp_create_nonce('epa_insert_dlg'),
			//'spinner' => admin_url('images/wpspin_light.gif'),
	);
	$locale = _WP_Editors::$mce_locale;
	$translated = 'tinyMCE.addI18n("' . $locale . '.epa", ' . json_encode ( $strings ) . ");\n";

	return $translated;
}

$strings = easy_photo_album_insert_dialog_translation ();