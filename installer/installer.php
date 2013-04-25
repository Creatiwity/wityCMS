<?php

/**
 * installer.php
 */
defined('IN_WITY') or die('Access denied');
define('DS', DIRECTORY_SEPARATOR);

require 'request.php';
require 'view.php';

/**
 * Installer installs Wity on the server (configuration files and MySQL tables)
 *
 * @package Installer
 * @author Julien Blatecky <julien1619@gmail.com>
 * @version 0.3-17-01-2013
 */
class Installer {

    private static $THEMES_DIR;
    private static $APPS_DIR;
    private static $CONFIG_DIR;

    private static $EXCLUDED_THEMES = array('system', 'admin');
    private static $EXCLUDED_APPS = array('admin');
    private static $EXCLUDED_DIRS = array('.', '..');

    private static $view;

    /**
     * Security system
     * 
     *  if (the lock file exists && the lock file is still valid) || lock file does not exist
     *      create lock file (again)
     *      execute control
     *  else
     *      return an error message (msg: delete lock file) 
     *  
     */
    public static function launch() {
        self::$THEMES_DIR = "themes";
        self::$APPS_DIR = "apps";
        self::$CONFIG_DIR = "system".DS."config";

        self::$view = new View();

        $data = Request::getAssoc(array('command', 'installer', 'step', 'group'), array('command'=>'START', 'installer'=>'', 'step'=>'', 'group'=>''), 'POST');

        switch ($data['command']) {
            case 'START':
                self::$view->render();
                return;

            // Groups
            case 'GENERALITIES':
                //Test name, theme and configure config.php
                break;

            case 'DATABASE':
                //Test database and configure Database.php
                break;

            case 'DEFAULT_CONFIG':
                //Set default app for front and admin mode
                break;

            case 'FIRST_ADMIN':
                //Configure the first user in the DB
                break;

            //Autocompletes
            case 'GET_THEMES':
                if($themes = self::getThemes()) {
                    self::$view->push_content("GET_THEMES", $themes);
                } else {
                    self::$view->error('installer', $data['installer'], 'Fatal Error', 'Themes directory cannot be found.');
                }
                break;

            case 'GET_FRONT_APPS':
                if($themes = self::getFrontApps()) {
                    self::$view->push_content("GET_FRONT_APPS", $themes);
                } else {
                    self::$view->error('installer', $data['installer'], 'Fatal Error', 'Applications directory cannot be found.');
                }
                break;

            case 'GET_ADMIN_APPS':
                if($themes = self::getAdminApps()) {
                    self::$view->push_content("GET_ADMIN_APPS", $themes);
                } else {
                    self::$view->error('installer', $data['installer'], 'Fatal Error', 'Applications directory cannot be found.');
                }
                break;
        }

        self::$view->respond();
    }

    /**
     * Validators
     **/
    private static function isURL($url) {

    }

    private static function isVerifiedString($string) {

    }

    private static function isFrontApp($app) {

    }

    private static function isAdminApp($app) {

    }

    private static function isSQLServer($credentials) {

    }

    private static function isDatabase($credentials) {

    }

    private static function isPrefixExisting($prefix) {

    }

    private static function isEmail($email) {
        
    }

    private static function verifyDatabaseCredentials() {
        $data = Request::getAssoc(array('server', 'port', 'user', 'pw', 'dbname', 'prefix'), array('', '', '', '', '', ''), 'POST');

        if (!class_exists('PDO')) {
            //Error PDO not found
        }

        try {
            # Bug de PHP5.3 : constante PDO::MYSQL_ATTR_INIT_COMMAND n'existe pas
            $db = new PDO($dsn, $user, $password);
        } catch (PDOException $e) {
            //view.error("Impossible to connect to MySQL.<br />".utf8_encode($e->getMessage()));
        }
    }

    /**
     * Getters
     **/
    private static function getThemes() {
        if($result = scandir(self::$THEMES_DIR)) {
            foreach ($result as $key => $value) {
                if(in_array($value, self::$EXCLUDED_THEMES) || !is_dir(self::$THEMES_DIR.DS.$value) || in_array($value, self::$EXCLUDED_DIRS)) {
                    unset($result[$key]);
                }
            }
            $result[] = "_blank";
        }

        return $result;
    }

    private static function getFrontApps() {
        if($result = scandir(self::$APPS_DIR)) {
            foreach ($result as $key => $value) {
                if(in_array($value, self::$EXCLUDED_APPS) || !is_dir(self::$APPS_DIR.DS.$value.DS."front") || in_array($value, self::$EXCLUDED_DIRS)) {
                    unset($result[$key]);
                }
            }
        }

        return $result;
    }

    private static function getAdminApps() {
        if($result = scandir(self::$APPS_DIR)) {
            foreach ($result as $key => $value) {
                if(in_array($value, self::$EXCLUDED_APPS) || !is_dir(self::$APPS_DIR.DS.$value.DS."admin") || in_array($value, self::$EXCLUDED_DIRS)) {
                    unset($result[$key]);
                }
            }
        }

        return $result;
    }

}

?>
