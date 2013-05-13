<?php 
/**
 * WTemplate.php
 */

defined('IN_WITY') or die('Access denied');

include dirname(__FILE__).DIRECTORY_SEPARATOR.'WTemplateFile.php';
include dirname(__FILE__).DIRECTORY_SEPARATOR.'WTemplateParser.php';
include dirname(__FILE__).DIRECTORY_SEPARATOR.'WTemplateCompiler.php';

/**
 * WTemplate is the template engine used by WityCMS
 * 
 * @package System\WTemplate
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.3-26-10-2012
 */
class WTemplate {

	/**
	 * @var string Compilation directory: where to place compiled files
	 */
	private $compileDir;
	
	/**
	 * @var array Template variables
	 */
	private $tpl_vars = array();
	
	/**
	 * @var WTemplateCompiler Template compilator
	 */
	private $compiler;
	
	/**
	 * Setup template engine
	 * 
	 * @param string $baseDir script root directory
	 * @param string $compileDir optional compilation directory
	 * @throws Exception
	 */
	public function __construct($baseDir, $compileDir = '') {
		if (is_dir($baseDir)) {
			$this->baseDir = $baseDir;
		} else {
			throw new Exception("WTemplate::__construct(): Directory \"".$baseDir."\" does not exist.");
		}
		
		if (!empty($compileDir)) {
			$this->setCompileDir($compileDir);
		}
		
		$this->compiler = new WTemplateCompiler();
	}
	
	/**
	 * Sets the compile directory
	 * 
	 * @param string $compileDir the compile directory
	 * @throws Exception
	 */
	public function setCompileDir($compileDir) {
		if (is_dir($compileDir)) {
			$this->compileDir = $compileDir;
		} else {
			// Attempt to create compile directory
			if (@mkdir($compileDir, 0777)) {
				$this->compileDir = $compileDir;
			} else {
				throw new Exception("WTemplate::setCompileDir(): Impossible to create cache directory in ".$compileDir.".");
			}
		}
	}
	
	/**
	 * Adds variables whose names are in names and their values to the private property $tpl_vars
	 * 
	 * @param array|string $names
	 * @param array|mixed $values
	 */
	public function assign($names, $values = null) {
		if (is_array($names)) {
			$this->tpl_vars = array_merge($this->tpl_vars, $names);
		} else {
			$this->tpl_vars[$names] = $values;
		}
	}
	
	/**
	 * Adds values in a variable if exists
	 * 
	 * @param string    $var    variable name
	 * @param mixed     $value  value to append
	 */
	public function append($var, $value) {
		if (isset($this->tpl_vars[$var])) {
			if (!is_array($this->tpl_vars[$var])) {
				settype($this->tpl_vars[$var], 'array');
			}
			$this->tpl_vars[$var][] = $value;
		} else {
			$this->tpl_vars[$var] = array($value);
		}
	}
	
	/**
	 * Returns the variable value
	 * 
	 * @param string $var variable name
	 * @return mixed variable value or '' if it is not set
	 */
	public function getVar($var) {
		if (isset($this->tpl_vars[$var])) {
			return $this->tpl_vars[$var];
		} else {
			return '';
		}
	}
	
	/**
	 * Removes template variables
	 * 
	 * @param array|string $vars variable name or list of variable names to clear
	 */
	public function clear($vars) {
		if (is_array($vars)) {
			foreach ($vars as $v) {
				unset($this->tpl_vars[$v]);
			}
		} else {
			unset($this->tpl_vars[$vars]);
		}
	}
	
	/**
	 * Gets the resulting output of a compiled file without printing anything on screen
	 * 
	 * @param string $href file's href
	 * @return string output string
	 * @throws Exception
	 */
	public function parse($href) {
		// File init
		$file = new WTemplateFile($href, $this->baseDir, $this->compileDir);
		
		// Compilation (if needed)
		$code = $file->compile($this->compiler);
		
		// Buffer
		ob_start();
		
		try { // Critical section
			// Adds the php close balise at the begining because it is a whole php file being evaluated
			$eval_result = eval('?>'.$code);
		} catch (Exception $e) {
			// Just stores the exception into $e to throw it later
		}
		
		$buffer = ob_get_contents();
		ob_end_clean();
		
		// Throw exception if any
		if (!empty($e)) {
			throw $e;
		} else if ($eval_result === false) {
			throw new Exception("WTemplate::parse(): File $href encountered an error during evaluation :".$buffer);
		}
		
		return $buffer;
	}
	
	/**
	 * Displays a file on the screen
	 * 
	 * @param string $href file's href
	 */
	public function display($href) {
		// Display parsing result
		echo $this->parse($href);
	}
}

?>