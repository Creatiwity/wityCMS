/**
 * Contacts management script allowing ajax usage.
 * 
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4.0
 */
$(document).ready(function() {
	var setNote;

	setNote = function($domObject, jsonResponse) {
		var cleaned, app, $container, _i, _len;

		if (jsonResponse['app-name']) {

			app = jsonResponse['app-name'];

			$('[data-wity-note-app="' + app + '"]').remove();

			if (jsonResponse.notes.length > 0) {
				$container = $('<div class="row" data-wity-note-app="' + app + '"></div>');
				$domObject.before($container);

				for (_i = 0, _len = jsonResponse.notes.length; _i < _len; ++_i) {
					$container.append('<div class="alert alert-' + jsonResponse.notes[_i].level + '" data-note-code="' + jsonResponse.notes[_i].code + '">'
						+ '<button type="button" class="close" data-dismiss="alert">&times;</button>'
						+ jsonResponse.notes[_i].message
						+ '</div>');
				}
			}
		}
	};

	$('body').on('click', '[data-witycms-submit="ajax"]', function() {
		var $form, $button, url, method, data;

		$button = $(this);
		$button.button('loading');
		$form = $button.closest('form');
		url = $form.attr('action');
		method = $form.attr('method');
		data = $form.serialize();

		$.ajax({
			type: method,
			url: url,
			data: data,
			success: function(response) {
				var jResponse;

				$button.button('reset');

				try {
					jResponse = $.parseJSON(response);
				} catch(e) {
					// Debug code
					// $('body').prepend('<pre>' + response + '</pre>');
					return;
				}

				setNote($form, jResponse);
			}
		});
		
		return false;
	});

});
