<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Settings</name>

	<version>0.5.0-11-02-2016</version>

	<!-- Last update date -->
	<date>22-10-2014</date>

	<!-- Admin actions -->
	<admin>
		<action default="default" description="General">general</action>
		<action description="SEO">seo</action>
		<action description="Coordinates">coordinates</action>
		<action description="Languages">languages</action>
		<action menu="false">language_add</action>
		<action menu="false">language_edit</action>
		<action menu="false">language_delete</action>

		<action description="Translate">translate</action>
		<action menu="false">translate_app</action>
		<action menu="false">translate_theme</action>
		<action menu="false">translate_core</action>
	</admin>
</app>
