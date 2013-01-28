<?php
/**
 * view.php
 */

defined('IN_WITY') or die('Access denied');

/**
 * View sends all Installer's stuff to the client
 *
 * @package Installer
 * @author Julien Blatecky <julien1619@gmail.com>
 * @version 0.3-25-01-2013
 */
class View {
    
    /**
     *
     * @var array Stores the response which will be sent in json to the client 
     */
    private $response = array();
    
    /**
     * Stores an error in $response in the $state row
     * 
     * @param type $state
     * @param type $code
     * @param type $message
     */
    public function error($state,$code,$message) {
        View::$response[$state][] = array($code,$message);
    }
    
    public function success() {
        
    }
    
    public function info() {
        
    }
	
	public function render() {
		if(file_exists("view/view.html")) {
			$content = file_get_contents("view/view.html");
			echo $content;
		}
	}
}

?>
