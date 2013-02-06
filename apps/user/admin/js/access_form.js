/**
 * user_form_access.js
 * File to handle changes in access rights for a user
 * 
 * Some user properties:
 * - a type: a user access is given a type
 *      _ none = no access
 *      _ all = super admin
 *      _ custom = the user has access to certain application with selected permissions
 * - a group: a user has a group which may have its own access rules
 *            When a new group is selected to a user, this JS code automaticly sets the access
 *            type to custom showing the new access in the permissions table.
 */

/**
 * Checks all input access in the permissions table
 * 
 * @param string id Id of the table-row containing the access form
 */
function accessSelectAll(id) {
	$('#'+id+' .permissions input').attr('checked', true);
}

/**
 * Unchecks all input access in the permissions table
 * 
 * @param string id Id of the table-row containing the access form
 */
function accessDeselectAll(id) {
	$('#'+id+' .permissions input').removeAttr('checked');
}

/**
 * Changes a user access type
 * Triggers Checkers and Activators for input if needed
 * 
 * @param string id    Id of the table-row containing the access form
 * @param string type  User access type: none|all|custom
 */
function changeType(id, type) {
	if (type == 'all') {
		accessSelectAll(id);
	} else if (type == 'none') {
		accessDeselectAll(id);
	}
	// If the type does not match with the type selected in the input 
	// (because it was changed in the JS code), then update the input value
	if (!$('#'+id+' .access-type.'+type).attr('checked')) {
		$('#'+id+' .access-type').removeAttr('checked');
		$('#'+id+' .access-type.'+type).attr('checked', true);
	}
}

/**
 * This function configures the input base on an access string
 * 
 * @param string id      Id of the table-row containing the access form
 * @param string access  Access to assign to the inputs
 */
function assignPermissions(id, access) {
	if (access == 'all') {
		changeType(id, 'all');
	} else if (access == 'none') {
		changeType(id, 'none');
	} else {
		changeType(id, 'custom'); // Change to custom access type
		// Convert the acces_string into an array
		var access_split = access.split(',');
		var access_array = {};
		for (key in access_split) {
			app_access = access_split[key];
			first_bracket = app_access.indexOf('[');
			if (first_bracket != -1) {
				app_name = app_access.substring(0, first_bracket);
				permissions = app_access.substring(first_bracket+1, app_access.length-1);
				if (permissions != '') {
					access_array[app_name] = permissions.split('|');
				}
			}
		}
		
		// Iterates every acces input to check whether they match with the group access
		$('#'+id+' .permissions input').each(function(index, input) {
			var bracket_pos = input.name.indexOf(']');
			if (bracket_pos != -1) {
				var app_name = input.name.substring(7, bracket_pos);
				var perm = input.name.substring(bracket_pos+2, input.name.length-1);
				var checked = false;
				if (typeof access_array[app_name] != 'undefined') {
					for (key in access_array[app_name]) {
						if (access_array[app_name][key] === perm) {
							checked = true;
						}
					}
				}
				if (checked) {
					$(input).attr('checked', true);
				} else {
					$(input).removeAttr('checked');
				}
			}
		});
	}
}

/**
 * Changes a user access type
 * Triggers Checkers and Activators for input if needed
 * 
 * @param string type User access type: none|all|custom
 */
function accessGroup(group_id) {
	if (group_id == 0) {
		return;
	}
	// global var group_access is defined in the template file
	assignPermissions('user-access', group_access[group_id]);
}

$(document).ready(function() {
	if ($('#user-access .access-type .none').attr('checked')) {
		changeType('user-access', 'none');
	} else if ($('#user-access .access-type .all').attr('checked')) {
		changeType('user-access', 'all');
	}
	
	// Bind change event to every inputs
	$('#user-access .user-rights input').change(function() {
		if ($('#user-access .user-rights input:checked').size() == 0) {
			changeType('user-access', 'none');
		} else {
			changeType('user-access', 'custom');
		}
	});
});
