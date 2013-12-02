<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Media</name>

	<version>0.4</version>

	<!-- Last update date -->
	<date>14-09-2013</date>

	<!-- Tiny icone to be displayed in the admin board -->
	<icone></icone>

	<!-- Front pages -->
	<action>listing</action>
	<action requires="connected">upload</action>
	<action requires="connected">upload_button</action>
	<action requires="connected">metaedit</action>
	<action>relatives</action>
	<action>link</action>
	<action default="default">get</action>

	<!-- Admin pages -->
	<admin>
		<action desc="files_manager" default="default">manager</action>
		<action desc="files_cleaning">cleaning</action>
	</admin>
</app>
