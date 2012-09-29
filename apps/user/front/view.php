<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author	Fofif <Johan Dufau>
 * @version	$Id: apps/user/front/view.php 0001 12-05-2011 Fofif $
 */

class UserView extends WView {
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Petite vue pour le formulaire de connexion
	 */
	public function connexion($redirect) {
		$this->assign('redirect', $redirect);
	}
}

?>