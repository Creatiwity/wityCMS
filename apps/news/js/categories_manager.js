require(['jquery'], function($) {

	$(document).ready(function() {
		var model, currentEditedRow, backupRow, selectParent;

		model = ['news_cat_id', 'news_cat_name', 'news_cat_shortname', 'news_cat_parent'];
		selectParent = $("#selectParent").html();

		function buildEditRow(existingRow) {
			var row, datas, currentData, dataName;

			if (existingRow) {
				datas = $.parseJSON(existingRow.attr('data-wity-category'));
				$('#'+model[0]).val(datas[model[0]]);
			} else {
				datas = null;
			}

			row = $('<tr class="warning"></tr>');

			for (var ientry = 1, length = model.length; ientry < length; ientry++) {
				if (ientry !== 3) {
					if (datas) {
						currentData = datas[model[ientry]];
					} else {
						currentData = '';
					}
					dataName = model[ientry].substring(9);
					dataName = dataName.charAt(0).toUpperCase() + dataName.slice(1);
					row.append('<td><input type="text" class="form-control" name="' + model[ientry] + '" placeholder="' + dataName + '" value="' + currentData + '" /></td>');
				} else {
					if (datas) {
						currentData = datas[model[ientry]];
					} else {
						currentData = "0";
					}

					row.append($('<td>'+selectParent+'</td>').val(currentData));
				}
			}

			row.append('<td><button type="submit" class="btn btn-xs btn-success" title="Submit"><i class="glyphicon glyphicon-ok"></i></button> '
				+'<button type="button" class="btn btn-xs btn-danger cancel_cat_edit" title="Cancel"><i class="glyphicon glyphicon-remove"></i></button></td>');

			if(datas) {
				backupRow = existingRow;
				existingRow.before(row);
				existingRow.remove();
			} else {
				row.prependTo('#categories_body');
			}

			currentEditedRow = row;
		}

		function clean() {
			if(backupRow) {
				currentEditedRow && currentEditedRow.before(backupRow);
				currentEditedRow && currentEditedRow.remove();
				backupRow = null;
				currentEditedRow = null;
			} else {
				currentEditedRow && currentEditedRow.remove();
				currentEditedRow && (currentEditedRow = null);
			}
			$('#'+model[0]).val("");
		}

		$('body').on('click', '.wity_edit_category', function() {
			var row;
			clean();
			row = $(this).parents('tr');
			buildEditRow(row);
		});

		$('body').on('click', '#add_cat_button', function() {
			clean();
			buildEditRow();
		});

		$('body').on('click', '.cancel_cat_edit', function() {
			clean();
		});
	});

});
