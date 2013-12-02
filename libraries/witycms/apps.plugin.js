define({
	load: function (name, req, onload, config) {
		//Convert name from app_name[/sthg...] to ../apps/app_name/js[main.js|sthg...]
		var lib, params = name.split(/\/(.+)?/);

		lib = '../apps/' + params[0] + '/';
		lib += (params.length === 1 ? 'main' : params[1]);

		req([lib], function (value) {
			onload(value);
		});
	}
});
