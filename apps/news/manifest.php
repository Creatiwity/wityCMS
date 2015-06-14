<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>News</name>
	
	<version>1.0.0</version>
	
	<!-- Last update date -->
	<date>02-05-2015</date>
	
	<!-- Permissions -->
	<permission name="writer" />
	<permission name="category_manager" />
	<permission name="moderator" />
	
	<!-- Front pages -->
	<action default="default">listing</action>
	<action>detail</action>
	<action>preview</action>
	
	<!-- Admin pages -->
	<admin>
		<action default="default">news</action>
		<action requires="writer" menu="false">news-add</action>
		<action requires="writer" menu="false">news-edit</action>
		<action requires="writer" menu="false">news-save-preview</action>
		<action requires="moderator" menu="false">news-delete</action>
		<action requires="category_manager">categories</action>
		<action requires="category_manager, moderator" menu="false">category-delete</action>
	</admin>
</app>
