<?php 
/**
 * WTemplateParser.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WTemplateParser is the parser part of WTemplate
 *
 * @package System\WTemplate
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-22-11-2012
 */
class WTemplateParser {
	
	/**
	 * Replaces all nodes found in $string by the callback result
	 * 
	 * If the char '{' is backslashed or directly followed by a carriage return, it will be ignored.
	 * 
	 * @param string    $string     a string to parse
	 * @param string    $callback   the callback to call to replace the node
	 * @param type      $nodes      optional and unused
	 * @return string the parsed string on which all callback results are in it
	 * @throws Exception
	 */
	public static function replaceNodes($string, $callback) {
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
					if (!$comment) {
						// Check whether { is backslashed
						if ($string[$i+1] != "\n" && $string[$i+1] != "\r" && $last_char != '\\') {
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
							
							// We arrived at the end of the node => compile it
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
						throw new Exception("WTemplateParser::replaceNodes(): found illegal carriage return character in a node (".$tmp.").");
					}
					
					if ($level > 0) {
						// add the last backslash which was skipped
						if ($last_char == '\\') {
							$tmp .= '\\';
						} else if ($last_char == '%') {
							$tmp .= '%';
						}
						$tmp .= $char;
					} else {
						if ($last_char == '\\') {
							$code .= '\\';
						} else if ($last_char == '%') {
							$code .= '%';
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