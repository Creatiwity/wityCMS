/**
 * Main javascript file for the wityCMS Admin.
 */

require(['jquery'], function($) {

	// Add translatable tabs
	
	var translatable_html = '<div role="tabpanel" style="margin-bottom: 1em;"><ul class="nav nav-tabs translatable-tabs" role="tablist">';

	translatable_html += '<li role="presentation" class="active"><a href="#lang_'+wity_enabled_langs[0].id+'" class="lang">'+wity_enabled_langs[0].name+'</a></li>';

	for (var i=1;i<wity_enabled_langs.length;++i) {
		translatable_html += '<li role="presentation"><a href="#lang_'+wity_enabled_langs[i].id+'" class="lang">'+wity_enabled_langs[i].name+'</a></li>';
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
		var $this = $(this);
		var $base = $this.clone();
		$this.html('');

		for (var i=0;i<wity_enabled_langs.length;++i) {

			var $current = $base.clone();

			$current.find('label').each(function() {
				var $that = $(this);
				$that.attr('for', $that.attr('for') + '_' + wity_enabled_langs[i].id);
			})

			$current.find('input, textarea').each(function(){
				var $that = $(this);
				$that.data('lang', wity_enabled_langs[i].id);

				if ($that.is('input')) {
					$that.value = js_values[$that.attr('name') + '_' + wity_enabled_langs[i].id];
				} else {
					$that.html(js_values[$that.attr('name') + '_' + wity_enabled_langs[i].id]);
				}

				$that.attr('name', $that.attr('name') + '_' + wity_enabled_langs[i].id);
				$that.attr('id', $that.attr('id') + '_' + wity_enabled_langs[i].id);
			});

			var classes = 'lang_' + wity_enabled_langs[i].id;
			if (i > 0) {
				classes += ' hidden';
			}

			$this.append('<div class="lang ' + classes + '">' + $current.html() + '</div>');
		}
	});

	var roxyFileman = wity_base_url + 'libraries/fileman/index.html',
	options = {
		filebrowserBrowseUrl: roxyFileman,
		filebrowserImageBrowseUrl: roxyFileman + '?type=image',
		removeDialogTabs: 'link:upload;image:upload',
		height: '500px'
	};

	$('.ckedit').each(function() {
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
