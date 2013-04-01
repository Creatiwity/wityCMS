<?php defined('IN_WITY') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8"?>
<app>
	<!-- Application name -->
	<name>Page</name>
	
	<version>0.1</version>
	
	<!-- Last update date -->
	<date>20-01-2013</date>
	
	<!-- Tiny icone to be displayed in the admin board -->
	<icone></icone>
	
	<!-- Front actions
		Restriction rules :
		  0 - Public
		  1 - User connected required
		  2 - Require specific access -->
	<action default="default">display</action>
	
	<!-- Admin actions -->
	<admin>
		<action desc="Liste des actions" default="1">liste</action>
		<action desc="Ajouter une action">add</action>
		<action desc="Éditer une action" menu="false">edit</action>
		<action desc="Supprimer une action" menu="false">del</action>
	</admin>
</app>