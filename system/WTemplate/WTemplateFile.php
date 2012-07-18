<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author    Fofif
 * @version   $Id: WTemplate/WTemplateFile.php 0002 28-04-2012 fofif $
 * @desc      Moteur de template, classe fichier
 */

class WTemplateFile {
	// Chemin complet du fichier
	private $href;
	
	// Répertoire racine
	private $baseDir;
	
	// Répertoire des fichiers compilés
	private $compilationDir;
	
	// Chemin du fichier compilé
	private $compilationHref;
	
	// Date de création du fichier .tpl original
	private $creationTime;
	
	// Etat de la compilation
	private $compiled = false;
	
	// Durée de la compilation
	private $compilationTime = 0;
	
	public function __construct($href, $baseDir, $compDir) {
		// Vérification de l'existance du fichier
		if (!file_exists($href)) {
			throw new Exception("Le fichier '".$href."' est introuvable");
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
			.str_replace(array('/', '\\'), '-', trim(str_replace($this->baseDir, '', dirname($this->href)), DS))
			.'-'.str_replace('.tpl', '.tpl.php', basename($this->href));
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
	 * @param WTemplateCompiler Un compilateur de template
	 * @return void
	 */
	public function compile($compiler) {
		// Vérification de la compilation
		if (!$this->checkCompilation()) {
			// Temps marquant le début de la compilation
			$start = microtime(true);
			
			// Ouverture du fichier
			if (!($handler = fopen($this->href, 'r'))) {
				throw new Exception("WTemplateFile::compile() : Impossible d'ouvrir le fichier ".$this->href);
			}
			
			$code = "";
			$char = '';
			$level = 0;
			$tmp = "";
			// Tant qu'on est pas arrivé à la fin du fichier
			while (!feof($handler)) {
				// Récupération du caractère suivant
				$char = fgetc($handler);
				
				switch ($char) {
					case '{':
						if ($level > 0) {
							$tmp .= '{';
						}
						
						// On vérifie que l'accolade n'a pas été commentée
						if (strlen($code) > 0 && $code[strlen($code)-1] != '\\') {
							$level++;
						}
						break;
					
					case '}':
						// On vérifie que l'accolade n'a pas été commentée
						if (strlen($code) > 0 && $code[strlen($code)-1] != '\\') {
							$level--;
							// On a atteint le fermeture de la première accolade, on compile la chaîne trouvée
							if ($level == 0) {
								$code .= $compiler->compileTplCode($tmp);
								$tmp = "";
							}
						}
						
						if ($level > 0) {
							$tmp .= '}';
						}
						break;
					
					default: // Cas d'un caractère quelconque
						if ($level == 0) {
							// Si on est pas dans un sous niveau d'accolade, c'est du texte et on l'ajoute simplement à la chaîne compilée
							$code .= $char;
						} else {
							// Sinon, on la met dans la variable temporaire
							$tmp .= $char;
						}
						break;
				}
			}
			
			// Fermeture du fichier
			fclose($handler);
			
			// Suppression des short tags pour le xml
			$code = str_replace("<?xml", "<?php echo '<?xml'; ?>", $code);
			
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
			throw new Exception("Impossible d'ouvrir le fichier cache : ".$this->compilationHref);
		}
		
		// Ecriture
		fwrite($handle, $data);
		fclose($handle);
		
		// Chmod
		chmod($this->compilationHref, 0777);
	}
}

?>