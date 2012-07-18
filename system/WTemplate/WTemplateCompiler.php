<?php defined('IN_WITY') or die('Access denied');
/**
 * Wity CMS
 * Système de gestion de contenu pour tous.
 *
 * @author    Fofif
 * @version   $Id: WTemplate/WTemplateCompiler.php 0004 28-04-2012 Fofif $
 * @desc      Compilateur du moteur de template
 */

class WTemplateCompiler {
	/**
	 * Compilation d'un élément bien précis
	 */
	public function compileTplCode($tpl_code) {
		// Affichage d'une variable
		if ($tpl_code[0] == '$') {
			$output = $this->compile_var("name=".$tpl_code);
		}
		// Fermeture d'une balise
		else if ($tpl_code[0] == '/') {
			$handler = 'compile_'.trim($tpl_code, '/').'_close';
			if (method_exists('WTemplateCompiler', $handler)) {
				// Appel de la fonction
				$output = $this->$handler();
			} else {
				$output = '';
			}
		} else {
			// Récupération du nom de balise : {"name" ...}
			preg_match('#^([a-zA-Z0-9_]+)#', $tpl_code, $matches);
			$name = $matches[0];
			$handler = 'compile_'.$name;
			
			// On retire le name pour récupérer les arguments
			$args = substr($tpl_code, strlen($name));
			
			if (method_exists('WTemplateCompiler', $handler)) {
				// Appel de la fonction
				$output = $this->$handler($args);
			} else {
				$output = '';
			}
		}
		
		// Suppression des short tags pour le xml
		$output = preg_replace("#<?(^php)#", "", $output);
		
		return $output;
	}
	
	/**
	 * Trouve le niveau d'accolade recherché
	 * @param string $string chaîne de recherche
	 * @param int $asked_level niveau souhaité (-1 = niveau le plus bas)
	 * @return array
	 */
	public static function findCode($string, $asked_level) {
		$vars = array();
		$level = 0;
		$max_level = 0;
		$tmp = "";
		
		// Parcours de la chaîne
		for ($i = 0; $i < strlen($string); $i++) {
			// Récupération du caractère suivant
			$char = $string[$i];
			
			switch ($char) {
				case '{':
					// Si on est à un niveau inférieur, on sauvegarde les accolades
					if ($level >= $asked_level && $asked_level >= 0) {
						$tmp .= '{';
					}
					
					$level++;
					
					if ($level > $max_level && $asked_level == -1) {
						$max_level = $level;
						
						// Si on demande le niveau le plus bas, on nettoie ce qu'on a trouvé précédemment car c'était au-dessus
						$vars = array();
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
						$vars[] = $tmp;
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
			$vars[] = $tmp;
		}
		
		return $vars;
	}
	
	/**
	 * Recherche dans une chaîne une variable de la forme {$var} pour la parser via self::getVar()
	 */
	public static function parseVars($string) {
		$string = trim($string);
		
		$vars = self::findCode($string, 1);
		foreach ($vars as $v) {
			if ($v[0] == '$') {
				$string = str_replace('{'.$v.'}', WTemplateCompiler::getVar($v), $string);
			}
		}
		
		return $string;
	}
	
	/**
	 * Convertit une variable de la forme {$var.x} sous forme php
	 * Possibilité d'utiliser un sous niveau de variable (ex : {$var1.{$var2.x}}
	 */
	public static function getVar($string) {
		// On supprime le '$' du début
		if ($string[0] == '$') {
			$string = substr($string, 1);
		}
		
		$string = self::parseVars($string);
		
		$return = '$this->tpl_vars';
		foreach (explode('.', $string) as $s) {
			if ($s[0] == '$') {
				$return .= '['.$s.']';
			} else {
				$return .= "['".$s."']";
			}
		}
		
		return $return;
	}
	
	/**
	 * Fonction permettant de parser une chaîne fournie en argument, dans les accolades
	 */
	public function getAttributes($string) {
		$string_arr = preg_split('#\s+#', trim($string));
		
		$args = array();
		foreach ($string_arr as $str) {
			list($name, $value) = explode('=', $str);
			$args[$name] = $value;
		}
		
		return $args;
	}
	
	// Schéma d'une variable : $var.sub_array(.sub2...)|functions
	public function compile_var($args) {
		$attr = $this->getAttributes($args);
		
		if (isset($attr['name'])) {
			// On sépare la variable des fonctions de traitement en queue
			$var_structure = explode('|', $attr['name']);
			
			$root = $this->getVar($var_structure[0]);
			
			// Couche fonctions
			if (isset($var_structure[1])) {
				$functions = preg_split('#,\s*#', $var_structure[1]);
				foreach ($functions as $f) {
					$root = $f.'('.$root.')';
				}
			}
			
			return '<?php echo '.$root.'; ?>';
		} else {
			return '';
		}
	}
	
	/**
	 * Fonction traitant l'inclusion de fichier .tpl via le moteur
	 * 
	 * @param string $file Le fichier à inclure
	 */
	public function compile_include($args) {
		$attr = $this->getAttributes($args);
		
		if (isset($attr['file'])) {
			$file = $this->parseVars(str_replace(array('{', '}'), array('".{', '}."'), $attr['file']));
			
			return '<?php $this->display("'.$file.'"); ?>';
		} else {
			return '';
		}
	}
	
	public function compile_if($args) {
		$cond = trim($args);
		
		// Traitement des variables de la condition
		$cond = $this->parseVars($cond);
		
		return '<?php if ('.$cond.'): ?>';
	}
	
	public function compile_else($args) {
		return '<?php else: ?>';
	}
	
	public function compile_elseif($args) {
		return str_replace('<?php if', '<?php elseif', $this->compile_if($args));
	}
	
	public function compile_if_close() {
		return '<?php endif; ?>';
	}
	
	public function compile_block($args) {
		$attr = $this->getAttributes($args);
		
		if (isset($attr['name'])) {
			$name = trim($attr['name'], '"');
			return "<?php \$this->tpl_vars['count'] = 0;"
				."if (isset(\$this->tpl_vars['".$name."_block']) && is_array(\$this->tpl_vars['".$name."_block'])):\n"
				. "\tforeach (\$this->tpl_vars['".$name."_block'] as \$this->tpl_vars['".$name."']): ?>";
		} else {
			return '';
		}
	}
	
	public function compile_block_close() {
		return '<?php $this->tpl_vars[\'count\']++; endforeach; endif; ?>';
	}
	
	/* Syntaxe de foreach :
	 * {foreach item="{$array}" as="{$var}"}
	 */
	public function compile_foreach($args) {
		$args = $this->parseVars($args);
		$attr = $this->getAttributes($args);
		
		if (isset($attr['item']) && isset($attr['as'])) {
			return '<?php '.$attr['as'].'[\'count\'] = -1;'."\n"
				.'foreach('.$attr['item'].' as '.$attr['as'].'[\'key\'] => '.$attr['as'].'[\'value\']):'."\n"
				.$attr['as'].'[\'count\']++; ?>';
		} else {
			return '';
		}
	}
	
	public function compile_foreach_close() {
		return '<?php endforeach; ?>';
	}
	
	public function compile_while($args) {
		
	}
	
	public function compile_for($args) {
		
	}
}

?>