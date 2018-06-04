/**
 * Contacts management script allowing ajax usage.
 *
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
require(['jquery'], function($) {
	$(document).ready(function() {
		var setNote;

		setNote = function($domObject, jsonResponse) {
			var cleaned, app, $container, _i, _len;

			if (jsonResponse['app']) {
				app = jsonResponse['app'];

				$('[data-wity-note-app="' + app + '"]').remove();

				if (jsonResponse.notes.length > 0) {
					$container = $('<div data-wity-note-app="' + app + '"></div>');
					$domObject.before($container);

					for (_i = 0, _len = jsonResponse.notes.length; _i < _len; ++_i) {
						$container.append('<div class="alert alert-' + jsonResponse.notes[_i].level +
							'" data-note-code="' + jsonResponse.notes[_i].code + '">' +
								'<button type="button" class="close" data-dismiss="alert">&times;</button>' +
								jsonResponse.notes[_i].message +
							'</div>'
						);
					}
				}
			}
		};

		$('body').on('click', '[data-witycms-submit="ajax"]', function() {
			var $button = $(this),
				$form = $button.closest('form'),
				method = $form.attr('method'),
				url = $form.attr('action').replace('/contact', '/m/contact'),
				data,
				$inputFile = $form.find('.upload-document-input'),
				processData = true,
				contentType = 'application/x-www-form-urlencoded';

			if (!window.FormData && $inputFile.val()) {
				// FormData not supported
				return;
			}

			if ($inputFile.val()) {
				data = new FormData($form[0]);
				processData = false;
				contentType = false;
			} else {
				data = $form.serialize();
			}

			$button.button('loading');
			$form.find(':input').attr('disabled', true);

			$.ajax({
				type: method,
				url: url,
				data: data,
				processData: processData,
				contentType: contentType,
				success: function(response) {
					var jResponse;

					$button.button('reset');
					$form.find(':input').attr('disabled', false);

					try {
						jResponse = $.parseJSON(response);
					} catch(e) {
						// Debug code
						// $('body').prepend('<pre>' + response + '</pre>');
						setNote($form.parent(), {
							'app': 'contact',
							'notes': [{
								level: 'danger',
								code: 'unknown_error',
								message: 'An unknown error occurred.'
							}]
						});
						return;
					}

					setNote($form.parent(), jResponse);

					if (jResponse && jResponse.result && jResponse.result.code === 'email_sent') {
						$form.remove();
					}
				}
			});

			return false;
		});

		var namespace = '.wity-app-contact.wity-action-form ',
			$uploadDocumentButton = $(namespace + '.upload-document'),
			$uploadDocumentInput = $(namespace + '.upload-document-input'),
			$uploadDocumentDelete = $(namespace + '.upload-document-delete');

		/* Document */
		$uploadDocumentButton.click(function() {
			$uploadDocumentInput.click();
		});

		$uploadDocumentInput.change(function() {
			var value = $uploadDocumentInput.val();

			if (!value || value === '') {
				$uploadDocumentDelete.addClass('hidden');
				$uploadDocumentButton.prop('disabled', false);
			} else {
				$uploadDocumentDelete.find('span.text').text(' ' + $uploadDocumentInput[0].files[0].name);
				$uploadDocumentDelete.removeClass('hidden');
				$uploadDocumentButton.prop('disabled', true);
			}
		});

		$uploadDocumentDelete.click(function() {
			$uploadDocumentInput.replaceWith($uploadDocumentInput = $uploadDocumentInput.clone(true));
			$uploadDocumentDelete.addClass('hidden');
			$uploadDocumentButton.prop('disabled', false);
		});
	});
});
