/**
 * Add or edit an article
 */

require(['jquery'], function($) {
	$(document).ready( function() {
		$("#news_title").on('keyup blur', function() {
			var value = $(this).val();

			if (value === '') {
				$("#news_url").val(value);
				return;
			}

			// Traitement de la valeur
			value = value.toLowerCase();
			value = value.replace(/[^a-z0-9.]/gi, '-');
			value = value.replace(/-{2,}/g, '-');
			value = value.replace(/(^-|-$)/g, '');

			if (value === '') {
				//Titre invalide
				return;
			}

			$("#news_url").val(value);
		});
		
		// Prevents user from accidentally refreshing or leaving the page
		window.onbeforeunload = function() {
			return "Warning: if you leave without saving, all modifications will be lost.";
		};
		
		$('.wity-app button[type="submit"]').click(function() {
			window.onbeforeunload = null;
		});
	});
});
