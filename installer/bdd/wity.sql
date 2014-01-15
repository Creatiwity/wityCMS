-- WityCMS Database Dump
-- Version: 0.4
-- Updated on: 28/11/2013

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `WityCMS`
--

-- --------------------------------------------------------

--
-- Table structure for table `prefix_news`
--

DROP TABLE IF EXISTS `prefix_news`;
CREATE TABLE IF NOT EXISTS `prefix_news` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` tinytext NOT NULL,
  `title` tinytext NOT NULL,
  `author` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `meta_title` tinytext NOT NULL,
  `keywords` mediumtext NOT NULL,
  `description` text NOT NULL,
  `views` int(11) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_news_cats`
--

DROP TABLE IF EXISTS `prefix_news_cats`;
CREATE TABLE IF NOT EXISTS `prefix_news_cats` (
  `cid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  `shortname` tinytext NOT NULL,
  `parent` int(11) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_news_cats_relations`
--

DROP TABLE IF EXISTS `prefix_news_cats_relations`;
CREATE TABLE IF NOT EXISTS `prefix_news_cats_relations` (
  `news_id` int(11) unsigned NOT NULL,
  `cat_id` int(11) unsigned NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_users`
--

DROP TABLE IF EXISTS `prefix_users`;
CREATE TABLE IF NOT EXISTS `prefix_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(100) COLLATE utf8_bin NOT NULL,
  `password` varchar(50) NOT NULL,
  `confirm` varchar(25) NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `country` varchar(25) NOT NULL,
  `lang` varchar(10) NOT NULL,
  `groupe` int(11) NOT NULL,
  `access` text NOT NULL,
  `valid` tinyint(4) NOT NULL DEFAULT '1',
  `last_activity` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(50) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_users_config`
--

DROP TABLE IF EXISTS `prefix_users_config`;
CREATE TABLE IF NOT EXISTS `prefix_users_config` (
  `name` varchar(20) NOT NULL,
  `value` varchar(50) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

INSERT INTO `prefix_users_config` (`name`, `value`, `created_date`) VALUES
('register', '1', NOW()),
('email_conf', '0', NOW()),
('admin_check', '0', NOW()),
('summary', '1', NOW()),
('keep_users', '1', NOW());

--
-- Table structure for table `prefix_users_groups`
--

DROP TABLE IF EXISTS `prefix_users_groups`;
CREATE TABLE IF NOT EXISTS `prefix_users_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `access` text NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_contact`
--

DROP TABLE IF EXISTS `prefix_contact`;
CREATE TABLE IF NOT EXISTS `prefix_contact` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(130) NOT NULL,
  `from` varchar(300) NOT NULL,
  `from_id` mediumint(5) DEFAULT NULL,
  `to` text NOT NULL,
  `cc` text,
  `bcc` text,
  `reply_to` text,
  `name` varchar(200) NOT NULL,
  `organism` varchar(200) DEFAULT NULL,
  `object` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_contact_config`
--

DROP TABLE IF EXISTS `prefix_contact_config`;
CREATE TABLE IF NOT EXISTS `prefix_contact_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` varchar(1000) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `prefix_contact_config` (`key`, `value`, `created_date`) VALUES
('site_from_name', '', NOW()),
('site_from_email', '', NOW());

-- --------------------------------------------------------

--
-- Table structure for table `prefix_mail_action_history`
--

DROP TABLE IF EXISTS `prefix_mail_action_history`;
CREATE TABLE IF NOT EXISTS `prefix_mail_action_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash_action` varchar(40) NOT NULL,
  `hash_mail` varchar(40) NOT NULL,
  `user_id` mediumint(9) NOT NULL,
  `email` varchar(300) NOT NULL,
  `url` varchar(2000) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_mail_available_actions`
--

DROP TABLE IF EXISTS `prefix_mail_available_actions`;
CREATE TABLE IF NOT EXISTS `prefix_mail_available_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash_action` varchar(40) NOT NULL,
  `hash_mail` varchar(40) NOT NULL,
  `one_time` varchar(1) NOT NULL,
  `expires` datetime NOT NULL,
  `url` varchar(2000) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_mail_configuration`
--

DROP TABLE IF EXISTS `prefix_mail_configuration`;
CREATE TABLE IF NOT EXISTS `prefix_mail_configuration` (
  `key` varchar(100) NOT NULL,
  `value` varchar(500) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_mail_list`
--

DROP TABLE IF EXISTS `prefix_mail_list`;
CREATE TABLE IF NOT EXISTS `prefix_mail_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(40) NOT NULL,
  `mailing_hash_id` varchar(27) NOT NULL,
  `from` text NOT NULL,
  `to` text NOT NULL,
  `cc` text NOT NULL,
  `bcc` text NOT NULL,
  `attachments` text NOT NULL,
  `subject` text NOT NULL,
  `body` text NOT NULL,
  `compiled_subject` text NOT NULL,
  `compiled_body` text NOT NULL,
  `params` text NOT NULL,
  `state` varchar(10) NOT NULL,
  `date_state_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_mail_mailing`
--

DROP TABLE IF EXISTS `prefix_mail_mailing`;
CREATE TABLE IF NOT EXISTS `prefix_mail_mailing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `internal_id` varchar(27) NOT NULL,
  `action_expiration` varchar(150) NOT NULL,
  `response_policy` varchar(5) NOT NULL,
  `response_callback` varchar(300) NOT NULL,
  `sender_id` mediumint(9) NOT NULL,
  `origin_app` varchar(100) NOT NULL,
  `origin_action` varchar(100) NOT NULL,
  `origin_parameters` varchar(1000) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Structure de la table `prefix_media_access_history`
--

DROP TABLE IF EXISTS `prefix_media_access_history`;
CREATE TABLE IF NOT EXISTS `prefix_media_access_history` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `file_id` mediumint(9) NOT NULL,
  `state` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Structure de la table `prefix_media_filetag_rel`
--

DROP TABLE IF EXISTS `prefix_media_filetag_rel`;
CREATE TABLE IF NOT EXISTS `prefix_media_filetag_rel` (
  `file_id` mediumint(9) NOT NULL,
  `tag_id` mediumint(9) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Structure de la table `prefix_media_list`
--

DROP TABLE IF EXISTS `prefix_media_list`;
CREATE TABLE IF NOT EXISTS `prefix_media_list` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `fileID` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `mime` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `extension` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Structure de la table `prefix_media_tags`
--

DROP TABLE IF EXISTS `prefix_media_tags`;
CREATE TABLE IF NOT EXISTS `prefix_media_tags` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `app` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `action` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `parameters` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `extras` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
