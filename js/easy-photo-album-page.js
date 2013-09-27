/*
Easy Photo Album Wordpress plugin javascript.

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

// Support for string.format function.
if (!String.prototype.format) {
	String.prototype.format = function() {
		var args = arguments;
		return this.replace(/{(\d+)}/g, function(match, number) {
			return typeof args[number] != 'undefined' ? args[number] : match;
		});
	};
}

window.TVproductions = window.TVproductions || {};
(function(EPA, $, undefined) {

	// Private {

	// Wordpress (wp.media) uploader
	var uploader = null;

	// Makes shure the right actions are shown
	var correctActions = function() {
		if (EPA.maxOrder < 1) {
			// Only one image
			$(
					'.easy-photo-album-table .row-actions .order_up, .easy-photo-album-table .row-actions .order_down')
					.hide();
		} else {
			$('#the-list .column-image')
					.each(
							function(index, elm) {
								$row = getRowFromElement(elm);
								order = getOrder(getIdFromElement(elm));
								if (order <= 0) {
									// most upper row
									$('.row-actions .order_up', $row).hide();
								} else if (order >= EPA.maxOrder) {
									// most lower row
									$('.row-actions .order_down', $row).hide();
									// remove |
									$('.row-actions .order_up', $row).html(
											$('.row-actions .order_up', $row)
													.html().replace(' | ', ''));
								} else {
									// show all the actions
									$(
											'.row-actions .order_down, .row-actions .order_up',
											$row).show();
									if ($('.row-actions .order_up', $row)
											.html().indexOf('|') === -1) {
										// the | is removed, so add it
										$('.row-actions .order_up', $row)
												.append(' | ');
									}
								}
							});
		}
		// Update also the image count labels:
		var str;
		if (EPA.maxOrder == 0)
			str = EPA.lang.photo;
		else
			str = EPA.lang.photos;

		$("#easy-photo-album-images .displaying-num").html(
				(EPA.maxOrder + 1) + " " + str);
	};

	// Returns the order from the id
	var getOrder = function(id) {
		return parseInt($(
				'input[name="' + EPA.settingName + '[' + id + '][order]"]')
				.val());
	};

	// Returns the id from the order
	var getIdFromOrder = function(order) {
		var name = $(
				'input[value="' + order + '"][type="hidden"][name*="'
						+ EPA.settingName + '["][name$="[order]"]')
				.attr('name');
		var reg = /[0-9]+/;
		return reg.exec(name)[0];
	};

	// Returns the id from a DOMelement
	var getIdFromElement = function(element) {
		$row = getRowFromElement(element);
		return $('input[type=checkbox]', $row).val();
	};

	// Returns the row (jQuery objct) with the given id.
	var getRowFromId = function(id) {
		return getRowFromElement('input[type=checkbox][value="' + id + '"]');
	};

	// Returns the row (jQuery object) fromt the given element
	// (DOMelement/jQuery object)
	var getRowFromElement = function(element) {
		return $(element).closest('tr');
	};

	// Switch two rows. Switch movingId with oldOrder to the newOrder
	var switchRows = function(oldOrder, movingId, newOrder) {
		var forcedMoveId = getIdFromOrder(newOrder);
		var $forcedMoveRow = getRowFromId(forcedMoveId);
		var $movingRow = getRowFromId(movingId);
		if ($.isEmptyObject($forcedMoveRow) || $.isEmptyObject($movingRow)) {
			return; // moving not possible...
		}
		var forcedMoveHtml = $forcedMoveRow.html();
		var movingHtml = $movingRow.html();
		if (forcedMoveHtml == undefined || movingHtml == undefined) {
			console.error('Easy Photo Album: HTML Undefined Error');
			return;
		}
		$forcedMoveRow.html(replaceOrder(movingHtml, oldOrder, newOrder));
		$movingRow.html(replaceOrder(forcedMoveHtml, newOrder, oldOrder));
	};

	// replace the oldOrder with the newOrder in the given html (jQuery object)
	var replaceOrder = function(html, oldOrder, newOrder) {
		return html.replace('value="' + oldOrder + '"', 'value="' + newOrder
				+ '"');
	};

	// Set the order to order for the given id
	var setOrder = function(id, order) {
		$('input[name="' + EPA.settingName + '[' + id + '][order]"]')
				.val(order);
	};

	// Set the click handlers for the actions
	var setClickHandlers = function() {
		// first unbind the click events
		$('.order_up a.epa-move-up, .order_down a.epa-move-down, .delete a')
				.unbind('click');
		setTimeout(function() {
			$('.order_up a.epa-move-up').click(function(e) {
				if (e.target.tagName.toLowerCase() == 'a') {
					EPA.moveUp(this);
				}
				e.preventDefault();
			});
			$('.order_down a.epa-move-down').click(function(e) {
				if (e.target.tagName.toLowerCase() == 'a') {
					EPA.moveDown(this);
				}
				e.preventDefault();

			});
			$('.delete a').click(function(e) {
				if (e.target.tagName.toLowerCase() == 'a') {
					EPA.deletePhoto(this);
				}
				e.preventDefault();
			});
			$('input[name="' + EPA.settingName + '[add_photo]"]').click(
					function(e) {
						EPA.addPhoto(this);
						e.preventDefault();
					});
			// Sortable things: css
			$('easy-photo-album-table tbody tr').not('.alternate').css({
				'background-color' : '#F9F9F9'
			});
		}, 50);

	};

	// Returns the title of the photo with the given id
	var getTitle = function(id) {
		return $(
				'input[type="text"][name="' + EPA.settingName + '[' + id
						+ '][title]"]').val();
	};

	// } (end private)

	// Public {

	// Moves the given element (DOMelement or jQuery object) a position up
	EPA.moveUp = function(element) {
		var id = getIdFromElement(element);
		var order = getOrder(id);
		if (order <= 0)
			return; // upper image
		var newOrder = order - 1; // Move up
		switchRows(order, id, newOrder);

		correctActions();
		setClickHandlers();
	};

	// Moves the given element (DOMelement or jQuery object) a position down
	EPA.moveDown = function(element) {
		var id = getIdFromElement(element);
		var order = getOrder(id);
		if (order >= EPA.maxOrder)
			return; // bottom
		var newOrder = order + 1; // move down
		switchRows(order, id, newOrder);

		correctActions();
		setClickHandlers();
	};

	// Shows the media uploader and adds the selected photo's to the album
	EPA.addPhoto = function(element) {
		if (!uploader) {
			// make object
			if (!wp.media) {
				console
						.error("Easy Photo Album: Wordpress media files not included or loaded correctly. Uploads disabled.");
				return;
			}
			uploader = wp.media({
				title : EPA.lang.mediatitle,
				button : {
					text : EPA.lang.mediabutton
				},
				library : {
					type : 'image',
				},
				multiple : true,
				frame : 'select',
			});

			uploader
					.on(
							'select',
							function() {
								var selection = uploader.state().get(
										'selection');
								selection
										.map(function(att) {
											var attachment = att.toJSON();
											var order = EPA.maxOrder += 1;
											// make row from template
											var row = _
													.template(
															EPA.rowtemplate,
															{
																alternate : (order % 2 === 0 ? ' class="alternate"'
																		: ''),
																id : attachment.id,
																imgurl : (attachment.sizes.thumbnail == undefined ? attachment.sizes.full.url
																		: attachment.sizes.thumbnail.url),
																order : order,
																title : attachment.title,
																caption : attachment.caption
															});
											// append it to the album
											$('.easy-photo-album-table tr:last')
													.after(row);
										});

								correctActions();
								setClickHandlers();
							}, this);
		}
		// Set post id
		wp.media.model.settings.post.id = $('#post_ID').val();

		uploader.open();
	};

	// Deletes the photo fromt the given element (DOMelement or jQuery object)
	// from the album.
	EPA.deletePhoto = function(element) {
		var id = getIdFromElement(element);
		if (id && confirm(EPA.lang.deleteconfirm.format(getTitle(id)))) {
			var deletedorder = getOrder(id);

			var $row = getRowFromElement(element);
			$row.remove();

			// Correct the order numbers
			EPA.maxOrder -= 1;
			for ( var i = deletedorder; i <= EPA.maxOrder; i++) {
				var needfix = getIdFromOrder(i + 1);
				setOrder(needfix, i);
			}
			setClickHandlers();
			correctActions();
		}
	};

	// On init: Constructor
	(function() {
		// Load only when we are on the edit page
		if (jQuery(".easy-photo-album-table").length) {
			if (EPA.settingName == undefined) {
				console
						.info('EasyPhotoAlbum: settingName not set, use default');
				EPA.settingName = 'EasyPhotoAlbums';
			}
			if (EPA.maxOrder == undefined) {
				console
						.info('EasyPhotoAlbum: maxOrder not set, calculate value');
				EPA.maxOrder = $('#the-list tr').length;
			}

			correctActions();
			// Event handlers
			setClickHandlers();

			// Sortable
			$(".easy-photo-album-table tbody").sortable(
					{
						axis : 'y',
						handle : '.column-image img',
						placeholder : 'sortable-placeholder',
						forcePlaceholderSize : true,
						cursor : 'move',
						opacity : 0.6,
						update : function(event, ui) {
							// Correct the order after dragging:
							jQuery('.easy-photo-album-table tbody tr').each(
									function(index, elm) {
										// the current index is the order
										setOrder(getIdFromElement(elm), index);
									});
							// reset colum differents:
							$('.easy-photo-album-table tr:nth-child(odd)')
									.addClass('alternate');
							$('.easy-photo-album-table tr:nth-child(even)')
									.removeClass('alternate');
							correctActions();
							setClickHandlers();
						}
					});
		}// end if jQuery(".easy-photo-album-table").length
	})();

	// } (end public)

})(window.TVproductions.EasyPhotoAlbum = window.TVproductions.EasyPhotoAlbum
		|| {}, jQuery);