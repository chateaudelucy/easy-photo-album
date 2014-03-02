/*
 * This file is part of Easy Photo Album plugin for WordPress.
 *
 *
 */
(function() {
	tinymce.PluginManager.requireLangPack('EasyPhotoAlbum');
	tinymce.create('tinymce.plugins.EasyPhotoAlbum', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has
		 * been created. This call is done before the editor instance has
		 * finished it's initialization so use the onInit event of the editor
		 * instance to intercept that event.
		 *
		 * @param {tinymce.Editor}
		 *            ed Editor instance that the plugin is initialized in.
		 * @param {string}
		 *            url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			var s = {};
			if (epaInsertL10n.wp3_8) {
				s = {
					title : ed.getLang('epa.dlg_title'),
					cmd : 'epa_insert_album',
					icon : 'epa-dashicons-insert-album'
				};
			} else {
				s = {
					title : ed.getLang('epa.dlg_title'),
					cmd : 'epa_insert_album',
					image : url + '/insert.png'
				};
			}
			ed.addButton('epa_insert', s);

			ed.addCommand('epa_insert_album', function() {
				ed.windowManager.open({
					width : 480,
					height : "auto",
					wpDialog : true,
					id : 'epa-insert',
					title : ed.getLang('epa.dlg_title')
				}, {
					plugin_url : url,
				});
			});
		},

		/**
		 * Creates control instances based in the incomming name. This method is
		 * normally not needed since the addButton method of the tinymce.Editor
		 * class is a more easy way of adding buttons but you sometimes need to
		 * create more complex controls like listboxes, split buttons etc then
		 * this method can be used to create those.
		 *
		 * @param {String}
		 *            n Name of the control to create.
		 * @param {tinymce.ControlManager}
		 *            cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no
		 *         control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array. The
		 * current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the
		 *         plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Easy Photo Album Plugin',
				author : 'TV productions',
				authorurl : 'http://tv-productions.org',
				version : "0.1"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('EasyPhotoAlbum', tinymce.plugins.EasyPhotoAlbum);
})();