<?php defined('IN_WITY') or die('Access denied'); ?>
<?xml version="1.0" encoding="utf-8"?>
<app>
	<!-- Application name -->
	<name>News</name>
	
	<version>0.1</version>
	
	<!-- Last update date -->
	<date>27-02-2013</date>
	
	<!-- Tiny icone to be displayed in the admin board -->
	<icone></icone>
	
	<!-- Permissions -->
	<permission name="news_editor" />
	<permission name="global_editor" />
	<permission name="deletor" />
	
	<!-- Front pages -->
	<page default="1">listing</page>
	<page lang="Affichage d'un article">detail</page>
	
	<!-- Admin pages -->
	<admin>
		<page lang="Liste des articles" default="1">news_listing</page>
		<page lang="Ajouter un article" requires="news_editor">news_add_or_edit</page>
		<page lang="Suppression d'un article" menu="false" requires="news_editor,deletor">news_delete</page>
		<page lang="Gestion des catégories" requires="global_editor">categories_manager</page>
                <page lang="Suppression d'une catégorie" menu="false" requires="global_editor,deletor">category_delete</page>
	</admin>
</app>