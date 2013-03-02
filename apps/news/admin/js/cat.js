function editCat(id, name, shortname, parent) {	
	// Remplissage des données
        $("#news_cat_id").val(id);
        $("#news_cat_name").val(name);
        $("#news_cat_shortname").val(shortname);
	
	// Catégorie parente
	var select = $("#news_cat_parent");
	var i, index = 0;
	for (i = 0 ; i < select.length ; i++) {
		if (select.options[i].value == parent) {
			index = i;
		}
	}
	select.val(index);
	
	return false;
}
