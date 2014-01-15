<?php defined('IN_WITY') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>User</name>
	
	<version>0.4.0</version>
	
	<!-- Last update date -->
	<date>22-10-2013</date>
	
	<!-- Permissions -->
	<permission name="add" />
	<permission name="edit" />
	<permission name="delete" />
	<permission name="group_manager" />
	<permission name="config" />
	
	<!-- Front actions -->
	<action default="default" alias="connexion">login</action>
	<action requires="connected" alias="deconnexion">logout</action>
	<action requires="not-connected">register</action>
	<action requires="not-connected">confirm</action>
	<action requires="not-connected" alias="password-lost">password_lost</action>
	
	<!-- Admin actions -->
	<admin>
		<action default="default" description="action_listing">listing</action>
		<action requires="add" description="action_add">add</action>
		<action requires="edit" description="action_edit" menu="false">edit</action>
		<action requires="delete" description="action_delete" menu="false">delete</action>
		<action requires="group_manager" description="action_groups">Groups</action>
		<action requires="group_manager,delete" description="action_group_del" menu="false">group_del</action>
		<action requires="group_manager,edit" menu="false">group_diff</action>
		<action requires="group_manager,edit" menu="false">load_users_with_letter</action>
		<action requires="config" description="action_config">config</action>
	</admin>
</app>
