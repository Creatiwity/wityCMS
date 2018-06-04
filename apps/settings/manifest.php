<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Settings</name>

	<version>0.6.2</version>

	<!-- Last update date -->
	<date>20-11-2016</date>

	<!-- Admin actions -->
	<admin>
		<action default="default" description="General">general</action>
		<action description="SEO">seo</action>
		<action description="Coordinates">coordinates</action>
		<action description="Languages">languages</action>
		<action menu="false" description="Add a language">language_add</action>
		<action menu="false" description="Edit a language">language_edit</action>
		<action menu="false" description="Delete a language">language_delete</action>

		<action description="Translate">translate</action>
		<action menu="false" description="App Translator">translate_app</action>
		<action menu="false" description="Theme Translator">translate_theme</action>
	</admin>
</app>
