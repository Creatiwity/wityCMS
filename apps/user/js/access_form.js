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

define(['jquery'], function($) {
	/**
	 * Checks all input access in the permissions table
	 *
	 * @param string id Id of the table-row containing the access form
	 */
	function accessSelectAll(id) {
		$('#'+id+' .permissions input').prop('checked', true);
	}

	/**
	 * Unchecks all input access in the permissions table
	 *
	 * @param string id Id of the table-row containing the access form
	 */
	function accessDeselectAll(id) {
		$('#'+id+' .permissions input').prop('checked', false);
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
		if (!$('#'+id+' .access-type.'+type).prop('checked')) {
			$('#'+id+' .access-type').prop('checked', false);
			$('#'+id+' .access-type.'+type).prop('checked', true);
		}
	}

	/**
	 * Converts the acces_string into an array
	 */
	function parseAccess(access_string) {
		var access_split = access_string.split(',');
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
		return access_array;
	}

	/**
	 * This function configures the acces form based on the access string
	 *
	 * @param string id      Id of the table-row containing the access form
	 * @param string access  List of accesses to assign to the form
	 */
	function assignPermissions(id, access) {
		if (access == '') {
			changeType(id, 'none');
		} else if (access == 'all') {
			changeType(id, 'all');
		} else {
			changeType(id, 'custom'); // Change to custom access type
			// Convert the acces_string into an array
			var access_array = parseAccess(access);

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
					$(input).prop('checked', checked);
				}
			});
		}
	}

	/**
	 * Binds the events of a new group editing form
	 *
	 * @param int id Id of the div to be affected containing the form
	 */
	function bindEvents(id) {
		// Whenever the user type is changed
		$('#'+id+' .access-type').change(function() {
			changeType(id, $(this).val());
		});

		// Whenever the check or uncheck buttons are used
		$('#'+id+' .check-all').click(function() {
			changeType(id, 'custom');
			accessSelectAll(id);
		});

		$('#'+id+' .uncheck-all').click(function() {
			changeType(id, 'none');
		});

		// Whenever a checkbox is changed
		$('#'+id+' input[type="checkbox"]').change(function() {
			if ($('#'+id+' .user-rights input:checked').length == 0) {
				changeType(id, 'none');
			} else {
				changeType(id, 'custom');
			}
		});
	}

	/**
	 * This function is triggered whenever the group selector changes.
	 * It updates the access in the form according to the group selected access.
	 *
	 * @param string group_id Id of a group
	 */
	function accessGroup(group_id) {
		if (group_id == 0) {
			return;
		}

		// global var group_access is defined in the template file
		assignPermissions('user-access', group_access[group_id]);
	}

	$(document).ready(function() {
		bindEvents('user-access');

		// If user accesses are given, use them to update the form
		if (typeof user_access != 'undefined') {
			if (user_access != '') {
				assignPermissions('user-access', user_access);
			}
		}
	});

	$('#groupe').change(function() {
		accessGroup(this.options[this.selectedIndex].value);
	});

	return {
		bindEvents: bindEvents,
		assignPermissions: assignPermissions,
		changeType: changeType,
		parseAccess: parseAccess
	};
});
