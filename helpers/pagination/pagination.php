<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 * 
 * Classe destinée à générer le sommaire des pages
 * en fonction du nombre d'éléments à afficher par page
 * 
 * @author	Fofif
 * @version	$Id: pagination.php 0001 01-04-2010 Fofif $
 */

class Pagination {
	private 
		$n,
		$total,
		$limit,
		$currentPage,
		$scheme;
	
	public function __construct($total, $limit, &$currentPage, $scheme) {
		$this->total = $total <= 0 ? 1 : $total;
		$this->limit = $limit;
		$this->scheme = $scheme;
		
		// Calcul du nombre de pages (arrondi à l'entier sup)
		$this->n = ceil($this->total / $this->limit);
		
		if ($currentPage <= 0 || $currentPage > $this->n) {
			$currentPage = 1;
		}
		$this->currentPage = $currentPage;
	}
	
	public function getHtml() {
		// Ajout du css
		$tpl = WSystem::getTemplate();
		$tpl->assign('css', $tpl->getVar('css').'<link href="/helpers/pagination/pagination.css" rel="stylesheet" type="text/css" media="screen" />');
		
		// Début de la chaîne d'affichage
		$output = sprintf(
			'<div class="pages">
				<strong>Page %s sur %s</strong> ',
			$this->currentPage, $this->n
		);
		
		$firstDone = false;
		$lastDone = false;
		for ($i = 1; $i <= $this->n; $i++) {
			if ($i == $this->currentPage) {
				$output .= sprintf('<span class="current">%d</span> ', $i);
			}
			// On est autour de la page actuelle : on affiche
			else if (abs($this->currentPage - $i) <= 3) {
				$output .= sprintf('<a href="'.$this->scheme.'">%d</a> ', $i, $i);
			}
			// On affiche quelque chose avant d'omettre les pages inutiles
			else {
				// On est avant la page courante
				if (!$firstDone && $i < $this->currentPage) {
					$firstDone = true;
					$output .= sprintf(
						'<a href="%s">First</a> 
						... 
						<a href="%s" title="Page précédente">&laquo; Prev</a> ', 
						sprintf($this->scheme, 1), sprintf($this->scheme, $this->currentPage-1)
					);
				}
				// Après la page courante
				else if (!$lastDone && $i > $this->currentPage) {
					$lastDone = true;
					$output .= sprintf(
						'<a href="%s" title="Page suivante">Next &raquo;</a> 
						... 
						<a href="%s">Last</a>', 
						sprintf($this->scheme, $this->currentPage+1), sprintf($this->scheme, $this->n)
					);
				}
				// On a dépassé les cas qui nous intéressent : inutile de continuer
				else if ($i > $this->currentPage) {
					break;
				}
			}
		}
		
		$output .= '</div>';
		return $output;
	}
	
	public function __toString() {
		return $this->getHtml();
	}
}

?>