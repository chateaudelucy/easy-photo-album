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
 *
 */
class EPA_Renderer {
	protected $album_name = "";
	protected $photos = array ();

	/**
	 * Initializes the renderer with the given photo's.
	 *
	 * @param array $photos
	 * @param string $name
	 *        	[optional] The name of the album. This is needed when the user uses lightbox.
	 */
	public function __construct($photos, $name = '') {
		$this->photos = $photos;
		$this->album_name = sanitize_title_with_dashes ( $name );
	}

	/**
	 * Renders the photo's
	 *
	 * @param bool $echo
	 * @return string
	 */
	public function render($echo = false) {
		$html = '<ul class="epa-album">
';
		$count = 1;
		foreach ( $this->photos as $photo ) {
			$html .= $this->render_one_photo ( $photo );
			if (EasyPhotoAlbum::get_instance ()->numimageswhennotsingle == $count) {
				// $count is never 0, so by 0, all the images will be displayed.
				$html .= $this->moreTag ();
			}
			$count += 1;
		}

		$html .= '</ul>
';

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
		$src = wp_get_attachment_image_src ( $photo->id, array (
				EasyPhotoAlbum::get_instance ()->thumbnailwidth,
				EasyPhotoAlbum::get_instance ()->thumbnailheight
		) );
		$src = $src [0];

		$a_attr = "";
		switch (EasyPhotoAlbum::get_instance ()->linkto) {
			case 'lightbox' :
				$url = wp_get_attachment_image_src ( $photo->id, 'full' );
				$url = $url [0];
				$a_attr = 'data-lightbox="' . $this->album_name . '"';
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
		if (EasyPhotoAlbum::get_instance ()->showtitlewiththumbnail) {
			$title = '<span class="epa-title wp-caption">' . $photo->title . '</span>';
		}

		$w = EasyPhotoAlbum::get_instance ()->thumbnailwidth;
		$h = EasyPhotoAlbum::get_instance ()->thumbnailheight;

		return <<<HTML

		<li class="epa-image">
			<a href="{$url}" {$a_attr} title="{$photo->title}">
				<img src="{$src}" width="{$w}" height="{$h}" alt="{$photo->title}"/><br/>
				{$title}
			</a>
		</li>

HTML;
	}

	/**
	 * Returns the more tag
	 * @return string
	 */
	protected function moreTag() {
		return '
<!--more-->
				';
	}
}
