-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 31, 2013 at 12:36 PM
-- Server version: 5.5.20
-- PHP Version: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` tinytext CHARACTER SET latin1 NOT NULL,
  `title` tinytext CHARACTER SET latin1 NOT NULL,
  `author` varchar(30) CHARACTER SET latin1 NOT NULL,
  `content` text CHARACTER SET latin1 NOT NULL,
  `keywords` mediumtext CHARACTER SET latin1 NOT NULL,
  `cat` tinytext CHARACTER SET utf8 NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `editor_id` int(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `publier` tinyint(1) NOT NULL DEFAULT '0',
  `image` tinytext CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletters`
--

CREATE TABLE IF NOT EXISTS `newsletters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `de` varchar(100) CHARACTER SET latin1 NOT NULL,
  `objet` varchar(255) CHARACTER SET latin1 NOT NULL,
  `message` text CHARACTER SET latin1 NOT NULL,
  `destinataires` text CHARACTER SET latin1 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cc` tinytext CHARACTER SET latin1 NOT NULL,
  `cci` tinytext CHARACTER SET latin1 NOT NULL,
  `attachment` varchar(255) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_cats`
--

CREATE TABLE IF NOT EXISTS `news_cats` (
  `cid` tinyint(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext CHARACTER SET latin1 NOT NULL,
  `shortname` tinytext CHARACTER SET utf8 NOT NULL,
  `parent` tinyint(4) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_cats_relations`
--

CREATE TABLE IF NOT EXISTS `news_cats_relations` (
  `news_id` mediumint(9) NOT NULL,
  `cat_id` tinyint(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` tinytext CHARACTER SET latin1 NOT NULL,
  `title` tinytext CHARACTER SET latin1 NOT NULL,
  `author` varchar(30) CHARACTER SET latin1 NOT NULL,
  `content` text CHARACTER SET latin1 NOT NULL,
  `keywords` mediumtext CHARACTER SET latin1 NOT NULL,
  `creation_time` int(11) NOT NULL,
  `edit_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(100) CHARACTER SET latin1 NOT NULL,
  `password` varchar(50) CHARACTER SET utf8 NOT NULL,
  `confirm` varchar(25) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `email` varchar(100) CHARACTER SET latin1 NOT NULL,
  `firstname` varchar(100) CHARACTER SET utf8 NOT NULL,
  `lastname` varchar(100) CHARACTER SET utf8 NOT NULL,
  `country` varchar(25) CHARACTER SET utf8 NOT NULL,
  `groupe` int(4) NOT NULL,
  `access` text CHARACTER SET latin1 NOT NULL,
  `valid` tinyint(4) NOT NULL DEFAULT '1',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(50) CHARACTER SET utf8 NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_config`
--

CREATE TABLE IF NOT EXISTS `users_config` (
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(50) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

INSERT INTO `users_config` (`name`, `value`) VALUES
('register', '1'),
('email_conf', '1'),
('admin_check', '1'),
('summary', '1'),
('keep_users', '1');

--
-- Table structure for table `users_groups`
--

CREATE TABLE IF NOT EXISTS `users_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` mediumint(9) NOT NULL,
  `name` varchar(50) CHARACTER SET latin1 NOT NULL,
  `access` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
