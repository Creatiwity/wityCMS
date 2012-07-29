<?php defined('IN_WITY') or die('Access denied');
/**
 * WTemplate
 * Moteur de template pour le CMS Wity
 *
 * @author     Fofif
 * @version    $Id: WTemplate/WTemplateParser.php 0000 29-07-2012 Fofif $
 * @package    Wity
 * @subpackage WTemplate
 */

class WTemplateParser {
	/**
	 * Href of the file to parse
	 */
	private $href;
	
	/**
	 * The file's content to parse
	 */
	private $string;
	
	/**
	 * Length of the content of the file
	 */
	private $length;
	
	public function __construct($href) {
		$this->href = $href;
		
		// Read template file
		if (!($this->string = file_get_contents($this->href))) {
			throw new Exception("WTemplateParser::__construct() : Unable top read file \"".$this->href."\".");
		}
		
		$this->length = strlen($this->string);
	}
	
	/**
	 * This function reads a string and finds node matching {im a node}
	 * and gives these nodes to a compiler which will replace them.
	 * 
	 * @param WTemplateCompiler $compiler The compiler which will work on nodes
	 * @return string Parsed and compiled template file
	 */
	public function compileNodes($compiler) {
		$level = 0;
		$code = "";
		$tmp = "";
		$last_char = '';
		$in_node = false;
		$return = null;
		
		for ($i = 0; $i < $this->length; $i++) {
			// Get next char
			$char = $this->string[$i];
			
			switch ($char) {
				case '\\': // backslash
					if ($last_char == '\\') {
						$tmp .= '\\';
					}
					break;
				
				case '{':
					$tmp .= '{';
					
					// Check whether { is backslashed
					// If we are deeper than level 0
					if ($last_char != '\\') {
						$level++;
					}
					break;
				
				case '}':
					$tmp .= '}';
					
					// Check whether it's not been backslashed
					if ($last_char != '\\' && $level > 0) {
						$level--;
						// We are arrived at the end of the node => compile it
						if ($level == 0) {
							$code .= $compiler->compileTplCode($tmp, array('filename' => $this->href));
							$tmp = "";
						}
					}
					break;
				
				default:
					if ($last_char == '\\') {
						$tmp .= '\\';
					}
					
					if ($char == "\n" && $level > 0) {
						throw new Exception("WTemplateParser::findNextNode(): found illegal carriage return character in a node.");
					}
					
					if ($level > 0) {
						$tmp .= $char;
					} else {
						$code .= $char;
					}
					break;
			}
			
			$last_char = $char;
		}
		
		// Replace xml tag to prevent short open tag conflict
		$code = str_replace("<?xml", "<?php echo '<?xml'; ?>", $code);
		
		return $code;
	}
	
	/**
	 * Find all nodes matchin a given level
	 * 
	 * @param string $string String to search into
	 * @param int $asked_level Level wanted (can be set to -1 meaning the deeper level)
	 * @return array
	 */
	public static function findAllNodes($string, $asked_level) {
		$nodes = array();
		$level = 0;
		$max_level = 0;
		$tmp = "";
		$backslash = false;
		
		for ($i = 0; $i < strlen($string); $i++) {
			// Get next char
			$char = $string[$i];
			
			switch ($char) {
				case '\\':
					$backslash = !$backslash;
					break;
				
				case '{':
					// Si on est à un niveau inférieur, on sauvegarde les accolades
					if ($level >= $asked_level && $asked_level >= 0) {
						$tmp .= '{';
					}
					
					$level++;
					
					if ($level > $max_level && $asked_level == -1) {
						$max_level = $level;
						
						// Si on demande le niveau le plus bas, on nettoie ce qu'on a trouvé précédemment car c'était au-dessus
						$nodes = array();
						$tmp = "";
					}
					break;
				
				case '}':
					// Si on est à un niveau inférieur, on sauvegarde les accolades
					if ($level > $asked_level && $asked_level >= 0) {
						$tmp .= '}';
					}
					
					// On a atteint le fermeture de la première accolade, on compile la chaîne trouvée
					if ($level == $asked_level || ($asked_level == -1 && $level == $max_level)) {
						$nodes[] = $tmp;
						$tmp = "";
					}
					
					$level--;
					break;
				
				default: // Cas d'un caractère quelconque
					if ($level >= $asked_level) {
						$tmp .= $char;
					}
					break;
			}
		}
		
		// Ajout du reste
		if ($level == $asked_level && !empty($tmp)) {
			$nodes[] = $tmp;
		}
		
		return $nodes;
	}
}

?>