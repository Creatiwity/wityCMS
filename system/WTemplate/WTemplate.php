<?php defined('IN_WITY') or die('Access denied');
/**
 * WTemplate
 * Moteur de template pour le CMS Wity
 *
 * @author     Fofif
 * @version    $Id: WTemplate/WTemplate.php 0008 29-07-2012 Fofif $
 * @package    Wity
 * @subpackage WTemplate
 */

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

include dirname(__FILE__).DS.'WTemplateFile.php';
include dirname(__FILE__).DS.'WTemplateParser.php';
include dirname(__FILE__).DS.'WTemplateCompiler.php';

class WTemplate {
	/**
	 * Compilation directory: where to place compiled files
	 */
	private $compileDir;
	
	/**
	 * Template variables
	 */
	private $tpl_vars = array();
	
	/**
	 * Template compilator
	 */
	private $compiler;
	
	/**
	 * Buffer state
	 */
	private $buffer_launched = false;
	
	/**
	 * Constructor
	 * 
	 * @param $baseDir Script root directory
	 * @param $compileDir Compilation directory
	 */
	public function __construct($baseDir, $compileDir = '') {
		if (is_dir($baseDir)) {
			$this->baseDir = $baseDir;
		} else {
			throw new Exception("WTemplate::__construct(): Directory \"".$baseDir."\" does not exist.");
		}
		
		// Default value
		$this->compileDir = '.'.DS.'tpl_compiled'.DS;
		if (!empty($compileDir)) {
			$this->setCompileDir($compileDir);
		}
		
		$this->compiler = new WTemplateCompiler();
	}
	
	public function setCompileDir($compileDir) {
		if (is_dir($compileDir)) {
			$this->compileDir = $compileDir;
		} else {
			// Attempt to create compile directory
			if (mkdir($compileDir)) {
				$this->compileDir = $compileDir;
			}
		}
	}
	
	/**
	 * Assign variables
	 */
	public function assign($a, $b = null) {
		if (is_array($a)) {
			$this->tpl_vars = array_merge($this->tpl_vars, $a);
		} else {
			$this->tpl_vars[$a] = $b;
		}
	}
	
	/**
	 * Add values in a variable if exists
	 * (automaticy transforms this var into array)
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
	 * Get a template variable
	 */
	public function getVar($var) {
		if (isset($this->tpl_vars[$var])) {
			return $this->tpl_vars[$var];
		} else {
			return '';
		}
	}
	
	/**
	 * Remove template variables
	 * 
	 * @param $vars string|array(string) List of keys
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
	 * Get the resulting output of a compiled file without printing anything on screen
	 * 
	 * @param string $href File's href
	 * @return string Output string
	 */
	public function parse($href) {
		// File init
		$file = new WTemplateFile($href, $this->baseDir, $this->compileDir);
		
		// Compilation (if needed)
		$file->compile($this->compiler);
		
		// Buffer
		ob_start();
		
		try { // Critical section
			// Evaluation
			eval('?>'.file_get_contents($file->getCompilationHref()));
		} catch (Exception $e) {
			// Just stores the exception into $e to throw it later
		}
		
		$result = ob_get_contents();
		ob_end_clean();
		
		// Throw exception if any
		if (!empty($e)) {
			throw $e;
		}
		
		return $result;
	}
	
	/**
	 * Display a file on screen
	 * 
	 * @param string $href File's href
	 */
	public function display($href) {
		// Display parsing result
		echo $this->parse($href);
	}
}

?>