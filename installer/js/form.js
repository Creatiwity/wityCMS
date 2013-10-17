/**
 * Script for Installer module
 * 
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4
 */
$(document).ready(function() {

	var Form, FormNode, NOT_YET_VALIDATED = 0, NOT_VALIDATED = 1, NOT_VALIDATED_EMPTY_REQUIRED = 2, VALIDATING = 3, VALIDATED = 4, EMPTY_NOT_REQUIRED = 5;

	Form = (function() {

		function Form(options) {
			var handlers = {}, id = options.id;

			if (!options || !options.id) {
				console.error("options parameter is missing or empty, unable to create form.");
				return;
			}

			this.url = options.url ? options.url : document.location;

			this.context = {
				id: id,
				form: this,
				indexed: {}
			};
			
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
				this.context.$content = this.$content;
			}

			$('[data-wity-form-' + id + '-onsuccess]').each(function () {
				var $this = $(this), handler = $this.attr('data-wity-form-' + id + '-onsuccess');

				handlers.success = handlers.success || [];
				handlers.success.push([$this, handler]);
			});

			$('[data-wity-form-' + id + '-onfailure]').each(function () {
				var $this = $(this), handler = $this.attr('data-wity-form-' + id + '-onfailure');

				handlers.failure = handlers.failure || [];
				handlers.failure.push([$this, handler]);
			});

			this.node = new FormNode(options, this.context, this);

			this.$submit.on('click', function() {
				var $this = $(this);

				if (this.validated) {
					$this.button('loading');
				}
			});

			this.setButtonStatus(false);
		}

		Form.prototype.updateStatus = function() {
			this.validated = (this.node.validated === VALIDATED || this.node.validated === EMPTY_NOT_REQUIRED);

			this.setButtonStatus(this.validated);
		};

		Form.prototype.setButtonStatus = function(state) {
			var that = this;

			if(!state) {
				this.$submit.button('remaining')
				setTimeout(function() {that.$submit.attr("disabled", "disabled");}, 0);
			} else {
				this.$submit.button('reset');
			}
		};

		Form.prototype.getAjaxHtml = function (url, datas, callback, context) {
			var _url = url || this.url;

			$.ajax({
				url: _url,
				data: datas,
				success: callback,
				type: 'POST'
			});
		}

		Form.prototype.ajax = function (datas, callback, context) {	
			var realCallback, that = this;
		
			// show loading
		
			realCallback = function(data, textStatus, jqXHR) {
				var json;
				
				try {
					json = $.parseJSON(data);
				} catch(e) {
					// Display debug
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
				data: datas,
				success: realCallback,
				type: 'POST'
			});
		};

		return Form;

	})();

	FormNode = (function() {

		var tabCounter = 0;

		/**
		 * Validation updateStatus
		 */

		function FormNode(options, context, parent) {
			var _i, _len, $tabTitle, $tabStatus, $tabContainer, 
				$tabContent, $summaryLi, $summaryStatus, 
				$alertContainer, $fieldContainer, $field, 
				validationTimer, contentCache, that = this;

			if (!options || !context || !context.id || !context.form || !context.$content || context.$content.length === 0) {
				console.debug("context passed to FormNode isn't valid.");
				return false;
			}

			context.indexed[options.id] = this;

			this.id = options.id;
			this.views = [];
			this.indexed = context.indexed;
			this.parent = parent;
			this.root = (options.root === true);

			this.name = options.name || this.id;
			
			this.required = (options.required === true) || (options.root === true);
			this.validated = NOT_YET_VALIDATED;
			this.summary = false;

			this.validateDatas = options.validate || {};

			if (this.validateDatas.local && this.validateDatas.local.type === "equals") {
				$.extend(true, this.indexed[this.validateDatas.local.options].validateDatas, this.validateDatas);
				this.indexed[this.validateDatas.local.options].validateDatas.options = this.id;
			}

			// Build validate

			// Build FormNode in DOM and in logic
			contentCache = context.$content;

			if (options.root === true) {
				// Do nothing
			} else if (options.tab && options.tab === true) {
				if (context.$tabs.length > 0) {
					$tabStatus = $('<i class="glyphicon"></i>');
					$tabTitle = $('<a href="#form_' + context.id + '_' + this.id + '" data-toggle="tab"></a>').append($tabStatus).append(' ' + this.name);
					$tabTitle = $('<li></li>').append($tabTitle);

					$tabContainer = $('<div class="tab-pane" id="form_' + context.id + '_' + this.id + '"></div>');
					$tabContent = $('<fieldset class="form-horizontal"></fieldset>').appendTo($tabContainer);
					
					if (tabCounter === 0) {
						$tabTitle.addClass('active');
						$tabContainer.addClass('active');
					}

					context.$tabs.append($tabTitle);
					context.$content.append($tabContainer);
					context.$summary.append($('<h4>' + this.name + '</h4>'));

					context.$content = $tabContent;

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
				this.summary = options.summary !== false;
				this.type = options.type || "text";

				$alertContainer = $('<div></div>');
				$fieldContainer = $('<div class="form-group"></div>');

				if (this.type === "select") {
					$field = $('<select name="' + this.id + '" class="form-control"></select>');

					if ($.isArray(options.options)) {
						for (_i = 0, _len = options.options.length; _i < _len; ++_i) {
							$field.append('<option value="' + options.options[_i].value + '">' + options.options[_i].text + '</option>')
						}

						if (options.value) {
							$field.val(options.value);
						}

					} else if (options.options.url) {
						context.form.getAjaxHtml(options.options.url, null, function (data) {
							$field.append(data);

							if (options.value) {
								$field.val(options.value);
							}

						});
					} else {
						console.debug("No option provided for " + this.name + " in the form named " + context.id);
					}
				} else {
					$field = $('<input type="' + this.type + '" class="form-control"></input>');

					if (options.placeholder) {
						$field.attr('placeholder', options.placeholder);
					}

					if (options.value) {
						$field.val(options.value);
					}
				}

				this.$field = $field;

				context.$content.append($alertContainer);
				context.$content.append($fieldContainer);
				$fieldContainer.append('<label class="col-md-3 control-label" for="' + this.id + '">' + this.name + '</label>')
				$fieldContainer.append($('<div class="col-md-9"></div>').append($field));

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

				$field.on('blur changed', function() {
					if (validationTimer) {
						clearTimeout(validationTimer);
					}

					validationTimer = setTimeout(function() {that.validate();}, 0);
				});

			} else {
				this.summary = (options.summary === true);
			}

			if (this.summary) {
				$summaryStatus = $('<i class="glyphicon"></i>');
				$summaryLi = $('<li></li>');

				$summaryLi.append($summaryStatus);
				$summaryLi.append(' ' + this.name);

				context.$summary.append($summaryLi);

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

			context.$content = contentCache;

			if (options.hr === true) {
				context.$content.append('<hr />');
			}

			this.render();
		}

		FormNode.prototype.isValidated = function() {
			return (this.validated === VALIDATED);
		};

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

		FormNode.prototype.setChanged = function() {
			this.changed = true;
			this.parent && this.parent.setChanged && this.parent.setChanged();
		};

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

		FormNode.prototype.updateStatus = function() {
			var _i, _len, child, childrenValid = true, preventValidationIfFocus = false;

			for (_i = 0, _len = this.childs.length; _i < _len; ++_i) {
				child = this.childs[_i];
				if (child.validated === NOT_VALIDATED 
					|| child.validated === NOT_VALIDATED_EMPTY_REQUIRED) {
					childrenValid = false;
				} else if (child.hasFocus() 
					&& (child.validated !== EMPTY_NOT_REQUIRED 
						|| (child.validated === NOT_YET_VALIDATED && child.required === true))) {
					preventValidationIfFocus = true;
				} else if (child.validated === VALIDATING) {
					preventValidationIfFocus = true;
				}
			}

			if (!childrenValid || preventValidationIfFocus) {
				this.validated = NOT_VALIDATED;
				this.render();
				this.triggerParentUpdate();
			} else if (false) {
				this.validated = VALIDATING;
				this.render();
				this.triggerParentUpdate();
			} else {
				this.validate();
			}
		};

		FormNode.prototype.triggerParentUpdate = function() {
			this.parent.updateStatus();
		};

		FormNode.prototype.equals = function(formNodeId) {
			var node = this.indexed[formNodeId], valid = (node.value === this.value);
			node.validated = valid ? VALIDATED : NOT_VALIDATED;
			node.render();
			
			return valid;
		};

		FormNode.prototype.validate = function() {
			var _i, _len, empty, regexp, compareValue, localValid = true, localMessage, remoteValid = true;

			empty = this.isEmpty();

			if (this.hasChanged(true)) {
				this.validated = VALIDATING;

				if (this.required && empty) {
					this.validated = NOT_VALIDATED_EMPTY_REQUIRED;
					localValid = false;
				} else if (empty) {
					this.validated = EMPTY_NOT_REQUIRED;
					localValid = false;
				} else {
					if (this.validateDatas.local) {
						if (this.validateDatas.local.type === "regexp") {
							regexp = new RegExp(this.validateDatas.local.options, "i");
							localValid = regexp.test(this.value);
							localMessage = this.validateDatas.local.message;
						} else if (this.validateDatas.local.type === "equals") {
							localValid = this.equals(this.validateDatas.local.options);
							localMessage = this.validateDatas.local.message;
						}

						if (!localValid) {
							this.message = this.message || [];
							this.message.push({
								type: "danger",
								message: localMessage
							});

							this.validated = NOT_VALIDATED;
						}
					}

					if (localValid && this.validateDatas.remote) {

					}
				}

				if (localValid && remoteValid) {
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

		FormNode.prototype.render = function() {
			var _i, _len;

			for (_i = 0, _len = this.views.length; _i < _len; ++_i) {
				this.views[_i].updater();
			}
		};

		return FormNode;

	})();

	(function () {

		var installer = {
			id: "witycms",
			root: true,

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
