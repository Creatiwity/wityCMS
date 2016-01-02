<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Team</name>

	<version>0.5.0-dev-02-01-2015</version>

	<!-- Last update date -->
	<date>07-06-2015</date>

	<!-- Front pages -->
	<action default="default">members</action>

	<!-- Admin pages -->
	<admin>
		<action default="default">members</action>
		<action menu="false">member-add</action>
		<action menu="false">member-edit</action>
		<action menu="false">member-delete</action>
		<action menu="false">members-reorder</action>
	</admin>
</app>
