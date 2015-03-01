<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Newsletter</name>
	
	<version>1.0.0</version>
	
	<!-- Last update date -->
	<date>01-03-2015</date>
	
	<!-- Front pages -->
	<action default="default">add</action>
	
	<!-- Admin pages -->
	<admin>
		<action default="default" description="action_listing">listing</action>
		<action requires="moderator" description="action_delete" menu="false">delete</action>
		<action requires="moderator" description="action_export">export</action>
	</admin>
</app>
