<?php
/**
 * Wity CMS
 * 
 * SortingHelper est une classe qui permet de connaître selon quelle variable
 * d'un ensemble on doit faire un classement
 *
 * @author Fofif
 * @version	$Id: index.php 0002 12-07-2012 Fofif $
 */

class SortingHelper {
	// Every sorting variables
	private $fields = array();
	
	// The final sorting variable and direction
	private $sortBy;
	private $sens;
	
	// Default values
	private $sortByDef;
	private $sensDef;
	
	public function __construct(array $fields, $sortByDef = null, $sens = 'ASC') {
		if (empty($fields)) {
			throw new Exception("SortingHelper::__construct() : no sorting fields given.");
		}
		$this->fields = $fields;
		$this->defineDefault(empty($sortByDef) ? $fields[0] : $sortByDef, $sens);
	}
	
	public function defineDefault($sortBy, $sens) {
		if (in_array($sortBy, $this->fields)) {
			$this->sortByDef = $sortBy;
		}
		$this->sensDef = $this->formatDirection($sens);
	}
	
	private function formatDirection($sens) {
		return (strtoupper($sens) == 'DESC') ? 'DESC' : 'ASC';
	}
	
	public function getSorting($sortBy, $sens) {
		if (in_array($sortBy, $this->fields)) {
			$this->sortBy = $sortBy;
		} else {
			$this->sortBy = $this->sortByDef;
		}
		
		if (!empty($sens)) {
			$this->sens = $this->formatDirection($sens);
		} else {
			$this->sens = $this->sensDef;
		}
		
		return array($this->sortBy, $this->sens);
	}
	
	/**
	 * Génère les variables utiles pour l'affichage du template
	 * - les classes pour la variable de classement
	 * - le sens du classement
	 */
	public function getTplVars() {
		$vars = array();
		foreach ($this->fields as $field) {
			if ($field == $this->sortBy) {
				$vars[$field.'_class'] = ($this->sens == 'ASC') ? 'sortAsc' : 'sortDesc';
				$vars[$field.'_sort'] = ($this->sens == 'ASC') ? 'desc' : 'asc';
			} else {
				$vars[$field.'_class'] = '';
				$vars[$field.'_sort'] = 'asc';
			}
		}
		return $vars;
	}
}

?>