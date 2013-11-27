<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Mail</name>

	<version>0.4.0</version>

	<!-- Last update date -->
	<date>25-11-2013</date>

	<default_lang>fr</default_lang>

	<!-- Permissions -->
	<permission name="whitelist_manager" />

	<!-- Front pages -->
	<action default="default">send</action>
	<action>redirect</action>

	<!-- Admin pages -->
	<admin>
		<action default="default" desc="mail_history">mail_history</action>
		<action requires="whitelist_manager" desc="mail_whitelist">mail_whitelist</action>
	</admin>
</app>
