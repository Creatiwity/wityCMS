<?php 
/**
 * WTemplateCompiler.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WTemplateCompiler compiles the nodes used in templates parsed by WTemplate
 * 
 * It replaces a node by its PHP equivalent.
 * WTemplateCompiler is composed by several handlers, one for each node known by WTemplate.
 * 
 * What is a node?
 * Each <code>{exemple}</code> is called a "node".
 * In this case, "exemple" is the name of the node.
 * For a closing node, such as <code>{/exemple}</code>, the node name is "exemple_close".
 *
 * @package System\WTemplate
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.3-22-11-2012
 */
class WTemplateCompiler {
	
	/**
	 * @var array List of nodes opened to check whether they are properly closed
	 */
	private $openNodes = array();
	
	/**
	 * @var array Some useful information to help the compilation (such as file src, template directory, ...)
	 */
	private $data = array();
	
	/**
	 * @var array List of all registered external compilers  
	 */
	private static $external_compilers = array();
	
	/**
	 * Registers an external compiler
	 * 
	 * An external compiler is a node handler which belongs to an external class than WTemplateCompiler.
	 * It may be called by WTemplateCompiler whenever the node is found.
	 * 
	 * @param string $node_name Node's name to handle (without brackets)
	 * @param string $callback  The compiler to call
	 * @throws Exception
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
	 * Compiles an entire string containing nodes.
	 * This method is called when WTemplate is asked to parse a file.
	 * 
	 * @param string    $string the string that will be compiled
	 * @param array     $data   datas that will be used in the compiled file
	 * @return string the compiled file
	 * @throws Exception
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
	 * Compiles a single node
	 * 
	 * @param string $node a node that will be compiled
	 * @return string the compiled node
	 * @throws Exception
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
			} else {
				throw new Exception("WTemplateCompiler::compileNode(): no compiler handler found for node {".$node."}.");
			}
		}
		
		return $output;
	}
	
	/**
	 * Parses a variable node into PHP code
	 * Vars have this format: {$var.index1.index2...|function1|function2...}
	 * 
	 * Nesting vars are managed such as: {$var1.{$var2.x}}
	 * 
	 * @param string $string a string that will be compiled
	 * @return string the compiled string
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
	
	/**
	 * Replaces all variable nodes in a given string by their PHP values
	 * For each node, it calls WTemplateCompiler::parseVar()
	 * 
	 * @see WTemplateParser::replaceNodes()
	 * @param string $string a string in which variable names will be replaced by their values
	 * @return string a string with values instead of variable names
	 */
	public static function replaceVars($string) {
		return WTemplateParser::replaceNodes($string, array('WTemplateCompiler', 'parseVar'));
	}
	
	/**
	 * Compiles a variable displaying it
	 * 
	 * <code>{$array.index1.index2...|func1|func2...}</code>
	 * 
	 * @param string $args a string of variables that will be compiled
	 * @return string the compiled variables
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
	 * Compiles {include} node to include sub template files
	 * 
	 * <code>{include file_href}</code>
	 * 
	 * @todo Add a recursive compiler to be able to include multiple application in one page
	 * @param string $file the file to include
	 * @return string the php code displying the compiled file (only variables are compiled)
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
	
	/**
	 * Compiles {if condition}
	 * 
	 * @param string $args if arguments
	 * @return string the php-if code
	 */
	public function compile_if($args) {
		$cond = trim($args);
		
		// Traitement des variables de la condition
		$cond = $this->replaceVars($cond);
		
		return '<?php if ('.$cond.'): ?>';
	}
	
	/**
	 * Compiles {else}
	 * 
	 * @return string the php-else code
	 */
	public function compile_else() {
		return '<?php else: ?>';
	}
	
	/**
	 * Compiles {elseif ...}
	 * 
	 * @param string $args elseif arguments
	 * @return type the php-elseif code
	 */
	public function compile_elseif($args) {
		return str_replace('if', 'elseif', $this->compile_if($args));
	}
	
	/**
	 * Compiles {/if}
	 * 
	 * @return string the php-endif code
	 */
	public function compile_if_close() {
		return '<?php endif; ?>';
	}
	
	/**
	 * @var int Counts the number of {for} in order to make {empty} work properly
	 */
	private $for_count = 0;
	
	/**
	 * Compiles {for [$key, ]$value in $array}
	 * 
	 * @param string $args for arguments
	 * @return string php-for code
	 */
	public function compile_for($args) {
		$matches = array();
		// RegEx string to search "$key, $value in $array" substring
		if (!preg_match('#^(\{?\$([a-zA-Z0-9_]+)\}?,\s*)?\{?\$([a-zA-Z0-9_]+)\}?\s+in\s+(.+)$#U', $args, $matches)) {
			throw new Exception("WTemplateCompiler::compile_for(): Wrong syntax for node {for ".$args."}.");
		}
		
		if ($this->for_count < 0) {
			$this->for_count = 0;
		}
		$this->for_count++;
		list(,, $key, $value, $array) = $matches;
		$array = trim($array, ' {}');
		if ($array[0] == '$') {
			$array = $this->parseVar($array);
		}
		
		$s = "<?php \$hidden_counter".$this->for_count." = 0;\n";
		if (empty($key)) {
			$s .= "foreach((array) ".$array." as \$this->tpl_vars['".$value."']):\n";
		} else {
			$s .= "foreach((array) ".$array." as \$this->tpl_vars['".$key."'] => \$this->tpl_vars['".$value."']):\n";
		}
		return $s."	\$hidden_counter".$this->for_count."++; ?>";
	}
	
	/**
	 * Compiles {/for}
	 * 
	 * @return string php-endforeach code
	 */
	public function compile_for_close() {
		$this->for_count--;
		return '<?php endforeach; ?>';
	}
	
	/**
	 * Compiles {empty}
	 * 
	 * {empty} is to use right after a {for} node.
	 * Its content is displayed when the array iterated in the loop is empty.
	 * 
	 * @return string php-empty code
	 */
	public function compile_empty() {
		return "<?php if (isset(\$hidden_counter".($this->for_count+1).") && intval(\$hidden_counter".($this->for_count+1).") == 0): ?>";
	}
	
	/**
	 * Compiles {/empty}
	 * 
	 * @return string php-empty_close code
	 */
	public function compile_empty_close() {
		return "<?php endif; ?>";
	}
	
	/**
	 * Compiles an assignement
	 * 
	 * <code>{set $a = 5}
	 * {set {$a} = {$a} + 1}</code>
	 * 
	 * @param string $args an assignement <code>var=value</code>
	 * @return string the php-assignement code
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