<?php defined('WITYCMS_VERSION') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8" ?>
<app>
	<!-- Application name -->
	<name>User</name>

	<version>0.6.2</version>

	<!-- Last update date -->
	<date>09-05-2015</date>

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
		<action default="default" description="Users">users</action>
		<action requires="add" description="Add a user" menu="false">add</action>
		<action requires="edit" description="Edit a user" menu="false">edit</action>
		<action requires="delete" description="Delete a user" menu="false">delete</action>
		<action requires="group_manager" description="Groups">groups</action>
		<action requires="group_manager,delete" description="Delete a groupe" menu="false">group_del</action>
		<action requires="group_manager,edit" menu="false">group_diff</action>
		<action requires="group_manager,edit" menu="false">load_users_with_letter</action>
		<action requires="config" description="Configuration">config</action>
	</admin>
</app>
