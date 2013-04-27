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
    private static function isURL($url, $data,&$respond) {
        return (!empty($url) && preg_match('/^(http|https|ftp)://([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?/?/i', $url));
    }

    private static function isVerifiedString($string, $data, &$respond) {
        return (!empty($url) && preg_match("/^[A-Z]?'?[- a-zA-Z]( [a-zA-Z])*$/i", $string));
    }

    private static function isFrontApp($app, $data, &$respond) {
        return in_array(strtolower($app), getFrontApps());
    }

    private static function isAdminApp($app, $data, &$respond) {
        return in_array(strtolower($app), getAdminApps());
    }

    private static function isTheme($theme, $data, &$respond) {
        return in_array(strtolower($theme), getThemes());
    }

    private static function isSQLServer($credentials, $data, &$respond) {
        if (!class_exists('PDO')) {
            self::$view->error('installer', $data['installer'], 'System failure', 'PDO class cannot be found. This feature has been introduced since PHP5.1+');
            return $respond = false;
        }

        $dsn = 'mysql:dbname=;host='.WConfig::get('database.server');
        $dsn .= (isset($credentials['port']) && !empty($credentials['port']) && is_numeric($credentials['port'])) ? ';port='.$credentials['port']):'';

        try {
            new PDO($dsn, $credentials['user'], $$credentials['pw']);
        } catch (PDOException $e) {
            if(strstr($e->getMessage(), 'SQLSTATE[')) { 
                preg_match('/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $e->getMessage(), $matches);
                if ($matches[2] == "1049") {
                    return true;
                } else if ($matches[2] == "1044") {
                    self::$view->error('group', $data['group'], 'Unable to connect to the database', "Bad user/password.");
                    return $respond = false;
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    private static function isDatabase($credentials, $data, &$respond) {
        if (!class_exists('PDO')) {
            self::$view->error('installer', $data['installer'], 'System failure', 'PDO class cannot be found. This feature has been introduced since PHP5.1+');
            return $respond = false;
        }

        $dsn = 'mysql:dbname=;host='.WConfig::get('database.server');
        $dsn .= (isset($credentials['port']) && !empty($credentials['port']) && is_numeric($credentials['port'])) ? ';port='.$credentials['port']):'';

        try {
            new PDO($dsn, $credentials['user'], $$credentials['pw']);
        } catch (PDOException $e) {
            if(strstr($e->getMessage(), 'SQLSTATE[')) { 
                preg_match('/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $e->getMessage(), $matches);
                if ($matches[2] == "1049") {
                    self::$view->error('group', $data['group'], 'Unable to find the database', "The database you specified cannot be found.");
                    return $respond = false;
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    private static function isPrefixNotExisting($credentials, $data, &$respond) {
        if (!preg_match("/^[a-zA-Z0-9]$/", $credentials['prefix'])) {
            self::$view->error('group', $data['group'], 'Malformed prefix', 'The prefix must be only alphanumeric.');
            return $respond = false;
        }

        if (!class_exists('PDO')) {
            self::$view->error('installer', $data['installer'], 'System failure', 'PDO class cannot be found. This feature has been introduced since PHP5.1+');
            return $respond = false;
        }

        $dsn = 'mysql:dbname=;host='.WConfig::get('database.server');
        $dsn .= (isset($credentials['port']) && !empty($credentials['port']) && is_numeric($credentials['port'])) ? ';port='.$credentials['port']):'';

        try {
            $db = new PDO($dsn, $credentials['user'], $$credentials['pw']);
        } catch (PDOException $e) {
            if(strstr($e->getMessage(), 'SQLSTATE[')) { 
                preg_match('/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $e->getMessage(), $matches);
                if ($matches[2] == "1049") {
                    self::$view->error('group', $data['group'], 'Unable to find the database', "The database you specified cannot be found.");
                    return $respond = false;
                } else {
                    return false;
                }
            }
        }

        $prefix = (!empty($credentials['prefix'])) ? $credentials['prefix']."_":"";

        $prep = $db->prepare("SHOW TABLES LIKE :prefixedTable");
        $prep->bindParam(":prefixedTable", $prefix."user");
        $prep->execute();
        return $prep->fetch() ? false;true;
    }

    private static function isEmail($email, $data, &$respond) {
        return (!empty($email) && preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $email));
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
