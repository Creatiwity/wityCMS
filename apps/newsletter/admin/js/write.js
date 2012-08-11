
function checkForm() {
	var nb = 0;
	if (document.getElementById('toCount')) {
		nb = document.getElementById('toCount').innerHTML;
	}
	if (nb > 0) {
		return confirm("Confirmez-vous l'envoi de cette newsletter à "+nb+" personnes ?");
	} else {
		alert("Vous n'avez pas selectionné de destinataires !");
		return false;
	}
}

$(document).ready(function() {
	$('#objet').click(function() {
		$(this).attr('class', '');
		if ($(this).attr('value') == 'Objet') {
			$(this).attr('value', '');
		}
	});
	$('#objet').blur(function() {
		if ($(this).attr('value') == '') {
			$(this).attr('class', 'empty');
			$(this).attr('value', 'Objet');
		}
	});
	
	$('#selectTo').change(function() {
		var value = $(this).find(':selected').first().attr('value');
		if (value != '') {
			$.ajax({
				url: '/admin/newsletter/getMails/',
				success: function(data) {
					$('#toList').html(data);
				},
				data: {
					type: value
				},
				type: 'GET'
			});
		} else {
			$('#toList').html('');
		}
	});
});