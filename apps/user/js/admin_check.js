require(['jquery', 'bootstrap'], function($) {

	$(document).ready(function() {
		var button_admin_check = false;

		$('#admin-check a.validate').click(function() {
			if (!button_admin_check) {
				$('#admin-check-buttons').fadeIn();
				button_admin_check = true;
			}
			$(this).parent().find('a').removeClass('checked');
			$(this).addClass('checked');
			$(this).parent().find('input[value="validate"]').prop('checked', true);
		});
		$('#admin-check a.refuse').click(function() {
			if (!button_admin_check) {
				$('#admin-check-buttons').fadeIn();
				button_admin_check = true;
			}
			$(this).parent().find('a').removeClass('checked');
			$(this).addClass('checked');
			$(this).parent().find('input[value="refuse"]').prop('checked', true);
		});

		$('#cancel-button').click(function() {
			$('#admin-check input').prop('checked', false);
			$('#admin-check a').removeClass('checked');
			$('#admin-check-buttons').fadeOut();
			button_admin_check = false;
		});
		$('#admin-check-form').submit(function() {
			button_admin_check = false;
		});

		$(".waiting").popover({
			html: true,
			trigger: 'hover'
		});

		window.onbeforeunload = function() {
			if (button_admin_check) {
				return "If you leave this page without sending the form, users waiting for validation will remain unaffected.";
			}
		}
	});

});
