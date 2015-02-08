<?php defined('IN_WITY') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>Slideshow</name>
	
	<version>1.0.0</version>
	
	<!-- Last update date -->
	<date>07-02-2015</date>
	
	<!-- Front actions -->
	<action default="default">block</action>
	
	<!-- Admin pages -->
	<admin>
		<action default="default" description="Slides">slides</action>
		<action menu="false">slide_add</action>
		<action menu="false">slide_edit</action>
		<action menu="false">slide_delete</action>
		<action menu="false">slides_reorder</action>
		<action>configuration</action>
	</admin>
</app>
