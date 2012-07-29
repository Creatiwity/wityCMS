<?php defined('IN_WITY') or die('Access denied');
/**
 * WTemplate
 * Moteur de template pour le CMS Wity
 *
 * @author     Fofif
 * @version    $Id: WTemplate/WTemplateCompiler.php 0004 28-04-2012 Fofif $
 * @package    Wity
 * @subpackage WTemplate
 */

class WTemplateCompiler {
	/**
	 * List of tags opened so that we can check there is no jam in opening and closing the nodes
	 */
	private $openTags = array();
	
	/**
	 * Compilation d'un élément bien précis
	 */
	public function compileTplCode($node, $data = array()) {
		$node = substr($node, 1, strlen($node)-2);
		if (empty($node)) {
			return "";
		}
		
		// Variable display
		if ($node[0] == '$') {
			$output = $this->compile_var($node, $data);
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
				$output = $this->$handler($data);
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
			$args = substr($node, strlen($tag));
			
			if (method_exists('WTemplateCompiler', $handler)) {
				// Check whether it is not an open only tag
				if (method_exists('WTemplateCompiler', $handler.'_close')) {
					// Add item in open tags list
					$this->openTags[] = $tag;
				}
				
				// Call handler
				$output = $this->$handler($args, $data);
			} else {
				$output = '';
			}
		}
		
		return $output;
	}
	
	/**
	 * Recherche dans une chaîne une variable de la forme {$var} pour la parser via self::getVar()
	 */
	public static function parseVars($string) {
		$string = trim($string);
		
		$vars = WTemplateParser::findAllNodes($string, 1);
		foreach ($vars as $v) {
			if ($v[0] == '$') {
				$string = str_replace('{'.$v.'}', self::getVar($v), $string);
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
	
	/**
	 * Schéma d'une variable : $var.sub_array(.sub2...)|functions
	 */
	public function compile_var($args, array $data) {
		if (!empty($args)) {
			// On sépare la variable des fonctions de traitement en queue
			$var_structure = explode('|', $args);
			
			$root = $this->getVar($var_structure[0]);
			
			// Couche fonctions
			if (isset($var_structure[1])) {
				for ($i = 1; $i < sizeof($var_structure); $i++) {
					$root = $var_structure[$i].'('.$root.')';
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
	public function compile_include($args, array $data) {
		$attr = $this->getAttributes($args);
		
		if (isset($attr['file'])) {
			// {$var} are replaced by ".{$var}." so that they can concat with other strings
			$file = str_replace(array('{', '}'), array('".{', '}."'), $attr['file']);
			$file = $this->parseVars($file);
			
			if (isset($data['filename'])) {
				$file = str_replace('./', dirname($data['filename']).'/', $file);
				$file = str_replace('../', dirname(dirname($data['filename'])).'/', $file);
			}
			
			return '<?php $this->display("'.$file.'"); ?>';
		} else {
			return '';
		}
	}
	
	public function compile_if($args, array $data) {
		$cond = trim($args);
		
		// Traitement des variables de la condition
		$cond = $this->parseVars($cond);
		
		return '<?php if ('.$cond.'): ?>';
	}
	
	public function compile_else($args, array $data) {
		return '<?php else: ?>';
	}
	
	public function compile_elseif($args, array $data) {
		return str_replace('<?php if', '<?php elseif', $this->compile_if($args));
	}
	
	public function compile_if_close(array $data) {
		return '<?php endif; ?>';
	}
	
	public function compile_block($args, array $data) {
		$attr = $this->getAttributes($args);
		
		if (isset($attr['name'])) {
			$name = trim($attr['name'], '"');
			return "<?php \$this->tpl_vars['count'] = 0;"
				."if (isset(\$this->tpl_vars['".$name."_block']) && is_array(\$this->tpl_vars['".$name."_block'])):\n"
				. "\tforeach (\$this->tpl_vars['".$name."_block'] as \$this->tpl_vars['".$name."']): ?>\n";
		} else {
			return '';
		}
	}
	
	public function compile_block_close(array $data) {
		return '<?php $this->tpl_vars[\'count\']++; endforeach; endif; ?>';
	}
	
	/* Syntaxe de foreach :
	 * {foreach item="{$array}" as="{$var}"}
	 */
	public function compile_for($args, array $data) {
		$args = $this->parseVars($args);
		//$attr = $this->getAttributes($args);
		
		if (isset($attr['item']) && isset($attr['as'])) {
			return "<?php ".$attr['as']."['count'] = -1;\n"
				."foreach(".$attr['item']." as ".$attr['as']."['key'] => ".$attr['as']."['value']):\n"
				.$attr['as']."[\'count\']++; ?>\n";
		} else {
			return '';
		}
	}
	
	public function compile_for_close(array $data) {
		return '<?php endforeach; ?>';
	}
	
	public function compile_while($args, array $data) {
		
	}
}

?>