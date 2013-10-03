$(document).ready(function() {

	$('body').on('click', '[data-witycms-submit="ajax"]', function() {
		var $form, url, method, data;

    	$form = $(this).closest('form');
    	url = $form.attr('action');
    	method = $form.attr('method');
    	data = $form.serialize();

		$.ajax({
			type: method,
			url: url,
			data: data,
			success: function(response) {
				var jResponse;

				try {
					jResponse = $.parseJSON(response);
				} catch(e) {
					$('body').prepend('<pre>' + response + '</pre>');
				}
				
				$form.before(jResponse.view);
			}
		});
		
		return false;
	});

});