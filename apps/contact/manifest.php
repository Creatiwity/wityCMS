<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Contact</name>

	<version>0.6.2</version>

	<!-- Last update date -->
	<date>02-10-2013</date>

	<!-- Front actions -->
	<action default="default">form</action>

	<!-- Admin actions -->
	<admin>
		<action default="default" description="Inbox">mail_history</action>
		<action menu="false">mail_detail</action>
		<action description="Configuration">config</action>
		<action menu="false">download</action>
	</admin>
</app>
