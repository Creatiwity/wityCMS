/**
 * Script for Installer module
 * 
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.3
 */

$(document).ready(function() {
	
	var Installer, Step, Group, Field, processAjax, WITY_INSTALLER;
	
	/**
	 * Class Installer
	 */
	Installer = (function() {
		/**
		 * Constructor
		 * 
		 * @param DOMNode element Main installer HTML node
		 */
		function Installer(elem) {
			var that;
			
			// Global variable to access the installer
			WITY_INSTALLER = this;
			that = this;
			
			// Define init values for properties
			this.element = $(elem);
			this.id = this.element.attr('data-wity-installer');
			this.summary = $('[data-wity-installer-summary="'+this.id+'"]');
			this.button = $('[data-wity-installer-submit="'+this.id+'"]');
			this.stepInstances = {};
			this.validated = false;
			this.alertContainer = $('[data-wity-installer-alert="'+this.id+'"]');
			this.alerts = new Array();
			
			// Create the global manager
			$('[data-wity-installer-step]').each(function() {
				var stepId;
				
				stepId = $(this).attr('data-wity-installer-step');
				that.stepInstances[stepId] = new Step(this, that.summary);
			});
			
			// Define Event "validate-installer"
			// Trigger at final step
			this.element.on('validate-installer', '[data-wity-installer-step]', function(event, memo) {
				that.validated = true;
				
				// Check if each step is validated
				$.each(that.stepInstances, function(index, step) {
					that.validated = that.validated && step.validated;
				});
				
				// Display Ready button to trigger the final installation action
				if(that.validated) {
					that.btnReady();
				}
			});
			
			// Define Event click for installer button
			// Triggers final installation action
			this.button.on('click', function(event) {
				var datas, callback;

				datas = {};
				
				if(that.validated) {
					// Retrieve all the data in the form steps
					$.each(that.stepInstances, function(index, step) {
						var stepDatas;

						stepDatas = step.values();
						$.each(stepDatas, function(key, value) {
							datas[key] = value;
						});
					});

					datas.installer = that.id;
					datas.command = "FINISH_INSTALLATION";

					callback = function(datas) {
						if(!datas || !datas.installer || !datas.installer[that.id] || !datas.installer[that.id].success) {
							$.each(that.stepInstances, function(index, step) {
								step.dispatchMessages(datas);
							});
						}
					};
					
					// Trigger XHR request to launch the PHP installation
					processAjax(document.location, datas, callback, that);
				}
			});
			
			this.btnRemaining();
			
			// Init the installer
			processAjax(document.location, {command: "INIT_INSTALLER", installer: this.id}, null, this);
		};
		
		/**
		 * Treats the response from server
		 * 
		 * @param array data response from server
		 */
		Installer.prototype.processResponse = function(data) {
			var displayNotes, that;

			that = this;

			displayNotes = function(notes, classes) {
				$.each(notes, function(index, r) {
					var alert = $('<div class="alert">'+
						'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
					'</div>');
					$('<strong>'+r.head_message+'</strong>').appendTo(alert);
					alert.append(' '+r.message);
					alert.appendTo(that.alertContainer);
					alert.addClass(classes);
					that.alerts.push(alert);
				});
			};
			
			// Response received from server
			if(data && data.installer && data.installer[this.id]) {
				// Display warning
				if(data.installer[this.id].warning) {
					displayNotes(data.installer[this.id].warning, '');
				}
				
				// Display info
				if(data.installer[this.id].info) {
					displayNotes(data.installer[this.id].info, 'alert-info');
				}
				
				// Display error
				if(data.installer[this.id].error) {
					this.btnError();
					displayNotes(data.installer[this.id].error, 'alert-error');
					this.validated = false;
					return;
				}
				
				// Updates view on success
				if(data.installer[this.id].success) {
					$('[data-hide-onsuccess="'+this.id+'"]').hide();
					$('[data-show-onsuccess="'+this.id+'"]').show();
				}				
			}
		};
		
		/**
		 * Checks that an installer is valid: all steps are valids
		 * 
		 * @return bool
		 */
		Installer.prototype.isValid = function() {
			var valid = true;
			$.each(stepInstances, function(index, step) {
				return valid = step.isValid();
			});
			return valid;
		};
		
		/**
		 * Display installer button as loading
		 */
		Installer.prototype.btnLoading = function() {
			this.btnReset();
			this.button.button('loading');
			this.button.button('toggle');
			this.button.addClass('btn-info');
		};
		
		/**
		 * Display installer button as remaining
		 */
		Installer.prototype.btnRemaining = function() {
			this.btnReset();
			this.button.button('remaining');
			this.button.button('toggle');
			this.button.addClass('btn-info');
		};
		
		/**
		 * Display installer button as ready
		 */
		Installer.prototype.btnReady = function() {
			this.btnReset();
			this.button.addClass('btn-primary');
		};
		
		/**
		 * Display installer button as error
		 */
		Installer.prototype.btnError = function() {
			this.btnReset();
			this.button.button('error');
			this.button.button('toggle');
			this.button.addClass('btn-danger');
		};
		
		/**
		 * Resets the installer button
		 */
		Installer.prototype.btnReset = function() {
			this.button.button('reset');
			this.button.removeClass('btn-info btn-danger btn-primary');
		};
		
		return Installer;
	})();
	
	
	/**
	 * Class step
	 */
	Step = (function() {
		/**
		 * Constructor
		 * 
		 * @param DOM-Node stepElement  Main html node for the step
		 * @param DOM-Node summary      HTML element where to display the installer summary
		 */
		function Step(stepElement, summary) {
			var that;
			
			that = this;
			
			// Init properties
			this.element = $(stepElement);
			this.name = this.element.attr('data-wity-installer-step');
			this.groupInstances = {};
			this.validated = false;
			this.required = false;
			
			// Add current step to summary
			this.stepSummary = $('<li><h4>'+this.element.attr('data-wity-name')+'</h4></li>').appendTo(summary);
			this.stepStatus = $('[data-wity-installer-step-status="'+this.name+'"]');
			
			// Find groups in the step
			this.element.find('[data-wity-installer-group]').each(function() {
				var groupId, group;
				
				groupId = $(this).attr('data-wity-installer-group');
				
				if(!that.groupInstances[groupId]) {
					// Instantiates the group
					group = new Group(groupId, summary, that, this);
					that.groupInstances[groupId] = group;
					that.required = that.required || group.required;
				}
			});
			
			// Step is required based on the required attributes of the group it contains
			if(!this.required) {
				this.validated = true;
			}
			
			// Define event "validate-step"
			$(this.element).on('validate-step', '[data-wity-installer-group]', function(event, memo) {
				var oldValid = that.validated;
				that.validated = true;
				
				// Checks if each group is validated
				$.each(that.groupInstances, function(index, group) {
					that.validated = that.validated && group.validated;
				});
				
				// Updates the view of te ste
				that.showValid(that.validated);
				
				// Step status has been updated, so update installer status
				if(that.validated != oldValid) {
					that.element.trigger('validate-installer');
				}
			});
			
			// Default, show step as invalid
			this.showValid(false);
		};

		/**
		 * Displays a response message from the server to each sub-group
		 * 
		 * @param array datas
		 */
		Step.prototype.dispatchMessages = function(datas) {
			if(datas && datas.group) {
				$.each(this.groupInstances, function(index, group) {
					if(datas.group[group.name]) {
						group.processResponse(datas);
					}
				});
			}
		};
		
		/**
		 * Updates the display status for the step
		 * 
		 * @param bool isValid true: step is validated | false: step is not validated
		 */
		Step.prototype.showValid = function(isValid) {
			this.stepStatus.removeClass('icon-ok icon-remove');
			isValid ? this.stepStatus.addClass('icon-ok') : this.stepStatus.addClass('icon-remove');
		};
		
		/**
		 * Retrieves form inner values
		 * 
		 * @return array Values contained in the form
		 */
		Step.prototype.values = function() {
			var datas = {};
			
			$.each(this.groupInstances, function(index, group) {
				group.populateWithValues(datas);
			});
			
			return datas;
		};
		
		return Step;
	})();
	
	
	/**
	 * Group class
	 */
	Group = (function() {
		/**
		 * Constructor
		 * 
		 * @param string name Name of the group
		 * @param DOMNode summary HTML node of the summary
		 * @param Step step Container Step instance
		 * @param DOMNode fieldInGroup
		 */
		function Group(name, summary, step, fieldInGroup) {
			var label, that, relatedGroups;
			
			that = this;
			
			// Init properties
			this.name = name;
			this.step = step;
			this.fieldInstances = {};
			this.alerts = new Array();
			this.required = false;
			this.validated = false;
			this.element = $(fieldInGroup);
			this.requiredGroups = new Array();
			this.empty = true;
			
			// Get required groups to validate this group
			relatedGroups = this.element.attr('data-wity-require-groups');

			if(relatedGroups && relatedGroups !== "") {
				this.requiredGroups = relatedGroups.split(" ");
			}
			
			// For each field belonging to this group
			$("[data-wity-installer-group='"+name+"']").each(function(index, fieldElement) {
				var n, field, forcedRequired;
				
				forcedRequired = false;
				
				if(n = $(fieldElement).attr('data-wity-name')) {
					label = n;
				}

				if($(fieldElement).is('[data-wity-required-group]')) {
					forcedRequired = true;
					that.required = ($(fieldElement).attr('data-wity-required-group') === "true");
				}

				if(!forcedRequired) {
					that.required = (that.required || $(fieldElement).attr('data-wity-required') === "true");
				}
				
				// Instantiate the field
				field = new Field(fieldElement);
				that.fieldInstances[field.name] = field;
			});
			
			// Creates the group in the summary section and the alert container
			this.groupSummary = $('<li><em> '+label+'</em></li>').appendTo(summary);
			this.alertContainer = $('[data-wity-group-alert="'+name+'"]');
			
			if(!that.required) {
				this.groupSummary.addClass("muted");
			} else {
				this.groupSummary.addClass("text-info");
			}
			
			// Defines event "validate-group"
			$("[data-wity-installer-group='"+name+"']").on('validate-group', function(event, memo) {
				that.validate();
			});
		};
		
		/**
		 * Validates the group
		 */
		Group.prototype.validate = function() {
			var abortAjax, requiredGroup, that, values = {};
			
			that = this;
			abortAjax = false; // used to cancel final ajax request
			
			// For each required group
			for(var index = 0, length = this.requiredGroups.length; index < length; ++index) {
				requiredGroup = this.step.groupInstances[this.requiredGroups[index]];
				if(requiredGroup.validated) {
					// Retrieve values from the fields in the group
					requiredGroup.populateWithValues(values);
				} else {
					abortAjax = true;
				}
			}
			
			this.empty = true;
			
			// For each field in the group
			$.each(this.fieldInstances, function(index, field) {
				// Validate field
				field.validateInGroup();
				that.empty = that.empty && field.isEmpty();
				
				if(field.validated) {
					values[field.name] = field.value();
				}
				// Field not valid
				else if(field.required || !field.isEmpty()) {
					that.validated = false;
					abortAjax = true;
				}
			});
			
			if(abortAjax) {
				return false;
			}
			
			// Process XHR
			values.command = "GROUP_VALIDATION";
			values.group = this.name;
			values.installer = WITY_INSTALLER.id;
			values.step = this.step.name;
			
			processAjax(document.location, values, this.processResponse, this);
		};
		
		/**
		 * Inserts values of the fields contained in the group in a variable given by argument
		 * 
		 * @param array values Array in which values will be inserted
		 */
		Group.prototype.populateWithValues = function(values) {
			$.each(this.fieldInstances, function(index, field) {
				values[field.name] = field.value();
			});
		};
		
		/** 
		 * Clear group errors
		 */
		Group.prototype.clearErrors = function() {
			for(var i = this.alerts.length-1; i >= 0; --i) {
				this.alerts[i].alert('close');
				this.alerts.splice(i,1);
			}
		};
		
		/**
		 * 
		 * 
		 * @param array response
		 */
		Group.prototype.processResponse = function(response) {
			var displayNotes, oldValid, that;

			that = this;
			
			oldValid = this.validated;
			this.validated = true;
			this.clearErrors();
			
			response = response && response.group && response.group[this.name];
			
			displayNotes = function(notes, classes) {
				$.each(notes, function(index, r) {
					var alert = $('<div class="alert">'+
						'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
					'</div>');
					$('<strong>'+r.head_message+'</strong>').appendTo(alert);
					alert.append(' '+r.message);
					alert.appendTo(that.alertContainer);
					alert.addClass(classes);
					that.alerts.push(alert);
				});
			};
			
			// Apply response type to the group
			if(response) {
				if(response.success) {
					this.showValid(true);
				}
				
				if(response.warning) {
					this.showValid(true);
					displayNotes(response.warning, '');
				}
				
				if(response.error) {
					this.showValid(false);
					displayNotes(response.error, 'alert-error');
					this.validated = false;
				}
				
				if(response.info) {
					displayNotes(response.success, 'alert-info');
				}
			}
			
			// If group valid status changed, validate the step
			if(this.validated != oldValid) {
				this.element.trigger('validate-step');
			}
		};
		
		/**
		 * Changes the group status view
		 * 
		 * @param bool isValid
		 */
		Group.prototype.showValid = function(isValid) {
			this.groupSummary.removeClass("muted text-info text-success");			

			this.groupSummary.find('i').remove();
			if(isValid || (!this.required && this.empty)) {
				$('<i class="icon-ok"></i>').prependTo(this.groupSummary);
				this.groupSummary.addClass("text-success");
			} else if(!isValid || (this.required && this.empty)) {
				$('<i class="icon-remove"></i>').prependTo(this.groupSummary);
				
				// Display each field as error
				// @todo find the fields with issue
				$.each(this.fieldInstances, function(index, field) {
					field.validated = false;
					field.displayErrors();
				});
				
				if(!this.required) {
					this.groupSummary.addClass("muted");
				} else {
					this.groupSummary.addClass("text-info");
				}
			}
		};
		
		return Group;
	})();
	
	
	/**
	 * Field Class
	 */
	Field = (function() {
		/**
		 * Constructor
		 * 
		 * @param DOMNode elem HTML node of the field
		 */
		function Field(elem) {
			var that = this;
			
			// Init properties
			this.element = $(elem);
			this.validated = false;
			this.errors = new Array();
			this.name = this.element.attr('name');
			this.validatedContent = null;

			if(this.element.is('[data-wity-required-field]')) {
				this.required = (this.element.attr('data-wity-required-field') === "true");
			} else {
				this.required = (this.element.attr('data-wity-required') === "true");
			}
			
			if(this.element.is('select')) {
				this.type = "select";
			} else {
				this.type = this.element.attr('type');
			}
			
			// Defines validate event "blur"
			this.element.on('blur', function() {that.validateInField();});
		};
		
		/**
		 * Checks if the content of the field is valid
		 * 
		 * @return bool Field is valid?
		 */
		Field.prototype.validate = function() {
			var content, datas;

			content = this.value();
			this.validatedContent = content;
			
			if(this.required && (!content || content === "")) {
				this.storeErrors(["This field is required."]);
				return this.validated = false;
			}
			
			// Ask to the server if content is valid
			datas = {"content": content, "valid": true, "errors": new Array()};
			this.element.trigger('validate', [datas]);
			
			if(!datas.valid) {
				this.storeErrors(datas.errors);
				return this.validated = false;
			}
			
			this.clearErrors();
			return this.validated = true;
		};
		
		/**
		 * Validates the field after a user has blured it
		 * 
		 * @return bool Field is valid?
		 */
		Field.prototype.validateInField = function() {
			var content;
			
			content = this.validatedContent;
			
			// Validate field
			this.validate();
			
			if (!this.validated) {
				this.displayErrors();
			}
			
			// validated and content changed or validated changed
			if(content != this.validatedContent) {
				this.element.trigger('validate-group');
			}
		};
		
		/**
		 * Validates the field from a group validation request
		 * 
		 * @return bool Field is valid?
		 */
		Field.prototype.validateInGroup = function() {
			var oldValidatedContent, content;
			
			content = this.value();
			
			// Validate field
			this.validate();
			
			if (!this.validated) {
				if (this.validatedContent != null && this.validatedContent != content) {
					this.displayErrors(); // display errors on the field
				}
			} else {
				this.clearErrors();
			}
		};
		
		/**
		 * Remove errors displayed on the field
		 */
		Field.prototype.clearErrors = function() {
			var cg = this.element.closest('.control-group');
			cg.removeClass('error');
		};
		
		/**
		 * Store an error
		 * 
		 * @todo
		 */
		Field.prototype.storeErrors = function(errors) {

		};
		
		/**
		 * Display an error on a field
		 * 
		 * @param Array errors
		 */
		Field.prototype.displayErrors = function(errors) {
			this.clearErrors();
			var cg = this.element.closest('.control-group');
			cg.addClass('error');
		};
		
		/**
		 * Is the field empty?
		 * 
		 * @return bool
		 */
		Field.prototype.isEmpty = function() {
			var value = this.value();
			
			return value === null || value === ""; 
		};
		
		/**
		 * Gets or assigns a new value to the field
		 * 
		 * @param mixed New value to assign
		 * @return mixed Returns the previous value of the field
		 */
		Field.prototype.value = function(newValue) {
			var oldValue;
			if(this.type === "checkbox") {
				oldValue = this.element.is(':checked') ? true : null;
				
				if(newValue !== null && newValue !== undefined) {
					this.element.prop('checked', newValue == true);
				}
			} else {
				oldValue = this.element.val();
				
				if(newValue) {
					this.element.val(newValue);
				}
			}
			
			return oldValue;
		};
		
		return Field;
	})();
	
	$(document).on('validate', '[data-wity-validate-regexp]', function(event, datas) {
		var value, regexp, error;
		
		value = datas.content;
		regexp = new RegExp($(this).attr('data-wity-validate-regexp'), "i");
		error = $(this).attr('data-wity-regexp-message');
		
		if(value && !regexp.test(value)) {
			datas.valid = false;
			
			if(error) {
				datas.errors.push(error);
			}
		}
	});

	$(document).on('validate', '[data-wity-validate-equals]', function(event, datas) {
		var value, fieldName, otherValue, error;
		
		value = datas.content;
		fieldName = $(this).attr('data-wity-validate-equals');
		otherValue = $('[name="'+fieldName+'"]').val();
		error = $(this).attr('data-wity-equals-message');
		
		if(value !== otherValue) {
			datas.valid = false;
			
			if(error) {
				datas.errors.push(error);
			}
		}
	});
	
	/**
	 * Some values initialisation
	 **/
	$('[name="base"]').val(document.location);
	
	$('[data-wity-autocomplete]').typeahead({
		source: function(query, process) {
			var command, callback;
			command = this.$element.attr('data-wity-autocomplete');
			
			callback = function(data) {
				var prepared = new Array();
				
				data = (data && data.content) || {};
				
				for (var key in data[command]) {
					prepared.push(data[command][key]);
				}
				
				process(prepared);
			};
			
			processAjax(document.location, {"command": command}, callback, WITY_INSTALLER);			
		},
		minLength:0
	});
	
	processAjax = function (u, d, c, _this) {
		var realCallback, installer;

		installer = WITY_INSTALLER;
		installer.btnLoading();
		
		realCallback = function(data, textStatus, jqXHR ) {
			var json;
			
			json = $.parseJSON(data);

			if(installer.validated) {
				installer.btnReady();
			} else {
				installer.btnRemaining();
			}

			installer.processResponse(json);
			
			if(c) {
				return c.call(_this, json);
			}
		};
		
		$.ajax({
			url: u,
			data: d,
			success: realCallback,
			type: 'POST'
		});
	};
	
	$('[data-wity-installer]').each( function() {
		new Installer(this);
	});

	$('[data-wity-link-front]').attr('href', document.location);
	$('[data-wity-link-admin]').attr('href', document.location+'/admin');
});

/*
 * Type on a lonely field :
 * 
 * onBlur or onClick or onChange : 
 *		reset server group + itself cached validation ( + onKey ...)
 *		verify the field (required and validation)
 *		trigger(valid)
 *		verify each field in the group (required + client validation)
 *		if server validation not ok, ajax call with all fields in the group serialized to know if ok or not
 *			if all ok, cache validation, if change, up to step
 *			if not, dispatch errors on fields -> display error fields
 *		if validation not ok, nothing to do -> each field not validated itself display its error
 *		if required not ok -> each field in required-error and not yet filled or no okButton pressed, nothing to do
 *					otherwise display required-error.
 *					
 * step validation :
 *		calls validate on each group -> return cached response, or if null, call validation-group and return cache
 *		if valid, display valid, if change, call installer validate
 *		if not, display not valid, if change, invalidate installer
 * 
 */
