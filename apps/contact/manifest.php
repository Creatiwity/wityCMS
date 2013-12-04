<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Contact</name>
	
	<version>0.4</version>
	
	<!-- Last update date -->
	<date>02-10-2013</date>

	<!-- Dependencies -->
	<dependencies>
		<app>user</app>
	</dependencies>
	
	<!-- Front pages -->
	<action default="default">form</action>
	
	<!-- Admin pages -->
	<admin>
		<action description="mail_history" default="default">mail_history</action>
		<action menu="false">mail_detail</action>
		<!-- <action description="new_mail" alias="reply">new_mail</action> -->
		<action requires="config" description="action_config">config</action>
	</admin>
</app>
