/**
 * group.js
 *
 * This file needs access_form.js to work properly.
 * It handles the javascript animations for group management.
 */

require(['jquery'], function($) {

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

	$(document).ready(function() {
		bindEvents('group-add');
	});

});
