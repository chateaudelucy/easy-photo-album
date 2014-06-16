/*
 * This file is part of Easy Photo Album plugin for WordPress.
 *
 *
 */
(function() {
	tinymce.PluginManager.requireLangPack('EasyPhotoAlbum');
	tinymce.PluginManager.add('EasyPhotoAlbum', function(editor, url) {
		var albums;

		editor.addButton('epa_insert', {
			tooltip : editor.getLang('epa.dlg_title'),
			icon : 'epa-dashicons epa-dashicons-insert-album',
			cmd : 'epa_insert_album',
		});

		/**
		 * Retrieves the albums and returns an array of album titles with their
		 * ids. Used by the epa_insert_album command in the listbox control.
		 */
		function getAlbums(callback) {
			if (undefined == albums) {
				var data = 'action=epa_get_albums&_ajax_nonce='
						+ editor.getLang('epa.nonce');
				tinymce.util.XHR.send({
					url : window.ajaxurl,
					data : data,
					content_type : "application/x-www-form-urlencoded",
					type : "POST",
					success : function(text) {
						var tmp = [], data = tinymce.util.JSON.parse(text);
						for ( var i = 0; i < data.length; i++) {
							tmp.push({
								text : data[i].title,
								value : data[i].id
							});
						}
						albums = tmp;
						callback(tmp);
					}
				});
			} else {
				callback(albums);
			}
		}

		function showDialog(albums) {
			var inlineWindow;
			inlineWindow = editor.windowManager
					.open({
						inline : true,
						id : 'epa-insert',
						title : editor.getLang('epa.dlg_title',
								'Insert a photo album'),
						body : [
								{
									name : 'albumid',
									type : 'listbox',
									label : editor.getLang('epa.select_album',
											'Select an album to include'),
									autofocus : true,
									values : albums
								},
								{
									name : 'showtitle',
									type : 'checkbox',
									text : editor.getLang('epa.show_title',
											'Show the title'),
								},
								{
									name : 'displaylabel',
									type : 'label',
									text : editor.getLang('epa.display_label',
											'Display album like:'),
								},
								{
									name : 'excerpt',
									type : 'radio',
									style : 'cursor: pointer;',
									text : editor.getLang('epa.excerpt',
											'Excerpt'),
									checked : true,
									onClick : function(e) {
										// temporary untill radio is
										// implementend
										if (inlineWindow.find('#excerpt')
												.checked()) {
											inlineWindow.find('#full').checked(
													false);
										} else {
											inlineWindow.find('#full').checked(
													true);
										}
									}
								},
								{
									name : 'full',
									type : 'radio',
									text : editor.getLang('epa.full',
											'Full album'),
									style : 'cursor: pointer;',
									onClick : function(e) {
										// temporary untill radio is
										// implementend
										if (inlineWindow.find('#full')
												.checked()) {
											inlineWindow.find('#excerpt')
													.checked(false);
										} else {
											inlineWindow.find('#excerpt')
													.checked(true);
										}
									}
								} ],
						// Set buttons and their actions
						buttons : [ {
							text : editor.getLang('epa.insert', 'Insert'),
							onclick : 'submit',
							classes : 'btn primary'
						}, {
							text : editor.getLang('epa.cancel', 'Cancel'),
							onclick : 'close'
						} ],
						onSubmit : function(e) {
							// Create shortcode
							var shortcode, title, display = "excerpt";
							if (e.data.showtitle) {
								title = "true";
							} else {
								title = "false";
							}
							if (e.data.full) {
								display = "full";
							}
							shortcode = '[epa-album id="' + e.data.albumid
                                + '" show_title="' + title + '" display="'
                                + display + '"]';
                            editor.selection.setContent(shortcode);
						}
					});
		}

		editor.addCommand('epa_insert_album', function() {
			var load = editor.windowManager.open({
				inline : true,
				title : editor.getLang('epa.title_loading', 'Loading...'),
				body : [
						/*
						 Image type is not yet implemented.
						 {
							type : 'image',
							src : editor.getLang('epa.spinner',
									'images/wpspin_light.gif')
						},*/
						{
							type : 'label',
							text : editor.getLang('epa.loading',
									'Loading albums...')
						} ],
						// hide the buttons
				buttons : [ {
					hidden : true
				} ]
			});
			// Load albums
			getAlbums(function(data) {
				// Close the loading window
				load.close();
				// Show the real insert dialog.
				showDialog(data);
			})
		});
	});
})();