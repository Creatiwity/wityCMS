<?php defined('IN_WITY') or die('Access denied');
/**
 * WTemplate
 * Moteur de template pour le CMS Wity
 *
 * @author     Fofif
 * @version    $Id: WTemplate/WTemplateFile.php 0003 04-08-2012 Fofif $
 * @package    Wity
 * @subpackage WTemplate
 */

class WTemplateFile {
	/**
	 * Complete file href
	 */
	private $href;
	
	/**
	 * Directory containing this file
	 */
	private $baseDir;
	
	/**
	 * Compilation directory
	 */
	private $compilationDir;
	
	/**
	 * Compiled file href
	 */
	private $compilationHref;
	
	/**
	 * Creation time of this file
	 */
	private $creationTime;
	
	/**
	 * Compilation state
	 */
	private $compiled = false;
	
	/**
	 * Compilation ellapsed time
	 */
	private $compilationTime = 0;
	
	public function __construct($href, $baseDir, $compDir) {
		if (!file_exists($href)) {
			throw new Exception("WTemplateFile::__construct(): File \"".$href."\" does not exist.");
		}
		
		$this->href = $href;
		$this->baseDir = $baseDir;
		$this->compilationDir = $compDir;
		$this->compilationHref = $this->createCompilationHref();
		$this->creationTime = filemtime($href);
	}
	
	/**
	 * Create the position of the cached file compiled
	 */
	private function createCompilationHref() {
		return $this->compilationDir
			.str_replace(array('/', '\\'), '-', trim(str_replace($this->baseDir, '', dirname($this->href)), '\/'))
			.'-'.str_replace(array('.html', '.tpl'), '.php', basename($this->href));
	}
	
	public function getCompilationHref() {
		return $this->compilationHref;
	}
	
	public function getCompilationTime() {
		return $this->compilationTime;
	}
	
	/**
	 * Checks whether compilation is required
	 * 
	 * @return bool
	 */
	private function checkCompilation()	{
		if ($this->compiled) {
			return true;
		}
		
		// File doesn't exist
		if (!file_exists($this->compilationHref)) {
			return false;
		}
		
		// Cached file is too old
		$cacheDate = @filemtime($this->compilationHref);
		if ($cacheDate < $this->creationTime) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Compile this file
	 * 
	 * @param WTemplateCompiler $compiler This object is a compiler which will compile each nodes
	 * @return void
	 */
	public function compile(WTemplateCompiler $compiler) {
		if (!$this->checkCompilation()) {
			$start = microtime(true);
			
			// Read template file
			if (!($string = file_get_contents($this->href))) {
				throw new Exception("WTemplateFile::compile(): Unable top read file \"".$this->href."\".");
			}
			
			// Compile file
			$code = $compiler->compileString($string, array('dir' => dirname($this->href)));
			
			// Save output
			$this->saveFile($code);
			
			// Update data
			$this->compilationTime = microtime(true) - $start; // ellapsed time
			$this->compiled = true;
		}
	}
	
	/**
	 * Write output string into the cache file
	 */
	private function saveFile($data) {
		$handle = fopen($this->compilationHref, 'w');
		if (!$handle) {
			throw new Exception("WTemplateFile::saveFile(): Unable to open cache file \"".$this->compilationHref."\".");
		}
		
		// Write
		fwrite($handle, $data);
		fclose($handle);
		
		chmod($this->compilationHref, 0777);
	}
}

?>