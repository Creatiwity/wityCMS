<?php
/**
 * pagination.php
 */

/**
 * Pagination generates the page counter according to the number of elements to display
 *
 * @package Helpers
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-01-04-2010
 */
class Pagination {
    
    /**
     *
     * @var int Total pages number 
     */
	private $n;
    /**
     *
     * @var int Total elements number 
     */
	private $total;
    /**
     *
     * @var int Maximum number of elements on a page
     */
	private $limit;
    /**
     *
     * @var int Current page 
     */
	private $currentPage;
    /**
     *
     * @var string URL model for the current page
     */
	private $scheme;
	
    /**
     * Setup Pagination
     * 
     * @param type  $total          Total elements number
     * @param type  $limit          Maximum number of elements on a page
     * @param int   $currentPage    Page Current page
     * @param type  $scheme         URL model for the current page
     */
	public function __construct($total, $limit, &$currentPage, $scheme) {
		$this->total = $total <= 0 ? 1 : $total;
		$this->limit = $limit;
		$this->scheme = $scheme;
		
		// Computes the number of pages (rule for inexact value : trunc(n)+1)
		$this->n = ceil($this->total / $this->limit);
		
		if ($currentPage <= 0 || $currentPage > $this->n) {
			$currentPage = 1;
		}
		$this->currentPage = $currentPage;
	}
	
    /**
     * Returns the HTML code corresponding to the current page page-selector
     * 
     * @return string HTML code of the page selector
     */
	public function getHtml() {
		// CSS adding
		$tpl = WSystem::getTemplate();
		$tpl->assign('css', $tpl->getVar('css').'<link href="/helpers/pagination/pagination.css" rel="stylesheet" type="text/css" media="screen" />');
		
		// Beginning of the display-chain
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
			// We are around the current page : we display it
			else if (abs($this->currentPage - $i) <= 3) {
				$output .= sprintf('<a href="'.$this->scheme.'">%d</a> ', $i, $i);
			}
            // Displaying something before forgetting useless pages
			else {
				// Before the current page
				if (!$firstDone && $i < $this->currentPage) {
					$firstDone = true;
					$output .= sprintf(
						'<a href="%s">First</a> 
						... 
						<a href="%s" title="Page précédente">&laquo; Prev</a> ', 
						sprintf($this->scheme, 1), sprintf($this->scheme, $this->currentPage-1)
					);
				}
				// After the current page
				else if (!$lastDone && $i > $this->currentPage) {
					$lastDone = true;
					$output .= sprintf(
						'<a href="%s" title="Page suivante">Next &raquo;</a> 
						... 
						<a href="%s">Last</a>', 
						sprintf($this->scheme, $this->currentPage+1), sprintf($this->scheme, $this->n)
					);
				}
				// After interesting cases : stop
				else if ($i > $this->currentPage) {
					break;
				}
			}
		}
		
		$output .= '</div>';
		return $output;
	}
	
    /**
     * Returns the HTML code corresponding to the current page page-selector
     * 
     * @see Pagination::getHtml()
     * @return string HTML code of the page selector
     */
	public function __toString() {
		return $this->getHtml();
	}
}

?>