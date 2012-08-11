<?php
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author Fofif <Johan Dufau>
 * @version	$Id: apps/contact/front/view.php 0000 19-05-2011 Julien1619 $
 */

class ContactView extends WView {
	private $model;
	
	public function __construct(ContactModel $model) {
		parent::__construct();
		$this->model = $model;
	}
	
	/**
	 * Définition des valeurs de contenu du formulaire
	 */
	private function fillForm($model, $data) {
		foreach ($model as $item => $default) {
			$this->assign($item, isset($data[$item]) ? $data[$item] : $default);
		}
	}
	
	public function contact($data = array()) {
		$this->assign('css', '/apps/contact/front/css/style.css');
		$this->assign('pageTitle', 'Contactez le Forum Est-Horizon');
		
		$this->fillForm(
			array(
				'name' => !empty($_SESSION['prenom']) && !empty($_SESSION['nom']) ? $_SESSION['prenom'].' '.$_SESSION['nom'] : '', 
				'organisme' => isset($_SESSION['firmname']) ? $_SESSION['firmname'] : '',
				'email' => isset($_SESSION['email'] ) ? $_SESSION['email'] : '',
				'objet' => '',
				'message' => ''
			),
			$data
		);
		
		// Captcha
		require_once LIBS_DIR.'recaptcha-php-1.11/recaptchalib.php';
		$publickey = "6LdLDMoSAAAAAKsDcpaSAkHjx0NaMz_apMviI737";
		$this->assign('captcha', recaptcha_get_html($publickey));
	}
}

?>