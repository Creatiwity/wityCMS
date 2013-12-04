<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>News</name>
	
	<version>0.4.0</version>
	
	<!-- Last update date -->
	<date>22-10-2013</date>
	
	<default_lang>fr</default_lang>
	
	<!-- Permissions -->
	<permission name="writer" />
	<permission name="category_manager" />
	<permission name="moderator" />
	
	<!-- Front pages -->
	<action default="default">listing</action>
	<action>detail</action>
	
	<!-- Admin pages -->
	<admin>
		<action default="default" description="articles_listing">listing</action>
		<action requires="writer" description="article_add">add</action>
		<action requires="writer" description="article_edit" menu="false">edit</action>
		<action requires="moderator" description="article_delete" menu="false">news_delete</action>
		<action requires="category_manager" description="categories_management" >categories_manager</action>
		<action requires="category_manager,moderator" description="category_delete" menu="false">category_delete</action>
	</admin>
</app>
