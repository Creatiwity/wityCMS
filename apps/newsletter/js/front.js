/**
 * Newsletter form JavaScript logic.
 */

require(['jquery'], function($) {
	var namespace = '.wity-app-newsletter.wity-action-add ';

	$(namespace + 'form').submit(function(event) {
		// Stop form from submitting normally
		event.preventDefault();

		// Get some values from elements on the page:
		var $form = $(this),
			$text = $(namespace + 'span.newsletter-text'),
			$input = $form.find('input[name="email"]'),
			term = $input.val(),
			url = $form.attr('action');

		if (term === '') {
			$form.fadeOut('fast', function() {
				$form.fadeIn('fast', function() {
					$form.fadeOut('fast', function() {
						$form.fadeIn('fast');
					});
				});
			});

			return;
		}

		// Send the data using post
		var posting = $.post(url.replace('newsletter', 'm/newsletter'), {
			email: term
		});

		// Put the results in a div
		posting.done(function(data) {
			var returnedJSON,
				returnedErrorLevel,
				returnedCode,
				returnedMessage;

			try {
				returnedJSON = $.parseJSON(data);

				if (!returnedJSON.result) {
					return; // internal error
				}

				returnedErrorLevel = returnedJSON.result.level ? returnedJSON.result.level : '';
				returnedCode = returnedJSON.result.code ? returnedJSON.result.code : '';
				returnedMessage = returnedJSON.result.message ? returnedJSON.result.message : '';
			} catch (e) {
				returnedErrorLevel = 'error';
				returnedCode = 'unknown_error';
				returnedMessage = 'An unknown error occured';
			}

			$form.fadeOut('fast', function() {
				$text.hide();
				$text.html(returnedMessage + ((returnedErrorLevel == 'success') ? '' : '<br /><br /><a href="#back">&laquo; Retour</a>'));

				$text.find('a[href="#back"]').click(function() {
					$input.val('');

					$text.fadeOut('fast', function() {
						$form.fadeIn('fast');
					});

					return false;
				});

				$text.fadeIn('fast');
			});
		});
	});
});
