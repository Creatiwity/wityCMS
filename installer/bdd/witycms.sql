-- wityCMS Database Dump
-- Version: 0.6.2-04-06-2018

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `wityCMS`
--

-- --------------------------------------------------------

--
-- Table structure for table `prefix_news`
--

DROP TABLE IF EXISTS `prefix_news`;
CREATE TABLE IF NOT EXISTS `prefix_news` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `views` int(11) unsigned NOT NULL DEFAULT '0',
  `image` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_news_lang`
--

CREATE TABLE IF NOT EXISTS `prefix_news_lang` (
  `id_news` int(11) unsigned NOT NULL,
  `id_lang` int(11) unsigned NOT NULL,
  `title` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `author` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `url` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `meta_title` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `meta_description` text COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL,
  `publish_date` datetime NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_news`,`id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `id_news` int(11) unsigned NOT NULL,
  `id_cat` int(11) unsigned NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_news`,`id_cat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_users`
--

DROP TABLE IF EXISTS `prefix_users`;
CREATE TABLE IF NOT EXISTS `prefix_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
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

--
-- Content of table `prefix_users_config`
--

INSERT INTO `prefix_users_config` (`name`, `value`, `created_date`) VALUES
('register', '1', NOW()),
('email_conf', '0', NOW()),
('admin_check', '0', NOW()),
('summary', '1', NOW()),
('keep_users', '1', NOW());

-- --------------------------------------------------------

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
  `attachment` varchar(200),
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
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_mail_available_actions`
--

DROP TABLE IF EXISTS `prefix_mail_available_actions`;
CREATE TABLE IF NOT EXISTS `prefix_mail_available_actions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_mail_configuration`
--

DROP TABLE IF EXISTS `prefix_mail_configuration`;
CREATE TABLE IF NOT EXISTS `prefix_mail_configuration` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` varchar(500) NOT NULL,
  `user_id` mediumint(9) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Content of table `mail_configuration`
--

INSERT INTO `prefix_mail_configuration` (`id`, `key`, `value`, `user_id`, `created_date`, `created_by`, `modified_date`, `modified_by`) VALUES
(1, 'canReceive', '0', 0, NOW(), 0, '0000-00-00 00:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `prefix_mail_list`
--

DROP TABLE IF EXISTS `prefix_mail_list`;
CREATE TABLE IF NOT EXISTS `prefix_mail_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_mail_mailing`
--

DROP TABLE IF EXISTS `prefix_mail_mailing`;
CREATE TABLE IF NOT EXISTS `prefix_mail_mailing` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_page`
--

DROP TABLE IF EXISTS `prefix_page`;
CREATE TABLE IF NOT EXISTS `prefix_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `views` int(11) NOT NULL DEFAULT '0',
  `parent` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `menu` tinyint(4) NOT NULL DEFAULT '0',
  `image` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_page_lang`
--

DROP TABLE IF EXISTS `prefix_page_lang`;
CREATE TABLE IF NOT EXISTS `prefix_page_lang` (
  `id_page` int(11) unsigned NOT NULL,
  `id_lang` int(11) unsigned NOT NULL,
  `title` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `subtitle` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `author` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `url` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `meta_title` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `meta_description` text COLLATE utf8_unicode_ci NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_page`,`id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_slideshow_slide`
--

DROP TABLE IF EXISTS `prefix_slideshow_slide`;
CREATE TABLE IF NOT EXISTS `prefix_slideshow_slide` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_slideshow_slide_lang`
--

DROP TABLE IF EXISTS `prefix_slideshow_slide_lang`;
CREATE TABLE IF NOT EXISTS `prefix_slideshow_slide_lang` (
  `id_slide` int(11) unsigned NOT NULL,
  `id_lang` int(11) unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `legend` text COLLATE utf8_unicode_ci NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`id_slide`,`id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `prefix_slideshow_config`
--

DROP TABLE IF EXISTS `prefix_slideshow_config`;
CREATE TABLE IF NOT EXISTS `prefix_slideshow_config` (
  `key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Content of table `prefix_slideshow_config`
--

INSERT INTO `prefix_slideshow_config` (`key`, `value`, `created_date`, `created_by`, `modified_date`, `modified_by`) VALUES
('autoplay', '0', NOW(), 0, '0000-00-00 00:00:00', 1),
('time_pause', '4000', NOW(), 0, '0000-00-00 00:00:00', 1),
('time_transition', '500', NOW(), 0, '0000-00-00 00:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `prefix_newsletter`
--

DROP TABLE IF EXISTS `prefix_newsletter`;
CREATE TABLE IF NOT EXISTS `prefix_newsletter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_team_member`
--

DROP TABLE IF EXISTS `prefix_team_member`;
CREATE TABLE IF NOT EXISTS `prefix_team_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `linkedin` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `twitter` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `image_hover` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prefix_team_member_lang`
--

DROP TABLE IF EXISTS `prefix_team_member_lang`;
CREATE TABLE IF NOT EXISTS `prefix_team_member_lang` (
  `id_member` int(10) unsigned NOT NULL,
  `id_lang` int(11) unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`id_member`,`id_lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Structure de la table `prefix_languages`
--

DROP TABLE IF EXISTS `prefix_languages`;
CREATE TABLE IF NOT EXISTS `prefix_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `iso` varchar(50) CHARACTER SET utf8 NOT NULL,
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date_format_short` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date_format_long` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `is_default` tinyint(1) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `prefix_languages`
--

INSERT INTO `prefix_languages` (`id`, `name`, `iso`, `code`, `date_format_short`, `date_format_long`, `enabled`, `is_default`, `created_date`, `created_by`, `modified_date`, `modified_by`) VALUES
(1, 'Français', 'fr', 'fr_FR', '%d/%m/%Y', '%d/%m/%Y %H:%M', 1, 1, NOW(), 0, '0000-00-00 00:00:00', 0),
(2, 'English', 'en', 'en_EN', '%m/%d/%Y', '%m/%d/%Y %H:%M', 1, 0, NOW(), 0, '0000-00-00 00:00:00', 0);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
