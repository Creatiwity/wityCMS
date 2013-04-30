
$(document).ready(function() {
	
	var Installer, Step, Group, Field, processAjax;
	
	Installer = (function() {
		
		function Installer(elem) {
			var that;
			
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
				that.stepInstances[stepId] = new Step(this, that.summary, that);
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
		
		function Step(stepElement, summary, installer) {
			var that;
			
			this.installer = installer;
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
					group = new Group(groupId, summary, that.installer, that, this);
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
		
		function Group(name, summary, installer, step, fieldInGroup) {
			var label, that;
			
			that = this;
			
			this.name = name;
			this.installer = installer;
			this.step = step;
			this.fieldInstances = {};
			this.alerts = new Array();
			this.required = false;
			this.validated = false;
			this.element = $(fieldInGroup);
			
			$("[data-wity-installer-group='"+name+"']").each(function(index, fieldElement) {
				var field, n;
				
				if(n = $(fieldElement).attr('data-wity-name')) {
					label = n;
				}
				
				field = new Field(fieldElement);
				that.fieldInstances[field.name] = field;
				that.required = that.required || field.required;
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
			var values = {};
			
			$.each(this.fieldInstances, function(index, field) {
				field.validate(true);
				if(field.validated) {
					values[field.name] = field.value();
				} else {
					this.validated = false;
					return false;
				}
			});

			values.command = "GROUP_VALIDATION";
			values.group = this.name;
			values.installer = this.installer.id;
			values.step = this.step.name;
			
			processAjax(document.location, values, this.processResponse, this.installer, this);
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
					this.showValid(true, this.required);
				}
				
				if(response.warning) {
					this.showValid(true, this.required);
					displayNotes(response.warning, '');
				}
				
				if(response.error) {
					this.showValid(false, this.required);
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
		
		Group.prototype.showValid = function(isValid, isEmpty) {
			this.groupSummary.removeClass("muted text-info text-success");			

			this.groupSummary.find('i').remove();
			if(isValid && !isEmpty) {
				$('<i class="icon-ok"></i>').prependTo(this.groupSummary);
				this.groupSummary.addClass("text-success");
			} else if(!isValid) {
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
			this.required = this.element.attr('data-wity-required') ? true : false;
			if(this.element.is('select')) {
				this.type = "select";
				this.element.on('change', function() {that.validate(false);});
			} else {
				this.type = this.element.attr('type');
				this.element.on('blur change', function() {that.validate(false);});
			}
			this.name = this.element.attr('name');
			this.validatedContent = null;
		};
		
		Field.prototype.validate = function(withButton) {
			var content, datas, oldValid;
			
			oldValid = this.validated;
			//launch loading
			content = this.value();
			
			if(this.required && (!content || content === "") && (this.validated || withButton)) {
				if((this.validated !== null && this.validated !== undefined) || withButton) {
					this.displayErrors(false);
					this.validated = false;
				}
				this.chooseTrigger(oldValid, content, withButton);
				return false;
			}
			
			datas = {"content": content, "valid": true, "errors-messages": []};
			this.element.trigger('validate', [datas]);
			
			if(!datas.valid) {
				this.displayErrors(false);
				this.validated = false;
				this.chooseTrigger(oldValid, content, withButton);
				return false;
			}
			this.clearErrors();
			this.validated = true;
			this.chooseTrigger(oldValid, content, withButton);
			return true;
		};
		
		Field.prototype.chooseTrigger = function(oldValid, newContent, force) {
			if(!force) {
				if(oldValid !== this.validated || newContent !== this.validatedContent) {
					this.validatedContent = newContent;
					this.element.trigger('validate-group');
				}
			}
		}
		
		Field.prototype.clearErrors = function() {
			var cg = this.element.closest('.control-group');
			cg.removeClass('error');
		};
		
		Field.prototype.displayErrors = function(errors) {
			this.clearErrors();
			var cg = this.element.closest('.control-group');
			cg.addClass('error');
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
	
	$(document).on('validate', '[data-wity-validate-regexp]', function(datas) {
		var value, regexp, error;
		
		value = datas.content;
		regexp = $(this).attr('[data-wity-validate-regexp]');
		error = $(this).attr('[data-wity-regexp-message]');
		
		if(value && !regexp.test(value)) {
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
	
	processAjax = function (u, d, c, installer, _this) {
		var realCallback;

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
		WITY_INSTALLER = new Installer(this);
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
