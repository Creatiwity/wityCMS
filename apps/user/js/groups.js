/**
 * group.js
 *
 * This file needs access_form.js to work properly.
 * It handles the javascript animations for group management.
 */

require(['jquery', 'apps!user/access_form'], function($, accessForm) {
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
		if ($('#group-edit-'+groupid).length === 0) {
			var $clone = $('#group-add').clone().attr('id', 'group-edit-'+groupid);
			$clone.find('form').hide();
			$clone.find('h2').remove();

			$('<tr></tr>').append($('<td colspan="3"></td>').append($clone)).insertAfter('#group-'+groupid);

			$('#group-edit-'+groupid+' input[name="id"]').val(groupid);
			$('#group-edit-'+groupid+' input[name="name"]').val(name);
			$('#group-edit-'+groupid).removeClass('impair').addClass($('#group-'+groupid).attr('class'));

			// Bind change events to every inputs
			accessForm.bindEvents('group-edit-'+groupid);

			// Assign group permissions to inputs
			accessForm.assignPermissions('group-edit-'+groupid, access);
		}

		resetGroupForms();

		if ($('#group-edit-'+groupid+' form').css('display') == 'none') {
			$('#group-edit-'+groupid+' form').slideDown();
		}
	}

	$(document).ready(function() {
		accessForm.bindEvents('group-add');
	});

	$('#add_group_button').click(function() {
		showAddForm();

		return false;
	});

	$('body').on('click', '.group-edit-button', function() {
		var $this = $(this),
			groupId = $this.attr('data-group-id'),
			groupName = $this.attr('data-group-name'),
			groupAccess = $this.attr('data-group-access');

		showEditForm(groupId, groupName, groupAccess);

		return false;
	});

	$('body').on('click', '[data-reset-group-form]', function() {
		resetGroupForms();

		return false;
	});
});
