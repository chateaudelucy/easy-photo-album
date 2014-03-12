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
 * This class adds a button to the TinyMCE editor and handles the shorcode that it generates to
 * insert an album in a post.
 *
 * @author TV Productions
 * @package EasyPhotoAlbum
 *
 */
class EPA_Insert_Album {

	public function __construct() {
		add_action ( 'init', array (
				$this,
				'setup'
		) );
		add_action ( 'after_wp_tiny_mce', array (
				$this,
				'epa_insert_html'
		) );

		add_filter ( 'mce_external_languages', array (
				$this,
				'add_locale'
		) );

		add_shortcode ( 'epa-album', array (
				$this,
				'handle_shortcode'
		) );

		if (is_admin ()) {
			add_action ( 'wp_ajax_epa_get_albums', array (
					$this,
					'ajax_return_albums'
			) );
		}
	}

	public function setup() {
		// Use only if the current user is enabled to.
		if (! (current_user_can ( 'edit_epa_albums' )) || ! get_user_option ( 'rich_editing' ))
			return;

		add_filter ( 'mce_buttons', array (
				$this,
				'add_button_to_editor'
		) );
		add_filter ( 'mce_external_plugins', array (
				$this,
				'add_plugin'
		) );
	}

	/**
	 * Returns the album for the shortcode
	 *
	 * @param array $atts
	 * @return string
	 */
	public function handle_shortcode($atts) {
		$atts = shortcode_atts ( array (
				'id' => 0,
				'show_title' => 'true',
				'display' => 'excerpt'
		), $atts, 'epa-album' );

		// Is the curren album published or...?
		if (! in_array ( get_post_status ( $atts ['id'] ), apply_filters ( 'epa_include_album_status', array (
				'publish'
		) ) )) {
			// if the user is logged in and so forth..
			if (current_user_can ( get_post_type_object ( EPA_PostType::POSTTYPE_NAME )->cap->publish_posts ))
				return "<!-- You're not allowed to view the photo album. Is it a draft..? -->";
			else
				return;
		}

		global $EPA_DOING_SHORTCODE;
		/**
		 * This flag variable is set to true, when the current album is rendered from the shortcode.
		 *
		 * @var bool
		 */
		$EPA_DOING_SHORTCODE = true;

		$content = '';
		$album = get_post ( $atts ['id'] );
		if ($album != null) {
			// NOTE: Need we to override the display mode of the album if this isn't a single page?

			setup_postdata ( $album );
			switch ($atts ['display']) {
				case 'full' :
					if ($atts ['show_title'] == 'true') {
						$tag = apply_filters ( 'epa_include_album_title_tag', 'h2' );
						$content .= "<$tag>" . get_the_title ( $atts ['id'] ) . "</$tag>";
					}
					ob_start ();
					// Fix to force the_content to output the whole album
					// See:
					// http://codex.wordpress.org/Function_Reference/the_content#Overriding_Archive.2FSingle_Page_Behavior
					global $more;
					$more = 1;
					the_content ();
					$content .= ob_get_contents ();
					ob_end_clean ();
					break;
				default :
				case 'excerpt' :
					if ($atts ['show_title'] == 'true') {
						$tag = apply_filters ( 'epa_include_album_title_tag', 'h2' );
						$content .= "<$tag>" . '<a href="' . get_permalink ( $album ) . '">' . get_the_title ( $atts ['id'] ) . "</a></$tag>";
					}
					ob_start ();
					// Fix to force the_content to output the album untill the <!--more--> tag
					// See:
					// http://codex.wordpress.org/Function_Reference/the_content#Overriding_Archive.2FSingle_Page_Behavior
					global $more;
					$more = 0;
					the_content ( __ ( "View more photo's", 'epa' ) . ' &rarr;' );
					$content .= ob_get_contents ();
					ob_end_clean ();
					break;
			}
			wp_reset_postdata ();
		}
		$EPA_DOING_SHORTCODE = false;
		return $content;
	}

	/**
	 * Returns the albums to the insert dialog
	 */
	public function ajax_return_albums() {
		// Fix to prevent PHP Notice
		if (! isset ( $_REQUEST ['_wpnonce'] ))
			$_REQUEST ['_wpnonce'] = '';

		check_ajax_referer ( 'epa_insert_dlg' );
		$albums = get_posts ( array (
				'post_type' => EPA_PostType::POSTTYPE_NAME,
				'post_status' => 'publish'
		) );
		$result = array ();
		foreach ( $albums as $album ) {
			$result [] = array (
					'id' => $album->ID,
					'title' => $album->post_title
			);
		}
		if (empty ( $result ) || empty ( $albums ))
			die ( 0 );

		die ( json_encode ( $result ) );
	}

	/**
	 * Runs on mce_buttons filter.
	 *
	 * @param array $buttons
	 * @return array
	 */
	public function add_button_to_editor($buttons) {
		// Add the button to the end
		$buttons [] = 'epa_insert';
		return $buttons;
	}

	/**
	 * Adds the plugin to the editor
	 *
	 * @param array $plugins
	 * @return s array
	 */
	public function add_plugin($plugins) {
		$plugins ['EasyPhotoAlbum'] = plugin_dir_url ( __FILE__ ) . 'js/tinymce/editor_plugin' . (WP_DEBUG ? '_src' : '') . '.js';
		return $plugins;
	}

	/**
	 * Adds the translated strings for the editor plugin to the editor
	 *
	 * @param array $locales
	 */
	public function add_locale($locales) {
		$locales ['EasyPhotoAlbum'] = plugin_dir_path ( __FILE__ ) . 'js/tinymce/langs.php';
		return $locales;
	}

	/**
	 * Add the HTML for the WP-Dialog for the insert button.
	 */
	public function epa_insert_html() {
		// There is a tinymce editor loading....
		wp_enqueue_style ( 'epa-insert-css', plugins_url ( 'js/tinymce/styles.css', __FILE__ ), array (), EasyPhotoAlbum::$version );
		wp_enqueue_script ( 'epa-insert-js', plugins_url ( 'js/tinymce/epa-insert.js', __FILE__ ), array (
				'jquery'
		), EasyPhotoAlbum::$version, true );
		wp_localize_script ( 'epa-insert-js', 'epaInsertL10n', array (
				'not_found' => __ ( 'No albums found', 'epa' ),
				'wp3_8' => version_compare ( $GLOBALS ['wp_version'], '3.8', '>=' )
		) );
		wp_print_styles ( 'epa-insert-css' );
		wp_print_scripts ( 'epa-insert-js' );
		?>
<div style="display: none">
	<form id="epa-insert" tabindex="-1">
		<?php wp_nonce_field( 'epa_insert_dlg', '_ajax_epa_nonce', false ); ?>
		<p class="howto"><?php _e('Select an album to insert.', 'epa')?></p>
		<div id="albums" class="query-results">
			<ul></ul>
			<div class="river-waiting">
				<span class="spinner"></span>
			</div>
		</div>
		<div class="insert-options">
			<p class="howto"><?php _e('How should the album be displayed?', 'epa');?></p>
			<div>
				<label><input type="checkbox" id="epa-title" name="epa-title" /><?php _e('Show album title', 'epa');?></label>
			</div>
			<div>
				<label><span><?php _ex('Display:', 'Like Display: excerpt', 'epa');?></span>
					<input type="radio" name="epa-disp" value="excerpt" class="first"
					checked="checked" /><?php _e('Excerpt', 'epa');?></label><br /> <label>
					<input type="radio" name="epa-disp" value="full" /><?php _e('Full album', 'epa');?>
			</label>
			</div>
		</div>
		<div class="submitbox">
			<div id="wp-link-update">
				<input id="epa-insert-submit" class="button-primary" type="submit"
					name="epa-insert-submit"
					value="<?php _e('Insert Album', 'epa'); ?>">
			</div>
			<div id="epa-insert-cancel">
				<a class="submitdelete deletion" href="#"><?php _e('Cancel', 'epa')?></a>
			</div>
		</div>
	</form>
</div>
<?php
	}

	/**
	 * Checks if the current wp installation uses TinyMCE 4.x or higher or not.
	 *
	 * @return bool
	 * @since 1.3
	 */
	public function is_tinymce_4() {
		return version_compare ( $GLOBALS ['tinymce_version'], '4000-00000000', '>=' );
	}
}