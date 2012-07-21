<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author    Fofif
 * @version   $Id: WTemplate/WTemplate.php 0007 12-12-2010 fofif $
 * @desc      Moteur de template
 */

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

include dirname(__FILE__).DS.'WTemplateCompiler.php';
include dirname(__FILE__).DS.'WTemplateFile.php';

class WTemplate {
	// Répertoire des fichiers compilés
	private $compileDir;
	
	// Variables templates
	private $tpl_vars = array();
	
	// Liste des fichiers à ne pas afficher
	private $cancelPrinting = array();
	
	// Le compilateur (cf fonction compile)
	private $compiler;
	
	public function __construct($baseDir, $compileDir = '') {
		if (is_dir($baseDir)) {
			$this->baseDir = $baseDir;
		} else {
			throw new Exception("WTemplate::__construct(): Directory \"".$baseDir."\" does not exist.");
		}
		
		// Valeur par défaut
		$this->compileDir = '.'.DS.'tpl_compiled'.DS;
		// On tente de mettre à jour si un répertoire est fourni
		if (!empty($compileDir)) {
			$this->setCompileDir($compileDir);
		}
		
		$this->compiler = new WTemplateCompiler();
	}
	
	public function setCompileDir($compileDir) {
		if (is_dir($compileDir)) {
			$this->compileDir = $compileDir;
		}
	}
	
	/**
	 * Assignation des variables de template
	 */
	public function assign($a, $b = null) {
		if (is_array($a)) {
			$this->tpl_vars = array_merge($this->tpl_vars, $a);
		} else {
			$this->tpl_vars[$a] = $b;
		}
	}
	
	/**
	 * Assignation des variables de block
	 */
	public function assignBlockVars($blockName, $value) {
		if (!isset($this->tpl_vars[$blockName.'_block'])) {
			$this->tpl_vars[$blockName.'_block'] = array($value);
		} else {
			$this->tpl_vars[$blockName.'_block'][] = $value;
		}
	}
	
	/**
	 * Ajoute des élèments à la suite d'une variable
	 * (la transforme en array le cas échéant)
	 */
	public function append($var, $value) {
		if (isset($this->tpl_vars[$var])) {
			if (!is_array($this->tpl_vars[$var])) {
				settype($this->tpl_vars[$var], 'array');
			}
			$this->tpl_vars[$var][] = $value;
		} else {
			$this->tpl_vars[$var] = array($value);
		}
	}
	
	/**
	 * Récupération d'une variable de template
	 */
	public function getVar($var) {
		if (isset($this->tpl_vars[$var])) {
			return $this->tpl_vars[$var];
		} else {
			return '';
		}
	}
	
	/**
	 * Suppression de variables template
	 * 
	 * @param $vars mixed
	 */
	public function clear($vars) {
		if (is_array($vars)) {
			foreach ($vars as $v) {
				unset($this->tpl_vars[$v]);
			}
		} else {
			unset($this->tpl_vars[$vars]);
		}
	}
	
	/**
	 * Parsage et affichage d'un fichier
	 * 
	 * @param string $href adresse du fichier
	 */
	public function display($href) {
		// On vérifie que le fichier peut être imprimé
		if (!isset($this->cancelPrinting[$href])) {
			$file = new WTemplateFile($href, $this->baseDir, $this->compileDir);
			
			// On compile le fichier
			$file->compile($this->compiler);
			
			// Inclusion du fichier compilé pour qu'il s'exécute
			include $file->getCompilationHref();
		}
	}
	
	/**
	 * Retourne le résultat d'un fichier .tpl parsé
	 * 
	 * @param string $file Le nom du fichier
	 * @return string Code compilé
	 */
	public function parse($href) {
		$file = new WTemplateFile($href, $this->baseDir, $this->compileDir);
		$file->compile($this->compiler);
		
		ob_start();
		eval('?>'.file_get_contents($file->getCompilationHref()));
		$result = ob_get_contents();
		ob_end_clean();
		
		return $result;
	}
}

?>