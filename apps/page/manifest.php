<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Page</name>

	<version>0.6.2</version>

	<!-- Last update date -->
	<date>23-03-2015</date>

	<!-- Permissions -->
	<permission name="writer" />
	<permission name="moderator" />

	<!-- Front actions -->
	<action default="default">display</action>

	<!-- Admin actions -->
	<admin>
		<action default="default" description="Pages">pages</action>
		<action requires="writer" menu="false">add</action>
		<action requires="writer" menu="false">edit</action>
		<action requires="moderator" menu="false">delete</action>
	</admin>
</app>
