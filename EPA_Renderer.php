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
 * This class renders the album
 *
 * @author TV productions
 * @package EasyPhotoAlbum
 *
 */
class EPA_Renderer {
	protected $photos = array ();
	protected $display_options = array ();
	protected $album_id = '';
	private $closing_tags = array ();
	private $in_hidden_div = false;

	/**
	 * Set up a render object for the given album
	 *
	 * @param
	 *        	int | WP_Post $album	The post object or the id of the album.
	 */
	public function __construct($album) {
		$album = get_post ( $album );
		$data = get_post_meta ( $album->ID, EPA_PostType::SETTINGS_NAME, true );
		$data ['settings'] = isset ( $data ['settings'] ) ? $data ['settings'] : array ();
		$this->display_options = wp_parse_args ( $data ['settings'], EasyPhotoAlbum::get_instance ()->get_default_display_options () );
		unset ( $data ['settings'] );
		$this->photos = $data;
		$this->album_id = esc_attr ( 'epa-album-' . $album->ID );
	}

	/**
	 * Renders the photo's
	 *
	 * @param bool $echo
	 * @return string
	 */
	public function render($echo = false) {
		$html = '<!-- Easy Photo Album -->
';
		$html .= $this->render_style_block ();
		$html .= '<ul id="' . $this->album_id . '" class="epa-album epa-cf">
<li class="epa-row  epa-cf">
				';
		// Add the closing tags to the list
		array_push ( $this->closing_tags, 'ul', 'li' );

		$count = 1;
		$max = count ( $this->photos );
		foreach ( $this->photos as $photo ) {
			$html .= $this->render_one_photo ( $photo );
			if ($this->display_options ['excerpt_number'] == $count && $count != $max) {
				// $count is never 0, so by 0, all the images will be displayed.
				$html .= $this->more_tag ();
			}
			// IF: there is need for a new row and we are not in the hidden div
			// ($display_option['show_all_images_in_lightbox'])
			// ($count % $colums == 0 AND $count != $max AND !$in_hidden_div)
			if ($count % $this->display_options ['columns'] == 0 && $count != $max && $this->in_hidden_div == false) {
				$html .= '</li><li class="epa-row epa-cf">';
			}
			$count += 1;
		}

		$html .= $this->render_closing_tags ();

		if ($echo)
			echo $html;

		return $html;
	}

	/**
	 * Renders one photo
	 *
	 * @param stdClass $photo
	 *        	object with the photo properties
	 * @return string generated HTML
	 */
	protected function render_one_photo($photo) {
		$src = wp_get_attachment_image_src ( $photo->id, $this->display_options ['display_size'] );
		$src = $src [0];

		$a_attr = "";
		switch ($this->display_options ['link_to']) {
			case 'lightbox' :
				$url = wp_get_attachment_image_src ( $photo->id, 'full' );
				$url = $url [0];
				$a_attr = 'data-lightbox="' . $this->album_id . '"';
				break;
			case 'attachment' :
				$url = get_attachment_link ( $photo->id );
				break;
			case 'file' :
			default :
				$url = wp_get_attachment_image_src ( $photo->id, 'full' );
				$url = $url [0];
				break;
		}

		$title = "";
		if ($this->display_options ['show_caption']) {
			$title = '<br/><span class="epa-title wp-caption">' . $photo->title . '</span>';
		}
		$html = <<<HTML

		<div class="epa-image">
			<a href="{$url}" {$a_attr} title="{$photo->title}">
				<img src="{$src}" alt="{$photo->title}"/>
				{$title}
			</a>
		</div>

HTML;
		// Remove newlines for wpautop
		$html = preg_replace ( '/[\t\r\n\r\n]+/', '', trim ( $html ) );
		return $html;
	}

	protected function render_style_block() {
		// Calculate the widht (in %) of each image
		$used_margin = $this->display_options ['columns'] * 2;
		$width = floor ( (100 - $used_margin) / $this->display_options ['columns'] );
		return <<<STYLE
<style type="text/css">
	#{$this->album_id} .epa-image {
		width: {$width}%;
	}
</style>
STYLE;
	}

	/**
	 * Returns the more tag
	 *
	 * @return string
	 */
	protected function more_tag() {
		// If the user wants to display all the images in excerpt view.
		if ($this->display_options ['show_all_images_in_lightbox'] && $this->display_options ['link_to'] == 'lightbox') {
			$this->in_hidden_div = true;
			array_push ( $this->closing_tags, 'div' );
			return '
<div style="display:none">
					';
		}
		return '
<!--more-->
				';
	}

	/**
	 * Renders the closing tags, according to <code>$closing_tags</code>
	 *
	 * @return string
	 */
	private function render_closing_tags() {
		$output = '';
		foreach ( array_reverse ( $this->closing_tags ) as $tag ) {
			$output .= "</$tag>";
		}
		return $output;
	}
}