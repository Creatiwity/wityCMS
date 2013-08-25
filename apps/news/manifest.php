<?php defined('IN_WITY') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>News</name>
	
	<version>0.4</version>
	
	<!-- Last update date -->
	<date>17-08-2013</date>
	
	<!-- Tiny icone to be displayed in the admin board -->
	<icone></icone>
	
	<!-- Permissions -->
	<permission name="news_editor" />
	<permission name="global_editor" />
	<permission name="deletor" />

	<!-- Dynamic permissions -->
	<dyn name="categories" table="news_cats" c-id="cid" c-parent="parent" c-name="name" />
		<filter name="" model="lvl1/lvl2/cat_id" />
	</dyn>
	
	<!-- Front pages -->
	<action default="default">listing</action>
	<action>detail</action>
	
	<!-- Admin pages -->
	<admin>
		<action desc="articles_listing" default="1">listing</action>
		<action desc="article_add" requires="news_editor" alias="add">news_form</action>
		<action desc="article_edit" requires="news_editor" menu="false">edit</action>
		<action desc="article_delete" menu="false" requires="news_editor,deletor">news_delete</action>
		<action desc="categories_management" requires="global_editor">categories_manager</action>
		<action desc="category_delete" menu="false" requires="global_editor,deletor">category_delete</action>
	</admin>
</app>