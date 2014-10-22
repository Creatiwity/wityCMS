<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Page</name>
	
	<version>0.4.0</version>
	
	<!-- Last update date -->
	<date>22-10-2013</date>
	
	<default_lang>fr</default_lang>
	
	<!-- Permissions -->
	<permission name="writer" />
	<permission name="moderator" />
	
	<!-- Front pages -->
	<action>listing</action>
	<action default="default">display</action>
	
	<!-- Admin pages -->
	<admin>
		<action default="default" description="action_listing">listing</action>
		<action requires="writer" description="action_add" alias="add">form</action>
		<action requires="writer" description="action_edit" menu="false">edit</action>
		<action requires="moderator" description="action_delete" menu="false">delete</action>
	</admin>
</app>