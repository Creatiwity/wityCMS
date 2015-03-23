<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Page</name>
	
	<version>0.5.0</version>
	
	<!-- Last update date -->
	<date>23-03-2015</date>
	
	<!-- Permissions -->
	<permission name="writer" />
	<permission name="moderator" />
	
	<!-- Front pages -->
	<action default="default">display</action>
	
	<!-- Admin pages -->
	<admin>
		<action default="default">pages</action>
		<action requires="writer" description="action_add" alias="add" menu="false">form</action>
		<action requires="writer" description="action_edit" menu="false">edit</action>
		<action requires="moderator" description="action_delete" menu="false">delete</action>
	</admin>
</app>