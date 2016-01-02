<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Page</name>

	<version>0.5.0-dev-02-01-2015</version>

	<!-- Last update date -->
	<date>23-03-2015</date>

	<!-- Permissions -->
	<permission name="writer" />
	<permission name="moderator" />

	<!-- Front actions -->
	<action default="default">display</action>

	<!-- Admin actions -->
	<admin>
		<action default="default">pages</action>
		<action requires="writer" description="page_add" menu="false">add</action>
		<action requires="writer" description="page_edit" menu="false">edit</action>
		<action requires="moderator" description="page_delete" menu="false">delete</action>
	</admin>
</app>
