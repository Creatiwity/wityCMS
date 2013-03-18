
var button_admin_check = false;

$(document).ready(function() {
	$('#admin_check a.validate').click(function() {
		if (!button_admin_check) {
			$('#admin_check_buttons').fadeIn();
			button_admin_check = true;
		}
		$(this).parent().find('a').removeClass('checked');
		$(this).addClass('checked');
		$(this).parent().find('input[value="validate"]').attr('checked', true);
	});
	$('#admin_check a.refuse').click(function() {
		if (!button_admin_check) {
			$('#admin_check_buttons').fadeIn();
			button_admin_check = true;
		}
		$(this).parent().find('a').removeClass('checked');
		$(this).addClass('checked');
		$(this).parent().find('input[value="refuse"]').attr('checked', true);
	});
	
	$('#cancel_button').click(function() {
		$('#admin_check input').attr('checked', false);
		$('#admin_check a').removeClass('checked');
		$('#admin_check_buttons').fadeOut();
		button_admin_check = false;
	});
	$('#admin-check-form').submit(function() {
		button_admin_check = false;
	});
});

window.onbeforeunload = function() {
	if (button_admin_check) {
		return "If you leave this page without sending the form, users waiting for validation will remain unaffected.";
	}
}

function showUserDetails(nickname, email, firstname, lastname) {
	$('#ac-nickname').html(nickname);
	$('#ac-email').html(email);
	$('#ac-firstname').html(firstname);
	$('#ac-lastname').html(lastname);
	$('#admin-check-details').show();
}

function hideUserDetails() {
	$('#admin-check-details').hide();
}
