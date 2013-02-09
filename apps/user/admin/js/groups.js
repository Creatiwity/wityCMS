/**
 * group.js
 * 
 * This file needs access_form.js to work properly.
 * It handles the javascript animations for group management.
 */

/**
 * Resets all the forms opened
 */
function resetGroupForms() {
	$('.group-form form').each(function(index, form) {
		$(form).slideUp();
	});
}

/**
 * Opens the group adding form
 */
function showAddForm() {
	resetGroupForms();
	if ($('#group-add form').css('display') == 'none') {
		$('#group-add form').slideDown();
	}
}

/**
 * Opens the group editing form
 * 
 * @param int groupid Id of the group
 * @param string access  List of accesses to assign to the form
 */
function showEditForm(groupid, name, access) {
	// Form creation by clonage
	if ($('#group-edit-'+groupid).size() == 0) {
		var clone = $('#group-add').clone().attr('id', 'group-edit-'+groupid);
		clone.find('form').hide();
		clone.insertAfter('#group-'+groupid);
		$('#group-edit-'+groupid+' input[name="id"]').val(groupid);
		$('#group-edit-'+groupid+' input[name="name"]').val(name);
		$('#group-edit-'+groupid).removeClass('impair').addClass($('#group-'+groupid).attr('class'));
		
		// Bind change events to every inputs
		bindEvents('group-edit-'+groupid);
		
		// Assign group permissions to inputs
		assignPermissions('group-edit-'+groupid, access);
	}
	resetGroupForms();
	if ($('#group-edit-'+groupid+' form').css('display') == 'none') {
		$('#group-edit-'+groupid+' form').slideDown();
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
		if ($('#'+id+' .rights input:checked').size() == 0) {
			changeType(id, 'none');
		} else {
			changeType(id, 'custom');
		}
	});
}

$(document).ready(function() {
	bindEvents('group-add');
});

