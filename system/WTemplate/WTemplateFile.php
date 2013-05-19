<?php 
/**
 * WTemplateFile.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * WTemplateFile
 *
 * @package System\WTemplate
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.3-04-08-2012
 */
class WTemplateFile {

	/**
	 * @var string Complete file href
	 */
	private $href;
	
	/**
	 * @var string Directory containing this file
	 */
	private $baseDir;
	
	/**
	 * @var string Compilation directory
	 */
	private $compilationDir;
	
	/**
	 * @var string Compiled file href
	 */
	private $compilationHref;
	
	/**
	 * @var int Creation time of this file
	 */
	private $creationTime;
	
	/**
	 * @var bool Compilation state
	 */
	private $compiled = false;
	
	/**
	 * @var int Compilation ellapsed time
	 */
	private $compilationTime = 0;
	
	/**
	 * Setup WTemplateFile
	 * 
	 * @param string $href      Complete file href
	 * @param string $baseDir   Directory containing this file
	 * @param string $compDir   Compilation directory
	 * @throws Exception
	 */
	public function __construct($href, $baseDir, $compDir) {
		if (!file_exists($baseDir.$href)) {
			throw new Exception("WTemplateFile::__construct(): File \"".$href."\" does not exist.");
		}
		
		$this->href = $href;
		$this->baseDir = $baseDir;
		$this->compilationDir = $compDir;
		$this->compilationHref = $this->createCompilationHref();
		$this->creationTime = @filemtime($baseDir.$href);
	}
	
	/**
	 * Creates the position of the cached file compiled
	 * 
	 * @return string href of the cached file
	 */
	private function createCompilationHref() {
		return $this->compilationDir
			.str_replace(array('/', '\\'), '-', trim(str_replace($this->baseDir, '', dirname($this->href)), '\/'))
			.'-'.str_replace(array('.html', '.tpl'), '.php', basename($this->href));
	}
	
	/**
	 * Returns the href of the cached file
	 * 
	 * @return string href to the cached file
	 */
	public function getCompilationHref() {
		return $this->compilationHref;
	}
	
	/**
	 * Returns the compilation time
	 * 
	 * @return int compilation time
	 */
	public function getCompilationTime() {
		return $this->compilationTime;
	}
	
	/**
	 * Checks if there is a valid compiled file
	 * 
	 * @return boolean true if compilation is NOT required, false otherwise
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
		if (empty($this->compilationHref) || $cacheDate < $this->creationTime) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Compiles this file
	 * 
	 * @param WTemplateCompiler $compiler This object is a compiler which will compile each nodes
	 * @return Output compiled code
	 * @throws Exception
	 */
	public function compile(WTemplateCompiler $compiler) {
		if (!$this->checkCompilation()) {
			$start = microtime(true);
			
			// Read template file
			if (($string = file_get_contents($this->baseDir.$this->href)) === false) {
				throw new Exception("WTemplateFile::compile(): Unable to read file \"".$this->href."\".");
			}
			
			// Compile file
			$code = $compiler->compileString($string, array('dir' => dirname($this->href)));
			
			// Save output
			$this->saveFile($code);
			
			// Update data
			$this->compilationTime = microtime(true) - $start; // ellapsed time
			$this->compiled = true;
			
			return $code;
		}
		return file_get_contents($this->getCompilationHref());
	}
	
	/**
	 * Writes output string into the cache file
	 * 
	 * @param string $data the output string
	 * @throws Exception
	 */
	private function saveFile($data) {
		if (is_writable($this->compilationDir)) {
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
}

?>