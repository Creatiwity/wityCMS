
var first = true;

function editCat(id, name, access) {
	// Affichage du bloc
	if (first) {
		document.getElementById('edition').style.display = 'block';
		first = false;
	}
	
	// Remplissage des données
	document.getElementById('idEdit').value = id;
	document.getElementById('nameEdit').value = name;
	
	// Parsage access
	var select = document.getElementById('typeEdit');
	if (access == '') {
		$('#accessEdit').hide();
		select.options[0].selected = 'selected';
	} else if (access == 'all') {
		$('#accessEdit').hide();
		select.options[1].selected = 'selected';
	} else {
		$('#accessEdit').show();
		select.options[2].selected = 'selected';
		$('input.accessEdit').each(function(index, input) {
			var modName = input.name.substring(11, input.name.length-1),
				pos = access.indexOf(modName);
			if (pos != -1) {
				input.checked = 'checked';
				// Mise à jour du niveau
				if ($('#level'+modName).length == 1) {
					var niveau = access.substring(pos+modName.length+1, pos+modName.length+2);
					var level = document.getElementById('level'+modName);
					for (var i = 0; i < level.length; i++) {
						if (level.options[i].value == niveau) {
							level.options[i].selected = "selected";
						} else {
							level.options[i].selected = "";
						}
					}
				}
			} else {
				input.checked = '';
			}
		});
	}
	
	/**
	 * Parcours d'un select
	var select = document.getElementById('accessEdit');
	if (access == '') {
		select.options[0].selected = 'selected';
	} else {
		access = access.split(',');
		for (var i = 0 ; i < select.length ; i++) {
			if (in_array(select.options[i].value, access, false)) {
				select.options[i].selected = 'selected';
			} else {
				select.options[i].selected = '';
			}
		}
	}
	*/
	
	return false;
}

function checkDel(catName) {
	return confirm("Voulez-vous vraiment supprimer la catégorie \""+catName+"\" ?");
}

function in_array (needle, haystack, argStrict) {
    // Checks if the given value exists in the array  
    // 
    // version: 1103.1210
    // discuss at: http://phpjs.org/functions/in_array
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: vlado houba
    // +   input by: Billy
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);
    // *     returns 1: true
    // *     example 2: in_array('vlado', {0: 'Kevin', vlado: 'van', 1: 'Zonneveld'});
    // *     returns 2: false
    // *     example 3: in_array(1, ['1', '2', '3']);
    // *     returns 3: true
    // *     example 3: in_array(1, ['1', '2', '3'], false);
    // *     returns 3: true
    // *     example 4: in_array(1, ['1', '2', '3'], true);
    // *     returns 4: false
    var key = '',
        strict = !! argStrict;
 
    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true;
            }
        }
    }
 
    return false;
}
