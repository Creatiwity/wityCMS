<?php
/**
 * SortingHelper.php
 */

/**
 * SortingHelper helps you to add a sorting system to your pages
 *
 * Thanks to this class you'll be able to know what variable to choose if you want to sort a set
 *
 * @package Helpers
 * @author Johan Dufau <johan.dufau@creatiwity.net>
 * @version 0.3-12-07-2012
 */
class SortingHelper {

    /**
     *
     * @var array List of every sorting variables
     */
	private $fields = array();

    /**
     *
     * @var string The final sorting variable
     */
	private $sortBy;
    /**
     *
     * @var string The final sorting direction
     */
	private $sens;

    /**
     *
     * @var string The default sorting variable
     */
	private $sortByDef;
    /**
     *
     * @var string The default sorting direction
     */
	private $sensDef;

    /**
     * Setup SortingHelper
     *
     * @param array     $fields     list of every sorting variables
     * @param string    $sortByDef  the default sorting variable
     * @param string    $sens       the default sorting variable
     * @throws Exception
     */
	public function __construct(array $fields, $sortByDef = null, $sens = 'ASC') {
		if (empty($fields)) {
			throw new Exception("SortingHelper::__construct() : no sorting fields given.");
		}
		$this->fields = $fields;
		$this->defineDefault(empty($sortByDef) ? $fields[0] : $sortByDef, $sens);
	}

    /**
     * Defines the default values
     *
     * @param string $sortBy    the default sorting variable
     * @param string $sens      the default sorting variable
     */
	public function defineDefault($sortBy, $sens) {
		if (in_array($sortBy, $this->fields)) {
			$this->sortByDef = $sortBy;
		}
		$this->sensDef = $this->formatDirection($sens);
	}

    /**
     * Returns the formated direction parameter
     *
     * @param string $sens the direction parameter
     * @return string the formated direction parameter (DESC|ASC)
     */
	private function formatDirection($sens) {
		return (strtoupper($sens) == 'DESC') ? 'DESC' : 'ASC';
	}

    /**
     * Finds the final sorting parameters after checking their existency in the initial values
	 * and returns it.
     *
     * @param string    $sortBy the asked sorting variable
     * @param string    $sens   the asked sorting direction
     * @return array the final pair sorting variable and direction
     */
	public function findSorting($sortBy, $sens) {
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
     * Returns the final sorting parameters
     *
     * @return array the final pair sorting variable and direction
     */
	public function getSorting() {
		return array($this->sortBy, $this->sens);
	}

    /**
     * Returns some useful variables that will be used to display the template
     *
     * These variables are :
     * - the classes for the sorting variables
     * - the sorting direction
     *
     * @return array list of useful variables (see the long description)
     */
	public function getTplVars() {
		$vars = array();
		foreach ($this->fields as $field) {
			if ($field == $this->sortBy) {
				$vars[$field.'_class'] = ($this->sens == 'ASC') ? 'glyphicon glyphicon-chevron-up' : 'glyphicon glyphicon-chevron-down';
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
