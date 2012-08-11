<?php defined('IN_WITY') or die('Access denied');
/**
 * WTemplate
 * Moteur de template pour le CMS Wity
 *
 * @author     Fofif
 * @version    $Id: WTemplate/WTemplateFile.php 0003 04-08-2012 Fofif $
 * @package    Wity
 * @subpackage WTemplate
 */

class WTemplateFile {
	/**
	 * Chemin complet du fichier
	 */
	private $href;
	
	/**
	 * Répertoire racine
	 */
	private $baseDir;
	
	/**
	 * Répertoire des fichiers compilés
	 */
	private $compilationDir;
	
	/**
	 * Chemin du fichier compilé
	 */
	private $compilationHref;
	
	/**
	 * Date de création du fichier .tpl original
	 */
	private $creationTime;
	
	/**
	 * Etat de la compilation
	 */
	private $compiled = false;
	
	/**
	 * Durée de la compilation
	 */
	private $compilationTime = 0;
	
	public function __construct($href, $baseDir, $compDir) {
		// Vérification de l'existance du fichier
		if (!file_exists($href)) {
			throw new Exception("WTemplateFile::__construct(): File \"".$href."\" does not exist.");
		}
		
		$this->href = $href;
		$this->baseDir = $baseDir;
		$this->compilationDir = $compDir;
		$this->compilationHref = $this->createCompilationHref();
		$this->creationTime = filemtime($href);
	}
	
	/**
	 * Génère le chemin complet du fichier compilé
	 */
	private function createCompilationHref() {
		return $this->compilationDir
			.str_replace(array('/', '\\'), '-', trim(str_replace($this->baseDir, '', dirname($this->href)), '\/'))
			.'-'.str_replace(array('.html', '.tpl'), '.php', basename($this->href));
	}
	
	public function getCompilationHref() {
		return $this->compilationHref;
	}
	
	public function getCompilationTime() {
		return $this->compilationTime;
	}
	
	/**
	 * Vérifie si une compilation du fichier est nécessaire
	 * 
	 * @param string $file Le nom du fichier
	 * @return bool Le cache est-il valide ?
	 */
	private function checkCompilation()	{
		if ($this->compiled) {
			return true;
		}
		
		// Le fichier n'existe pas
		if (!file_exists($this->compilationHref)) {
			return false;
		}
		
		// Le fichier compilé est trop vieux
		$cacheDate = @filemtime($this->compilationHref);
		if ($cacheDate < $this->creationTime) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Compilation du fichier
	 * 
	 * @param WTemplateCompiler $compiler This object is a compiler that will compile each nodes
	 * @return void
	 */
	public function compile(WTemplateCompiler $compiler) {
		// Vérification de la compilation
		if (!$this->checkCompilation()) {
			$start = microtime(true);
			
			// Read template file
			if (!($string = file_get_contents($this->href))) {
				throw new Exception("WTemplateFile::compile() : Unable top read file \"".$this->href."\".");
			}
			
			// Compile file
			$code = $compiler->compileString($string, array('dir' => dirname($this->href)));
			
			// Enregistrement du fichier compilé
			$this->saveFile($code);
			
			// Mise à jour des infos
			$this->compilationTime = microtime(true) - $start; // temps de génération
		}
		
		$this->compiled = true; // fichier compilé
	}
	
	/**
	 * Enregistre le fichier
	 */
	private function saveFile($data) {
		// Ouverture
		$handle = fopen($this->compilationHref, 'w');
		if (!$handle) {
			throw new Exception("WTemplateFile::saveFile(): Unable to open cache file \"".$this->compilationHref."\".");
		}
		
		// Ecriture
		fwrite($handle, $data);
		fclose($handle);
		
		// Chmod
		chmod($this->compilationHref, 0777);
	}
}

?>