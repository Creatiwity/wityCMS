$(document).ready( function() {	
	$("#news_title").keyup( function() {
		var value = $(this).val();
		
		if (value === '') {
			$("#permalienInnerInput").val(value);
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
		
		$("#permalienInnerInput").val(value);
	});
});
