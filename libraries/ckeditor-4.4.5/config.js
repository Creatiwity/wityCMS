/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	config.extraPlugins = 'youtube';
	
	config.stylesSet = [
		{ name: 'Grey logo', element: 'span', styles: { color: '#7b7c7e' } },
		{ name: 'Light Grey', element: 'span', styles: { color: '#f1f1f1' } },
		{ name: 'Green Corporate', element: 'span', styles: { color: '#65b32e' } },
		{ name: 'Orange Ultrasound', element: 'span', styles: { color: '#ee7203' } },
		{ name: 'Green Lasers', element: 'span', styles: { color: '#c3d600' } },
		{ name: 'Yellow Supra 577nm', element: 'span', styles: { color: '#ffd141' } },
		{ name: 'Blue Single Use Lenses', element: 'span', styles: { color: '#4984be' } }
	];
};
