<?php defined('IN_WITY') or die('Access denied');
/**
 * WTemplate
 * Moteur de template pour le CMS Wity
 *
 * @author     Fofif
 * @version    $Id: WTemplate/WTemplateCompiler.php 0005 04-08-2012 Fofif $
 * @package    Wity
 * @subpackage WTemplate
 */

class WTemplateCompiler {
	/**
	 * List of tags opened so that we can check there is no jam in opening and closing the nodes
	 */
	private $openTags = array();
	
	private $data = array();
	
	/**
	 * Compile an entire file using the parser
	 * 
	 * @return string File compiled
	 */
	public function compileFile($href) {
		// clear open tags
		$this->openTags = array();
		
		// Read template file
		if (!($string = file_get_contents($href))) {
			throw new Exception("WTemplateCompiler::compileFile() : Unable top read file \"".$href."\".");
		}
		
		$this->data['href'] = $href;
		$code = WTemplateParser::replaceNodes($string, array($this, 'compileNode'));
		$this->data = array();
		
		// Replace xml tag to prevent short open tag conflict
		$code = str_replace("<?xml", "<?php echo '<?xml'; ?>", $code);
		
		return $code;
	}
	
	/**
	 * Compilation d'un élément bien précis
	 */
	public function compileNode($node) {
		$node = trim($node);
		if (empty($node)) {
			return "";
		}
		
		// Variable display
		if ($node[0] == '$') {
			$output = $this->compile_var($node);
		}
		// Closing tag
		else if ($node[0] == '/') {
			$tag = trim($node, '/');
			
			$handler = 'compile_'.$tag.'_close';
			if (method_exists('WTemplateCompiler', $handler)) {
				// Check last open tag
				if (array_pop($this->openTags) != $tag) {
					throw new Exception("WTemplateCompiler::compileTplCode(): mismatched ".$tag." opening tag.");
				}
				
				// Call handler
				$output = $this->$handler();
			} else {
				$output = '';
			}
		}
		// Opening tag
		else {
			// Get begining tag name : {"name" ...}
			$matches = null;
			preg_match('#^([a-zA-Z0-9_]+)#', $node, $matches);
			
			if (empty($matches)) {
				throw new Exception("WTemplateCompiler::compileTplCode(): invalid node \"{".$node."}\".");
			}
			
			$tag = $matches[0];
			$handler = 'compile_'.$tag;
			
			// Remove tag name to get following string
			$args = trim(substr($node, strlen($tag)));
			
			if (method_exists('WTemplateCompiler', $handler)) {
				// Check whether it is not an open only tag
				if (method_exists('WTemplateCompiler', $handler.'_close')) {
					// Add item in open tags list
					$this->openTags[] = $tag;
				}
				
				// Call handler
				$output = $this->$handler($args);
			} else {
				$output = '';
			}
		}
		
		return $output;
	}
	
	/**
	 * Convertit une variable de la forme {$var.index1[.index2...]|func1[|func2...]} sous forme php
	 * Possibilité d'utiliser des sous niveaux de variable (ex : {$var1.{$var2.x}}
	 */
	public static function parseVars($string) {
		if ($string[0] != '$') {
			return;
		}
		// On supprime le '$' du début
		$string = substr($string, 1);
		
		if (strpos($string, '{') !== false) {
			$string = WTemplateParser::replaceNodes($string, 'WTemplateCompiler::parseVars');
		}
		
		$functions = explode('|', $string);
		
		$var_string = array_shift($functions);
		
		$return = '$this->tpl_vars';
		// sub arrays
		foreach (explode('.', $var_string) as $s) {
			$s = trim($s);
			if ($s[0] == '$' || strpos($s, '(') !== false) {
				$return .= '['.$s.']';
			} else {
				$return .= "['".$s."']";
			}
		}
		
		// functions to apply
		foreach ($functions as $f) {
			$f = trim($f);
			switch ($f) {
				default:
					if (function_exists($f)) {
						$return = $f.'('.$return.')';
					}
					break;
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
	
	/**
	 * Schéma d'une variable : {$var.index1[.index2...]|func1[|func2...]}
	 */
	public function compile_var($args) {
		if (!empty($args)) {
			$var = $this->parseVars($args);
			return '<?php echo '.$var.'; ?>';
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
			// {$var} are replaced by ".{$var}." so that they can concat with other strings
			$file = str_replace(array('{', '}'), array('".{', '}."'), $attr['file']);
			$file = $this->parseVars($file);
			
			if (!empty($this->href)) {
				$dir = dirname($this->href);
				$file = str_replace('./', $dir.'/', $file);
				$file = str_replace('../', dirname($dir).'/', $file);
			}
			
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
		if (current($this->openTags) == 'for' && empty($this->data['for_else'])) {
			$this->data['for_else'] = true;
			return "<?php endforeach; endif; if (empty(\$this->tpl_vars['".current($this->data['for'])."'])): ?>";
		} else {
			return '<?php else: ?>';
		}
	}
	
	public function compile_elseif($args) {
		return str_replace('<?php if', '<?php elseif', $this->compile_if($args));
	}
	
	public function compile_if_close() {
		return '<?php endif; ?>';
	}
	
	/* Syntaxe de foreach :
	 * {for [$key, ]$value in $array}
	 */
	public function compile_for($args) {
		$matches = array();
		if (preg_match('#^(\$([a-zA-Z0-9_]+),\s*)?\$([a-zA-Z0-9_]+)\s+in\s+\$([a-zA-Z0-9_]+)$#', $args, $matches)) {
			list(, , $key, $value, $array) = $matches;
			$this->data['for'][] = $array;
			
			if (empty($key)) {
				return "<?php if (!empty(\$this->tpl_vars['".$array."'])):\n"
					."foreach(\$this->tpl_vars['".$array."'] as \$this->tpl_vars['".$value."']): ?>";
			} else {
				return "<?php if (!empty(\$this->tpl_vars['".$array."'])):\n"
					."foreach(\$this->tpl_vars['".$array."'] as \$this->tpl_vars['".$key."'] => \$this->tpl_vars['".$value."']): ?>";
			}
		} else {
			return '';
		}
	}
	
	public function compile_for_close() {
		// remove last element of for
		array_pop($this->data['for']);
		if (!empty($this->data['for_else'])) {
			unset($this->data['for_else']);
			return '<?php endif; ?>';
		} else {
			return '<?php endif; endforeach; ?>';
		}
	}
}

?>