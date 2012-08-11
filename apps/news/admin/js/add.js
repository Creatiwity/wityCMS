
var first = true;

function updatePermalien(value) {
	if (value == '') {
		return;
	}
	
	// Traitement de la valeur
	value = value.toLowerCase();
	value = value.replace(/[^a-z0-9.]/gi, '-');
	value = value.replace(/-{2,}/g, '-');
	value = value.replace(/(^-|-$)/g, '');
	
	if (value == '') {
		alert('Valeur de titre invalide pour la génération du permalien.');
		return;
	}
	
	if (first) {
		document.getElementById('permalien').style.display = 'block';
		first = false;
	}
	
	// On réinitialise les boutons
	permalienCancel();
	
	// Update permalienInnerInput
	document.getElementById('permalienInnerInput').value = value;
	
	// Update permalienInnerShow
	document.getElementById('permalienInnerShow').innerHTML = value;
}

function permalienClick() {
	// Hide permalienInnerShow
	document.getElementById('permalienInnerShow').style.display = 'none';
	// Show permalienInnerInput
	document.getElementById('permalienInnerInput').style.display = 'inline';
	
	// Hide modifButton
	document.getElementById('modifButton').style.display = 'none';
	// Show modifCommands
	document.getElementById('modifCommands').style.display = 'inline';
}

function permalienCancel() {
	// Réinitialisation de permalienInnerInput
	document.getElementById('permalienInnerInput').value = document.getElementById('permalienInnerShow').innerHTML;
	
	// Show permalienInnerShow
	document.getElementById('permalienInnerShow').style.display = 'inline';
	
	// Hide permalienInnerInput
	document.getElementById('permalienInnerInput').style.display = 'none';
	
	// Hiden modifCommands
	document.getElementById('modifCommands').style.display = 'none';
	// Show modifButton
	document.getElementById('modifButton').style.display = 'inline';
}

function check(input, def) {
	if (input.value == def) {
		input.value = '';
		input.className = '';
	} else if (input.value == '') {
		input.value = def;
		input.className = 'empty';
	}
}
