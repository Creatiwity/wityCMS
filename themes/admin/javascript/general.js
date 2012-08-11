/**
 * @name general.js
 */

// Execution au chargement de la page
window.onload = function() {
	
};

// Inclusion d'un fichier
function include(file)
{
	document.write('<script type="text/javascript" src="' + file + '"></script>');
}

// Vérification des inputs login
function checkValue(item, string)
{
	var value = item.value;
	
	if (value == string) {
		item.value = '';
	} else if (value == '') {
		item.value = string;
	}
}

// Fonction de preload d'images
function preloadImg()
{
	if (document.images) {
		if (typeof document.preload == 'undefined') {
			document.preload = [];
		}
		var args = preloadImg.arguments;
		
		for (var i = 0, j = document.preload.length; i < args.length; i++, j++) {
			document.preload[j] = new Image();
			document.preload[j].src = args[i];
		}
	}
}
