<?php

/**
 * route.php
 */
defined('IN_WITY') or die('Access denied');

/**
 * Route is a lite WRoute that will be used by the installer
 * 
 * @package Installer
 * @author Julien Blatecky <julien1619@gmail.com>
 * @author Johan Dufau <johandufau@gmail.com>
 * @version 0.3-17-01-2013
 */
class Route {
    /**
     * If the URL is http://mysite.fr/wity/user/login
     * and if wity is executed in /wity/, then the $query will be set to "user/login"
     */

    /**
     * @var string Request string of the page
     */
    private static $query;

    /**
     * Initializes Route
     */
    public static function init() {
        $dir = self::getDir();
        if ($dir != '/') {
            self::$query = str_replace($dir, '', $_SERVER['REQUEST_URI']);
        } else {
            self::$query = $_SERVER['REQUEST_URI'];
        }
    }

    /**
     * Launches the calculation of the route to find out the app to execute
     */
    public static function route() {
        $query = trim(self::$query, '/');

        // Loading URL config
        $routage = self::parseURL(self::$query);
    }

    /**
     * Returns the partial WityCMS root directory
     * 
     * If the website adress is http://mysite.fr/wity/user/login
     * it will return /wity/
     * 
     * @return string the partial root location of WityCMS
     */
    public static function getDir() {
        return substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1);
    }

    /**
     * Returns the full URL of the page
     * 
     * For example: http://mysite.fr/wity/user/login
     * 
     * @return string the full URL
     */
    public static function getURL() {
        return self::getBase() . self::$query;
    }

    /**
     * Return the referer (the previous address)
     * 
     * @return string the referer
     */
    public static function getReferer() {
        $base = self::getBase();
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $base) !== false) {
            return $_SERVER['HTTP_REFERER'];
        } else {
            return $base;
        }
    }

    /**
     * Parse the webpage URL
     * 
     * @param string $url webpage URL (ex: http://MySite.com/News/Read/1)
     * @return array the route (ex: array('app' => "News", 'args' => array(1)))
     */
    private static function parseURL($url) {
        $routage = array();

        // Cleaning
        $url = trim($url, '/');
        $url = str_replace(array('index.php', '.html', '.htm'), '', $url);
        $url = preg_replace('#\?.*$#', '', $url); // Nettoyage des query string

        $array = explode('/', $url);
        // Given application name
        if (!empty($array[0])) {
            $routage[] = strtolower(array_shift($array));
            if (sizeof($array) > 0) {
                // Storing arguments
                $routage[] = $array;
            } else {
                $routage[] = array();
            }
        }
        return $routage;
    }

}

?>
