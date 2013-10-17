$(document).ready(function() {

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
				
				$form.before(jResponse.view);
			}
		});
		
		return false;
	});

});