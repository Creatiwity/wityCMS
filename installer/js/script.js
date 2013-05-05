
$(document).ready(function() {
	
	var Installer, Step, Group, Field, processAjax, WITY_INSTALLER;
	
	Installer = (function() {
		
		function Installer(elem) {
			var that;
			
			WITY_INSTALLER = this;
			this.element = $(elem);
			this.id = this.element.attr('data-wity-installer');
			this.summary = $('[data-wity-installer-summary="'+this.id+'"]');
			this.button = $('[data-wity-installer-submit="'+this.id+'"]');
			this.stepInstances = {};
			this.validated = false;
			this.alertContainer = $('[data-wity-installer-alert="'+this.id+'"]');
			this.alerts = new Array();
			
			// Create the global manager
			that = this;
			$('[data-wity-installer-step]').each(function() {
				var stepId;
				
				stepId = $(this).attr('data-wity-installer-step');
				that.stepInstances[stepId] = new Step(this, that.summary);
			});
			
			$(this.element).on('validate-installer', '[data-wity-installer-step]', function(event, memo) {
				that.validated = true;

				$.each(that.stepInstances, function(index, step) {
					that.validated = that.validated && step.validated;
				});

				if(that.validated) {
					that.btnReady();
				}
			});

			this.button.on('click', function(event) {
				var datas, callback;

				datas = {};

				if(that.validated) {
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

					processAjax(document.location, datas, callback, that);
				}
			});
			
			this.btnRemaining();

			processAjax(document.location, {command: "INIT_INSTALLER", installer: this.id}, null, this, this);
		};
		
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

			if(data && data.installer && data.installer[this.id]) {

				if(data.installer[this.id].warning) {
					displayNotes(data.installer[this.id].warning, '');
				}
				
				if(data.installer[this.id].info) {
					displayNotes(data.installer[this.id].info, 'alert-info');
				}

				if(data.installer[this.id].error) {
					this.btnError();
					displayNotes(data.installer[this.id].error, 'alert-error');
					this.validated = false;
					return;
				}
				
				if(data.installer[this.id].success) {
					$('[data-hide-onsuccess="'+this.id+'"]').hide();
					$('[data-show-onsuccess="'+this.id+'"]').show();
				}				
			}
		};
		
		Installer.prototype.isValid = function() {
			var valid = true;
			$.each(stepInstances, function(index, step) {
				return valid = step.isValid();
			});
			return valid;
		};
		
		Installer.prototype.btnLoading = function() {
			this.btnReset();
			this.button.button('loading');
			this.button.button('toggle');
			this.button.addClass('btn-info');
		};
		
		Installer.prototype.btnRemaining = function() {
			this.btnReset();
			this.button.button('remaining');
			this.button.button('toggle');
			this.button.addClass('btn-info');
		};
		
		Installer.prototype.btnReady = function() {
			this.btnReset();
			this.button.addClass('btn-primary');
		};
		
		Installer.prototype.btnError = function() {
			this.btnReset();
			this.button.button('error');
			this.button.button('toggle');
			this.button.addClass('btn-danger');
		};
		
		Installer.prototype.btnReset = function() {
			this.button.button('reset');
			this.button.removeClass('btn-info btn-danger btn-primary');
		};
		
		return Installer;
	})();
	
	Step = (function() {
		
		function Step(stepElement, summary) {
			var that;
			
			this.element = $(stepElement);
			this.name = this.element.attr('data-wity-installer-step');
			this.groupInstances = {};
			this.validated = false;
			this.required = false;
			
			that = this;
			
			this.stepSummary = $('<li><h4>'+this.element.attr('data-wity-name')+'</h4></li>').appendTo(summary);
			this.stepStatus = $('[data-wity-installer-step-status="'+this.name+'"]');
			
			this.element.find('[data-wity-installer-group]').each(function() {
				var groupId, group;
				
				groupId = $(this).attr('data-wity-installer-group');
				
				if(!that.groupInstances[groupId]) {
					group = new Group(groupId, summary, that, this);
					that.groupInstances[groupId] = group;
					that.required = that.required || group.required;
				}
			});

			if(!this.required) {
				this.validated = true;
			}
			
			$(this.element).on('validate-step', '[data-wity-installer-group]', function(event, memo) {
				var oldValid = that.validated;
				that.validated = true;

				$.each(that.groupInstances, function(index, group) {
					that.validated = that.validated && group.validated;
				});

				that.showValid(that.validated);

				if(that.validated != oldValid) {
					that.element.trigger('validate-installer');
				}
			});
			
			this.showValid(false);
		};

		Step.prototype.dispatchMessages = function(datas) {
			if(datas && datas.group) {
				$.each(this.groupInstances, function(index, group) {
					if(datas.group[group.name]) {
						group.processResponse(datas);
					}
				});
			}
		};
		
		Step.prototype.showValid = function(isValid) {
			this.stepStatus.removeClass('icon-ok icon-remove');
			isValid ? this.stepStatus.addClass('icon-ok') : this.stepStatus.addClass('icon-remove');
		};
		
		return Step;
	})();
	
	Group = (function() {
		
		function Group(name, summary, step, fieldInGroup) {
			var label, that, forcedRequired, relatedGroups;
			
			that = this;
			
			this.name = name;
			this.step = step;
			this.fieldInstances = {};
			this.alerts = new Array();
			this.required = false;
			this.validated = false;
			this.element = $(fieldInGroup);
			this.requiredGroups = new Array();
			this.empty = true;

			relatedGroups = this.element.attr('data-wity-require-groups');

			if(relatedGroups && relatedGroups !== "") {
				this.requiredGroups = relatedGroups.split(" ");
			}

			forcedRequired = false;
			
			$("[data-wity-installer-group='"+name+"']").each(function(index, fieldElement) {
				var n, field;				
				
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
				
				field = new Field(fieldElement);
				that.fieldInstances[field.name] = field;
			});
			
			this.groupSummary = $('<li><em> '+label+'</em></li>').appendTo(summary);
			this.alertContainer = $('[data-wity-group-alert="'+name+'"]');
			
			if(!that.required) {
				this.groupSummary.addClass("muted");
			} else {
				this.groupSummary.addClass("text-info");
			}

			$("[data-wity-installer-group='"+name+"']").on('validate-group', function(event, memo) {
				var oldValid = that.validated;
				that.validate();
			});
		};
		
		Group.prototype.validate = function() {
			var abortAjax, requiredGroup, that, values = {};

			abortAjax = false;
			that = this;

			for(var index = 0, length = this.requiredGroups.length; index < length; ++index) {
				requiredGroup = this.step.groupInstances[this.requiredGroups[index]];
				if(requiredGroup.validated) {
					requiredGroup.populateWithValues(values);
				} else {
					abortAjax = true;
				}
			}

			this.empty = true;
			
			$.each(this.fieldInstances, function(index, field) {
				field.validateInGroup();
				that.empty = that.empty && field.isEmpty();
				if(field.validated) {
					values[field.name] = field.value();
				} else if((field.required && field.isEmpty()) || !field.isEmpty()) {
					that.validated = false;
					abortAjax = true;
				}
			});

			if(abortAjax) {
				return false;
			}

			values.command = "GROUP_VALIDATION";
			values.group = this.name;
			values.installer = WITY_INSTALLER.id;
			values.step = this.step.name;
			
			processAjax(document.location, values, this.processResponse, this);
		};

		Group.prototype.populateWithValues = function(values) {
			$.each(this.fieldInstances, function(index, field) {
				values[field.name] = field.value();
			});
		};
		
		Group.prototype.clearErrors = function() {
			for(var i = this.alerts.length-1; i >= 0; --i) {
				this.alerts[i].alert('close');
				this.alerts.splice(i,1);
			}
		};
		
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
			
			//true if changed, false otherwise
			if(this.validated != oldValid) {
				this.element.trigger('validate-step');
			}
		};
		
		Group.prototype.showValid = function(isValid) {
			this.groupSummary.removeClass("muted text-info text-success");			

			this.groupSummary.find('i').remove();
			if(isValid || (!this.required && this.empty)) {
				$('<i class="icon-ok"></i>').prependTo(this.groupSummary);
				this.groupSummary.addClass("text-success");
			} else if(!isValid || (this.required && this.empty)) {
				$('<i class="icon-remove"></i>').prependTo(this.groupSummary);
				if(!this.required) {
					this.groupSummary.addClass("muted");
				} else {
					this.groupSummary.addClass("text-info");
				}
			}
		};
		
		return Group;
	})();
	
	Field = (function() {
		
		function Field(elem) {
			var that = this;
			
			//element, name, validator, required
			this.element = $(elem);
			this.validated = false;
			this.errors = new Array();

			if(this.element.is('[data-wity-required-field]')) {
				this.required = (this.element.attr('data-wity-required-field') === "true");
			} else {
				this.required = (this.element.attr('data-wity-required') === "true");
			}
			
			if(this.element.is('select')) {
				this.type = "select";
				this.element.on('blur change', function() {that.validateInField();});
			} else {
				this.type = this.element.attr('type');
				this.element.on('blur change', function() {that.validateInField();});
			}
			this.name = this.element.attr('name');
			this.validatedContent = null;
		};

		Field.prototype.validateInField = function() {
			var oldValid, oldValidatedContent, content;

			oldValid = this.validated;
			oldValidatedContent = this.validatedContent;
			content = this.value();

			this.validate();

			if(!this.validated && (!content || content === "") && this.required) {
				// not validated : field is empty and required
				this.clearErrors();
			} else if(!this.validated) {
				// display errors on the field
				this.displayErrors();
				// validated and content changed or validated changed
			} else if((this.validatedContent !== oldValidatedContent) || (oldValid !== this.validated)) {
				this.element.trigger('validate-group');
			}

		};

		Field.prototype.validateInGroup = function() {
			var oldValidatedContent;

			if(this.validated && (this.value() === this.validatedContent)) {
				return true;
			} else {
				oldValidatedContent = this.validatedContent;
				this.validate();

				if(!this.validated && (!oldValidatedContent || oldValidatedContent === "") && (this.value || this.value === "") && this.required) {
					// not validated : field is empty and required
					this.clearErrors();
				} else if(!this.validated) {
					// display errors on the field
					this.displayErrors();
				}
			}
		};

		Field.prototype.validate = function() {
			var content, datas;

			content = this.value();

			if(this.required && (!content || content === "")) {
				this.storeErrors(["This field is required."]);
				this.validated = false;
				return false;
			}

			datas = {"content": content, "valid": true, "errors": new Array()};
			this.element.trigger('validate', [datas]);
			if(!datas.valid) {
				this.storeErrors(datas.errors);
				this.validated = false;
				return false;
			}
			this.validatedContent = content;
			this.clearErrors();
			this.validated = true;
			return true;
		};
		
		Field.prototype.clearErrors = function() {
			var cg = this.element.closest('.control-group');
			cg.removeClass('error');
		};

		Field.prototype.storeErrors = function(errors) {

		};
		
		Field.prototype.displayErrors = function(errors) {
			this.clearErrors();
			var cg = this.element.closest('.control-group');
			cg.addClass('error');
		};

		Field.prototype.isEmpty = function() {
			var value = this.value();

			return (value!==null && value!==undefined && value!==""); 
		};
		
		Field.prototype.value = function(newValue) {
			var oldValue;
			if(this.type === "checkbox") {
				oldValue = this.element.is(':checked') ? true : null;
				
				if(newValue !== null && newValue !== undefined) {
					if(newValue) {
						this.element.prop('checked', true);
					} else {
						this.element.prop('checked', false);
					}
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
		regexp = new RegExp($(this).attr('data-wity-validate-regexp'));
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
			
			processAjax(document.location, {"command": command}, callback, WITY_INSTALLER, WITY_INSTALLER);			
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
