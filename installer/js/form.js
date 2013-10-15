/**
 * Script for Installer module
 * 
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4
 */
$(document).ready(function() {

	var Form, FormNode;

	Form = (function() {

		function Form(options) {
			var $alert, $submit, $summary, $tabs, $content, handlers = {}, id = options.id;

			if (!options || !options.id) {
				console.error("options parameter is missing or empty, unable to create form.");
				return;
			}

			this.url = options.url ? options.url : document.location;

			this.context = {
				id: id,
				form: this
			};
			
			this.$alert = $('[data-wity-form-alert="' + id + '"]');
			this.$submit = $('[data-wity-form-submit="' + id + '"]');

			this.$summary = $('[data-wity-form-summary="' + id + '"]');
			if (this.$summary.length > 0) {
				this.context.summary = this.$summary;
			}

			this.$tabs = $('[data-wity-form-tabs="' + id + '"]');
			if (this.$tabs.length > 0) {
				this.context.tabs = this.$tabs;
			}

			this.$content = $('[data-wity-form-content="' + id + '"]');
			if (this.$content.length > 0) {
				this.context.content = this.$content;
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

			this.node = FormNode(options, context);
		}

		Form.prototype.ajax = function (datas, callback, _context) {	
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
					return callback.call(_context, json);
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

	});

	FormNode = (function() {

		var tabCounter = 0, NEVER_VALIDATED = 0, NOT_VALIDATED = 1, VALIDATED = 2;

		function FormNode(options, context) {
			var _i, _len, $tabTitle, $tabStatus, $tabContainer, _context = context, that = this;

			if (!options || !context || !context.id || !context.form || !context.content) {
				console.debug("context passed to FormNode isn't valid.");
				return false;
			}

			this.id = options.id;
			this.views = [];

			if (options.name) {
				this.name = options.name;
			} else {
				this.name = this.id;
			}

			this.required = (options.required === true);
			this.validated = NEVER_VALIDATED;

			// Build validate

			// Build FormNode in DOM and in logic
			if (options.tab && options.tab === true) {
				if (context.$tabs.length > 0) {
					$tabStatus = $('<i class="glyphicon"></i>');
					$tabTitle = $('<a href="#form_' + context.id + '_' + this.id + '" data-toggle="tab"></a>').append($tabStatus);
					$tabTitle = $('<li></li>').append($tabTitle);

					$tabContainer = $('<div class="tab-pane" id="#form_' + context.id + '_' + this.id + '"><fieldset class="form-horizontal"></fieldset></div>');
					
					if (tabCounter === 0) {
						$tabTitle.addClass('active');
						$tabContainer.addClass('active');
					}

					context.$tabs.append($tabTitle);
					context.$content.append($tabContainer);
					context.$summary.append($('<h4>' + this.name + '</h4>'));

					_context = jQuery.extend(true, {}, context);
					_context.$content = $tabContainer;

					this.views.push({
						updater: function() {
							$tabStatus.removeClass('glyphicon-remove glyphicon-ok');

							switch (that.validated) {
								case NEVER_VALIDATED:
								$tabStatus.addClass('glyphicon-remove');

								case NOT_VALIDATED:
								$tabStatus.addClass('glyphicon-remove');

								case VALIDATED:
								$tabStatus.addClass('glyphicon-ok');
							}
						}
					});

					tabCounter++;
				}
			}

			// Build DOM

			// Constructs all children of this node
			if (options.childs && options.childs.length > 0) {
				this.childs = [];

				for (_i = 0, _len = options.childs.length; _i < _len; ++_i) {
					this.childs.push(FormNode(options.childs[_i], _context));
				}
			}

			this.render();
		}

		FormNode.prototype.validate = function() {
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
			var _i, _len, view;

			for (_i = 0, _len = this.views.length; _i < _len; ++_i) {
				view = this.views[_i];
				view.updater(view.container);
			}
		};

		return FormNode;

	});

	(function () {

		var installer = {
			id: "witycms",

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
								remote: true
							}
						},
						{
							id: "base",
							name: "Base URL",
							required: true,
							validate: {
								local: {
									type: "regexp",
									options: "^(http|https|ftp)\:\/\/[A-Z0-9][A-Z0-9_-]*(\.[A-Z0-9][A-Z0-9_-]*)*(\/[A-Z0-9~\._-]+)*\/?$", 
									message: "Base URL must be a valid URL"
								},
								remote: true
							},
							type: "url"
						},
						{
							id: "theme",
							name: "Theme",
							required: true,
							validate: {
								remote: true
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
							name: "Name",
							required: true,
							type: "select",
							options: {
								url: "/installer/view/timezones.html"
							}
						},
						{
							id: "front_app",
							name: "Front app.",
							required: true,
							autocomplete: "GET_FRONT_APPS"
						},
						{
							id: "admin_app",
							name: "Admin app.",
							required: true,
							autocomplete: "GET_ADMIN_APPS"
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

							validate: {
								remote: true
							},

							childs: [
								{
									id: "dbserver",
									name: "Server",
									required: true
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
									}

									type: "number",
									placeholder: "3306"
								},
								{
									id: "dbuser",
									name: "User",

									required: true
								},
								{
									id: "dbpassword",
									name: "Password",

									type: "password"
								}
							]
						},
						{
							id: "dbname",
							name: "Database name",
							required: true,
							requires: "credentials",

							validate: {
								remote: true
							}
						},
						{
							id: "dbprefix",
							name: "Tables prefix",
							requires: "dbname",

							validate: {
								remote: true
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
								remote: true
							}
						},
						{
							id: "upassword",
							name: "Password",
							required: true,

							validate: {
								remote: true
							}

							childs: [
								{
									id: "password",
									name: "Password",
									required: true,

									type: "password"
								},
								{
									id: "confirm",
									name: "Confirmation",
									required: true,

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

							validate: {
								local: {
									type: "regexp",
									options: "^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$",
									message: "This email is not valid."
								},
								remote: true
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
								remote: true
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
								remote: true
							}
						}
					]
				}
			]
		}

		Form(installer);

	})();
});
