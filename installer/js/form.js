/**
 * Form generator
 *
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.6.2-04-06-2018
 */
var Form, FormNode;

(function(){
	// FormNode states declaration (like enum)
	var	NOT_YET_VALIDATED = 0,            // Field never focused
		NOT_VALIDATED = 1,                // Field not validated
		NOT_VALIDATED_EMPTY_REQUIRED = 2, // Field required and empty
		VALIDATING = 3,                   // Field being validated, maybe ajax validation
		VALIDATED = 4,                    // Validated
		EMPTY_NOT_REQUIRED = 5;           // Empty but not required

	var updateNavBar = function() {
		var $formNavLi = $('.form-nav li'),
			$grayBar = $('.form-nav-bar .gray-bar'),
			$activeBar = $('.form-nav-bar .active-bar'),
			$formNavLiFirst = $formNavLi.first(),
			tabStatusFirstOffset = $formNavLiFirst.position(),
			tabStatusLastOffset = $formNavLi.last().position();

		$grayBar.css({
			top: tabStatusFirstOffset.top + 35,
			left: tabStatusFirstOffset.left + $formNavLiFirst.outerWidth() / 2,
			width: tabStatusLastOffset.left - tabStatusFirstOffset.left
		});

		var $formNavActiveLi = $('.form-nav li.valid, .form-nav li.active'),
			tabStatusActiveLastOffset = $formNavActiveLi.last().position();

		if ($formNavActiveLi.length > 0) {
			$activeBar.css({
				top: tabStatusFirstOffset.top + 35,
				left: tabStatusFirstOffset.left + $formNavLiFirst.outerWidth() / 2,
				width: tabStatusActiveLastOffset.left - tabStatusFirstOffset.left
			});
		}
	};

	/**
	 * Form class manages the whole form logic and view and initializes the FormNode elements
	 *
	 * @var options JSON object describing the form
	 */
	Form = (function() {

		function Form(options) {
			var id = options.id, that = this;

			this.id = id;

			this.handlers = {};

			this.isDebug = options.debug || false;

			this.debug('time', 'Form:' + id + ' initialize');

			if (!options || !options.id) {
				this.debug('timeEnd', 'Form:' + id + ' initialize');
				this.debug('error', "options parameter is missing or empty, unable to create form.");
				return;
			}

			// Set the <form> action attribute
			this.url = options.url ? options.url : document.location;

			// Initialize the context that will be passed through each FormNode instance
			this.context = {
				id: id,
				form: this,
				indexed: {}
			};

			// Register all form containers (alerts, submit button, ...)
			this.$alert = $('[data-wity-form-alert="' + id + '"]');
			this.$submit = $('[data-wity-form-submit="' + id + '"]');

			this.$tabs = $('[data-wity-form-tabs="' + id + '"]');
			if (this.$tabs.length > 0) {
				this.context.$tabs = this.$tabs;
			}

			this.$content = $('[data-wity-form-content="' + id + '"]');
			if (this.$content.length > 0) {
				// Store a reference to the root container of this form
				this.context.$content = this.$content;
			}

			// Register all success actions available in the DOM
			$('[data-wity-form-' + id + '-onsuccess]').each(function () {
				var $this = $(this), handler = $this.attr('data-wity-form-' + id + '-onsuccess');

				that.handlers.success = that.handlers.success || [];
				that.handlers.success.push([$this, handler]);
			});

			// Register all error actions available in the DOM
			$('[data-wity-form-' + id + '-onerror]').each(function () {
				var $this = $(this), handler = $this.attr('data-wity-form-' + id + '-onerror');

				that.handlers.error = that.handlers.error || [];
				that.handlers.error.push([$this, handler]);
			});

			// Root node
			options.root = true;
			this.node = new FormNode(options, this.context, this);

			// Bind submit action
			this.$submit.on('click', function() {
				var $this = $(this);

				if (that.validated) {
					$this.button('loading');
					that.validate();
				}
			});

			// Replace submit button
			$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
				var $tab = $(e.target);

				updateNavBar();

				if ($tab.parent().index() == $('[data-wity-form-tabs] li').length - 1) {
					var $nextButton = $($tab.attr('href')).find('.next-button');

					if ($nextButton.length > 0) {
						$nextButton.replaceWith(that.$submit.detach());
					}
				}
			});

			// Bar
			updateNavBar();
			$(window).resize(updateNavBar);

			// Initialize all submit buttons statuses to disabled
			this.setButtonStatus(false);

			this.ajax({
				installer: this.id,
				command: 'INIT_INSTALLER'
			}, null, this);

			this.debug('timeEnd', 'Form:' + id + ' initialize');
		}

		Form.prototype.validate = function() {
			var options = {
				installer: this.id,
				command: "FINISH_INSTALLATION"
			};

			$.extend(options, this.node.getValues());

			// Trigger XHR request to launch the PHP installation
			this.ajax(options, null, this);
		};

		// Update form status (submit button enabled or not) based on root node status
		Form.prototype.updateStatus = function() {
			this.validated = (this.node.validated === VALIDATED || this.node.validated === EMPTY_NOT_REQUIRED);

			this.debug('Form:%s is now %s.', this.id, (this.validated ? 'validated' : 'not validated'));

			this.setButtonStatus(this.validated);
		};

		// Set submit button to the correct state, enabled or disabled with the right text in it (Loading or Not validated)
		Form.prototype.setButtonStatus = function(state) {
			var that = this;

			if (!state) {
				this.debug('Form:%s submit button DISABLED', this.id);
				this.$submit.button('remaining');
				setTimeout(function() {that.$submit.attr("disabled", "disabled");}, 0);
			} else {
				this.debug('Form:%s submit button ENABLED', this.id);
				this.$submit.button('reset');
				setTimeout(function() {that.$submit.removeAttr("disabled");}, 0);
			}
		};

		// Execute a callback on ajax success
		Form.prototype.getAjaxHtml = function(url, data, callback, context) {
			var _url = url || this.url;

			this.debug('GET ajax HTML request on %s with %O.', url, data);

			$.ajax({
				url: _url,
				data: data,
				success: callback,
				type: 'GET'
			});
		};

		// Ajax internal function to test FormNode validity remotely when needed
		Form.prototype.ajax = function(sData, callback, context) {
			var realCallback, that = this;

			// show loading
			this.debug('POST ajax request on %s with %O.', this.url, sData);

			realCallback = function(data, textStatus, jqXHR) {
				var json, installerData;

				try {
					json = $.parseJSON(data);
				} catch(e) {
					// Display debug
					that.debug('error', 'Form:ajax response error from url %s with data: %O\nResponse: %s', that.url, sData, data);
					return;
				}

				// process json
				if (json.installer) {
					installerData = json.installer;
					delete json.installer;

					that.decode(installerData);
				}

				//process callback ?
				if (callback) {
					return callback.call(context, json);
				}
			};

			$.ajax({
				url: this.url,
				data: sData,
				success: realCallback,
				type: 'POST'
			});
		};

		/**
		 * Decode remote validation response
		 */
		Form.prototype.decode = function(installerData) {
			var validated = false;

			if (installerData && installerData[this.id]) {
				if (installerData[this.id].success) {
					validated = true;
				}

				if (installerData[this.id].warning) {
					this.displayNotes(installerData[this.id].warning, 'alert-warning');
				}

				if (installerData[this.id].error) {
					validated = false;
					this.displayNotes(installerData[this.id].error, 'alert-danger');
					this.finishError();
				}

				if (installerData[this.id].info) {
					this.displayNotes(installerData[this.id].success, 'alert-info');
				}
			}

			if (validated) {
				this.finishSuccess();
			}

			return validated;
		};

		// Display notes
		Form.prototype.displayNotes = function(notes, classes) {
			var that = this;

			if (this.$alert) {
				$.each(notes, function(index, r) {
					var alert = $('<div class="alert alert-dismissable">'+
						'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'+
					'</div>');
					$('<strong>'+r.head_message+'</strong>').appendTo(alert);
					alert.append(' '+r.message);
					alert.appendTo(that.alertContainer);
					alert.addClass(classes);
					that.$alert.append(alert);
				});
			}
		};

		Form.prototype.finishSuccess = function() {
			// Do success
			var _i, _len;

			if (this.handlers && this.handlers.success) {
				for (_i = 0, _len = this.handlers.success.length; _i < _len; ++_i) {
					if (this.handlers.success[_i][1] === 'show') {
						this.handlers.success[_i][0].removeClass('hide hidden').show();
					} else if (this.handlers.success[_i][1] === 'hide') {
						this.handlers.success[_i][0].hide();
					} else if (this.handlers.success[_i][0][this.handlers.success[_i][1]]) {
						this.handlers.success[_i][0][this.handlers.success[_i][1]]();
					}
				}
			}
		};

		Form.prototype.finishError = function() {
			// Do error
			var _i, _len;

			if (this.handlers && this.handlers.error) {
				for (_i = 0, _len = this.handlers.error.length; _i < _len; ++_i) {
					if (this.handlers.error[_i][1] === 'show') {
						this.handlers.error[_i][0].removeClass('hide hidden').show();
					} else if (this.handlers.error[_i][1] === 'hide') {
						this.handlers.error[_i][0].hide();
					} else if (this.handlers.error[_i][0][this.handlers.error[_i][1]]) {
						this.handlers.error[_i][0][this.handlers.error[_i][1]]();
					}
				}
			}
		};

		// Debug logger
		Form.prototype.debug = function() {
			var params, type = 'debug';
			if (this.isDebug && arguments.length > 0) {
				params = Array.prototype.slice.call(arguments);

				if (console[params[0]]) {
					type = params.shift();
				}

				if (params.length > 0) {
					console[type].apply(console, params);
				}
			}
		};

		return Form;

	})();

	/**
	 * FormNode is the base class of all form elements. It can be real or virtual and hierarchically organised.
	 *
	 * @var options JSON object describing this node and its descendent
	 * @var context is the context initialized in the Form object. Used to call form methods and communicate with the whole form.
	 * @var parent is the parent node instance
	 */
	FormNode = (function() {

		var tabCounter = 0;

		/**
		 * Validation updateStatus
		 */

		function FormNode(options, context, parent) {
			var _i, _len, $tabTitle, $tabStatus, $tabContainer,
				$tabContent, $summaryLi, $summaryStatus,
				$alertContainer, $fieldContainer, $field,
				validationTimer, contentCache, that = this, willValidate = false;

			// Check context validity
			if (!options || !context || !context.id || !context.form || !context.$content || context.$content.length === 0) {
				throw "context passed to FormNode isn't valid.";
			}

			this.context = context;

			this.debug('time', 'FormNode:' + options.id + ' initialize');

			// Index this node in the context, will be used to access all nodes by their node's id
			context.indexed[options.id] = this;

			this.id = options.id;
			// views lists all the views to update each time this node changes its status. Each view has an 'updater()' function.
			this.views = [];
			// Keep a pointer to the index in this node to have a faster access to the nodes of this Form
			this.indexed = context.indexed;
			this.parent = parent;
			// The root node is only here as a container and do the relation between all FormNode instances and the related Form instance
			this.root = (options.root === true);

			this.name = options.name || this.id;

			// This node is required if specified or if it's the root node
			this.required = (options.required === true) || (options.root === true);

			if (options.image) {
				this.image = options.image;
			}

			if (options.introduction) {
				this.introduction = options.introduction;
			}

			// getValues will returns the values of the "requires" node
			this.requires = options.requires;

			// Initialize the status to not yet validated, to avoid empty required node to be not validated at the beginning of the process
			this.validated = NOT_YET_VALIDATED;
			// A summary (optional) can be bound to each node, by default it's initialized to false
			// A real node will have a summary by default, a virtual not, will be treated after
			this.summary = false;

			// Contains the help message to display on a field
			this.helpMessage = '';

			// validateDatas store all the validation datas needed to validate this node locally and/or remotely
			this.validateDatas = options.validate || {};

			// If true, synchronize not validated state view on childs
			this.errorsOnChilds = options.errorsOnChilds;

			// Stores all fields that we must try to validate after this one
			this.validateAfter = [];

			this.validEmpty = options.validEmpty;

			if (this.requires) {
				this.indexed[this.requires].validateAfter.push(this.id);
			}

			// Build FormNode in DOM and in logic

			// context.$content refers to the current DOM element on which children will append their content
			// We have to cache it now, it will be restored to its initial value after children contruction
			contentCache = context.$content;

			if (options.root === true) {
				// Do nothing
			} else if (options.tab && options.tab === true) {
				// Tab node: it's like a virtual node but with a tab view
				if (context.$tabs.length > 0) {
					// Add the tab only if a place has been reserved to place it
					$tabStatus = $('<i class="glyphicon"></i>');
					$tabTitle = $('<a href="#form_' + context.id + '_' + this.id + '" data-toggle="tab"></a>').append($('<span class="icon"></span>').append($tabStatus)).append(' ' + this.name);
					$tabTitle = $('<li></li>').append($tabTitle);

					// Set tab content container
					$tabContainer = $('<div class="tab-pane" id="form_' + context.id + '_' + this.id + '"></div>');

					if (this.image || this.introduction) {
						var $introduction = $('<div class="introduction"></div>');

						if (this.image) {
							$introduction.append('<img src="'+this.image+'" alt="" class="img-responsive" />');
						}

						if (this.introduction) {
							$introduction.append(this.introduction);
						}

						$introduction.append('<div class="clearfix"></div>');

						$tabContainer.append($introduction);
					}

					$tabContent = $('<fieldset class="form-horizontal"></fieldset>').appendTo($tabContainer);

					if (tabCounter > 0) {
						$prevButton = $('<button type="button" class="btn btn-default btn-lg prev-button">Back</button>').appendTo($tabContainer);
						$prevButton.on('click', function() {
							$tabTitle.prev().find('a').trigger('click');
						});
					}

					$nextButton = $('<button type="button" class="btn btn-primary btn-lg pull-right next-button">Next</button>').appendTo($tabContainer);
					$nextButton.on('click', function() {
						$tabTitle.next().find('a').trigger('click');
					});

					// Set the first tab added as active
					if (tabCounter === 0) {
						$tabTitle.addClass('active');
						$tabContainer.addClass('active');
					}

					context.$tabs.append($tabTitle);
					context.$content.append($tabContainer);

					context.$content = $tabContent;

					// Pushing view updater to update tab status
					this.views.push({
						updater: function(state) {
							var vstate = state || that.validated,
								$li = $tabStatus.closest('li');
							$tabStatus.removeClass('glyphicon-remove glyphicon-ok');
							$li.removeClass('valid invalid');

							switch (vstate) {
								case NOT_YET_VALIDATED:
								case NOT_VALIDATED:
								case NOT_VALIDATED_EMPTY_REQUIRED:
									$tabStatus.addClass('glyphicon-remove');
									$li.addClass('invalid');
									break;

								case EMPTY_NOT_REQUIRED:
								case VALIDATED:
									$tabStatus.addClass('glyphicon-ok');
									$li.addClass('valid');
									break;
							}

							updateNavBar();
						}
					});

					tabCounter++;
				}
			} else if (!options.virtual) {
				// A real node with a field

				// Treat the summary option
				this.summary = options.summary !== false;
				// Field type is text by default
				this.type = options.type || "text";

				$alertContainer = $('<div></div>');
				$fieldContainer = $('<div class="form-group"></div>');

				if (this.type === "select") {
					// Select field
					$field = $('<select id="' + this.id + '" name="' + this.id + '" class="form-control"></select>');

					willValidate = true;

					if ($.isArray(options.options)) {
						// Preconfigured values
						for (_i = 0, _len = options.options.length; _i < _len; ++_i) {
							$field.append('<option value="' + options.options[_i].value + '">' + options.options[_i].text + '</option>');
						}

						// Set the default specified value if it exists
						if (options.value) {
							$field.val(options.value);
						}

					} else if (options.options.url) {
						// Ajax generated values
						context.form.getAjaxHtml(options.options.url, null, function (data) {
							$field.append(data);

							// Set the default specified value if it exists
							if (options.value) {
								$field.val(options.value);
							}

							// Async call so willValidate will not be checked
							that.validate();
						});
					} else if (options.options.command) {
						// Ajax generated values
						context.form.ajax({
							command: options.options.command,
							installer: context.form.id
						}, function (data) {
							if (data.content && data.content[options.options.command]) {
								$field.append(data.content[options.options.command]);

								// Set the default specified value if it exists
								if (options.value) {
									$field.val(options.value);
								}

								// Async call so willValidate will not be checked
								that.validate();
							}
						});
					} else {
						// No option provided, error?
						console.debug("No option provided for " + this.name + " in the form named " + context.id);
					}
				} else {
					// All other cases (text, number, email, ...)
					$field = $('<input id="' + this.id + '" name="' + this.id + '" type="' + this.type + '" class="form-control"></input>');

					if (options.placeholder) {
						$field.attr('placeholder', options.placeholder);
					}

					// Set the default specified value if it exists
					if (options.value) {
						$field.val(options.value);

						willValidate = true;
					}
				}

				// Store the field
				this.$field = $field;

				this.$helpBlock = $('<div class="help-block"></div>');

				// Add it in the view
				context.$content.append($alertContainer);
				context.$content.append($fieldContainer);
				$fieldContainer.append('<label class="col-md-4 control-label" for="' + this.id + '">' + this.name + (this.required ? '*' : '') + '</label>');
				$fieldContainer.append($('<div class="col-md-8"></div>').append($field).append(this.$helpBlock));

				// Push the field updater in the views stack
				this.views.push({
					updater: function(state) {
						var vstate = state || that.validated;
						$fieldContainer.removeClass('has-success has-error has-warning');

						switch (vstate) {
							case NOT_YET_VALIDATED:
								break;

							case NOT_VALIDATED:
								$fieldContainer.addClass('has-error');
								break;

							case NOT_VALIDATED_EMPTY_REQUIRED:
								$fieldContainer.addClass('has-error');
								break;

							case VALIDATING:
								break;

							case VALIDATED:
								$fieldContainer.addClass('has-success');
								break;

							case EMPTY_NOT_REQUIRED:
								break;
						}

						if (that.helpMessage !== '') {
							that.$helpBlock.removeClass('hidden');
						} else {
							that.$helpBlock.addClass('hidden');
						}

						that.$helpBlock.html(that.helpMessage);
					}
				});

				// Bind validator to the blur and change events
				$field.on('input change propertychange', function() {
					if (validationTimer) {
						clearTimeout(validationTimer);
					}

					// Calls the validation only one time at the end of all events treatment
					validationTimer = setTimeout(function() {that.validate();}, 1000);
				});

			}

			if (this.summary) {
				// Add the summary to the view
				$summaryStatus = $('<i class="glyphicon"></i>');

				$fieldContainer.find('label').prepend(' ').prepend($summaryStatus);

				// Push the summary view to the view stack of this node
				this.views.push({
					updater: function(state) {
						var vstate = state || that.validated;
						$summaryStatus.removeClass('glyphicon-remove glyphicon-ok');

						switch (vstate) {
							case NOT_YET_VALIDATED:
								break;

							case NOT_VALIDATED:
							case NOT_VALIDATED_EMPTY_REQUIRED:
								$summaryStatus.addClass('glyphicon-remove');
								break;

							case VALIDATING:
								break;

							case VALIDATED:
								$summaryStatus.addClass('glyphicon-ok');
								break;

							case EMPTY_NOT_REQUIRED:
								break;
						}
					}
				});
			}

			// Constructs all children of this node
			this.childs = [];

			if (options.childs && options.childs.length > 0) {
				for (_i = 0, _len = options.childs.length; _i < _len; ++_i) {
					this.childs.push(new FormNode(options.childs[_i], context, this));
				}
			}

			// Restore $content reference in the context
			context.$content = contentCache;

			// Add an <hr /> separator if specified
			if (options.hr === true) {
				context.$content.append('<hr />');
			}

			// Render this node
			this.render();

			if (willValidate) {
				setTimeout(function() {that.validate();}, 250);
			}

			this.debug('timeEnd', 'FormNode:' + options.id + ' initialize');
		}

		// Shortcut to compare current state with VALIDATED
		FormNode.prototype.isValidated = function() {
			return (this.validated === VALIDATED);
		};

		/**
		 * Return a JSON object containing all the values of this node and its children as a {node_id: value} pair
		 */
		FormNode.prototype.getValues = function() {
			var _i, _len, values = {};

			if (this.$field && this.$field.length > 0) {
				values[this.id] = this.$field.val();
			}

			for (_i = 0, _len = this.childs.length; _i < _len; ++_i) {
				$.extend(values, this.childs[_i].getValues());
			}

			if (this.requires && this.indexed[this.requires]) {
				$.extend(values, this.indexed[this.requires].getValues());
			}

			return values;
		};

		/**
		 * Set a flag on this node to notify changes on the content of this node and propagate it to the parents nodes
		 * The changed flag will be reset
		 */
		FormNode.prototype.setChanged = function() {
			this.debug('warn', 'FormNode:%s has CHANGED.', this.id);

			this.changed = true;
			this.parent && this.parent.setChanged && this.parent.setChanged();
		};

		/**
		 * Refresh and get the 'changed' flag state.
		 *
		 * For a real node, if the value of the field and the internal value are different, setChanged() is called and true returned,
		 * and if swap is set to true, the internal value will be synchronized with the one in the field
		 * For others, calling this method will reset the 'changed' flag if it was true. If not, if one children hasChanged(), we will return true.
		 *
		 * @var swap If true the current value of the field will be synchronized with
		 * 				the internal value of the node, corresponding to the tested one (valid or not)
		 */
		FormNode.prototype.hasChanged = function(swap) {
			var _i, _len, changed, currentValue;

			if (this.$field && this.$field.length > 0) {
				currentValue = this.$field.val();

				changed = (this.value !== currentValue);

				if (swap === true && changed === true) {
					this.value = currentValue;
					this.setChanged();
				}

				return changed;
			}

			if (this.changed !== undefined && this.changed !== null && this.changed === true) {
				if (swap === true) {
					this.changed = false;
				}

				return true;
			}

			for (_i = 0, _len = this.childs.length; _i < _len; ++_i) {
				if (this.childs[_i].hasChanged() === true) {
					return true;
				}
			}

			return false;
		};

		/**
		 * Return true if this field is empty, or if it has children, if all of them are empty too, false otherwise
		 */
		FormNode.prototype.isEmpty = function() {
			var value;

			if (this.$field && this.$field.length > 0) {
				value = this.$field.val();
				return (value === undefined || value === null || value === "");
			}

			for (_i = 0, _len = this.childs.length; _i < _len; ++_i) {
				if (this.childs[_i].isEmpty() === false) {
					return false;
				}
			}

			return true;
		};

		/**
		 * Return true if this node or one of its children have the focus
		 */
		FormNode.prototype.hasFocus = function() {
			var _i, _len;

			if (this.$field && this.$field.length > 0) {
				if (this.$field.is(':focus')) {
					return true;
				}
			}

			for (_i = 0, _len = this.childs.length; _i < _len; ++_i) {
				if (this.childs[_i].hasFocus() === true) {
					return true;
				}
			}

			return false;
		};

		/**
		 * Key function that will choose to trigger validation or not in order to update the status of this node
		 */
		FormNode.prototype.updateStatus = function() {
			var _i, _len, child, childrenValid = true, validating = false, causeId = '', cause = 0;

			// Test all childs
			for (_i = 0, _len = this.childs.length; _i < _len; ++_i) {
				child = this.childs[_i];

				// Not validated
				// Or Validate()

				if (child.validated === NOT_VALIDATED ||
					child.validated === NOT_VALIDATED_EMPTY_REQUIRED ||
					(child.validated === NOT_YET_VALIDATED && child.required === true)) {
					causeId = child.id;
					cause = 1;
					validating = false;
					childrenValid = false;
					break;
				} else if (child.validated === VALIDATING) {
					causeId = child.id;
					cause = 2;
					validating = true;
					childrenValid = false;
				}
			}

			if (!childrenValid) {
				this.debug('warn', "FormNode::%s has been INVALIDATED by FormNode:%s in an updateStatus call", this.id, causeId);

				if (validating) {
					this.validated = VALIDATING;
				} else {
					this.validated = NOT_VALIDATED;
				}

				this.render();
				this.triggerParentUpdate();
			} else {
				this.debug('warn', "FormNode::%s VALIDATING (updateStatus call)", this.id);

				this.validate();
			}
		};

		// Shortcut method to trigger an updateStatus() on the parent of this ndoe
		FormNode.prototype.triggerParentUpdate = function() {
			this.parent.updateStatus();
		};

		// Validate function
		// force parameter forces validate to run even if the content hasn't changed
		FormNode.prototype.validate = function(force) {
			var _i, _len, empty, regexp, compareValue,
				localValid = true,  // If true, local validation passed successfully
				localMessage,       // Contains the local message used to explain the error to the user
				remoteValid = true, // If true, remote validation passed successfully
				that = this,
				options,
				values,
				_val;

			empty = this.isEmpty();

			// Check if the value changed and synchronize the field value with the internal one
			// If the value didn't change, no need to try validating again
			if ((((force && (this.validated !== NOT_YET_VALIDATED || this.validEmpty)) || !force) && this.hasChanged(true)) || (force && this.validated !== NOT_YET_VALIDATED)) {
				// Validating state
				this.validated = VALIDATING;

				this.helpMessage = '';

				if (this.required && empty) {
					// Empty and required
					this.validated = NOT_VALIDATED_EMPTY_REQUIRED;
					localValid = false;
				} else if (empty && !this.validEmpty) {
					// Empty but not required
					this.validated = EMPTY_NOT_REQUIRED;
					localValid = false;
				} else if (this.requires && this.indexed[this.requires].validated !== VALIDATED) {
					this.validated = NOT_VALIDATED;
					localValid = false;
				} else {
					// Local validation needed
					if (this.validateDatas.local) {
						if (this.validateDatas.local.type === "regexp") {
							// Regexp validation
							regexp = new RegExp(this.validateDatas.local.options, "i");
							localValid = regexp.test(this.value);
							localMessage = this.validateDatas.local.message;
						} else if (this.validateDatas.local.type === "equals") {
							// Equals validation
							values = this.getValues();

							$.each(values, function(index) {
								if (_val) {
									localValid = localValid && (_val === values[index]);
								} else {
									_val = values[index];
								}
							});
							localMessage = this.validateDatas.local.message;
						}

						// Local validation failed
						if (!localValid) {
							this.helpMessage += localMessage;

							this.validated = NOT_VALIDATED;
						}
					}

					// If remote validation needed
					if (localValid && this.validateDatas.remote) {
						// Send data to the server
						// Register callback to expose returned message and set status
						options = {
							command: 'REMOTE_VALIDATION',
							installer: this.context.form.id,
							group: this.validateDatas.remote
						};

						$.extend(options, this.getValues());

						this.context.form.ajax(options, function(response) {
							that.debug('warn', 'FormNode:%s response received %O.', that.id, response);

							if (that.decode(response)) {
								that.debug('warn', "FormNode:%s is VALIDATED", that.id);

								that.validated = VALIDATED;
							} else {
								that.debug('warn', "FormNode:%s is NOT VALIDATED", that.id);

								that.validated = NOT_VALIDATED;
							}

							that.render();

							for (_i = 0, _len = this.validateAfter.length; _i < _len; ++_i) {
								this.indexed[this.validateAfter[_i]].validate(true);
							}

							that.triggerParentUpdate();
						} ,this);

						return;
					}
				}

				if (localValid && remoteValid) {
					this.debug('warn', "FormNode:%s is VALIDATED", this.id);

					this.validated = VALIDATED;
				}

				this.render();

				for (_i = 0, _len = this.validateAfter.length; _i < _len; ++_i) {
					this.indexed[this.validateAfter[_i]].validate(true);
				}
			}

			// Must be triggered in any case
			this.triggerParentUpdate();
		};

		/**
		 * Decode remote validation response
		 */
		FormNode.prototype.decode = function(response) {
			var validated = false;

			if (response.installer) {
				//this.context.form.decode(response);
			}

			this.helpMessage = '';

			if (response.group && response.group[this.validateDatas.remote]) {
				if (response.group[this.validateDatas.remote].success) {
					validated = true;
				}

				if (response.group[this.validateDatas.remote].warning) {
					validated = true;
					this.displayNotes(response.group[this.validateDatas.remote].warning, 'text-warning');
				}

				if (response.group[this.validateDatas.remote].error) {
					validated = false;
					this.displayNotes(response.group[this.validateDatas.remote].error, 'text-danger');
				}

				if (response.group[this.validateDatas.remote].info) {
					this.displayNotes(response.group[this.validateDatas.remote].success, 'text-info');
				}
			}

			return validated;
		};

		/**
		 * Displays notes on the group
		 *
		 * @param array notes
		 * @param string CSS Classes to be used for this set of notes (alert-error, alert-info)
		 */
		FormNode.prototype.displayNotes = function(notes, classes) {
			var that = this;
			$.each(notes, function(index, r) {
				var alert = '<span class="' + classes + '">' +
					'<strong>' +
						r.head_message +
					'</strong> ' +
					r.message +
				'</span>';

				if (that.helpMessage !== '') {
					that.helpMessage += '<br/>';
				}

				that.helpMessage += alert;
			});
		};

		/**
		 * Render method will update all views that are placed in the views stack of this node
		 */
		FormNode.prototype.render = function(state, onChilds) {
			var _i, _len, realState = state || this.validated;

			for (_i = 0, _len = this.views.length; _i < _len; ++_i) {
				this.views[_i].updater(state);
			}

			if (this.errorsOnChilds || onChilds) {
				for (_i = 0, _len = this.childs.length; _i < _len; ++_i) {
					this.childs[_i].render(realState, true);
				}
			}
		};

		FormNode.prototype.debug = function() {
			if (this.context && this.context.form && this.context.form) {
				this.context.form.debug.apply(this.context.form, arguments);
			}
		};

		return FormNode;

	})();
}());
