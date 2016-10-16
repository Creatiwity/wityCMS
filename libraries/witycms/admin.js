/**
 * Main javascript file for the wityCMS Admin.
 */

require(['jquery'], function($) {
	// Add translatable tabs
	var translatable_html = '<div role="tabpanel" style="margin-bottom: 1em;"><ul class="nav nav-tabs translatable-tabs" role="tablist">';

	for (var i = 0; i < wity_lang_enabled_langs.length; ++i) {
		var act = '';

		if (wity_lang_enabled_langs[i].id == wity_lang_default_id) {
			act = ' class="active"';
		}

		translatable_html += '<li role="presentation"'+act+'><a href="#lang_'+wity_lang_enabled_langs[i].id+'" class="lang">'+wity_lang_enabled_langs[i].name+'</a></li>';
	}

	translatable_html += '</ul></div>';

	$('#translatable-tabs').append(translatable_html);

	$('.translatable-tabs a.lang').click(function(e) {
		var $this = $(this);

		e.preventDefault();
		$this.tab('show');

		var lang = $this.attr('href').replace('#', '');

		$('.translatable .lang').addClass('hidden');
		$('.translatable .lang.' + lang).removeClass('hidden');
	});

	// Add translatable fields
	$('.translatable').each(function () {
		var $this = $(this),
			$base = $this.children().detach();

		for (var i = 0; i < wity_lang_enabled_langs.length; ++i) {
			var classes = 'lang_' + wity_lang_enabled_langs[i].id;
			if (wity_lang_enabled_langs[i].id != wity_lang_default_id) {
				classes += ' hidden';
			}

			var $current = $('<div class="lang ' + classes + '"></div>').append($base.clone());

			$current.find('label').each(function() {
				var $that = $(this);
				$that.attr('for', $that.attr('for') + '_' + wity_lang_enabled_langs[i].id);
			});

			$current.find('input, select, textarea').each(function() {
				var $that = $(this);
				$that.data('lang', wity_lang_enabled_langs[i].id);

				if ($that.attr('name').indexOf('[') !== -1) {
					$that.attr('name', $that.attr('name').replace('[', '_' + wity_lang_enabled_langs[i].id + '['));
				} else {
					$that.attr('name', $that.attr('name') + '_' + wity_lang_enabled_langs[i].id);
				}

				$that.attr('id', $that.attr('id') + '_' + wity_lang_enabled_langs[i].id);

				if (wity_lang_form_values) {
					if ($that.is('input[type="checkbox"]')) {
						$that.attr('checked', wity_lang_form_values[$that.attr('name')] == "1");
					} else if ($that.is('input') || $that.is('select')) {
						$that.attr('value', wity_lang_form_values[$that.attr('name')]);
					} else {
						$that.html(wity_lang_form_values[$that.attr('name')]);
					}
				}
			});

			$this.append($current);
		}
	});

	var roxyFileman = wity_base_url + 'libraries/fileman/index.html',
	options = {
		filebrowserBrowseUrl: roxyFileman,
		filebrowserImageBrowseUrl: roxyFileman + '?type=image',
		removeDialogTabs: 'link:upload;image:upload'
	};

	$('.ckedit').each(function() {
		var $this = $(this),
			height = $this.data('height') || '500px';

		options.height = height;

		if (CKEDITOR) {
			CKEDITOR.replace($(this).attr('id'), options);
		}
	});

	$('[data-reordable-url]').each(function(index, reordableTable) {
		var $reordableTable = $(reordableTable),
			url = $reordableTable.data('reordableUrl'),
			$parametersElement = $reordableTable.closest('[data-reordable-parameters]'),
			parameters = $parametersElement.length > 0 ? $parametersElement.data('reordableParameters') : {},
			$sortableElements,
			bindMove,
			unbindMove,
			$rootElement,
			moving = false;

		bindMove = function($element) {
			$sortableElements = $reordableTable.find('tr').not('thead>tr, tfoot>tr, tr.not-reordable');

			moving = true;

			$rootElement = $element;
			$rootElement.addClass('reordering');

			$sortableElements.on('mouseenter', function() {
				var $this = $(this),
					rootElementPosition,
					elementPosition;

				if (!$this.is($rootElement)) {
					rootElementPosition = $rootElement.index();
					elementPosition = $this.index();

					$rootElement.detach();

					if (rootElementPosition < elementPosition) {
						$rootElement.insertAfter($this);
					} else {
						$rootElement.insertBefore($this);
					}
				}
			});
		};

		unbindMove = function() {
			if (moving) {
				var data;

				$rootElement.removeClass('reordering');
				$sortableElements.off('mouseenter');
				moving = false;

				if (url) {
					data = $.extend({}, parameters);
				}

				$sortableElements.each(function(index, element) {
					var $element = $(element),
						position = $element.index();

					$element.find('.drag-handler .drag-group .position').text(position);

					if (url) {
						data['positions[' + $element.data('reordableId') + ']'] = position;
					}
				});

				if (url) {
					$.post(url, data, function(data, textStatus, jqXHR) {
						console.log(data);
					});
				}

				$reordableTable.trigger('witycms.reordered');
			}
		};

		$reordableTable.on('touchstart mousedown', 'tbody tr .drag-handler', function(event) {
			var $this = $(event.target);

			if (!moving) {
				bindMove($this.closest('tr'));
			}

			return false;
		});

		$('body').on('mouseup mouseleave touchend', unbindMove);
	});

	// Input counter
	$('input[maxlength].counter').each(function(index, element) {
		var $element = $(element),
			$parent = $element.parent(),
			$inputGroup,
			maxLength = $element.attr('maxlength'),
			$counterWrapper = $('<span class="input-group-addon">/' + maxLength + '</span>'),
			$counter = $('<span class="title-count">0</span>');

		$counter.prependTo($counterWrapper);

		if (!$parent.is('.input-group')) {
			$inputGroup = $('<div>').addClass('input-group');
			$inputGroup.appendTo($parent);
		} else {
			$inputGroup = $parent;
		}

		$element.detach().appendTo($inputGroup);
		$element.addClass('form-control');

		$inputGroup.append($counterWrapper);

		$element.on('input', function() {
			$counter.text($element.val().length);
		});

		$counter.text($element.val().length);
	});
});
