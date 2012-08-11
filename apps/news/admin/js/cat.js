
var first = true;

function editCat(id, name, shortname, parent) {
	// Affichage du bloc
	if (first) {
		document.getElementById('editCat').style.display = 'block';
		first = false;
	}
	
	// Remplissage des données
	document.getElementById('cIdEdit').value = id;
	document.getElementById('cNameEdit').value = name;
	document.getElementById('cShortnameEdit').value = shortname;
	
	// Catégorie parente
	var select = document.getElementById('cParentEdit');
	var i, index = 0;
	for (i = 0 ; i < select.length ; i++) {
		if (select.options[i].value == parent) {
			index = i;
		}
	}
	document.getElementById('cParentEdit').selectedIndex = index;
	
	return false;
}

function checkDel(catName) {
	return confirm("Voulez-vous vraiment supprimer la catégorie \""+catName+"\" ?");
}
