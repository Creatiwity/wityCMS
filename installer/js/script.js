$(document).ready(function() {

    Installer = (function() {
		var stepInstances;
		
        function Installer() {
			var that;
			
			stepInstances = {};
            //Create the global manager
			that = this;
			$('[data-wity-installer-step]').each(function(index, stepElement) {
				var step;
				
				step = new Step(stepElement);
				that.stepInstances[step.getName()] = step;
			});
        }

        Installer.prototype.isValid = function() {
			var valid = true;
			$.each(stepInstances, function(index, step) {
				return valid = step.isValid();
			});
			return valid;
        };

        return Installer;
    })();
    
    Step = (function() {
		var groupInstances;
		
        function Step(name) {
            //Build the group list
        }
        
        return Step;
    })();
    
    Group = (function() {
		var fieldInstances;
		
		fieldInstances = {};
        function Group(name) {
            //Build the field list
			$("[data-wity-group='"+name+"']").each(function(index, fieldElement) {
				var field;
				
				field = new Field(fieldElement);
				fieldInstances[field.name] = field;
			});
            //Stores url (command)
        }
		
		Group.prototype.validate = function() {
			//
		};
		
		Group.prototype.clearErrors = function() {
			//clear
		};
		
		Group.prototype.displayErrors = function(errors) {
			//Global group errors
			//Dispatch fields error by name
			//for() display
		};
        
        return Group;
    })();
    
    Field = (function() {
		var element, type, required, validated, errorsContainer;
		
        function Field(elem) {
			//element, name, validator, required
            element = elem;
			required = element.attr('data-wity-form-required') ? true : false;
			if(element.is('select')) {
				type = "select";
			} else {
				type = element.attr('type');
			}			
			this.name = element.attr('name');
			errorsContainer = element.attr('data-wity-errors-container') ;
			
			element.on('change', this.validate);
        };
		
		Field.prototype.validate = function(withButton) {
			var content, datas;
			//launch loading
			content = this.value();
			if(required && !content && (validated || withButton)) {
				if((validated !== null && validated !== undefined) || withButton) {
					//display error
					validated === false;
				}
				return false;
			}
			
			datas = {"content": content, "valid": true, "errors-messages": []};
			element.trigger('validate', [datas]);
			
			if(!datas.valid) {
				//display errors messages
				return false;
			}
			return true;
		};
		
		Field.prototype.clearErrors = function() {
			//clear
		};
		
		Field.prototype.displayErrors = function(errors) {
			this.clearErrors();
			//for() display
		};
		
		Field.prototype.value = function(newValue) {
			var oldValue;
			if(type === "checkbox") {
				oldValue = element.is(':checked') ? true : null;
				
				if(newValue !== null && newValue !== undefined) {
					if(newValue) {
						element.attr('checked', 'checked');
					} else {
						element.removeAttr('checked');
					}
				}
			} else {
				oldValue = element.val();
				
				if(newValue) {
					element.val(newValue);
				}
			}
			
			return oldValue;
		}
        
        return Field;
    })();
	
	$(document).on('validate', '[data-wity-validate-regexp]', function(datas) {
		var value, regexp, error;
		
		value = datas.content;
		regexp = this.attr('[data-wity-validate-regexp]');
		error = this.attr('[data-wity-regexp-message]');
		
		if(value && !regexp.test(value)) {
			datas.valid = false;
			
			if(error) {
				datas.errors.push(error);
			}
		}
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
