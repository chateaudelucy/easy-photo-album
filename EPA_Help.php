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
class EPA_Help {

	public function __construct() {
		add_action ( 'admin_head', array (
				&$this,
				'add_help'
		) );
	}

	public function add_help() {
		// settings help
		$this->add_tab ( 'settings_page_epa-settings', 'epa-media-options', __ ( "Easy Photo Album Options", 'epa' ), sprintf ( '<p>%1$s</p><p><b>%2$s</b><br/>%3$s</p><p>%4$s <a href="http://wordpress.org/support/plugin/easy-photo-album">%5$s</a>.</p>', __ ( "On this page can you set the default options for the photo albums. These default options can be overriden by each album in the Album display options meta box", 'epa' ), __('Overriding options', 'epa'), __('The album specific options can be overriden by the default options. Check the override checkbox and press Save Changes.', 'epa'), __('For more help, please visit the','epa'), __('support forums', 'epa') ) );
		// photo album edit screen
		//$this->add_tab(EPA_PostType::POSTTYPE_NAME, 'epa-edit-help', __($text), $content);
	}

	private function add_tab($screen_id, $tab_id, $title, $content) {
		$screen = get_current_screen ();

		if ($screen->id != $screen_id)
			return;

		$screen->add_help_tab ( array (
				'id' => $tab_id,
				'title' => $title,
				'content' => $content
		) );
	}
}