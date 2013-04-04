$(document).ready(function() {

	$('body').on('click', '[data-link-modal]', function() {
		var link, modalId, modal;
		
		// create the backdrop and wait for next modal to be triggered
		$('body').modalmanager('loading');
		
		link = $(this).attr('data-link-modal');
		modalId = $(this).attr('data-modal-container');
		modal = $('#'+modalId);
		
		modal.load(link, '', function() {
			modal.modal();
		})
	});
	
});


