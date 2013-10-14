/**
 * Script for Installer module
 * 
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4
 */
$(document).ready(function() {

	var Form, FormNode;

	Form = (function() {

		function Form() {

		}

		return Form;

	});

	FormNode = (function() {

		function FormNode() {

		}

		return FormNode;

	});

	(function () {

		var installer = {
			id: "witycms",

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
								remote: true
							}
						},
						{
							id: "base",
							name: "Base URL",
							required: true,
							validate: {
								local: {
									type: "regexp",
									options: "^(http|https|ftp)\:\/\/[A-Z0-9][A-Z0-9_-]*(\.[A-Z0-9][A-Z0-9_-]*)*(\/[A-Z0-9~\._-]+)*\/?$", 
									message: "Base URL must be a valid URL"
								},
								remote: true
							},
							type: "url"
						},
						{
							id: "theme",
							name: "Theme",
							required: true,
							validate: {
								remote: true
							},
							autocomplete: "GET_THEMES"
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
							name: "Name",
							required: true,
							type: "select",
							options: {
								url: "/installer/view/timezones.html"
							}
						},
						{
							id: "front_app",
							name: "Front app.",
							required: true,
							autocomplete: "GET_FRONT_APPS"
						},
						{
							id: "admin_app",
							name: "Admin app.",
							required: true,
							autocomplete: "GET_ADMIN_APPS"
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

							validate: {
								remote: true
							},

							childs: [
								{
									id: "dbserver",
									name: "Server",
									required: true
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
									}

									type: "number",
									placeholder: "3306"
								},
								{
									id: "dbuser",
									name: "User",

									required: true
								},
								{
									id: "dbpassword",
									name: "Password",

									type: "password"
								}
							]
						},
						{
							id: "dbname",
							name: "Database name",
							required: true,
							requires: "credentials",

							validate: {
								remote: true
							}
						},
						{
							id: "dbprefix",
							name: "Tables prefix",
							requires: "dbname",

							validate: {
								remote: true
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
							name: "Pseudo",
							required: true,

							validate: {
								local: {
									type: "regexp",
									options: "^[a-zA-Z0-9]([a-zA-Z0-9])*$",
									message: "The nickname must be an alphanumeric value."
								},
								remote: true
							}
						},
						{
							id: "upassword",
							name: "Password",
							required: true,

							validate: {
								remote: true
							}

							childs: [
								{
									id: "password",
									name: "Password",
									required: true,

									type: "password"
								},
								{
									id: "confirm",
									name: "Confirmation",
									required: true,

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

							validate: {
								local: {
									type: "regexp",
									options: "^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$",
									message: "This email is not valid."
								},
								remote: true
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
								remote: true
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
								remote: true
							}
						}
					]
				}
			]
		}

		Form(installer);

	})();
});
