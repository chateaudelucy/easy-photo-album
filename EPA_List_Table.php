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
require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

/**
 * The class for the display of the images at the back-end.
 *
 * @author TV productions
 * @package EasyPhotoAlbum
 */
class EPA_List_Table extends WP_List_Table {
	var $_column_headers = array ();

	/**
	 * Sets up the list table class
	 *
	 * @param WP_Screen $screen
	 */
	function __construct($screen, $items) {
		parent::__construct ( array (
				'plural' => __ ( 'Images', 'epa' ),
				'singular' => __ ( 'Image', 'epa' ),
				'ajax' => false,
				'screen' => $screen
		) );
		add_action ( 'admin_footer', array (
				$this,
				'add_js_vars'
		) );

		$this->items = $items;
		$this->prepare_items ();
	}

	/* (non-PHPdoc)
	 * @see WP_List_Table::prepare_items()
	 */
	function prepare_items() {
		// no pagination
		$this->set_pagination_args ( array (
				'total_items' => count ( $this->items ),
				'total_pages' => 1,
				'per_page' => count ( $this->items )
		) );

		$hidden_columns = array ();
		if (! EasyPhotoAlbum::get_instance ()->showtitleintable)
			$hidden_columns = array_merge ( $hidden_columns, array (
					'title'
			) );
		$this->_column_headers = array (
				$this->get_columns (), // columns
				$hidden_columns,
				$this->get_sortable_columns ()
		);
	}

	/* (non-PHPdoc)
	 * @see WP_List_Table::no_items()
	 */
	function no_items() {
		return __ ( "No photos added yet.", 'epa' );
	}

	/* (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns() {
		return array (
				'cb' => __ ( 'Select All' ),
				'image' => __ ( 'Image' ),
				'caption' => __ ( 'Caption' ),
				'title' => __ ( 'Title' )
		);
	}

	/**
	 * Renders the contents of the image column for each item.
	 *
	 * @param stdClass $item
	 */
	function column_image($item) {
		$w = '150';
		$h = '150';
		if ($item->id != '<%= id %>') {
			$img_url = wp_get_attachment_image_src ( $item->id, 'thumbnail' );
			if (isset ( $img_url [0] )) {
				$w = $img_url [1];
				$h = $img_url [2];
				$img_url = $img_url [0];
			} else {
				$img_url = plugin_dir_url ( __FILE__ ) . 'css/img/image_not_found.jpg';
			}
		} else {
			$img_url = '<%= imgurl %>';
		}

		echo <<<IMG

		<img src="{$img_url}" widht="{$w}" height="{$h}" class="epa-image"/>

IMG;
		// Showing the right actions is done with javascript.
		$actions = array (
				'delete' => '<a href="#">' . __ ( 'Delete' ) . '</a>',
				'order_up' => '<a href="#" class="epa-move-up">' . __ ( 'Up', 'epa' ) . '</a>',
				'order_down' => '<a href="#" class="epa-move-down">' . __ ( 'Down', 'epa' ) . '</a>'
		);
		echo $this->row_actions ( $actions );
	}

	/**
	 * Renders the contents of the checkbox column for each item.
	 *
	 * @param stdClass $item
	 */
	function column_cb($item) {
		echo '<input type="checkbox" name="' . EPA_PostType::INPUT_NAME . '[cb][]" value="' . $item->id . '"/>';
	}

	/**
	 * Renders the contents of the caption column for each item.
	 *
	 * @param stdClass $item
	 */
	function column_caption($item) {
		echo '<textarea style="width: 100%; height: 100%" id="' . EPA_PostType::INPUT_NAME . '-caption-' . $item->id . '">' . $item->caption . '</textarea>';
	}

	/**
	 * Renders the contents of the title column for each item.
	 *
	 * @param stdClass $item
	 */
	function column_title($item) {
		echo '<input type="text" style="width: 100%;" id="' . EPA_PostType::INPUT_NAME . '-title-' . $item->id . '" value="' . $item->title . '"/>';
	}

	/* (non-PHPdoc)
	 * @see WP_List_Table::get_bulk_actions()
	 */
	function get_bulk_actions() {
		return array (
				'delete-photos' => __ ( 'Delete' )
		);
	}

	/* (non-PHPdoc)
	 * @see WP_List_Table::bulk_actions()
	 */
	function bulk_actions() {
		/* * * * *
		 * This is a fix: the default name of the select is action (or action2),
		 * so this function changed it to epa-action (or epa-action2).
		 * The name action collides with the action of the current post.
		 * * * * */
		if (is_null ( $this->_actions )) {
			$no_new_actions = $this->_actions = $this->get_bulk_actions ();
			// This filter can currently only be used to remove actions.
			$this->_actions = apply_filters ( 'bulk_actions-' . $this->screen->id, $this->_actions );
			$this->_actions = array_intersect_assoc ( $this->_actions, $no_new_actions );
			$two = '';
		} else {
			$two = '2';
		}

		if (empty ( $this->_actions ))
			return;

		echo "<select name='epa-action$two'>\n";
		echo "<option value='-1' selected='selected'>" . __ ( 'Bulk Actions' ) . "</option>\n";

		foreach ( $this->_actions as $name => $title ) {
			$class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

			echo "\t<option value='$name'$class>$title</option>\n";
		}

		echo "</select>\n";

		submit_button ( __ ( 'Apply' ), 'action', false, false, array (
				'id' => "doaction$two"
		) );
		echo "\n";
	}

	/* (non-PHPdoc)
	 * @see WP_List_Table::display_tablenav()
	 */
	function display_tablenav($which) {
		/* * * * *
		 * Removed the nonce field, the page is already checked by the admin post.php
		 *
		 * Old code:
		 *
		 *	if ( 'top' == $which )
		 *	wp_nonce_field( 'bulk-' . $this->_args['plural'], 'epa_nonce' );
		 *
		 * * * * */
		?>
<div class="tablenav <?php echo esc_attr( $which ); ?>">

	<div class="alignleft actions">
				<?php $this->bulk_actions(); ?>
			</div>
	<?php
		$this->extra_tablenav ( $which );
		$this->pagination ( $which );
		?>

			<br class="clear" />
</div>
<?php
	}

	/* (non-PHPdoc)
	 * @see WP_List_Table::get_table_classes()
	 */
	function get_table_classes() {
		$classes = parent::get_table_classes ();
		$classes [] = 'hide-if-no-js';
		$classes [] = 'easy-photo-album-table';
		return $classes;
	}

	/* (non-PHPdoc)
	 * @see WP_List_Table::single_row()
	 */
	function single_row($item) {
		static $row_class = '';
		$row_class = ($row_class == '' ? ' class="alternate"' : '');

		// the only difference is to add the data attributes to the tr element.
		echo '<tr' . $row_class . ' data-epa-id="' . $item->id . '" data-epa-order="' . $item->order . '">';
		$this->single_row_columns ( $item );
		echo '</tr>';
	}

	/**
	 * Add some javascript so that some data can be accessed by javascript.
	 */
	function add_js_vars() {
		// Make the row template {
		$dummy = new stdClass ();
		foreach ( array (
				'id',
				'order',
				'title',
				'caption'
		) as $prop ) {
			$dummy->$prop = "<%= $prop %>";
		}
		ob_start ();
		$this->single_row ( $dummy );
		$rowtemplate = ob_get_contents ();
		ob_clean ();
		// } end row template

		$namespaced_args = array (
				'settingName' => EPA_PostType::INPUT_NAME,
				'maxOrder' => count ( $this->items ) - 1,
				'lang' => array (
						'mediatitle' => __ ( 'Choose image(s)', 'epa' ),
						'mediabutton' => __ ( 'Select image(s)', 'epa' ),
						'deleteconfirm' => __ ( "The photo '{0}' will be removed. Are you shure?", 'epa' ),
						'photo' => _x ( 'photo', "like 1 photo", 'epa' ),
						'photos' => _x ( "photos", "like 2 photos", 'epa' )
				),
				'rowtemplate' => str_replace ( 'class="alternate"', '<%= alternate %>', $rowtemplate )
		);
		$vars = '';
		foreach ( $namespaced_args as $name => $value ) {
			$vars .= 'EPA.' . $name . '=' . json_encode ( $value ) . ';';
		}
		printf ( '<script>
				window.TVproductions = window.TVproductions || {};(function(EPA, $, undefined) {%s})(window.TVproductions.EasyPhotoAlbum = window.TVproductions.EasyPhotoAlbum || {}, jQuery);
</script>', $vars );
	}
}