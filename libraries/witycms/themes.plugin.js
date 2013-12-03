define({
	load: function (name, req, onload) {
		//Convert name from theme_name[/sthg...] to ../themes/theme_name/js/[main.js|sthg...]
		var lib = '../themes/', slashPos = name.indexOf('/');

		if (slashPos !== -1) {
			lib += name.substring(0, slashPos) +
				'/js/' +
				name.substring(slashPos + 1);
		} else {
			lib += name + '/js/main';
		}

		req([lib], function (value) {
			onload(value);
		});
	}
});
