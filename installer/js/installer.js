$(window).ready(function() {

	(function () {

		var installer = {
			id: "witycms",
			debug: true,

			childs: [
				{
					id: "site",
					name: "Site",
					tab: true,
					required: true,

					childs: [
						{
							id: "site_name",
							name: "Site name",
							required: true,
							validate: {
								remote: 'site_name'
							}
						},
						{
							id: "base",
							name: "Base URL",
							required: true,
							value: document.location.href,
							validate: {
								local: {
									type: "regexp",
									options: "^(http|https|ftp)\:\/\/[A-Z0-9][A-Z0-9_-]*(\.[A-Z0-9][A-Z0-9_-]*)*(:[0-9]+)?(\/[A-Z0-9~\._-]+)*\/?$",
									message: "Base URL must be a valid URL"
								},
								remote: 'base_url'
							},
							type: "url"
						},
						{
							id: "theme",
							name: "Theme",
							required: true,
							type: 'select',
							options: {
								command: "GET_THEMES"
							},
							validate: {
								remote: 'theme'
							}
						},
						{
							id: "language",
							name: "Language",
							required: true,
							type: "select",
							options: [
								{
									value: "en-EN",
									text: "English (en-EN)"
								},
								{
									value: "fr-FR",
									text: "Français (fr-FR)"
								}
							]
						},
						{
							id: "timezone",
							name: "Timezone",
							required: true,
							type: "select",
							hr: true,
							options: {
								url: "installer/view/timezones.html"
							}
						},
						{
							id: "front_app",
							name: "Front app.",
							required: true,
							type: 'select',
							options: {
								command: "GET_FRONT_APPS"
							},

							validate: {
								remote: 'front_app'
							}
						},
						{
							id: "admin_app",
							name: "Admin app.",
							required: true,
							type: 'select',
							options: {
								command: "GET_ADMIN_APPS"
							},

							validate: {
								remote: 'admin_app'
							}
						}
					]
				},
				{
					id: "database",
					name: "Database",
					tab: true,
					required: true,

					childs: [
						{
							id: "credentials",
							name: "Credentials",
							virtual: true,
							required: true,
							summary: true,
							errorsOnChilds: true,
							hr: true,

							validate: {
								remote: 'db_credentials'
							},

							childs: [
								{
									id: "dbserver",
									name: "Server",
									required: true,
									summary: false
								},
								{
									id: "dbport",
									name: "Port",

									validate: {
										local: {
											type: "regexp",
											options: "^[0-9]*$",
											message: "Database port must be a number"
										}
									},

									type: "number",
									placeholder: "3306",
									summary: false
								},
								{
									id: "dbuser",
									name: "User",

									required: true,
									summary: false
								},
								{
									id: "dbpassword",
									name: "Password",

									type: "password",
									summary: false
								}
							]
						},
						{
							id: "dbname",
							name: "Database name",
							required: true,
							requires: "credentials",

							validate: {
								remote: 'db_name'
							}
						},
						{
							id: "dbprefix",
							name: "Tables prefix",
							requires: "dbname",
							validEmpty: true,

							validate: {
								remote: 'tables_prefix'
							}
						}
					]
				},
				{
					id: "user",
					name: "User",
					tab: true,
					required: true,

					childs: [
						{
							id: "nickname",
							name: "Nickname",
							required: true,

							validate: {
								local: {
									type: "regexp",
									options: "^[a-zA-Z0-9]([a-zA-Z0-9])*$",
									message: "The nickname must be an alphanumeric value."
								},
								remote: 'user_nickname'
							}
						},
						{
							id: "upassword",
							name: "Password",
							required: true,
							virtual: true,
							summary: true,

							validate: {
								remote: 'user_password'
							},

							childs: [
								{
									id: "password",
									name: "Password",
									required: true,
									summary: false,

									type: "password"
								},
								{
									id: "confirm",
									name: "Confirmation",
									required: true,
									summary: false,

									validate: {
										local: {
											type: "equals",
											options: "password",
											message: "Password and confirmation are different."
										}
									},

									type: "password"
								}
							]
						},
						{
							id: "email",
							name: "Email",
							required: true,
							hr: true,

							validate: {
								local: {
									type: "regexp",
									options: "^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$",
									message: "This email is not valid."
								},
								remote: 'user_email'
							},

							type: "email"
						},
						{
							id: "firstname",
							name: "Firstname",

							validate: {
								local: {
									type: "regexp",
									options: "^[a-zA-Z]([- a-zA-Z])*$",
									message: "The firstname is not valid."
								},
								remote: 'user_firstname'
							}
						},
						{
							id: "lastname",
							name: "Lastname",

							validate: {
								local: {
									type: "regexp",
									options: "^[a-zA-Z]([- a-zA-Z])*$",
									message: "The lastname is not valid."
								},
								remote: 'user_lastname'
							}
						}
					]
				}
			]
		}

		new Form(installer);

	})();
});
