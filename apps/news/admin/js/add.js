$(document).ready( function() {
	var first = true;
	
	$("#nTitle").keyup( function() {
		var value = $(this).val();
		
		/*$("#permalienInnerView").removeClass("hide");
		$("#permalienInnerInput").addClass("hide");*/
		
		if (value == '') {
			//$(".editPermalien").attr("disabled","disabled");
			$("#permalienInnerInput").val(value);
			//$("#permalienInnerView").html(value);
			return;
		}
		
		// Traitement de la valeur
		value = value.toLowerCase();
		value = value.replace(/[^a-z0-9.]/gi, '-');
		value = value.replace(/-{2,}/g, '-');
		value = value.replace(/(^-|-$)/g, '');
		
		if (value == '') {
			//Titre invalide
			return;
		}
		
		//$("#editPermalien").removeAttr("disabled");
		$("#permalienInnerInput").val(value);
		//$("#permalienInnerView").html(value);
	});
	
	/*$("#editPermalien").click( function() {
		$("#permalienInnerView").addClass("hide");
		$("#permalienInnerInput").removeClass("hide");
	});*/
});
