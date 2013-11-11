<?php defined('IN_WITY') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Contact</name>
	
	<version>0.4</version>
	
	<!-- Last update date -->
	<date>02-10-2013</date>
	
	<!-- Tiny icone to be displayed in the admin board -->
	<icone></icone>

	<!-- Dependencies -->
	<dependencies>
		<app>user</app>
	</dependencies>
	
	<!-- Front pages -->
	<action default="default">form</action>
	
	<!-- Admin pages -->
	<admin>
		<action desc="mail_history" default="default" menu="false">mail_history</action>
		<action menu="false">mail_detail</action>
		<!-- <action desc="new_mail" alias="reply">new_mail</action> -->
	</admin>
</app>
