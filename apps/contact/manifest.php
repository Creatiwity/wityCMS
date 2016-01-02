<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Contact</name>

	<version>0.5.0-dev-02-01-2015</version>

	<!-- Last update date -->
	<date>02-10-2013</date>

	<!-- Front actions -->
	<action default="default">form</action>

	<!-- Admin actions -->
	<admin>
		<action description="mail_history" default="default">mail_history</action>
		<action menu="false">mail_detail</action>
		<action requires="config" description="action_config">config</action>
		<action menu="false">download</action>
	</admin>
</app>
