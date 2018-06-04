<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Team</name>

	<version>0.6.2</version>

	<!-- Last update date -->
	<date>07-06-2015</date>

	<!-- Front actions -->
	<action default="default">members</action>

	<!-- Admin actions -->
	<admin>
		<action default="default" description="Members">members</action>
		<action menu="false">member-add</action>
		<action menu="false">member-edit</action>
		<action menu="false">member-delete</action>
		<action menu="false">members-reorder</action>
	</admin>
</app>
