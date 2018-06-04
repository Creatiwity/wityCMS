<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Newsletter</name>

	<version>0.6.2</version>

	<!-- Last update date -->
	<date>01-03-2015</date>

	<!-- Front actions -->
	<action default="default">add</action>

	<!-- Admin actions -->
	<admin>
		<action default="default" description="Subscribers">listing</action>
		<action requires="moderator" menu="false">delete</action>
		<action requires="moderator" description="Export">export</action>
	</admin>
</app>
