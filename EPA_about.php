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
 * File with the about page.
 *
 * @since 1.2
 * @package EasyPhotoAlbum
 */

// No direct access
defined ( 'ABSPATH' ) or die ();
// This file is included by EPA_Admin

?>
<div class="wrap about-wrap">

	<h1><?php printf( __( 'Welcome to Easy Photo Album %s', 'epa' ), EasyPhotoAlbum::$version ); ?></h1>

	<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version. Easy Photo Album %s makes it even easier for you to create and manage photo albums.', 'epa' ), EasyPhotoAlbum::$version ); ?></div>

	<div class="wp-badge" style="background-image: none,
		url('<?php echo plugin_dir_url ( __FILE__ );?>css/img/epa-badge.svg?v=20140302');"><?php printf( __( 'Version %s', 'epa' ), EasyPhotoAlbum::$version ); ?></div>
	<h2 class="nav-tab-wrapper">
		<a class="nav-tab nav-tab-active" href="index.php?page=epa-about">
			<?php _e("What's New", 'epa');?> </a>
	</h2>
	<div class="changelog">
		<h3><?php _e( 'Video documentation', 'epa' ); ?></h3>

		<div class="feature-section">
			<img alt=""
				src="<?php echo plugin_dir_url(__FILE__); ?>css/img/epa-videos-1.3.png"
				style="width: 35%; float: right; margin: 0 5px 12px 2em;" />
			<h4><?php _e( 'Learn quick how this plugin works', 'epa' ); ?></h4>
			<p><?php _e( "An enthousiastic Easy Photo Album user has recorded a couple of videos that shows how to create an album, how to change the photo order, how to add albums to your menu and an introduction into the settings.", 'epa' ); ?></p>
			<p><?php printf(__( 'If you have still questions, please head over to the %1$ssupport forums%2$s.', 'epa'), '<a href="'.EasyPhotoAlbum::get_instance()->forumurl.'" target="_blank">', '</a>'); ?></p>
			<h4>
				<a href="<?php echo EasyPhotoAlbum::get_instance()->helpurl;?>"
					target="_blank"><?php _e('View the video documentation', 'epa')?> &rarr;</a>
			</h4>
		</div>
	</div>

	<div class="changelog">
		<h3><?php _e( 'Clear settings page', 'epa' ); ?></h3>

		<div class="feature-section">
			<img alt=""
				src="<?php echo plugin_dir_url(__FILE__); ?>css/img/epa-settings-1.3.png"
				style="width: 35%; float: left; margin: 0 2em 12px 5px;" />
			<h4><?php _e( 'Clear and consistent settings', 'epa' ); ?></h4>
			<p><?php _e( "The new settings page makes it very easy for you to change the display of the albums. The settings are grouped together, which makes everyting more clear", 'epa'); ?></p>
			<p><?php _e( 'Besides the options on the settings page, you can edit some options for each individual album. You can adjust the display of the album to the contents of it.', 'epa'); ?></p>
			<p><?php _e( 'Each option, also on the album edit screen, has now some helptext. And all this in the fresh WordPress 3.8 style.', 'epa' ); ?></p>
		</div>

		<div class="feature-section col two-col">
			<div>
				<h4><?php _e( 'Updated revisions support', 'epa' ); ?></h4>
				<p><?php _e( "Don't be afraid to change your albums. There is now full support for revisions. The differences between photos are easy to compare with the updated display of the album. Change you album with confidence.", 'epa' ); ?></p>

			</div>
			<div class="last-feature">
				<h4><?php _e( 'Easy Photo Album is doing well!', 'epa' ); ?></h4>
				<p><?php printf(__( 'Easy Photo Album has been downloaded %s times! Thank you very much for using this plugin. If you want to support the development of this plugin, please consider a donation.', 'epa' ), '<strong>'.EasyPhotoAlbum::get_instance()->get_download_count().'</strong>'); ?></p>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post"
					target="_blank">
					<input type="hidden" name="cmd" value="_s-xclick"> <input
						type="hidden" name="hosted_button_id" value="BP8BX6Y5SHQN6"> <input
						type="image"
						src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif"
						name="submit" alt="PayPal – The safer, easier way to pay online.">
				</form>
				<p><?php _e('You can also contribute by creating documentation or by translating this plugin into your language.', 'epa');?></p>
			</div>
		</div>
		<div class="feature-section col two-col">
			<div>
				<h4><?php _e( 'Title versus Caption', 'epa' ); ?></h4>
				<p><?php _e( 'You can see two fields for each image on the album edit screen. The title field and the caption field. The title is just a short name, so you know about what the image is when you read the title. The caption is a short description of the image. Easy Photo Album uses the caption for the text under the images.', 'epa' ); ?></p>
			</div>
			<div class="last-feature">
				<h4><?php _e( 'Featured image and comments support', 'epa' ); ?></h4>
				<p><?php _e('This means that you can set a featured image for an album. How it will be used, depends on your theme.', 'epa')?></p>
				<p><?php _e('Get feedback on your albums. Comments on albums are now supported and look and feel the same as normal post comments.', 'epa')?></p>
			</div>
		</div>
	</div>

	<div class="changelog">
		<h3><?php _e( 'And further on', 'epa' ); ?></h3>

		<div class="feature-section col three-col">
			<div>
				<h4><?php _e( 'Bugfixes', 'epa' ); ?></h4>
				<p><?php printf(_n('We fixed one bug in this release. See the %2$schangelog%3$s.', 'We fixed %1$s bugs in this release. See the %2$schangelog%3$s.', 7, 'epa'), 7, '<a href="http://wordpress.org/plugins/easy-photo-album/changelog/" target="_blank">', '</a>'); ?></p>
			</div>
			<div>
				<h4><?php _e( 'Support', 'epa' ); ?></h4>
				<p><?php printf(__( 'Do you have a question, a bug found or a feature request? Report it at the %1$ssupport forums%2$s.', 'epa' ), '<a href="'.EasyPhotoAlbum::get_instance()->forumurl.'" target="_blank">', '</a>'); ?></p>
			</div>
			<div class="last-feature">
				<h4><?php _e( 'Translation', 'epa' ); ?></h4>
				<p><?php printf(__( 'This translation is made by: %s.','epa' ), _x('TV productions', 'Translators: insert your name here (with link if you want)', 'epa')); ?></p>
			</div>
		</div>
	</div>

	<div class="return-to-dashboard">
	<?php
	if (! is_network_admin ()) {
		?>
		<a
			href="<?php echo esc_url( self_admin_url( 'options-general.php?page=epa-settings' ) ); ?>"><?php
		_e ( 'Go to Settings &rarr; Easy Photo Album', 'epa' );
		?></a> | <?php
	}
	?><a href="<?php echo esc_url( self_admin_url() ); ?>"><?php
			is_blog_admin () ? _e ( 'Go to Dashboard &rarr; Home', 'epa' ) : _e ( 'Go to Dashboard', 'epa' );
			?></a>
	</div>

</div>
<?php

return;
_n_noop ( 'Maintenance Release', 'Maintenance Releases', 'epa' );
_n_noop ( 'Version %1$s has %2$s bugfixes. For more information, check the %3$schangelog%4$s.', 'Version %1$s has %2$s bugfixes. For more information, check the %3$schangelog%4$s.', 'epa' );