<?php defined('IN_WITY') or die('Access denied');
/**
 * WTemplate
 * Moteur de template pour le CMS Wity
 *
 * @author     Fofif
 * @version    $Id: WTemplate/WTemplateCompiler.php 0006 22-11-2012 Fofif $
 * @package    Wity
 * @subpackage WTemplate
 */

/**
 * WTemplateCompiler
 * 
 * Nodes
 * -----
 * Each <code>{exemple}</code> is called a "node".
 * In this case, "exemple" is the name of the node.
 * For a closing node, such as <code>{/exemple}</code>, the node name is "exemple_close".
 */

class WTemplateCompiler {
	/**
	 * List of tags opened so that we can check there is no jam in opening and closing the nodes
	 */
	private $openNodes = array();
	
	private $data = array();
	
	private static $external_compilers = array();
	
	/**
	 * Define an external compiler
	 * 
	 * @param string   $compiler_name Node name (without brackets)
	 * @param callback $callback      Compiler to call
	 * @static
	 */
	public static function registerCompiler($node_name, $callback) {
		if (is_callable($callback)) {
			if (!isset(self::$external_compilers[$node_name])) {
				self::$external_compilers[$node_name] = $callback;
			}
		} else {
			if (is_array($callback)) {
				$class = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
				$callback = $class.'::'.$callback[1];
			}
			throw new Exception("WTemplateParser::registerCompiler(): callback function \"".$callback."\" given is not callable.");
		}
	}
	
	/**
	 * Compile an entire file using the parser
	 * 
	 * @return string File compiled
	 */
	public function compileString($string, array $data = array()) {
		// clear open tags
		$this->openNodes = array();
		$this->data = $data;
		
		$code = WTemplateParser::replaceNodes($string, array($this, 'compileNode'));
		
		if (!empty($this->openNodes)) {
			throw new Exception("WTemplateCompiler::compileString(): some tags were not properly closed (".implode(', ', $this->openNodes).").");
		}
		
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
		$output = "";
		
		// Variable display
		if (strpos($node, '$') === 0) {
			$output = $this->compile_var($node);
		}
		// Closing tag
		else if (strpos($node, '/') === 0) {
			$node = substr($node, 1);
			$node_name = $node.'_close';
			$handler = 'compile_'.$node_name;
			
			// Check last open tag
			if (array_pop($this->openNodes) != $node) {
				throw new Exception("WTemplateCompiler::compileNode(): mismatched node {".$node."} opening tag.");
			}
			
			// Call handler
			if (method_exists('WTemplateCompiler', $handler)) {
				$output = $this->$handler();
			} else if (isset(self::$external_compilers[$node_name])) {
				$output = call_user_func(self::$external_compilers[$node_name]);
			}
		}
		// Opening tag
		else {
			// Get begining tag name : {"name" ...}
			$matches = null;
			preg_match('#^([a-zA-Z0-9_]+)#', $node, $matches);
			
			if (empty($matches)) {
				throw new Exception("WTemplateCompiler::compileNode(): invalid node \"{".$node."}\".");
			}
			
			$node_name = $matches[0];
			$handler = 'compile_'.$node_name;
			
			// Remove node name to get following string
			$args = trim(substr($node, strlen($node_name)));
			
			if (method_exists('WTemplateCompiler', $handler)) {
				// Check whether it is not an open only node
				if (method_exists('WTemplateCompiler', $handler.'_close')) {
					// Add item in open nodes list
					$this->openNodes[] = $node_name;
				}
				
				// Call handler
				$output = $this->$handler($args);
			} else if (isset(self::$external_compilers[$node_name])) {
				if (isset(self::$external_compilers[$node_name.'_close'])) {
					// Add item in open nodes list
					$this->openNodes[] = $node_name;
				}
				
				$output = call_user_func(self::$external_compilers[$node_name], $args);
			}
		}
		
		return $output;
	}
	
	/**
	 * Compile vars having this format {$var.index1[.index2...]|func1[|func2...]} into php format
	 * Several levels of vars are managed (for instance: {$var1.{$var2.x}}
	 */
	public static function parseVar($string) {
		if (strpos($string, '$') !== 0) {
			return;
		}
		
		// Remove begining '$' char
		$string = substr($string, 1);
		
		if (strpos($string, '{') !== false) {
			$string = self::replaceVars($string);
		}
		
		$functions = explode('|', $string);
		
		$var_string = array_shift($functions);
		
		$return = '$this->tpl_vars';
		// sub arrays
		foreach (explode('.', $var_string) as $s) {
			$s = trim($s);
			if (strpos($s, '$') === 0 || strpos($s, '(') !== false) {
				$return .= '['.$s.']';
			} else {
				$return .= "['".$s."']";
			}
		}
		
		// functions to apply
		foreach ($functions as $f) {
			$f = trim($f);
			switch ($f) {
				// Add personnal fonctions here case 'perso': ...
				
				default:
					if (function_exists($f)) {
						$return = $f.'('.$return.')';
					}
					break;
			}
		}
		
		return $return;
	}
	
	public static function replaceVars($string) {
		return WTemplateParser::replaceNodes($string, array('WTemplateCompiler', 'parseVar'));
	}
	
	/**
	 * Compile vars {$var.index1[.index2...]|func1[|func2...]}
	 */
	public function compile_var($args) {
		if (!empty($args)) {
			$var = $this->parseVar($args);
			return '<?php echo '.$var.'; ?>';
		} else {
			return '';
		}
	}
	
	/**
	 * Compile {include} node to include sub template files
	 * 
	 * @param string $file Le fichier à inclure
	 */
	public function compile_include($file) {
		if (empty($file)) {
			return '';
		}
		
		// {$var} are replaced by ".{$var}." so that they can concat with other strings
		$file = str_replace(array('"', "'"), '', $file);
		$file = str_replace(array('{', '}'), array('".{', '}."'), $file);
		$file = $this->replaceVars($file);
		
		if (!empty($this->data['dir'])) {
			$file = str_replace('./', $this->data['dir'].'/', $file);
			$file = str_replace('../', dirname($this->data['dir']).'/', $file);
		}
		
		return '<?php $this->display("'.$file.'"); ?>';
	}
	
	public function compile_if($args) {
		$cond = trim($args);
		
		// Traitement des variables de la condition
		$cond = $this->replaceVars($cond);
		
		return '<?php if ('.$cond.'): ?>';
	}
	
	public function compile_else($args) {
		return '<?php else: ?>';
	}
	
	public function compile_elseif($args) {
		return str_replace('if', 'elseif', $this->compile_if($args));
	}
	
	public function compile_if_close() {
		return '<?php endif; ?>';
	}
	
	/**
	 * Counts the number of {for} in order to make {empty} work properly
	 */
	private $for_count = 0;
	
	/**
	 * {for [$key, ]$value in $array}
	 */
	public function compile_for($args) {
		$matches = array();
		// RegEx string to search "$key, $value in $array" substring
		if (preg_match('#^(\{?\$([a-zA-Z0-9_]+)\}?,\s*)?\{?\$([a-zA-Z0-9_]+)\}?\s+in\s+(.+)$#U', $args, $matches)) {
			if ($this->for_count < 0) {
				$this->for_count = 0;
			}
			$this->for_count++;
			list(, , $key, $value, $array) = $matches;
			
			if (strpos($array, '$') === 0) {
				$array = "\$this->tpl_vars['".substr($array, 1)."']";
			} else if (strpos($array, '{') === 0) {
				$array = $this->parseVar(substr($array, 1, -1));
			}
			
			$s = "<?php \$hidden_counter".$this->for_count." = 0;\n";
			if (empty($key)) {
				$s .= "foreach((array) ".$array." as \$this->tpl_vars['".$value."']):\n";
			} else {
				$s .= "foreach((array) ".$array." as \$this->tpl_vars['".$key."'] => \$this->tpl_vars['".$value."']):\n";
			}
			return $s."	\$hidden_counter".$this->for_count."++; ?>";
		} else {
			return '';
		}
	}
	
	public function compile_for_close() {
		$this->for_count--;
		return '<?php endforeach; ?>';
	}
	
	/**
	 * Case of emptiness of last for's array being iterated
	 */
	public function compile_empty($args) {
		return "<?php if (isset(\$hidden_counter".($this->for_count+1).") && intval(\$hidden_counter".($this->for_count+1).") == 0): ?>";
	}
	
	public function compile_empty_close() {
		return "<?php endif; ?>";
	}
	
	/**
	 * {set $a = 5}
	 * {set {$a} = {$b} + 1}
	 */
	public function compile_set($args) {
		$a = explode('=', $args);
		if (count($a) != 2) {
			return '';
		}
		list($var, $value) = $a;
		$var = trim($var, '{}');
		if ($var[0] != '$') {
			return '';
		}
		$var = $this->parseVar(trim($var));
		$value = $this->replaceVars(trim($value));
		return "<?php ".$var." = ".$value."; ?>";
	}
}

?>