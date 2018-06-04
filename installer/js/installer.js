/**
 * Installer form description
 *
 * @package Installer
 * @version 0.6.2-04-06-2018
 */

$(window).ready(function() {

	(function () {

		var installer = {
			id: "witycms",

			childs: [
				{
					id: "general",
					name: "General",
					tab: true,
					required: true,
					image: 'installer/img/img-01.png',
					introduction: '<h2>wityCMS is incredibly customizable</h2>' +
						'<p>After the installation, you will be able to connect to an administration panel to edit the configuration of your website. wityCMS enables the creation of new applications quickly from another one based on the retriever concept (more details by clicking on the question mark).</p>' +
						'<p><em>NB:</em> The site name will be displayed on the top-left corner of the administration. The base URL is the URL of your website which can be read from the top bar of your browser.</p>',

					childs: [
						{
							id: "site_title",
							name: "Site name",
							required: true,
							validate: {
								remote: 'site_title'
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
									value: "en_EN",
									text: "English (en_EN)"
								},
								{
									value: "fr_FR",
									text: "Français (fr_FR)"
								}
							]
						},
						{
							id: "timezone",
							name: "Timezone",
							required: true,
							type: "select",
							options: {
								url: "installer/view/timezones.html"
							}
						}
					]
				},
				{
					id: "applications",
					name: "Applications",
					tab: true,
					required: true,
					image: 'installer/img/img-02.png',
					introduction: '<h2>Define your home page</h2>' +
						'<p>The Home application is the first application executed by wityCMS and that will be displayed when users connect to the root page of your website. For instance, if you want to create a blog, we advise you to select the News application.</p>' +
						'<p>The main admin application is the first application loaded when you connect to the administration. We advise you to select the application you will use most of the time (user or news for instance).</p>',

					childs: [
						{
							id: "front_app",
							name: "Home application",
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
							name: "Main admin application",
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
					image: 'installer/img/img-03.png',
					introduction: '<h2>Configure the database</h2>' +
						'<p>wityCMS needs a MySQL database to run which is available on most of the web servers. Fill in the credentials provided by your hosting company in the form.</p>' +
						'<p>The prefix tables field allows you to defined the prefix added at the begining of the name of the tables. We advise you to let the value "wity" (nb: an underscore character will be automatically added after the prefix).</p>',

					childs: [
						{
							id: "credentials",
							name: "Credentials",
							virtual: true,
							required: true,
							summary: true,
							errorsOnChilds: true,

							validate: {
								remote: 'db_credentials'
							},

							childs: [
								{
									id: "dbserver",
									name: "Server",
									required: true,
									summary: false,
									value: 'localhost'
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
									summary: false,
									value: 'root'
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
							name: "Prefix for tables",
							requires: "dbname",
							validEmpty: true,
							value: 'wity',

							validate: {
								remote: 'tables_prefix'
							}
						}
					]
				},
				{
					id: "user",
					name: "Admin account",
					tab: true,
					required: true,
					image: 'installer/img/img-04.png',
					introduction: '<h2>One step left before launching the installation!</h2>' +
						'<p>The following fields will allow you create your user account. This account will have full power to do modifications in the administration. Please, chose carefully your password.</p>' +
						'<p>Once you click on the "Launch install" button, wityCMS will install and you will be able connect to the administration panel.</p>',

					childs: [
						{
							id: "nickname",
							name: "Nickname",
							required: true,

							validate: {
								local: {
									type: "regexp",
									options: "^[a-zA-Z0-9\.@_]+$",
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
							errorsOnChilds: true,

							validate: {
								local: {
									type: 'equals',
									message: "Password and confirmation are different."
								},
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

									type: "password"
								}
							]
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
						},
						{
							id: "email",
							name: "Email",
							required: true,

							validate: {
								local: {
									type: "regexp",
									options: "^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$",
									message: "This email is not valid."
								},
								remote: 'user_email'
							},

							type: "email"
						}
					]
				}
			]
		};

		new Form(installer);

	})();
});
