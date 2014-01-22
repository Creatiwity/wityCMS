/**
 * Script for Installer module
 *
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4
 */
$(document).ready(function() {

	var Form, FormNode,
		// FormNode states declaration (like enum)
		NOT_YET_VALIDATED = 0,				// Field never focused
		NOT_VALIDATED = 1,					// Field not validated
		NOT_VALIDATED_EMPTY_REQUIRED = 2,	// Field required and empty
		VALIDATING = 3,						// Field being validated, maybe ajax validation
		VALIDATED = 4,						// Validated
		EMPTY_NOT_REQUIRED = 5;				// Empty but not required

	/**
	 * Form class manages the whole form logic and view and initializes the FormNode elements
	 *
	 * @var options JSON object describing the form
	 */
	Form = (function() {

		function Form(options) {
			var handlers = {}, id = options.id;

			this.id = id;

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

			this.$summary = $('[data-wity-form-summary="' + id + '"]');
			if (this.$summary.length > 0) {
				this.context.$summary = this.$summary;
			}

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

				handlers.success = handlers.success || [];
				handlers.success.push([$this, handler]);
			});

			// Register all failure actions available in the DOM
			$('[data-wity-form-' + id + '-onfailure]').each(function () {
				var $this = $(this), handler = $this.attr('data-wity-form-' + id + '-onfailure');

				handlers.failure = handlers.failure || [];
				handlers.failure.push([$this, handler]);
			});

			// Root node
			options.root = true;
			this.node = new FormNode(options, this.context, this);

			// Bind submit action
			this.$submit.on('click', function() {
				var $this = $(this);

				if (this.validated) {
					$this.button('loading');
				}
			});

			// Initialize all submit buttons statuses to disabled
			this.setButtonStatus(false);

			this.debug('timeEnd', 'Form:' + id + ' initialize');
		}

		// Update form status (submit button enabled or not) based on root node status
		Form.prototype.updateStatus = function() {
			this.validated = (this.node.validated === VALIDATED || this.node.validated === EMPTY_NOT_REQUIRED);

			this.debug('Form:%s is now %s.', this.id, (this.validated ? 'validated' : 'not validated'));

			this.setButtonStatus(this.validated);
		};

		// Set submit button to the correct state, enabled or disabled with the right text in it (Loading or Not validated)
		Form.prototype.setButtonStatus = function(state) {
			var that = this;

			if(!state) {
				this.debug('Form:%s submit button DISABLED', this.id);
				this.$submit.button('remaining')
				setTimeout(function() {that.$submit.attr("disabled", "disabled");}, 0);
			} else {
				this.debug('Form:%s submit button ENABLED', this.id);
				this.$submit.button('reset');
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
		}

		// Ajax internal function to test FormNode validity remotely when needed
		Form.prototype.ajax = function(sData, callback, context) {
			var realCallback, that = this;

			// show loading
			this.debug('POST ajax request on %s with %O.', this.url, sData);

			realCallback = function(data, textStatus, jqXHR) {
				var json;

				try {
					json = $.parseJSON(data);
				} catch(e) {
					// Display debug
					this.debug('error', 'Form:ajax response error from url %s with data: %O\nResponse: %s', this.url, sData, data);
					return;
				}

				// process json

				//process callback ?
				if(callback) {
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
			// Initialize the status to not yet validated, to avoid empty required node to be not validated at the beginning of the process
			this.validated = NOT_YET_VALIDATED;
			// A summary (optional) can be bound to each node, by default it's initialized to false
			// A real node will have a summary by default, a virtual not, will be treated after
			this.summary = false;

			// validateDatas store all the validation datas needed to validate this node locally and/or remotely
			this.validateDatas = options.validate || {};

			// Special treatment for the 'equals' validator that need to bind two nodes
			if (this.validateDatas.local && this.validateDatas.local.type === "equals") {
				$.extend(true, this.indexed[this.validateDatas.local.options].validateDatas, this.validateDatas);
				this.indexed[this.validateDatas.local.options].validateDatas.options = this.id;
			}

			// Build validate

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
					$tabTitle = $('<a href="#form_' + context.id + '_' + this.id + '" data-toggle="tab"></a>').append($tabStatus).append(' ' + this.name);
					$tabTitle = $('<li></li>').append($tabTitle);

					// Set tab content container
					$tabContainer = $('<div class="tab-pane" id="form_' + context.id + '_' + this.id + '"></div>');
					$tabContent = $('<fieldset class="form-horizontal"></fieldset>').appendTo($tabContainer);

					// Set the first tab added as active
					if (tabCounter === 0) {
						$tabTitle.addClass('active');
						$tabContainer.addClass('active');
					}

					context.$tabs.append($tabTitle);
					context.$content.append($tabContainer);

					if (context.$summary.length > 0) {
						context.$summary.append($('<h4>' + this.name + '</h4>'));
					}

					context.$content = $tabContent;

					// Pushing view updater to update tab status
					this.views.push({
						updater: function() {
							$tabStatus.removeClass('glyphicon-remove glyphicon-ok');

							switch (that.validated) {
								case NOT_YET_VALIDATED:
								$tabStatus.addClass('glyphicon-remove');
								break;

								case NOT_VALIDATED:
								$tabStatus.addClass('glyphicon-remove');
								break;

								case NOT_VALIDATED_EMPTY_REQUIRED:
								$tabStatus.addClass('glyphicon-remove');
								break;

								case EMPTY_NOT_REQUIRED:
								$tabStatus.addClass('glyphicon-ok');
								break;

								case VALIDATED:
								$tabStatus.addClass('glyphicon-ok');
								break;
							}
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
					$field = $('<select name="' + this.id + '" class="form-control"></select>');

					willValidate = true;

					if ($.isArray(options.options)) {
						// Preconfigured values
						for (_i = 0, _len = options.options.length; _i < _len; ++_i) {
							$field.append('<option value="' + options.options[_i].value + '">' + options.options[_i].text + '</option>')
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
					$field = $('<input type="' + this.type + '" class="form-control"></input>');

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

				// Add it in the view
				context.$content.append($alertContainer);
				context.$content.append($fieldContainer);
				$fieldContainer.append('<label class="col-md-3 control-label" for="' + this.id + '">' + this.name + '</label>')
				$fieldContainer.append($('<div class="col-md-9"></div>').append($field));

				// Push the field updater in the views stack
				this.views.push({
					updater: function() {
						$fieldContainer.removeClass('has-success has-error has-warning');

						switch (that.validated) {
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
					}
				});

				// Bind validator to the blur and change events
				$field.on('input propertychange', function() {
					if (validationTimer) {
						clearTimeout(validationTimer);
					}

					// Calls the validation only one time at the end of all events treatment
					validationTimer = setTimeout(function() {that.validate();}, 1000);
				});

			} else {
				// A virtual field can have a summary if specified (two password fields that have to appear only once in the summary)
				this.summary = (options.summary === true);
			}

			if (this.summary && context.$summary.length > 0) {
				// Add the summary to the view
				$summaryStatus = $('<i class="glyphicon"></i>');
				$summaryLi = $('<li></li>');

				$summaryLi.append($summaryStatus);
				$summaryLi.append(' ' + this.name);

				context.$summary.append($summaryLi);

				// Push the summary view to the view stack of this node
				this.views.push({
					updater: function() {
						$summaryLi.removeClass('text-success text-danger text-warning text-primary text-muted');
						$summaryStatus.removeClass('glyphicon-remove glyphicon-ok');

						switch (that.validated) {
							case NOT_YET_VALIDATED:
							if (that.required) {
								$summaryLi.addClass('text-primary');
							}
							break;

							case NOT_VALIDATED:
							$summaryLi.addClass('text-danger');
							$summaryStatus.addClass('glyphicon-remove');
							break;

							case NOT_VALIDATED_EMPTY_REQUIRED:
							$summaryLi.addClass('text-danger');
							$summaryStatus.addClass('glyphicon-remove');
							break;

							case VALIDATING:
							$summaryLi.addClass('text-info');
							break;

							case VALIDATED:
							$summaryLi.addClass('text-success');
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

			if(this.$field && this.$field.length > 0) {
				values[this.id] = this.$field.val();
			}

			for (_i = 0, _len = this.childs.length; _i < _len; ++_i) {
				$.extend(values, this.childs[_i].getValues());
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
				if(this.$field.is(':focus')) {
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
				// Validate

				if (child.validated === NOT_VALIDATED
					|| child.validated === NOT_VALIDATED_EMPTY_REQUIRED
					|| (child.validated === NOT_YET_VALIDATED && child.required === true)) {
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

		// Return true if the node formNodeId has the same value than the value of this node, change the validated status of formNodeId
		FormNode.prototype.equals = function(formNodeId) {
			var node = this.indexed[formNodeId], valid = (node.value === this.value);
			node.validated = valid ? VALIDATED : NOT_VALIDATED;
			node.render();

			return valid;
		};

		// Validate function
		FormNode.prototype.validate = function() {
			var _i, _len, empty, regexp, compareValue,
				localValid = true,	// If true, local validation passed successfully
				localMessage,		// Contains the local message used to explain the failure to the user
				remoteValid = true; // If true, remote validation passed successfully

			empty = this.isEmpty();

			// Check if the value changed and synchronize the field value with the internal one
			// If the value didn't change, no need to try validating again
			if (this.hasChanged(true)) {
				// Validating state
				this.validated = VALIDATING;

				if (this.required && empty) {
					// Empty and required
					this.validated = NOT_VALIDATED_EMPTY_REQUIRED;
					localValid = false;
				} else if (empty) {
					// Empty but not required
					this.validated = EMPTY_NOT_REQUIRED;
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
							localValid = this.equals(this.validateDatas.local.options);
							localMessage = this.validateDatas.local.message;
						}

						// Local validation failed
						if (!localValid) {
							this.message = this.message || [];
							this.message.push({
								type: "danger",
								message: localMessage
							});

							this.validated = NOT_VALIDATED;
						}
					}

					// If remote validation needed
					if (localValid && this.validateDatas.remote) {
						// Send data to the server
						// Register callback to expose returned message and set status
					}
				}

				if (localValid && remoteValid) {
					this.debug('warn', "FormNode::%s is VALIDATED", this.id);

					this.validated = VALIDATED;
				}

				this.render();
			}

			// Must be triggered in any case
			this.triggerParentUpdate();

			// Trigger updateStatus on parent

			// Triggered on blur or changed or keyup && no focus

			// Test empty, if required return false (always update view), else return true
			// Not empty
			// Validate local
			// Test "requires" -> how ?
			// If not return false
			// Otherwise, validate remote
			// Update view (loading)
			// On callback, if content changed, abort result
			// If not
		};

		/**
		 * Render method will update all views that are placed in the views stack of this node
		 */
		FormNode.prototype.render = function() {
			var _i, _len;

			for (_i = 0, _len = this.views.length; _i < _len; ++_i) {
				this.views[_i].updater();
			}
		};

		FormNode.prototype.debug = function() {
			if (this.context && this.context.form && this.context.form) {
				this.context.form.debug.apply(this.context.form, arguments);
			}
		};

		return FormNode;

	})();

	(function () {

		var installer = {
			id: "witycms",
			debug: true,

			childs: [
				{
					id: "site",
					name: "Site",
					tab: true,
					required: true,

					childs: [
						{
							id: "site_name",
							name: "Site name",
							required: true,
							validate: {
								remote: 'site_name'
							}
						},
						{
							id: "base",
							name: "Base URL",
							required: true,
							value: document.location.href,
							validate: {
								local: {
									type: "regexp",
									options: "^(http|https|ftp)\:\/\/[A-Z0-9][A-Z0-9_-]*(\.[A-Z0-9][A-Z0-9_-]*)*(:[0-9]+)?(\/[A-Z0-9~\._-]+)*\/?$",
									message: "Base URL must be a valid URL"
								},
								remote: 'base_url'
							},
							type: "url"
						},
						{
							id: "theme",
							name: "Theme",
							required: true,
							validate: {
								remote: 'theme'
							},
							autocomplete: "GET_THEMES"
						},
						{
							id: "language",
							name: "Language",
							required: true,
							type: "select",
							options: [
								{
									value: "en-EN",
									text: "English (en-EN)"
								},
								{
									value: "fr-FR",
									text: "Français (fr-FR)"
								}
							]
						},
						{
							id: "timezone",
							name: "Timezone",
							required: true,
							type: "select",
							hr: true,
							options: {
								url: "installer/view/timezones.html"
							}
						},
						{
							id: "front_app",
							name: "Front app.",
							required: true,
							autocomplete: "GET_FRONT_APPS",

							validate: {
								remote: 'front_app'
							}
						},
						{
							id: "admin_app",
							name: "Admin app.",
							required: true,
							autocomplete: "GET_ADMIN_APPS",

							validate: {
								remote: 'admin_app'
							}
						}
					]
				},
				{
					id: "database",
					name: "Database",
					tab: true,
					required: true,

					childs: [
						{
							id: "credentials",
							name: "Credentials",
							virtual: true,
							required: true,
							summary: true,
							hr: true,

							validate: {
								remote: 'db_credentials'
							},

							childs: [
								{
									id: "dbserver",
									name: "Server",
									required: true,
									summary: false
								},
								{
									id: "dbport",
									name: "Port",

									validate: {
										local: {
											type: "regexp",
											options: "^[0-9]*$",
											message: "Database port must be a number"
										}
									},

									type: "number",
									placeholder: "3306",
									summary: false
								},
								{
									id: "dbuser",
									name: "User",

									required: true,
									summary: false
								},
								{
									id: "dbpassword",
									name: "Password",

									type: "password",
									summary: false
								}
							]
						},
						{
							id: "dbname",
							name: "Database name",
							required: true,
							requires: "credentials",

							validate: {
								remote: 'db_name'
							}
						},
						{
							id: "dbprefix",
							name: "Tables prefix",
							requires: "dbname",

							validate: {
								remote: 'tables_prefix'
							}
						}
					]
				},
				{
					id: "user",
					name: "User",
					tab: true,
					required: true,

					childs: [
						{
							id: "nickname",
							name: "Pseudo",
							required: true,

							validate: {
								local: {
									type: "regexp",
									options: "^[a-zA-Z0-9]([a-zA-Z0-9])*$",
									message: "The nickname must be an alphanumeric value."
								},
								remote: 'user_nickname'
							}
						},
						{
							id: "upassword",
							name: "Password",
							required: true,
							virtual: true,
							summary: true,

							childs: [
								{
									id: "password",
									name: "Password",
									required: true,
									summary: false,

									type: "password"
								},
								{
									id: "confirm",
									name: "Confirmation",
									required: true,
									summary: false,

									validate: {
										local: {
											type: "equals",
											options: "password",
											message: "Password and confirmation are different."
										}
									},

									type: "password"
								}
							]
						},
						{
							id: "email",
							name: "Email",
							required: true,
							hr: true,

							validate: {
								local: {
									type: "regexp",
									options: "^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$",
									message: "This email is not valid."
								},
								remote: 'user_email'
							},

							type: "email"
						},
						{
							id: "firstname",
							name: "Firstname",

							validate: {
								local: {
									type: "regexp",
									options: "^[a-zA-Z]([- a-zA-Z])*$",
									message: "The firstname is not valid."
								},
								remote: 'user_firstname'
							}
						},
						{
							id: "lastname",
							name: "Lastname",

							validate: {
								local: {
									type: "regexp",
									options: "^[a-zA-Z]([- a-zA-Z])*$",
									message: "The lastname is not valid."
								},
								remote: 'user_lastname'
							}
						}
					]
				}
			]
		}

		new Form(installer);

	})();
});
