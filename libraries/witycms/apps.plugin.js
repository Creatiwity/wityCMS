define({
	load: function (name, req, onload) {
		//Convert name from app_name[/sthg...] to ../apps/app_name/js/[main.js|sthg...]
		var lib = '../apps/', slashPos = name.indexOf('/');

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
