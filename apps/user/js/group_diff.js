
require(['jquery'], function($) {

	/**
	 * This function configures the acces form based on the access string
	 *
	 * @param string id      Id of the table-row containing the access form
	 * @param string access  List of accesses to assign to the form
	 */
	function assignDiffPermissions(id, group_access, user_access) {
		var group_type, user_type;
		if (group_access == '') {
			group_type = 'none';
		} else if (group_access == 'all') {
			group_type = 'all';
		} else {
			group_type = 'custom';
		}

		if (user_access == '') {
			user_type = 'none';
		} else if (user_access == 'all') {
			user_type = 'all';
		} else {
			user_type = 'custom';
		}

		if (user_type == group_type) {
			$('#'+id+' .access-type.'+user_type).parent().addClass('shared');
		} else {
			$('#'+id+' .access-type.'+user_type).parent().addClass('plus');
			$('#'+id+' .access-type.'+group_type).parent().addClass('minus');
		}

		changeType(id, user_type); // Change to custom access type

		// Convert the acces_string into an array
		var group_access_array = parseAccess(group_access);
		var access_array = parseAccess(user_access);

		// Iterates every acces input to check whether they match with the group access
		$('#'+id+' .permissions input').each(function(index, input) {
			var first_bracket_pos = input.name.indexOf(']');
			bracket_pos = input.name.substring(first_bracket_pos+1).indexOf(']');
			if (bracket_pos != -1) {
				var app_name = input.name.substring(first_bracket_pos+2, first_bracket_pos+bracket_pos+1);
				var perm = input.name.substring(first_bracket_pos+bracket_pos+3, input.name.length-1);
				var checked = false, group_checked = false;
				if (typeof group_access_array[app_name] != 'undefined') {
					for (key in group_access_array[app_name]) {
						if (group_access_array[app_name][key] === perm) {
							group_checked = true;
						}
					}
				}
				if (typeof access_array[app_name] != 'undefined') {
					for (key in access_array[app_name]) {
						if (access_array[app_name][key] === perm) {
							checked = true;
						}
					}
				}
				if (checked || user_access == 'all') {
					if (group_checked || group_access == 'all') {
						$(input).parent().addClass('shared');
					} else {
						$(input).parent().addClass('plus');
					}
					$(input).prop('checked', true);
				} else {
					if (group_checked) {
						$(input).parent().addClass('minus');
					}
					$(input).prop('checked', false);
				}
			}
		});
	}

	function loadUsersBeginingBy(letter, groupid) {
		var base = window.location.toString();
		base = base.substring(0, base.lastIndexOf('admin'));
		var letter_encoded = letter.replace('#', 'sharp');

		// Hide elements
		$('dl.users-list dt, dl.users-list dd').hide();

		if ($('dt.letter-'+letter_encoded).size() != 0) {
			$('dt.letter-'+letter_encoded).each(function(index, el) {
				$(el).show();
				if (!$(this).find('input').prop('checked')) {
					var classes = $(el).attr('class');
					var id = classes.split(' ')[0];
					$('#'+id).show();
				}
			});
			$('.users-list-container p').removeClass('loading');
			$('dl.users-list').fadeIn();
		} else {
			$.ajax({
				url: base+'m/admin/user/load_users_with_letter/',
				type: 'POST',
				data: 'letter='+letter+'&groupe='+groupid,
				dataType: 'json'
			}).success(function(data) {
				data = data.result;
				for (var i = 0; i < data.length; i++) {
					// Clone pattern
					var dt_clone = $('dt.pattern').clone().removeClass('pattern').addClass('user-'+data[i].id+' letter-'+letter_encoded);
					var dd_clone = $('dd.pattern').clone().removeClass('pattern').addClass('user-'+data[i].id+' letter-'+letter_encoded);

					// Config
					dt_clone.show();
					dd_clone.attr('id', 'user-'+data[i].id);
					dt_clone.find('input').attr('name', 'user['+data[i].id+']');
					dd_clone.find('input').each(function(index, el) {
						$(el).attr('name', $(el).attr('name').replace('[]', '['+data[i].id+']'));
					});
					dt_clone.find('span.nickname').html(data[i].nickname);

					dt_clone.appendTo('dl.users-list');
					dd_clone.appendTo('dl.users-list');

					// Event checkbox toggle
					dt_clone.find('input').change(function() {
						var classes = $(this).parent().parent().attr('class');
						var id = classes.split(' ')[0];
						$('#'+id).slideToggle();
					});

					// Bind change events to every inputs
					bindEvents('user-'+data[i].id);
					$('#user-'+data[i].id+' a.reset').click(function() {
						var el_id = $(this).parent().attr('id');
						var id = el_id.substring(5);
						assignDiffPermissions(el_id, group_access, data[i].access);
					});
					// Assign group permissions to inputs
					assignDiffPermissions('user-'+data[i].id, group_access, data[i].access);
				}

				$('.users-list-container p.caption').removeClass('loading');
				$('dl.users-list').fadeIn();
			});
		}
	}

	$(document).ready(function() {
		if (!$('#display-custom').prop('checked')) {
			$('#group-diff .listing-wrapper').slideDown();
		}

		$('#display-custom').change(function() {
			$('#group-diff .listing-wrapper').slideToggle();

			// Load some users
			if ($('dl.users-list dt').size() == 1) {
				// Find first Letter having users to show
				var first_load = false;
				$('.alphabet a').each(function(index, el) {
					if ($(el).html().indexOf('(') != -1) {
						// Add event handler on <a> el
						$(el).click(function() {
							var letter = $(this).html().substring(0, 1);
							if (!$(this).hasClass('current')) {
								// Display loading gif
								$('.users-list-container p.caption').addClass('loading');
								$('dl.users-list').fadeOut(400, function() {
									loadUsersBeginingBy(letter, groupid);
								});
								$('.alphabet a').removeClass('current');
								$(this).addClass('current');
							}
						});
						if (!first_load) {
							$(el).click();
							first_load = true;
						}
					}
				});
			}
		});
	});

});
