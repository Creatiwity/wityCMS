
/**
 * This function configures the acces form based on the access string
 * 
 * @param string id      Id of the table-row containing the access form
 * @param string access  List of accesses to assign to the form
 */
function assignDifPermissions(id, group_access, user_access) {
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
		$('#'+id+' .access-type.'+user_type).parent().addClass('same');
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
					$(input).parent().addClass('same');
				} else {
					$(input).parent().addClass('plus');
				}
				$(input).attr('checked', true);
			} else {
				if (group_checked) {
					$(input).parent().addClass('minus');
				}
				$(input).removeAttr('checked');
			}
		}
	});
}

function loadUsersBeginingBy(letter, groupid) {
	var base = window.location.toString();
	base = base.substring(0, base.length-1); // make sure to remove last '/'
	base = base.substring(0, base.lastIndexOf('groups'));
	
	// Hide elements + display loading
	$.ajax({
		url: base+'load_users_with_letter/',
		type: 'POST',
		data: 'letter='+letter+'&groupe='+groupid,
		dataType: 'json'
	}).done(function(data) {
		$('dl.users-list dt, dl.users-list dd').each(function(index, el) {
			if ($(el).attr('class') != 'pattern') {
				$(this).remove();
			}
		});
		
		for (userid in data) {
			// Clone pattern
			var dt_clone = $('dt.pattern').clone().removeClass('pattern').addClass('user-'+userid);
			var dd_clone = $('dd.pattern').clone().removeClass('pattern').addClass('user-'+userid);
			
			// Config
			dt_clone.attr('class', 'user-'+userid);
			dd_clone.attr('class', 'user-'+userid);
			dd_clone.attr('id', 'user-'+userid);
			dt_clone.find('input').attr('name', 'user['+userid+']');
			dd_clone.find('input').each(function(index, el) {
				$(el).attr('name', $(el).attr('name').replace('[]', '['+userid+']'));
			});
			// dd_clone.find('input').attr('name', 'user['+userid+']');
			dt_clone.find('span.nickname').html(data[userid].nickname);
			
			dt_clone.appendTo('dl.users-list');
			dd_clone.appendTo('dl.users-list');
			
			// Event
			dt_clone.find('input').change(function() {
				var id = $(this).parent().parent().attr('class');
				$('#'+id).slideToggle();
			});
			
			// Bind change events to every inputs
			bindEvents('user-'+userid);
			$('#user-'+userid+' a.reset').click(function() {
				var el_id = $(this).parent().attr('id');
				var id = el_id.substring(5);
				assignDifPermissions(el_id, group_access, data[id].access);
			});
			// Assign group permissions to inputs
			assignDifPermissions('user-'+userid, group_access, data[userid].access);
		}
	});
}

$(document).ready(function() {
	if ($('#display-custom').attr('checked') == '') {
		$('#group-dif .listing-wrapper').slideDown();
	}
	
	$('#display-custom').change(function() {
		$('#group-dif .listing-wrapper').slideToggle();
		
		// Load some users
		if ($('dl.users-list dt').size() == 1) {
			// Find first Letter having users to show
			var first_load = false;
			$('.alphabet a').each(function(index, el) {
				if ($(el).html().indexOf('(') != -1) {
					// Add event handler on <a> el
					$(el).click(function() {
						if (!$(this).hasClass('current')) {
							loadUsersBeginingBy($(this).html().substring(0, 1), groupid);
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
