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

// No direct access
defined ( 'ABSPATH' ) or die ();
// This file is included by EPA_Admin
?>

<div class="wrap about-wrap">

	<h1><?php printf( __( 'Welcome to Easy Photo Album %s', 'epa' ), EasyPhotoAlbum::$version ); ?></h1>

	<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version. Easy Photo Album %s makes it even easier for you to create and manage photo albums.', 'epa' ), EasyPhotoAlbum::$version ); ?></div>

	<div class="wp-badge" style="background: url('<?php echo plugin_dir_url(__FILE__);?>css/img/epa-badge.png?ver=20131027') no-repeat scroll 0 0 rgba(0, 0, 0, 0);" ><?php printf( __( 'Version %s', 'epa' ), EasyPhotoAlbum::$version ); ?></div>
	<h2 class="nav-tab-wrapper"></h2>
	<div class="changelog">
		<h3><?php _e( 'New settings page', 'epa' ); ?></h3>

		<div class="feature-section images-stagger-right">
			<img alt=""
				src="<?php echo plugin_dir_url(__FILE__); ?>css/img/epa-settings-1.2.png"
				style="width: 35%; float: right; margin: 0 5px 12px 2em;" />
			<h4><?php _e( 'Change the display of the albums', 'epa' ); ?></h4>
			<p><?php _e( "The new settings page makes it very easy for you to change the display of the albums. The options for the lightbox are now grouped together, so they are easy to find.", 'epa'); ?></p>
			<p><?php _e( 'Besides the options on the settings page, you can edit some options for each individual album. You can adjust the display of the album to the contents of it.', 'epa'); ?></p>
			<p><?php _e( 'You can find this new settings page under <strong>Settings > Easy Photo Album</strong>', 'epa' ); ?></p>
		</div>
	</div>

	<div class="changelog">
		<h3><?php _e( 'No need to edit your theme', 'epa' ); ?></h3>

		<div class="feature-section images-stagger-right">
			<img alt=""
				src="<?php echo plugin_dir_url(__FILE__); ?>css/img/epa-album-1.2.png"
				style="width: 35%; float: left; margin: 0 5px 12px 2em;" />
			<h4><?php _e( 'The albums display almost always nice right away', 'epa' ); ?></h4>
			<p></p>
			<p><?php _e( "The only thing you have to do is to add the photos, change the order, write a nice caption for them and press the publish button!", 'epa' ); ?></p>
			<p><?php _e( 'And the files for the albums will be only included on the pages when that is necessary. This makes your website faster.' , 'epa'); ?></p>
		</div>

		<div class="feature-section col two-col">
			<div>
				<h4><?php _e( 'Title versus Caption', 'epa' ); ?></h4>
				<p><?php _e( 'You can see two fields for each image on the album edit screen. The title field and the caption field. The title is just a short name, so you know about what the image is when you read the title. The caption is a short description of the image. Easy Photo Album uses the caption for the text under the images.', 'epa' ); ?></p>
			</div>
			<div class="last-feature">
				<h4><?php _e( 'Responsive albums' ); ?></h4>
				<p><?php _e( 'The albums are responsive and displays nice on every device. The number of columns stays always the same.', 'epa' ); ?></p>
			</div>
		</div>
	</div>

	<div class="changelog">
		<h3><?php _e( 'And further on', 'epa' ); ?></h3>

		<div class="feature-section col three-col">
			<div>
				<h4><?php _e( 'Bugfixes', 'epa' ); ?></h4>
				<p><?php printf(_n('We fixed one bug in this release. See the <a href="%2$s" target="_blank">changelog</a>.', 'We fixed %1$s bugs in this release. See the <a href="%2$s" target="_blank">changelog</a>.', 5, 'epa'), 5, 'http://wordpress.org/plugins/easy-photo-album/changelog/'); ?></p>
			</div>
			<div>
				<h4><?php _e( 'Support', 'epa' ); ?></h4>
				<p><?php printf(__( 'Do you have a question, a bug found or a feature request? Report it at the %1$ssupport forums%2$s.', 'epa' ), '<a href="http://wordpress.org/support/plugin/easy-photo-album" target="_blank">', '</a>'); ?></p>
			</div>
			<div class="last-feature">
				<h4><?php _e( 'Translation', 'epa' ); ?></h4>
				<p><?php printf(__( 'This translation is made by: %s.','epa' ), _x('TV productions', 'Translators: insert your name here (with link if you want)', 'epa')); ?></p>
			</div>
		</div>
	</div>

	<div class="return-to-dashboard">
		<a
			href="<?php echo esc_url( self_admin_url( 'options-general.php?page=epa-settings' ) ); ?>"><?php
			_e ( 'Go to Settings &rarr; Easy Photo Album', 'epa' );
			?></a> | <a href="<?php echo esc_url( self_admin_url() ); ?>"><?php
			is_blog_admin () ? _e ( 'Go to Dashboard &rarr; Home', 'epa' ) : _e ( 'Go to Dashboard', 'epa' );
			?></a>
	</div>

</div>
<?php
return;
// Extra translated strings?