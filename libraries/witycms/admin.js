/**
 * Main javascript file for the wityCMS Admin.
 */

require(['jquery'], function($) {
	// Handle translatable fields
	$('.translatable-tabs a.lang').click(function(e) {
		var $this = $(this),
			previousLang = $('.translatable-tabs .active a').attr('href').replace('#', '');
		
		e.preventDefault();
		$this.tab('show');
		
		var lang = $this.attr('href').replace('#', '');
		
		$('.translatable .lang').addClass('hidden');
		$('.translatable .lang.' + lang).removeClass('hidden');
		
		lang = lang.replace('lang_', '');
		previousLang = previousLang.replace('lang_', '');
		
		// Update label's for attribute
		$('.translatable label').each(function(index, element) {
			var $this = $(this);
			$this.attr('for', $this.attr('for').replace(previousLang, lang));
		});
	});

	$('[data-reordable-url]').each(function(index, reordableTable) {
		var $reordableTable = $(reordableTable),
			url = $reordableTable.data('reordableUrl'),
			$parametersElement = $reordableTable.closest('[data-reordable-parameters]'),
			parameters = $parametersElement.length > 0 ? $parametersElement.data('reordableParameters') : {},
			$sortableElements,
			bindMove, unbindMove, $rootElement, moving = false;

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
				$rootElement.removeClass('reordering');
				$sortableElements.off('mouseenter');
				moving = false;

				if (url) {
					var data = $.extend({}, parameters);
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
});
