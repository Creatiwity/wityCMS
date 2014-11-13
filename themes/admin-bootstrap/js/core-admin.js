require(['jquery', 'bootstrap'], function($) {

	$(document).ready(function() {

		$('body').on('click', '[data-link-modal]', function() {
			var link, modalId, modal;

			link = $(this).attr('data-link-modal');
			modalId = $(this).attr('data-modal-container');
			modal = $('#'+modalId);

			$.ajax(link, {
				success: function(data) {
					var json;

					try {
						json = $.parseJSON(data);
					} catch (e) {
						console.error('Error parsing modal JSON');
						return;
					}

					modal.html(json.view);
					modal.modal();
				}
			});
		});

	});

});
