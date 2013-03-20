<?php defined('IN_WITY') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8"?>
<app>
	<!-- Application name -->
	<name>News</name>
	
	<version>0.1</version>
	
	<!-- Last update date -->
	<date>17-01-2013</date>
	
	<!-- Tiny icone to be displayed in the admin board -->
	<icone></icone>
	
	<!-- Services available to communicate with the application -->
	<!--<service>
		<name>last_news</name>
		<view>block_last_news.html</view>
	</service>-->
	
	<!-- Front actions
		Restriction rules :
		  0 - Public
		  1 - User connected required
		  2 - Require specific access -->
	<action restriction="1" default="default">index</action>
	<action desc="Affichage d'un article">detail</action>
	
	<!-- Admin actions -->
	<admin>
		<action desc="Liste des articles" default="1">index</action>
		<action desc="Ajouter un article">add</action>
		<action desc="Édition d'un article" menu="false">edit</action>
		<action desc="Suppression d'un article" menu="false">del</action>
		<action desc="Gestion des catégories">cat</action>
	</admin>
</app>