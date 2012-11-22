<?php defined('IN_WITY') or die('Access denied');
/**
 * WTemplate
 * Moteur de template pour le CMS Wity
 *
 * @author     Fofif
 * @version    $Id: WTemplate/WTemplateParser.php 0002 22-11-2012 Fofif $
 * @package    Wity
 * @subpackage WTemplate
 */

class WTemplateParser {
	/**
	 * This function reads a string and finds node matching {im a node}
	 * and gives these nodes to a compiler which will replace them.
	 * 
	 * If the car { is backslashed or directly followed by a carriage return, it will be ignored.
	 * 
	 * @param WTemplateCompiler $compiler The compiler which will work on nodes
	 * @return string Parsed and compiled template file
	 */
	public static function replaceNodes($string, $callback, &$nodes = null) {
		$length = strlen($string);
		$level = 0;
		$code = ""; // $code stocks the entire code compiled
		$tmp = ""; // $tmp stocks the node currently being read
		$last_char = '';
		$return = null;
		$comment = false;
		
		if (!is_callable($callback)) {
			if (is_array($callback)) {
				$class = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
				$callback = $class.'::'.$callback[1];
			}
			throw new Exception("WTemplateParser::replaceNodes(): callback function \"".$callback."\" given is not callable.");
		}
		
		for ($i = 0; $i < strlen($string); $i++) {
			// Get next char
			$char = $string[$i];
			
			switch ($char) {
				case '\\': // backslash
					// backslash in a node are always saved since it is up to self::findAllNodes to manage them
					if ($level > 0) {
						$tmp .= '\\';
					} else {
						if ($last_char == '\\') {
							$code .= '\\';
							
							// $char set to null in order to set $last_char to null because the '\' char has been canceled by the previous '\'
							$char = '';
						}
					}
					break;
				
				case '%': // comment node
					if ($level > 0 && $last_char == '{') {
						$comment = true;
					}
					break;
				
				case '{':
					// Check whether { is backslashed
					if ($string[$i+1] != "\n" && $string[$i+1] != "\r" && $string[$i+1] != "\r\n" && $last_char != '\\') {
						$level++;
					}
					
					// Are we in a node?
					if ($level > 0) {
						if ($level > 1) {
							$tmp .= '{';
						}
					} else {
						$code .= '{';
					}
					break;
				
				case '}':
					if ($level > 0) {
						if (!$comment) {
							if ($level > 1) {
								$tmp .= '}';
							}
							
							// Check whether } is backslashed
							if ($last_char != '\\') {
								$level--;
							}
							
							// We are arrived at the end of the node => compile it
							if ($level == 0) {
								$code .= call_user_func($callback, $tmp);
								$tmp = "";
							}
						} else if ($last_char == '%') {
							$comment = false;
							$level--;
							$tmp = "";
						}
					} else {
						$code .= '}';
					}
					break;
				
				default:
					if ($char == "\n" && $level > 0 && !$comment) {
						var_dump($comment);
						throw new Exception("WTemplateParser::replaceNodes(): found illegal carriage return character in a node.");
					}
					
					if ($level > 0) {
						// add the last backslash which was skipped
						if ($last_char == '\\') {
							$tmp .= '\\';
						}
						$tmp .= $char;
					} else {
						if ($last_char == '\\') {
							$code .= '\\';
						}
						$code .= $char;
					}
					break;
			}
			
			$last_char = $char;
		}
		
		return $code;
	}
}

?>