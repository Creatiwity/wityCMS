<?php
/**
 * pagination.php
 */

/**
 * Pagination generates the page counter according to the number of elements to display
 *
 * @package Helpers
 * @author Johan Dufau <johan.dufau@creatiwity.net>
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
	 * @var string URL pattern for the current page
	 */
	private $urlPattern;

	/**
	 * Setup Pagination
	 *
	 * @param type  $total          Total elements number
	 * @param type  $limit          Maximum number of elements on a page
	 * @param int   $currentPage    Page Current page
	 * @param type  $urlPattern     URL model for the current page
	 */
	public function __construct($total, $limit, $currentPage, $urlPattern) {
		$this->total = $total <= 0 ? 1 : $total;
		$this->limit = $limit;
		$this->urlPattern = $urlPattern;

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
	public function getHTML() {
		// CSS adding
		$tpl = WSystem::getTemplate();

		// Beginning of the display-chain
		$output = "<div class='row text-center'>
						<ul class='pagination'>";

		$firstDone = false;
		$lastDone = false;
		for ($i = 1; $i <= $this->n; $i++) {
			if ($i == $this->currentPage) {
				$output .= sprintf('<li class="active"><span>%d <span class="sr-only">(current)</span></span></li> ', $i);
			}
			// We are around the current page : we display it
			else if (abs($this->currentPage - $i) <= 5) {
				$output .= sprintf('<li><a href="'.urldecode($this->urlPattern).'">%d</a></li> ', $i, $i);
			}
			// Displaying something before forgetting useless pages
			else {
				// Before the current page
				if (!$firstDone && $i < $this->currentPage) {
					$firstDone = true;
					$output .= sprintf(
						'<li><a href="%s">First</a></li>
						<li><a href="%s" title="Page précédente">&laquo;</a></li> ',
						sprintf(urldecode($this->urlPattern), 1), sprintf(urldecode($this->urlPattern), $this->currentPage-1)
					);
				}
				// After the current page
				else if (!$lastDone && $i > $this->currentPage) {
					$lastDone = true;
					$output .= sprintf(
						'<li><a href="%s" title="Page suivante">&raquo;</a></li>
						<li><a href="%s">Last</a></li>',
						sprintf(urldecode($this->urlPattern), $this->currentPage+1), sprintf(urldecode($this->urlPattern), $this->n)
					);
				}
				// After interesting cases : stop
				else if ($i > $this->currentPage) {
					break;
				}
			}
		}

		$output .= '</ul></div>';
		return $output;
	}

	/**
	 * Returns the HTML code corresponding to the current page page-selector
	 *
	 * @see Pagination::getHTML()
	 * @return string HTML code of the page selector
	 */
	public function __toString() {
		return $this->getHTML();
	}
}

?>
