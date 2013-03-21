<?php defined('IN_WITY') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8"?>
<app>
	<!-- Application name -->
	<name>User</name>
	
	<version>0.3</version>
	
	<!-- Last update date -->
	<date>05-03-2013</date>
	
	<!-- Tiny icone to be displayed in the admin board -->
	<icone></icone>
	
	<!-- Permissions -->
	<permission name="add" />
	<permission name="edit" />
	<permission name="delete" />
	<permission name="groups" />
	<permission name="config" />
	
	<!-- Front actions -->
	<action default="default" alias="connexion">login</action>
	<action requires="connected" alias="deconnexion">logout</action>
	<action requires="not-connected">register</action>
	<action requires="not-connected">confirm</action>
	<action requires="not-connected" alias="password-lost">password_lost</action>
	
	<!-- Admin actions -->
	<admin>
		<action desc="action_listing" default="default">listing</action>
		<action desc="action_add" requires="add">add</action>
		<action desc="action_edit" menu="false" requires="edit">edit</action>
		<action desc="action_delete" menu="false" requires="delete">del</action>
		<action desc="action_groups" requires="groups">Groups</action>
		<action desc="action_group_del" menu="false" requires="groups,delete">group_del</action>
		<action menu="false" requires="groups,edit">group_diff</action>
		<action menu="false" requires="groups,edit">load_users_with_letter</action>
		<action desc="action_config" requires="config">config</action>
	</admin>
</app>