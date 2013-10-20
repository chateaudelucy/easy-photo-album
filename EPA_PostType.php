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
 * This class handles all the posttype things, needed for the album posttype.
 *
 * @author TV productions
 * @package EasyPhotoAlbum
 */
class EPA_PostType {
	const SETTINGS_NAME = 'EasyPhotoAlbumSettings';
	const INPUT_NAME = 'EasyPhotoAlbums';
	const POSTTYPE_NAME = 'easy-photo-album';
	private $current_photos = array ();
	private $current_options = array ();
	private $current_post_id = - 1;

	/**
	 * Hooks all the functions on to some specific actions.
	 */
	public function __construct() {
		add_action ( 'init', array (
				&$this,
				'add_album_posttype'
		) );
		add_action ( 'init', array (
				&$this,
				'on_init'
		) );
		add_action ( 'admin_head', array (
				&$this,
				'admin_head'
		) );
		add_action ( 'save_post', array (
				&$this,
				'save_metadata'
		), 1, 2 );
		add_action ( 'wp_enqueue_scripts', array (
				&$this,
				'enqueue_scripts'
		) );
		// Make shure there is no html added to the content of the album
		if (remove_filter ( 'the_content', 'wpautop' )) {
			// filter existed and is removed
			add_filter ( 'the_content', array (
					&$this,
					'autop_fix'
			), 10 );
		}
		add_filter ( 'the_content', array (
				&$this,
				'replace_css_id_when_included'
		) );
		add_filter ( 'post_updated_messages', array (
				&$this,
				'album_messages'
		), 11, 1 );
		add_filter ( 'the_content_more_link', array (
				&$this,
				'special_more_link'
		), 10, 2 );
		add_filter ( 'the_excerpt', array (
				&$this,
				'special_excerpt'
		) );

		// Archive nav menu item not yet ready for use.
		/*
		add_filter ( 'nav_menu_items_' . self::POSTTYPE_NAME, array (
				&$this,
				'add_archive_nav_item'
		), 10, 3 );
		add_filter ( 'wp_setup_nav_menu_item', array (
				&$this,
				'setup_archive_item'
		) );
		// Fix for the archive menuitem
		add_action ( 'wp_ajax_add-menu-item', array (
				&$this,
				'change_menu_item_type_to_custom'
		), 0 );
		add_filter ( 'wp_nav_menu_objects', array (
				&$this,
				'update_archive_link_after_rewrite'
		) );*/
	}

	/**
	 * Functions to handle on init
	 */
	public function on_init() {
		if (EasyPhotoAlbum::get_instance ()->inmainloop) {
			add_action ( 'pre_get_posts', array (
					&$this,
					'add_to_main_loop'
			), 99 );
		}
	}

	/**
	 * Registers the <code>easy-photo-album</code> post type.
	 * The formal name is <code>Photo Album</code>
	 *
	 * @uses register_post_type
	 */
	public function add_album_posttype() {
		if (! post_type_exists ( self::POSTTYPE_NAME )) {
			register_post_type ( self::POSTTYPE_NAME, array (
					'labels' => array (
							'name' => _x ( 'Photo Albums', 'General Photo Albums name (multiple)', 'epa' ),
							'singular_name' => _x ( 'Photo Album', 'General singular Photo Albums name', 'epa' ),
							'add_new' => _x ( 'Add New', 'Add new menu item', 'epa' ),
							'add_new_item' => _x ( 'Add New Photo Album', 'Add new menu item extended', 'epa' ),
							'edit_item' => _x ( 'Edit Photo Album', 'Edit menu item', 'epa' ),
							'new_item' => _x ( 'New Photo Album', 'New menu item', 'epa' ),
							'view_item' => _x ( 'View Photo Album', 'View menu item', 'epa' ),
							'search_items' => _x ( 'Search Photo Album', 'Search menu item', 'epa' ),
							'not_found' => _x ( 'No Photo Albums found', 'No Photo Albums found message', 'epa' ),
							'not_found_in_trash' => _x ( 'No Photo Albums found in Trash', 'No Photo Albums found in trash message', 'epa' ),
							'parent_item_colon' => _x ( 'Parent Photo Album:', 'Parent Photo album label', 'epa' ),
							'menu_name' => _x ( 'Photo Albums', 'Menu name', 'epa' )
					),
					'hierarchical' => false,
					'description' => _x ( 'Post easy Photo Albums with Easy Photo Album', 'Posttype description', 'epa' ),
					'supports' => array (
							'title',
							'author',
							'revisions'
					),
					'public' => true,
					'show_ui' => true,
					'show_in_menu' => true,
					'menu_icon' => plugin_dir_url ( __FILE__ ) . 'css/img/epa-16.png',
					'menu_position' => 11,
					'show_in_nav_menus' => true,
					'publicly_queryable' => true,
					'exclude_from_search' => false,
					'has_archive' => true,
					'query_var' => true,
					'can_export' => true,
					'rewrite' => array (
							'slug' => _x ( 'albums', 'Rewrite slug', 'epa' )
					),
					'capability_type' => 'epa_album',
					'map_meta_cap' => true,
					'register_meta_box_cb' => array (
							&$this,
							'register_metabox'
					),
					'taxonomies' => array ()
			) );
		}
	}

	/**
	 * Registers the metabox for the posttype
	 */
	public function register_metabox() {
		add_meta_box ( 'easy-photo-album-display-options', __ ( "Album display options", 'epa' ), array (
				&$this,
				'display_options_metabox'
		), null, 'side', 'default' );
		add_meta_box ( 'easy-photo-album-images', __ ( "Album images", 'epa' ), array (
				&$this,
				'display_photo_metabox'
		), null, 'normal', 'high' );
	}

	/**
	 * This function changes the css ID of the album if the album is included in a post.
	 *
	 * @param string $content
	 * @return mixed
	 */
	public function replace_css_id_when_included($content) {
		static $count = 0;
		// Global $id is set in the shortcode code with setup_postdata();
		global $EPA_DOING_SHORTCODE, $id;
		if ($EPA_DOING_SHORTCODE) {
			$old_id = 'epa-album-' . $id;
			$new_id = $old_id . '-' . $this->get_current_post_id () . '-' . $count ++;
			$content = str_replace ( $old_id, $new_id, $content );
		}
		return $content;
	}

	/**
	 * Displays the content of the metabox for this posttype
	 */
	public function display_photo_metabox() {
		$this->display_no_js_waring ();

		$this->load_data ();
		$l = new EPA_List_Table ( get_current_screen (), $this->current_photos );
		echo "\n" . '<div class="hide-if-no-js">' . "\n";
		echo '<input type="button" name="' . self::INPUT_NAME . '[add_photo]" value="' . __ ( "Add one or more photo's", 'epa' ) . '" class="button"/>' . "\n";
		$l->display ();
		echo "\n" . '</div>' . "\n";
	}

	public function display_options_metabox() {
		$this->load_data ();
		?>
<p><?php _e('Override the default display options of the photo albums here.', 'epa')?></p>
<table class="form-table">
	<tr>
		<th scope="row">
<?php _e('Columns', 'epa');?>
</th>
		<td><input type="number"
			name="<?php echo self::INPUT_NAME;?>[option][columns]"
			class="small-text" step="1" min="1"
			value="<?php echo $this->current_options['columns'];?>" /></td>
	</tr>
	<tr>
		<th scope="col">
<?php _e('Number of images for excerpt', 'epa');?><br /> <span
			class="description"><?php _e('Set to 0 to show all images', 'epa');?></span>
		</th>
		<td><input type="number"
			name="<?php echo self::INPUT_NAME;?>[option][excerpt_number]"
			class="small-text" step="1" min="0"
			value="<?php echo $this->current_options['excerpt_number'];?>" /></td>
	</tr>
	<tr>
		<th scope="col" colspan="2"><input type="checkbox" value="true"
			name="<?php echo self::INPUT_NAME;?>[option][show_caption]"
			<?php checked($this->current_options['show_caption']);?>
			id="epa-option-show-title" /> <label for="epa-option-show-title"><?php _e('Show title', 'epa');?></label>
		</th>
	</tr>
	<tr>
		<th scope="col" colspan="2">
			<?php _e('Link image to', 'epa');?>

		<select name="<?php echo self::INPUT_NAME;?>[option][link_to]"
			style="float: right;">
				<option value="file"
					<?php selected($this->current_options['link_to'], 'file');?>><?php _e('The image file', 'epa');?></option>
				<option value="attachment"
					<?php selected($this->current_options['link_to'], 'attachment');?>><?php _e('The attachment page', 'epa');?></option>
				<option value="lightbox"
					<?php selected($this->current_options['link_to'], 'lightbox');?>><?php _e('Lightbox display', 'epa');?></option>
		</select>
		</th>
	</tr>
	<tr>
		<th scope="col" colspan="2">
			<?php _e('Image size', 'epa');?>

	<select name="<?php echo self::INPUT_NAME;?>[option][display_size]"
			style="float: right;">
			<?php
		// Using the same filter as in wp-admin/includes/media.php for the function
		// image_size_input_fields. Other plugins can use this filter to add their image size.
		$size_names = apply_filters ( 'image_size_names_choose', array (
				'thumbnail' => __ ( 'Thumbnail' ),
				'medium' => __ ( 'Medium' ),
				'large' => __ ( 'Large' ),
				'full' => __ ( 'Full Size' )
		) );
		foreach ( $size_names as $size => $displayname ) {
			$selected = selected ( $this->current_options ['display_size'], $size, false );
			echo <<<HTML
			<option value="{$size}" {$selected}>{$displayname}</option>
HTML;
		}
		?>
			</select>
		</th>
	</tr>
	<tr>
		<th scope="col" colspan="2"><input type="checkbox" value="true"
			name="<?php echo self::INPUT_NAME;?>[option][show_all_images_in_lightbox]"
			<?php checked($this->current_options['show_all_images_in_lightbox']);?>
			id="epa-option-show-all-images-in-lightbox" /> <label
			for="epa-option-show-all-images-in-lightbox"><?php _e('Show all images in lightbox', 'epa');?></label>
		</th>
	</tr>
</table>

<?php
	}

	/**
	 * Loads the photos from the database and stores them in the <code>$current_photos</code>
	 * variable.
	 */
	private function load_data() {
		if (empty ( $this->current_photos ) || empty ( $this->current_options )) {
			// get the post id
			$post_id = $this->get_current_post_id ();
			$data = get_post_meta ( $post_id, self::SETTINGS_NAME, true );
			if ($data && ! empty ( $data )) {
				if (array_key_exists ( 'options', $data )) {
					$this->current_options = $data ['options'];
					unset ( $data ['options'] );
				}
				$this->current_photos = $data;
				if (empty ( $this->current_photos )) {
					$this->current_photos = array ();
				}
			}
			// prase settings
			$this->current_options = wp_parse_args ( $this->current_options, EasyPhotoAlbum::get_instance ()->get_default_display_options () );
		}
	}

	/**
	 * Saves the photos to the database from the <code>current_photos</code>
	 * variable.
	 */
	private function save_data() {
		// sort the array by order
		ksort ( $this->current_photos );
		// Make shure the index starts at 0 and ends with MaxOrder
		$this->current_photos = array_values ( array_filter ( $this->current_photos ) );
		foreach ( $this->current_photos as $index => $v ) {
			$this->current_photos [$index]->order = $index;
		}
		$data = $this->current_photos;
		$data ['options'] = $this->current_options;
		update_post_meta ( $this->get_current_post_id (), self::SETTINGS_NAME, $data );
	}

	/**
	 * Saves the data from the metabox for this post type
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public function save_metadata($post_id, $post) {
		// return if the current user has not the edit_epa_album cap or (if the user isn't the
		// author) the edit_others_epa_albums cap
		if (! current_user_can ( 'edit_epa_album', $post_id ) || ($post->post_author != get_current_user_id () && ! current_user_can ( 'edit_others_epa_albums' ))) {
			return;
		}
		// It is from the right post type
		if (isset ( $_POST [self::INPUT_NAME] ) && is_array ( $_POST [self::INPUT_NAME] )) {
			// Make shure everyting is loaded
			$this->load_data ();

			// Validate and save the album specific settings
			$valid = $this->current_options;
			$input = $_POST [self::INPUT_NAME] ['option'];
			$valid ['columns'] = is_numeric ( $input ['columns'] ) && intval ( $input ['columns'] ) >= 1 ? intval ( $input ['columns'] ) : $valid ['columns'];
			$valid ['excerpt_number'] = is_numeric ( $input ['excerpt_number'] ) ? intval ( $input ['excerpt_number'] ) : $valid ['excerpt_number'];
			$valid ['show_caption'] = isset ( $input ['show_caption'] ) && $input ['show_caption'] == 'true' ? true : false;
			$valid ['link_to'] = in_array ( $input ['link_to'], array (
					'file',
					'attachment',
					'lightbox'
			) ) ? $input ['link_to'] : $valid ['link_to'];
			$valid ['display_size'] = in_array ( $input ['display_size'], get_intermediate_image_sizes () ) ? $input ['display_size'] : $valid ['display_size'];
			$valid ['show_all_images_in_lightbox'] = isset ( $input ['show_all_images_in_lightbox'] ) && $input ['show_all_images_in_lightbox'] == 'true' ? true : false;
			$this->current_options = $valid;

			// Empty the current photos var
			$this->current_photos = array ();

			// get the id's of the images
			$image_ids = isset ( $_POST [self::INPUT_NAME] ['id'] ) ? $_POST [self::INPUT_NAME] ['id'] : array ();
			// Bulk actions
			$action = (isset ( $_REQUEST ['epa-action'] ) || isset ( $_REQUEST ['epa-action2'] ) ? ($_REQUEST ['epa-action'] == '-1' ? $_REQUEST ['epa-action2'] : $_REQUEST ['epa-action']) : '');
			switch ($action) {
				case 'delete-photos' :
					$ids_to_delete = isset ( $_POST [self::INPUT_NAME] ['cb'] ) ? $_POST [self::INPUT_NAME] ['cb'] : array ();
					foreach ( $ids_to_delete as $id ) {
						$index = array_search ( $id, $image_ids );
						if (false !== $index) {
							unset ( $image_ids [$index] );
						}
					}
					break;
			}

			foreach ( $image_ids as $imageid ) {
				$img = new stdClass ();

				// update the fields
				wp_update_post ( array (
						'ID' => $imageid,
						'post_title' => $_POST [self::INPUT_NAME] [$imageid] ['title'],
						'post_content' => $_POST [self::INPUT_NAME] [$imageid] ['caption']
				) );

				// create object
				$img->id = $imageid;
				$img->order = $_POST [self::INPUT_NAME] [$imageid] ['order'];
				$img->title = $_POST [self::INPUT_NAME] [$imageid] ['title'];
				$img->caption = $_POST [self::INPUT_NAME] [$imageid] ['caption'];

				// In the data array
				$this->current_photos [$_POST [self::INPUT_NAME] [$imageid] ['order']] = $img;
			}

			// save it
			$this->save_data ();
			// Generate HTML and set it as the post content
			$renderer = new EPA_Renderer ( $this->get_current_post_id () );
			// unhook this function so it doesn't loop infinitely
			remove_action ( 'save_post', array (
					&$this,
					'save_metadata'
			), 1, 2 );
			// update the post, which calls save_post again
			wp_update_post ( array (
					'ID' => $post_id,
					'post_content' => $renderer->render ( false )
			) );
			// re-hook this function
			add_action ( 'save_post', array (
					&$this,
					'save_metadata'
			), 1, 2 );
		}
	}

	/**
	 * Adds the styles and the scripts at the admin side
	 */
	public function admin_head() {
		// Add icon
		if (get_current_screen ()->post_type == 'easy-photo-album') {
			// only on the necessary screens.
			$url = plugin_dir_url ( __FILE__ ) . 'css/img/epa-32.png';
			echo <<<CSS
<!-- Easy Photo Album CSS -->
<style type="text/css">
	.icon32-posts-easy-photo-album {
		background-image: url('$url') !important;
		background-position: left top !important;
	}
	.easy-photo-album-table tbody tr td.column-image img:hover {
		cursor: move;
	}
	.sortable-placeholder {
		height: 100px;
	}
</style>
<!-- End Easy Photo Album CSS -->

CSS;
			// Add media
			wp_enqueue_media ();
			$min = (defined ( 'WP_DEBUG' ) && WP_DEBUG ? '' : '.min');
			wp_enqueue_script ( 'easy-photo-album-page-js', plugins_url ( 'js/easy-photo-album-page' . $min . '.js', __FILE__ ), array (
					'jquery',
					'underscore'
			), EasyPhotoAlbum::$version, true );
		}
	}

	/**
	 * Enqueue scripts and styles for the front-end
	 */
	public function enqueue_scripts() {
		global $post;
		// if the post is a photo album OR we are in the main query (and option is set) /*OR it is
		// the
		// archive page*/ OR when the current page has a photo album shortcode
		if ((isset ( $post->post_type ) && self::POSTTYPE_NAME == $post->post_type) || (is_main_query () && EasyPhotoAlbum::get_instance ()->inmainloop) /*|| ($post->ID == EasyPhotoAlbum::get_instance ()->archivepageid)*/ || has_shortcode ( $post->post_content, 'epa-album' )) {
			// it is a photo album
			wp_enqueue_style ( 'epa-template', plugins_url ( 'css/easy-photo-album-template.css', __FILE__ ), array (), EasyPhotoAlbum::$version, 'all' );

			if (EasyPhotoAlbum::get_instance ()->linkto == 'lightbox') {
				wp_enqueue_script ( 'lightbox2-js', plugins_url ( 'js/lightbox.js', __FILE__ ), array (
						'jquery'
				), '2.6', true );
				wp_localize_script ( 'lightbox2-js', 'lightboxSettings', array (
						'wrapAround' => EasyPhotoAlbum::get_instance ()->wraparound,
						'showAlbumLabel' => EasyPhotoAlbum::get_instance ()->showalbumlabel,
						'albumLabel' => EasyPhotoAlbum::get_instance ()->albumlabel,
						'scaleLightbox' => EasyPhotoAlbum::get_instance()->scalelightbox
				) );
				wp_enqueue_style ( 'lightbox2-css', plugins_url ( 'css/lightbox.css', __FILE__ ), array (), '2.6' );
			}
		}
	}

	/**
	 * Make shure that the content of the album isn't changed
	 *
	 * @param string $content
	 * @return string
	 */
	public function autop_fix($content) {
		if (get_post_type () == self::POSTTYPE_NAME) {
			// no operation needed
			return $content;
		} else {
			return wpautop ( $content );
		}
	}

	/**
	 * Set the right localized messages for this post type.
	 *
	 * @param array $messages
	 * @return array
	 */
	public function album_messages($messages) {
		global $post, $post_ID;

		$messages [self::POSTTYPE_NAME] = array (
				0 => '', // Unused. Messages start at index 1.
				1 => sprintf ( __ ( 'Photo Album updated. <a href="%s">View Album</a>', 'epa' ), esc_url ( get_permalink ( $post_ID ) ) ),
				2 => __ ( 'Custom field updated.' ),
				3 => __ ( 'Custom field deleted.' ),
				4 => __ ( 'Photo Album updated.', 'epa' ),
				/* translators: %s: date and time of the revision */
				5 => isset ( $_GET ['revision'] ) ? sprintf ( __ ( 'Photo Album restored to revision from %s', 'epa' ), wp_post_revision_title ( ( int ) $_GET ['revision'], false ) ) : false,
				6 => sprintf ( __ ( 'Photo Album published. <a href="%s">View Album</a>', 'epa' ), esc_url ( get_permalink ( $post_ID ) ) ),
				7 => __ ( 'Photo Album saved.', 'epa' ),
				8 => sprintf ( __ ( 'Photo Album submitted. <a target="_blank" href="%s">Preview Album</a>', 'epa' ), esc_url ( add_query_arg ( 'preview', 'true', get_permalink ( $post_ID ) ) ) ),
				9 => sprintf ( __ ( 'Photo Album scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Album</a>', 'epa' ),
						// translators: Publish box date format, see http://php.net/date
						date_i18n ( __ ( 'M j, Y @ G:i' ), strtotime ( $post->post_date ) ), esc_url ( get_permalink ( $post_ID ) ) ),
				10 => sprintf ( __ ( 'Photo Album draft updated. <a target="_blank" href="%s">Preview Album</a>', 'epa' ), esc_url ( add_query_arg ( 'preview', 'true', get_permalink ( $post_ID ) ) ) )
		);

		return $messages;
	}

	/**
	 * Adds the closing <code>&lt;/ul&gt;</code> tag for the albums to the more link
	 *
	 * @param string $more_link
	 * @return string
	 */
	public function special_more_link($more_link, $more_text) {
		// Using the global var $id, cause setup_postdata() doesn't set $post;
		global $id;

		if (get_post_type ( $id ) == self::POSTTYPE_NAME) {
			global $EPA_DOING_SHORTCODE;
			if ($EPA_DOING_SHORTCODE == true) {
				return '</li></ul><!-- epa more -->' . apply_filters ( 'epa_album_more_link', ' <a href="' . get_permalink ( $id ) . "#more-{$id}\" class=\"more-link\">$more_text</a>", $more_text );
			}
			return '</li></ul><!-- epa more -->' . apply_filters ( 'epa_album_more_link', $more_link, $more_text );
		} else {
			return $more_link;
		}
	}

	public function special_excerpt($excerpt) {
		// Using the global var $id, cause setup_postdata() doesn't set $post;
		global $id;
		if (get_post_type ( $id ) == self::POSTTYPE_NAME) {
			return get_the_content ( apply_filters ( 'epa_excerpt_more_link_text', __ ( "More photo's...", 'epa' ) ) );
		} else {
			return $excerpt;
		}
	}

	/**
	 * Adds the post type to the main loop (i.e.
	 * blogpage)
	 *
	 * @param WP_Query $query
	 */
	public function add_to_main_loop($query) {
		// is_home() checks if the current query is for the BLOG homepage (not the homepage, which
		// can be checked by is_front_page). So only then the album post type is added.
		if (! is_admin () && $query->is_main_query () && $query->is_home ()) {
			// Other plugins can add post types the same way, first get the current value
			// ($query->get('post_type')) and then add the post types needed.
			$query->set ( 'post_type', apply_filters ( 'epa_main_loop_post_types', array_merge ( ( array ) $query->get ( 'post_type' ), array (
					'post',
					self::POSTTYPE_NAME
			) ) ) );
		}
		return $query;
	}

	public function display_archive($content) {
		if ($this->get_current_post_id () == EasyPhotoAlbum::get_instance ()->archivepageid) {
			// this is the archive page id
		} else {
			return $content;
		}
	}

	public function add_archive_nav_item($posts, $args, $post_type) {
		$archive_item_id = get_option ( 'easy_photo_album_archive_nav_item_id' );
		if ($archive_item_id == false) {
			$archive_item_data = array (
					'menu-item-title' => esc_attr ( __ ( 'Photo Album Archive', 'epa' ) ),
					'menu-item-type' => 'post_type_archive',
					'menu-item-object' => esc_attr ( self::POSTTYPE_NAME ),
					'menu-item-url' => get_post_type_archive_link ( self::POSTTYPE_NAME )
			);
			$archive_item_id = wp_update_nav_menu_item ( 0, 0, $archive_item_data );
			if (is_wp_error ( $archive_item_id ))
				return $posts;

			update_option ( 'easy_photo_album_archive_nav_item_id', $archive_item_id );
		}

		$archive_item_object = get_post ( $archive_item_id );
		if (! empty ( $archive_item_object->ID )) {
			$archive_item_object = wp_setup_nav_menu_item ( $archive_item_object );
			$archive_item_object->label = $archive_item_object->title;
		}

		$posts [] = $archive_item_object;
		return $posts;
		// wp_setup_nav_menu_item()
	}

	/**
	 * Assign menu item the appropriate url and ID
	 *
	 * @param object $menu_item
	 * @return object $menu_item
	 */
	public function setup_archive_item($menu_item) {
		if ($menu_item->type !== 'post_type_archive' || $menu_item->ID != get_option ( 'easy_photo_album_archive_nav_item_id' ))
			return $menu_item;

		$post_type = $menu_item->object;
		$menu_item->url = get_post_type_archive_link ( $post_type );
		$menu_item->object_id = $menu_item->ID;

		return $menu_item;
	}

	/**
	 * Fix notices in admin-ajax.php (wp_ajax_add_menu_item)
	 * by changing the menu-item-type to custom (in place of post_type_archive)
	 */
	public function change_menu_item_type_to_custom() {
		check_ajax_referer ( 'add-menu_item', 'menu-settings-column-nonce' );

		if (! current_user_can ( 'edit_theme_options' ))
			wp_die ( - 1 );

		$id = get_option ( 'easy_photo_album_archive_nav_item_id' );

		// If the menu item is our archive one, fix the notices in ajax-actions.php
		if (isset ( $_POST ['menu-item'] [$id] )) {
			$_POST ['menu-item'] [$id] ['menu-item-type'] = 'custom';
		}
	}

	public function update_archive_link_after_rewrite($items) {
		foreach ( $items as $item ) {
			if (($item->ID == get_option ( 'easy_photo_album_archive_nav_item_id' ) + 1) && ($item->url != get_post_type_archive_link ( self::POSTTYPE_NAME ))) {
				$item->url = get_post_type_archive_link ( self::POSTTYPE_NAME );
			}
		}
		return $items;
	}

	/**
	 * Displays the warning if javascript is disabled
	 */
	private function display_no_js_waring() {
		$message = __ ( "Javascript is disabled. Please enable Javascript and reload the page before you continue.", 'epa' );
		echo <<<NO_JS
		<noscript>
			<div class="error"><p>$message</p></div>
		</noscript>
NO_JS;
	}

	/**
	 * Returns the current post id
	 *
	 * @return int id
	 */
	private function get_current_post_id() {
		global $post;
		return ($this->current_post_id == - 1 ? (is_object ( $post ) && ! empty ( $post->ID ) ? $post->ID : (empty ( $_REQUEST ['post'] ) ? - 1 : $_REQUEST ['post'])) : $this->current_post_id);
	}
}

?>