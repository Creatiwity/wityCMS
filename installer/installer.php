<?php

/**
 * installer.php
 */
defined('IN_WITY') or die('Access denied');

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
        Installer::$view = new View();

        $data = Request::getAssoc(array('state'), 'START', 'POST');

        switch ($data) {
            case 'START':
                //echo view.html
                break;

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
        }
    }

    public static function verifyDatabaseCredentials() {
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

}

?>
