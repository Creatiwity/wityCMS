-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le : Ven 30 Décembre 2011 à 14:48
-- Version du serveur: 5.5.16
-- Version de PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `test`
--

-- --------------------------------------------------------

--
-- Structure de la table `contact_mails`
--

CREATE TABLE IF NOT EXISTS `contact_mails` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(9) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `organisme` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `objet` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=75 ;

--
-- Contenu de la table `contact_mails`
--

INSERT INTO `contact_mails` (`id`, `userid`, `date`, `name`, `organisme`, `email`, `objet`, `message`) VALUES
(51, 0, '2011-09-05 12:54:23', 'marcadella', 'Fives Cryo', 'sylvie.marcadella@fivesgroup.com', 'Stand', 'J''avais demandé il y a quelques temps un plan d''implantation des stands afin de définir le surface à réserver. \r\nJe n''ai toujour pas de réponse à ce jour. \r\nDe plus aujourd''hui je ne parviens pas à m''identifier. Egalement nous serions éventuellement intéressé par la journée du 13 octobre mais nous n''avons aucun renseignement. merci de votre aide.\r\n'),
(50, 0, '2011-09-01 12:27:13', 'jean philippe', 'maison carree', 'jpheditions@orange.fr', 'restauration', 'bonjour, \r\n\r\npuis je vous inviter demain a midi a la maison carree pour vous faire des propositions pour le repas\r\nnous travaillons au parc des expo de nancy'),
(49, 0, '2011-09-01 08:56:48', 'Anne-Sophie BAUDENS', 'ACTIMAGE', 'anne-sophie.baudens@actimage.com', 'Renseignements', 'Bonjour,\r\n\r\nNous sommes une société de conseils en informatique basée au Luxembourg et nous aimerions obtenir des informations sur votre forum pour pouvoir peut-être y participer.\r\n\r\nPouvez-vous me transmettre votre grille tarifaire des stands svp?\r\n\r\nCordialement,\r\nAnne-Sophie BAUDENS'),
(16, 0, '2011-05-20 11:41:24', 'Morel Alexis', 'Canaljob.fr', 'alexis@can.aljob.com', 'Proposition de partenariat', 'Bonjour,\r\n\r\nje travaille pour Canaljob.fr, site d''emploi généraliste'),
(74, 0, '2011-11-19 15:47:00', 'THINUS jean-marc', 'JMT CONSEIL', 'jean-marc.thinus@orange.fr', 'PARUTION CATALOGUE', 'CONTACTER MONSIEUR THINUS'),
(20, 0, '2011-05-20 11:42:32', 'Morel Alexis', 'Canaljob.fr', 'alexis@canaljob.com', 'Proposition de partenariat', 'Bonjour,\r\n\r\nfaites vous des partenariats (échange de visibilité) avec des job boards ? Si oui, nous serions interessé.\r\n\r\nCanaljob.fr\r\nAlexis Morel\r\nTel : 06 27 15 43 84\r\n'),
(68, 0, '2011-11-15 10:45:29', 'zrari elodie', 'nancy 2', 'e.zrari3@gmail.com', 'acces forum', 'Bonjour, \r\n\r\nquelles sont les conditions d''accès à ce forum? \r\nDoit-on avoir une carte d''étudiant?\r\nMerci d''avance\r\nCordialement'),
(69, 0, '2011-11-16 15:05:03', 'Denais Clément', '', 'clement.denais@gmail.com', 'Accès au Forum', 'Bonjour,\r\nDiplômé 2010 de l’ICN, je souhaiterais savoir si je peux accéder au forum en tant que visiteur?\r\nMerci pour votre réponse.\r\nCordialement'),
(47, 0, '2011-08-09 09:24:39', 'MISBAH Magali', 'BNP Paribas', 'magali.misbah@bnpparibas.com', 'Inscription à l''édition 2011 duForum Est Horizon', 'Bonjour,\r\n\r\nJe me permets de vous solliciter car je ne parviens pas à accéder au formulaire d''inscription concernant l''édition 2011 du Forum Est Horizon.\r\n\r\nMes identifiants de l''année dernière ne fonctionnent pas.\r\nEt je ne peux pas créer de nouveau profil exposant.\r\n\r\nPouvez-vous, s''il vous plaît, revenir vers moi à ce sujet ?\r\n\r\nEn vous remerciant par avance.\r\n\r\nCordialement,\r\nMagali MISBAH\r\nRessources Humaines\r\nBNP Paribas\r\n\r\nTél : 01.40.14.47.11'),
(24, 0, '2011-05-23 14:00:41', 'Demur Mathieu', 'Ex FEH', 'mathieu.demur@mines-nancy.org', 'Rendu site Internet', 'Bravo pour la nouvelle mise en page du site, c''est bien beau !'),
(72, 0, '2011-11-19 09:42:58', 'thinus jean-marc', 'jmt conseil', 'jean-marc.thinus@orange.fr', 'parution catalogue', 'cotacter monsieur thinus'),
(73, 0, '2011-11-19 09:47:05', 'thinus jean-marc', 'jmt conseil', 'jean-marc.thinus@orange.fr', 'parution catalogue', 'contacter monsieur thinus'),
(45, 93, '2011-07-22 15:56:12', 'BRUN Thomas', 'Capgemini', 'thomas.brun@capgemini.com', 'Inscription ', 'Bonjour, \r\n\r\nJe souhaite m''assurer auprès de vous que l''inscription a bien été prise en compte. \r\n\r\nJe vous remercie par avance pour votre retour. \r\n\r\nCordialement,\r\n\r\nThomas Brun\r\nCapgemini\r\n0149673429'),
(70, 0, '2011-11-17 19:23:36', 'TORRACCA antoine', '', 'antoine.torracca@gmail.com', 'public', 'Le forum est-il ouvert à tous?'),
(43, 0, '2011-07-12 08:29:20', 'Norbert MAIRE', 'relais capimmec médéric nancy', 'norbert.danielle.maire@wanadoo.fr', 'participation au pole conseil', 'Comme chaque année, notre organisme peut mettre a votre disposition 4 consultant bénévoles en matière de techniques de recherche d''emploi ( cv, lettres de motivation et simulations d''entretien)\r\nL''an passé nous avons rencontré 45 étudiants.\r\nA votre disposition pour en parler a votre proche convenance soit par tel ou lors d''une rencontre.\r\nMeilleurs voeux de réussite pour l''opération 2011\r\n\r\nNorbert MAIRE  tel : 0329069151  ou  0682213390.'),
(71, 0, '2011-11-18 09:09:36', 'Morice DUMOULIN', 'étudiant', 'dumoulin_morice@hotmail.com', 'Demande de renseignement', 'Bonjour,\r\n\r\nSuite à notre conversation de hier, vous deviez me faire parvenir un plan de votre forum. Sans doute la retranscription de mon adresse mail contenait une erreur.\r\n\r\nMerci d''avance pour vos renseignements,\r\n\r\nMorice DUMOULIN'),
(41, 0, '2011-07-01 14:28:36', 'Heuga Aurélie', 'BEM', 'aurelie.heuga@bem.edu', 'Demande de documentation', 'Bonjour, \r\n\r\nJe souhaiterai obtenir plus d’informations sur le forum Est Horizon. Pourriez-vous me faire parvenir votre plaquette ainsi que les conditions tarifaires pour y participer en tant qu’exposant ?\r\n\r\nCordialement, \r\n'),
(40, 0, '2011-06-07 07:21:26', 'Gabach Claire', 'DIGILOR', 'cgabach@digilor.fr', 'Demande d''informations', 'Bonjour,\r\n\r\nJe suis une ICN1 actuellement stagiaire chez DIGILOR (start-up créée par des ICN2) et je suis chargée d''organiser un salon d''entreprises B to B. \r\n\r\nJe vous sollicite pour que vous me donniez des conseils pour l''organisation d''un tel événement.\r\n\r\nCordialement,\r\n\r\nClaire GABACH\r\nResponsable événementiel\r\nDIGILOR\r\n\r\ncgabach@digilor.fr\r\n06 33 51 65 41'),
(52, 0, '2011-09-06 07:41:46', 'LEROUGE Séverine', 'PERTUY Construction', 's.lerouge@pertuy-construction.fr', 'Inscription Forum 2011.', 'Bonjour, \r\n\r\nJe me permets de vous contacter concernant le Forum 2011 car nous participons chaque année à ce forum or à ce jour nous n''avons pas reçu d''invitation.\r\n\r\nJe souhaitais également vous faire une remarque car cette année pour la 1ère fois le Forum Est Horizon a lieu à la même date que le Forum Alsace Tech qui sont 2 forums très importants au niveau des Relations Ecoles et il est dommage que les 2 forums aient lieu à la même date.\r\n\r\nCordialement\r\n\r\nSéverine LEROUGE\r\nRelations Ecoles Norpac - Pertuy Construction'),
(53, 0, '2011-09-08 07:30:11', 'Zohra BA', 'ESC TOULOUSE', 'z.ba@esc-toulouse.fr', 'STAND POUR MS ET 3E CYCLES ESCT', 'Merci de bien vouloir me transmettre les tarifs pour un stand le 24/11.\r\nBien cordialement,'),
(55, 133, '2011-09-13 13:03:04', 'Jean-paul Schoeser', 'Pôle Emploi', 'jpaul.schoeser@pole-emploi.fr', 'Réunion préparatoire du 13 octobre', 'Bonjour,\r\nJe souhaiterais participer à la réunion préparatoire. Néanmoins, cela ne sera possible que si elle à lieu le matin.'),
(56, 133, '2011-09-13 13:30:58', 'Jean-paul Schoeser', 'Pôle Emploi', 'jpaul.schoeser@pole-emploi.fr', 'logo', 'Bonjour,\r\nJe n''arrive pas à insérer notre logo dans le texte de saisie de la brochure visiteurs.'),
(59, 0, '2011-09-16 09:43:04', 'DUGRAVOT Virginie', 'Arhs Developments', 'virginie.dugravot@arhs-developments.com', 'Demande de Tarifs ', 'Bonjour,\r\n\r\nPourriez-vous me faire parvenir les tarifs concernant votre Forum du 24 novembre 2001 ?\r\n\r\nMerci\r\nCordialement\r\nVirginie Dugravot'),
(60, 93, '2011-09-19 14:01:45', 'Aurélie Jacquot', 'Capgemini France', 'aurelie.jacquot@capgemini.com', 'pré-forum est horizon', 'Bonjour, \r\n\r\nje me permets de vous contcater au sujet du pré-forum du jeudi 13 Octobre 2011 à L''Ecole des Mines de Nancy.\r\n\r\nVous précisez sur le site que seront organisés des conférences, tables rondes, aide à la constitution d''un CV et préparation d''entretiens d''embauche.\r\n\r\nSerait-i possible d''avoir plus de détails sur le déroulé de la journée? Coment peut-on s''inscrire SVP?\r\n\r\nMerci d''avance\r\n\r\nbien cordialement\r\n\r\nAurélie Jacquot\r\nCapgemini \r\n01 49 67 47 68'),
(62, 142, '2011-09-22 14:00:20', 'COTT Pascale', 'ERDF', 'pascale.cott@erdfdistribution.fr', 'réservation stand nu de 12 m2', 'Bonjour,\r\nPouvez-vous nous réserver un stand nu de 12 m2 ?\r\nJ''aurais également besoin d''avoir le plan d''implantation ainsi que le dossier technique, afin de communiquer ces informations à notre standiste PROFIL.\r\nCordialement,');

-- --------------------------------------------------------

--
-- Structure de la table `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` tinytext CHARACTER SET latin1 NOT NULL,
  `title` tinytext CHARACTER SET latin1 NOT NULL,
  `author` varchar(30) CHARACTER SET latin1 NOT NULL,
  `content` text CHARACTER SET latin1 NOT NULL,
  `keywords` mediumtext CHARACTER SET latin1 NOT NULL,
  `cat` tinytext NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `editor_id` int(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `publier` tinyint(1) NOT NULL DEFAULT '0',
  `image` tinytext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Contenu de la table `news`
--

INSERT INTO `news` (`id`, `url`, `title`, `author`, `content`, `keywords`, `cat`, `date`, `modified`, `editor_id`, `views`, `publier`, `image`) VALUES
(5, 'ouverture-du-site-et-des-inscriptions', 'Ouverture du site et des inscriptions', 'Dufau', '<p>\r\n	Bonjour et bienvenue sur la nouvelle version de notre site internet !</p>\r\n<p>\r\n	Cette ann&eacute;e, le site a &eacute;t&eacute; enti&egrave;rement r&eacute;nov&eacute; et les inscriptions &agrave; l&#39;&eacute;dition 2011 sont d&eacute;sormais <strong>ouvertes</strong>. Vous pouvez vous inscrire via le bouton sur la droite de la page d&#39;accueil.</p>\r\n<p>\r\n	N&#39;h&eacute;sitez pas &agrave; nous rapporter des probl&egrave;mes &eacute;ventuels&nbsp;au travers de notre formulaire de contact.</p>\r\n<p>\r\n	Bonne visite.</p>\r\n', '', '16', '2011-06-11 13:37:34', '2011-09-02 13:29:28', 0, 0, 0, 'ouverturedusite.jpg'),
(6, 'pr-forum-le-13-octobre-2011', 'Pré-Forum le 13 octobre 2011', 'DeLichy', '<p>\r\n	Le <a href="http://www.est-horizon.com/preforum/">pr&eacute;-forum</a> de l&#39;&eacute;dition 2011 aura lieu <strong>le jeudi 13 octobre 2011</strong> &agrave; l&#39;Ecole des <strong>Mines de Nancy</strong>. Vous pouvez vous inscrire pour y participer via notre formulaire de <a href="http://www.est-horizon.com/contact/">contact</a>.</p>\r\n', '', '14,16', '2011-06-11 16:25:20', '2011-10-01 19:50:17', 0, 0, 0, ''),
(7, 'vos-outils', 'Vos outils', 'Blatecky', '<p>\r\n	Vous pouvez d&eacute;sormais utiliser votre nouvelle bo&icirc;te &agrave; outils.</p>\r\n<p>\r\n	Celle-ci pr&eacute;sente pour le moment 3 fonctions principales :</p>\r\n<ul>\r\n	<li>\r\n		L&#39;&eacute;dition de votre profil</li>\r\n	<li>\r\n		La saisie des donn&eacute;es n&eacute;cessaires &agrave; la constitution de votre page dans la brochure Visiteurs</li>\r\n	<li>\r\n		La r&eacute;servation de votre stand</li>\r\n</ul>\r\n<p>\r\n	Si vous rencontrez un quelconque probl&egrave;me lors de l&#39;utilisation du site Internet, contactez-nous.</p>\r\n<p>\r\n	Bonne visite</p>\r\n', 'profil', '14', '2011-07-20 12:32:12', '2011-08-14 18:23:15', 0, 0, 0, ''),
(12, 'programme-du-pr-forum', 'Programme du Pré-Forum', 'Dufau', '<p>\r\n	Le programme de la journ&eacute;e du 13 octobre a &eacute;t&eacute; mis en ligne dans la rubrique <a href="http://www.est-horizon.com/preforum/">Pr&eacute;-Forum</a>.</p>\r\n', '', '', '2011-10-02 10:48:29', '2011-10-02 10:48:29', 12, 0, 0, ''),
(13, 'programme-du-forum', 'Programme du Forum', 'Dufau', '<p>\r\n	Le programme de la journ&eacute;e du 24 novembre vient d&#39;&ecirc;tre publi&eacute;.</p>\r\n<p>\r\n	Vous trouverez dans la section <strong>Le Forum</strong> <a href="http://www.est-horizon.com/forum/exposants/">la liste compl&egrave;te des exposants</a>&nbsp;ainsi que le <a href="http://www.est-horizon.com/forum/programme/">programme d&eacute;taill&eacute;</a>.<br />\r\n	Retrouvez &eacute;galement les horaires des <u>navettes gratuites</u> sur la page <a href="http://www.est-horizon.com/acces/">acc&egrave;s</a>.</p>\r\n<p>\r\n	Nous vous attendons nombreux sur le salon !</p>\r\n', '', '', '2011-11-12 14:57:37', '2011-11-19 14:19:00', 0, 0, 0, '');

-- --------------------------------------------------------

--
-- Structure de la table `newsletters`
--

CREATE TABLE IF NOT EXISTS `newsletters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `de` varchar(100) NOT NULL,
  `objet` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `destinataires` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cc` tinytext NOT NULL,
  `cci` tinytext NOT NULL,
  `attachment` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=27 ;

--
-- Contenu de la table `newsletters`
--

INSERT INTO `newsletters` (`id`, `userid`, `de`, `objet`, `message`, `destinataires`, `date`, `cc`, `cci`, `attachment`) VALUES
(18, 26, '', '[Forum Est-Horizon] Réservez votre stand', '<p>\r\n	Bonjour,</p>\r\n<p>\r\n	Vous &ecirc;tes inscrit sur notre site www.est-horizon.com pour participer au Forum Est-Horizon le 24 novembre prochain, et nous vous en remercions.</p>\r\n<p>\r\n	Sauf erreur de notre part, vous n&#39;avez toujours pas r&eacute;serv&eacute; votre stand. Nous vous serions reconnaissants d&#39;effectuer cette t&acirc;che d&#39;ici le mercredi 26 octobre 2011. Vous pourrez le faire en vous connectant avec vos identifiant et mot de passe sur notre site internet <a href="http://www.est-horizon.com">http://www.est-horizon.com</a>, n&#39;h&eacute;sitez pas &agrave; nous contacter en cas de difficult&eacute; rencontr&eacute;e.</p>\r\n<p>\r\n	Bien cordialement,</p>\r\n<p>\r\n	Nicolas de Lichy<br />\r\n	Secr&eacute;taire G&eacute;n&eacute;ral du Forum Est-Horizon</p>\r\n', 'cgoffin@groupe-arcan.com; christianmaillard@yahoo.fr; contact@mso-loisirs.fr; dboudemdane@amaris.com; delesalle@siege.colas.fr; eeva.hjelt@eurovia.com; elisabeth.rucheton@eurogiciel.fr; elodie.gomes@banque-kolb.fr; elodie.roger@fr.ey.com; jfalliot@astek.fr; lisa.gaucherot@bplc.banquepopulaire.fr; marie.point@live.fr; paul.mevel@kurtsalmon.com; reve.roy@mc2i.fr; s.lerouge@pertuy-construction.fr; sarah.gledel@lu.pwc.com; sophie.bisserier@ifpen.fr; unicef.nancy@unicef.fr; ', '2011-10-20 15:59:06', 'johan.dufau@mines.inpl-nancy.fr', '', ''),
(19, 26, '', '[Forum Est-Horizon] Brochure Visiteurs', '<p>\r\n	Bonjour,</p>\r\n<p>\r\n	Votre entreprise est inscrite pour participer au Forum Est-Horizon le 24 Novembre prochain. A ce titre, elle b&eacute;n&eacute;ficie d&#39;une page de pr&eacute;sentation dans la brochure visiteurs. Or, sauf erreur de notre part, nous n&#39;avons pas re&ccedil;u la pr&eacute;sentation de votre entreprise.</p>\r\n<p>\r\n	Nous vous rappelons que nous avons besoin de cette information avant le vendredi 28 octobre.</p>\r\n<p>\r\n	Pour cela, connectez vous sur <a href="http://www.est-horizon.com">www.est-horizon.com</a> avec vos identifiant et mot de passe, puis allez dans l&#39;onglet &quot;Vos Outils&quot;, et enfin &quot;Brochure Visiteurs&quot;. Vous pourrez alors remplir la description de votre entreprise.<br />\r\n	Un visuel de votre page vous sera ensuite envoy&eacute; par mail pour confirmation.<br />\r\n	Si vous nous avez d&eacute;ja fait parvenir votre descriptif par mail, merci de ne pas tenir compte de ce courriel.</p>\r\n<p>\r\n	N&#39;h&eacute;sitez pas &agrave; nous contacter en cas de difficult&eacute; rencontr&eacute;e.</p>\r\n<p>\r\n	Bien cordialement,</p>\r\n<p>\r\n	Nicolas de Lichy<br />\r\n	Secr&eacute;taire G&eacute;n&eacute;ral</p>\r\n<p>\r\n	&nbsp;</p>\r\n', 'barbara.thomas@abylsen.com; cgoffin@groupe-arcan.com; christianmaillard@yahoo.fr; claire.oger@eulerhermes.com; communication@mines.inpl-nancy.fr; dboudemdane@amaris.com; elisabeth.rucheton@eurogiciel.fr; elodie.gomes@banque-kolb.fr; hawa.konkobo@atos.net; jacky.chef@promotech.fr; jfalliot@astek.fr; lisa.gaucherot@bplc.banquepopulaire.fr; marie.gabrielle.rouviere@heineken.fr; marie.point@live.fr; nicole.pascuttini@areva.com; pascale.cott@erdfdistribution.fr; robert.s@sfeir.lu; s.lerouge@pertuy-construction.fr; sarah.gledel@lu.pwc.com; sophie.bisserier@ifpen.fr; sylvie.marcadella@fivesgroup.com; ', '2011-10-25 13:43:03', '', '', ''),
(20, 26, '', '[Forum Est-Horizon] Informations Pratiques', '<p>\r\n	Bonjour,</p>\r\n<p>\r\n	Je vous prie de bien vouloir trouver ci-jointes les informations pratiques de l&#39;&eacute;dition 2011 du Forum Est-Horizon.</p>\r\n<p>\r\n	Merci &eacute;galement de nous envoyer les noms de vos collaborateurs qui seront pr&eacute;sents au Forum Est-Horizon afin de pouvoir pr&eacute;voir les badges.<br />\r\n	Nous aurions besoin de cette information avant le jeudi 17 Novembre.</p>\r\n<p>\r\n	Merci d&#39;avance.</p>\r\n<p>\r\n	Nous restons &agrave; votre disposition pour toute information compl&eacute;mentaire.</p>\r\n<p>\r\n	Bien Cordialement,<br />\r\n	Nicolas de Lichy<br />\r\n	Secr&eacute;taire G&eacute;n&eacute;ral</p>\r\n<p>\r\n	<br />\r\n	&nbsp;</p>\r\n<p>\r\n	&nbsp;</p>\r\n', 'angela.loge@intech-grp.com; anne.aubry@inria.fr; anthony.michel@marine.defense.gouv.fr; barbara.thomas@abylsen.com; carbustama@hotmail.com; catherine.mangin@creditmutuel.fr; cgoffin@groupe-arcan.com; christianmaillard@yahoo.fr; christine.simonetti@experis-france.fr; cir.metz@gendarmerie.interieur.gouv.fr; cirfa.nancy@terre-net.defense.gouv.fr; claire.oger@eulerhermes.com; communication@mines.inpl-nancy.fr; daniele.roussel@arcelormittal.com; david.gisler@elca.ch; dbaumann@vinci-energies.com; dboudemdane@amaris.com; delesalle@siege.colas.fr; eeva.hjelt@eurovia.com; elisabeth.rucheton@eurogiciel.fr; elodie.gomes@banque-kolb.fr; elodie.roger@fr.ey.com; gallet@essec.fr; hawa.konkobo@atos.net; jacky.chef@promotech.fr; jfalliot@astek.fr; jpaul.schoeser@pole-emploi.fr; latifa.varnerot@saint-gobain.com; lisa.gaucherot@bplc.banquepopulaire.fr; magali.misbah@bnpparibas.com; marie.gabrielle.rouviere@heineken.fr; marie.point@live.fr; myriam.rousseau@logica.com; nicole.pascuttini@areva.com; pamela.dutertre@accenture.com; pascale.cott@erdfdistribution.fr; robert.s@sfeir.lu; s.lerouge@pertuy-construction.fr; sandrine.nourry@hilti.com; sarah.gledel@lu.pwc.com; sophie.bisserier@ifpen.fr; sophie.cannizzo@mazars.fr; sophie.delecroix@eiffage.com; sylvie.marcadella@fivesgroup.com; thomas.brun@capgemini.com; vfoutel@technip.com; virginie.zanin@schneider-electric.com; zoe.pataud@edf.fr; ; ', '2011-10-27 12:39:37', '', '', '/homez.424/esthoriz/www/upload/newsletters/Informations_pratiques_FEH_2011_vd.pdf'),
(21, 121, '', 'Rappel : réservez votre stand', '<p>\r\n	Bonjour,</p>\r\n<p>\r\n	Nous vous rappelons qu&#39;il vous faut maintenant r&eacute;server votre stand au Forum Est-Horizon <u>avant le 1er novembre</u> via votre espace exposant sur notre site internet &nbsp;: www.est-horizon.com.</p>\r\n<p>\r\n	Nous vous remercions de votre participation &agrave; cet &eacute;v&egrave;nement et restons bien-s&ucirc;r &agrave; votre disposition pour toute demande d&#39;information.</p>\r\n<p>\r\n	Cordialement,</p>\r\n<p>\r\n	--</p>\r\n<p>\r\n	<span style="font-family: arial, helvetica, sans-serif; color: rgb(102, 102, 0); "><span style="color: rgb(0, 0, 0); ">L&#39;&eacute;quipe du</span></span><span style="font-family: arial, helvetica, sans-serif; color: rgb(102, 102, 0); font-weight: bold; ">&nbsp;Forum Est-Horizon</span></p>\r\n<p>\r\n	<br />\r\n	<span style="font-family: ''bookman old style'', ''new york'', times, serif; ">Forum Est-Horizon&nbsp;</span><br style="font-family: ''bookman old style'', ''new york'', times, serif; " />\r\n	<span style="font-family: ''bookman old style'', ''new york'', times, serif; ">Parc de Saurupt&nbsp;</span><br style="font-family: ''bookman old style'', ''new york'', times, serif; " />\r\n	<span style="font-family: ''bookman old style'', ''new york'', times, serif; ">54 042 Nancy Cedex&nbsp;</span><br style="font-family: ''bookman old style'', ''new york'', times, serif; " />\r\n	<span style="font-family: ''bookman old style'', ''new york'', times, serif; ">Tel :&nbsp;<span class="skype_pnh_container" dir="ltr" style="background-attachment: scroll !important; background-color: transparent !important; background-image: none !important; border-color: initial !important; border-left-color: rgb(0, 0, 0) !important; border-left-style: none !important; border-left-width: 0px !important; border-top-color: rgb(0, 0, 0) !important; border-top-style: none !important; border-top-width: 0px !important; border-right-color: rgb(0, 0, 0) !important; border-right-style: none !important; border-right-width: 0px !important; border-bottom-color: rgb(0, 0, 0) !important; border-bottom-style: none !important; border-bottom-width: 0px !important; border-collapse: separate !important; bottom: auto !important; clear: none !important; clip: auto !important; cursor: pointer !important; direction: ltr !important; display: inline !important; float: none !important; font-style: normal !important; left: auto !important; letter-spacing: 0px !important; list-style-image: none !important; list-style-position: outside !important; list-style-type: disc !important; overflow-x: hidden !important; overflow-y: hidden !important; padding-left: 0px !important; padding-top: 0px !important; padding-right: 0px !important; padding-bottom: 0px !important; page-break-after: auto !important; page-break-before: auto !important; page-break-inside: auto !important; position: static !important; right: auto !important; table-layout: auto !important; text-align: left !important; text-decoration: none !important; text-indent: 0px !important; text-transform: none !important; top: auto !important; white-space: nowrap !important; word-spacing: normal !important; z-index: 0 !important; color: rgb(73, 83, 90) !important; font-family: Tahoma, Arial, Helvetica, sans-serif !important; font-size: 11px !important; font-weight: bold !important; height: 14px !important; line-height: 14px !important; margin-left: 0px !important; margin-top: 0px !important; margin-right: 0px !important; margin-bottom: 0px !important; vertical-align: baseline !important; width: auto !important; background-position: 0px 0px !important; background-repeat: no-repeat no-repeat !important; " tabindex="-1">&nbsp;<span class="skype_pnh_highlighting_inactive_common" dir="ltr" skypeaction="skype_dropdown" style="background-attachment: scroll !important; background-color: transparent !important; background-image: none !important; border-color: initial !important; border-left-color: rgb(0, 0, 0) !important; border-left-style: none !important; border-left-width: 0px !important; border-top-color: rgb(0, 0, 0) !important; border-top-style: none !important; border-top-width: 0px !important; border-right-color: rgb(0, 0, 0) !important; border-right-style: none !important; border-right-width: 0px !important; border-bottom-color: rgb(0, 0, 0) !important; border-bottom-style: none !important; border-bottom-width: 0px !important; border-collapse: separate !important; bottom: auto !important; clear: none !important; clip: auto !important; cursor: pointer !important; direction: ltr !important; display: inline !important; float: none !important; font-style: normal !important; left: auto !important; letter-spacing: 0px !important; list-style-image: none !important; list-style-position: outside !important; list-style-type: disc !important; overflow-x: hidden !important; overflow-y: hidden !important; padding-left: 0px !important; padding-top: 0px !important; padding-right: 0px !important; padding-bottom: 0px !important; page-break-after: auto !important; page-break-before: auto !important; page-break-inside: auto !important; position: static !important; right: auto !important; table-layout: auto !important; text-align: left !important; text-decoration: none !important; text-indent: 0px !important; text-transform: none !important; top: auto !important; white-space: nowrap !important; word-spacing: normal !important; z-index: 0 !important; color: rgb(73, 83, 90) !important; font-family: Tahoma, Arial, Helvetica, sans-serif !important; font-size: 11px !important; font-weight: bold !important; height: 14px !important; line-height: 14px !important; margin-left: 0px !important; margin-top: 0px !important; margin-right: 0px !important; margin-bottom: 0px !important; vertical-align: baseline !important; width: auto !important; background-position: 0px 0px !important; background-repeat: no-repeat no-repeat !important; " title="Appeler ce numéro de téléphone en/au(x) France avec Skype&nbsp;: +33383584128"><span class="skype_pnh_left_span" skypeaction="skype_dropdown" style="background-attachment: scroll !important; background-color: transparent !important; background-image: url(chrome-extension://lifbcibllhkdhoafpjfnlhfpfgnpldfl/numbers_common_inactive_icon_set.gif) !important; border-color: initial !important; border-left-color: rgb(0, 0, 0) !important; border-left-style: none !important; border-left-width: 0px !important; border-top-color: rgb(0, 0, 0) !important; border-top-style: none !important; border-top-width: 0px !important; border-right-color: rgb(0, 0, 0) !important; border-right-style: none !important; border-right-width: 0px !important; border-bottom-color: rgb(0, 0, 0) !important; border-bottom-style: none !important; border-bottom-width: 0px !important; border-collapse: separate !important; bottom: auto !important; clear: none !important; clip: auto !important; cursor: pointer !important; direction: ltr !important; display: inline !important; float: none !important; font-style: normal !important; left: auto !important; letter-spacing: 0px !important; list-style-image: none !important; list-style-position: outside !important; list-style-type: disc !important; overflow-x: hidden !important; overflow-y: hidden !important; padding-left: 0px !important; padding-top: 0px !important; padding-right: 0px !important; padding-bottom: 0px !important; page-break-after: auto !important; page-break-before: auto !important; page-break-inside: auto !important; position: static !important; right: auto !important; table-layout: auto !important; text-align: left !important; text-decoration: none !important; text-indent: 0px !important; text-transform: none !important; top: auto !important; white-space: nowrap !important; word-spacing: normal !important; z-index: 0 !important; color: rgb(73, 83, 90) !important; font-family: Tahoma, Arial, Helvetica, sans-serif !important; font-size: 11px !important; font-weight: bold !important; height: 14px !important; line-height: 14px !important; margin-left: 0px !important; margin-top: 0px !important; margin-right: 0px !important; margin-bottom: 0px !important; vertical-align: baseline !important; width: 6px !important; background-position: 0px 0px !important; background-repeat: no-repeat no-repeat !important; " title="Actions Skype">&nbsp;&nbsp;</span><span class="skype_pnh_dropart_span" skypeaction="skype_dropdown" style="background-attachment: scroll !important; background-color: transparent !important; background-image: url(chrome-extension://lifbcibllhkdhoafpjfnlhfpfgnpldfl/numbers_common_inactive_icon_set.gif) !important; border-color: initial !important; border-left-color: rgb(0, 0, 0) !important; border-left-style: none !important; border-left-width: 0px !important; border-top-color: rgb(0, 0, 0) !important; border-top-style: none !important; border-top-width: 0px !important; border-right-color: rgb(0, 0, 0) !important; border-right-style: none !important; border-right-width: 0px !important; border-bottom-color: rgb(0, 0, 0) !important; border-bottom-style: none !important; border-bottom-width: 0px !important; border-collapse: separate !important; bottom: auto !important; clear: none !important; clip: auto !important; cursor: pointer !important; direction: ltr !important; display: inline !important; float: none !important; font-style: normal !important; left: auto !important; letter-spacing: 0px !important; list-style-image: none !important; list-style-position: outside !important; list-style-type: disc !important; overflow-x: hidden !important; overflow-y: hidden !important; padding-left: 0px !important; padding-top: 0px !important; padding-right: 0px !important; padding-bottom: 0px !important; page-break-after: auto !important; page-break-before: auto !important; page-break-inside: auto !important; position: static !important; right: auto !important; table-layout: auto !important; text-align: left !important; text-decoration: none !important; text-indent: 0px !important; text-transform: none !important; top: auto !important; white-space: nowrap !important; word-spacing: normal !important; z-index: 0 !important; color: rgb(73, 83, 90) !important; font-family: Tahoma, Arial, Helvetica, sans-serif !important; font-size: 11px !important; font-weight: bold !important; height: 14px !important; line-height: 14px !important; margin-left: 0px !important; margin-top: 0px !important; margin-right: 0px !important; margin-bottom: 0px !important; vertical-align: baseline !important; width: 27px !important; background-position: -11px 0px !important; background-repeat: no-repeat no-repeat !important; " title="Actions Skype"><span class="skype_pnh_dropart_flag_span" skypeaction="skype_dropdown" style="background-attachment: scroll !important; background-color: transparent !important; background-image: url(chrome-extension://lifbcibllhkdhoafpjfnlhfpfgnpldfl/flags.gif) !important; border-color: initial !important; border-left-color: rgb(0, 0, 0) !important; border-left-style: none !important; border-left-width: 0px !important; border-top-color: rgb(0, 0, 0) !important; border-top-style: none !important; border-top-width: 0px !important; border-right-color: rgb(0, 0, 0) !important; border-right-style: none !important; border-right-width: 0px !important; border-bottom-color: rgb(0, 0, 0) !important; border-bottom-style: none !important; border-bottom-width: 0px !important; border-collapse: separate !important; bottom: auto !important; clear: none !important; clip: auto !important; cursor: pointer !important; direction: ltr !important; display: inline !important; float: none !important; font-style: normal !important; left: auto !important; letter-spacing: 0px !important; list-style-image: none !important; list-style-position: outside !important; list-style-type: disc !important; overflow-x: hidden !important; overflow-y: hidden !important; padding-left: 0px !important; padding-top: 0px !important; padding-right: 0px !important; padding-bottom: 0px !important; page-break-after: auto !important; page-break-before: auto !important; page-break-inside: auto !important; position: static !important; right: auto !important; table-layout: auto !important; text-align: left !important; text-decoration: none !important; text-indent: 0px !important; text-transform: none !important; top: auto !important; white-space: nowrap !important; word-spacing: normal !important; z-index: 0 !important; color: rgb(73, 83, 90) !important; font-family: Tahoma, Arial, Helvetica, sans-serif !important; font-size: 11px !important; font-weight: bold !important; height: 14px !important; line-height: 14px !important; margin-left: 0px !important; margin-top: 0px !important; margin-right: 0px !important; margin-bottom: 0px !important; vertical-align: baseline !important; width: 18px !important; background-position: -1949px 1px !important; background-repeat: no-repeat no-repeat !important; ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;</span><span class="skype_pnh_textarea_span" style="background-attachment: scroll !important; background-color: transparent !important; background-image: url(chrome-extension://lifbcibllhkdhoafpjfnlhfpfgnpldfl/numbers_common_inactive_icon_set.gif) !important; border-color: initial !important; border-left-color: rgb(0, 0, 0) !important; border-left-style: none !important; border-left-width: 0px !important; border-top-color: rgb(0, 0, 0) !important; border-top-style: none !important; border-top-width: 0px !important; border-right-color: rgb(0, 0, 0) !important; border-right-style: none !important; border-right-width: 0px !important; border-bottom-color: rgb(0, 0, 0) !important; border-bottom-style: none !important; border-bottom-width: 0px !important; border-collapse: separate !important; bottom: auto !important; clear: none !important; clip: auto !important; cursor: pointer !important; direction: ltr !important; display: inline !important; float: none !important; font-style: normal !important; left: auto !important; letter-spacing: 0px !important; list-style-image: none !important; list-style-position: outside !important; list-style-type: disc !important; overflow-x: hidden !important; overflow-y: hidden !important; padding-left: 0px !important; padding-top: 0px !important; padding-right: 0px !important; padding-bottom: 0px !important; page-break-after: auto !important; page-break-before: auto !important; page-break-inside: auto !important; position: static !important; right: auto !important; table-layout: auto !important; text-align: left !important; text-decoration: none !important; text-indent: 0px !important; text-transform: none !important; top: auto !important; white-space: nowrap !important; word-spacing: normal !important; z-index: 0 !important; color: rgb(73, 83, 90) !important; font-family: Tahoma, Arial, Helvetica, sans-serif !important; font-size: 11px !important; font-weight: bold !important; height: 14px !important; line-height: 14px !important; margin-left: 0px !important; margin-top: 0px !important; margin-right: 0px !important; margin-bottom: 0px !important; vertical-align: baseline !important; width: auto !important; background-position: -125px 0px !important; background-repeat: no-repeat no-repeat !important; "><span class="skype_pnh_text_span" style="background-attachment: scroll !important; background-color: transparent !important; background-image: url(chrome-extension://lifbcibllhkdhoafpjfnlhfpfgnpldfl/numbers_common_inactive_icon_set.gif) !important; border-color: initial !important; border-left-color: rgb(0, 0, 0) !important; border-left-style: none !important; border-left-width: 0px !important; border-top-color: rgb(0, 0, 0) !important; border-top-style: none !important; border-top-width: 0px !important; border-right-color: rgb(0, 0, 0) !important; border-right-style: none !important; border-right-width: 0px !important; border-bottom-color: rgb(0, 0, 0) !important; border-bottom-style: none !important; border-bottom-width: 0px !important; border-collapse: separate !important; bottom: auto !important; clear: none !important; clip: auto !important; cursor: pointer !important; direction: ltr !important; display: inline !important; float: none !important; font-style: normal !important; left: auto !important; letter-spacing: 0px !important; list-style-image: none !important; list-style-position: outside !important; list-style-type: disc !important; overflow-x: hidden !important; overflow-y: hidden !important; padding-left: 5px !important; padding-top: 0px !important; padding-right: 0px !important; padding-bottom: 0px !important; page-break-after: auto !important; page-break-before: auto !important; page-break-inside: auto !important; position: static !important; right: auto !important; table-layout: auto !important; text-align: left !important; text-decoration: none !important; text-indent: 0px !important; text-transform: none !important; top: auto !important; white-space: nowrap !important; word-spacing: normal !important; z-index: 0 !important; color: rgb(73, 83, 90) !important; font-family: Tahoma, Arial, Helvetica, sans-serif !important; font-size: 11px !important; font-weight: bold !important; height: 14px !important; line-height: 14px !important; margin-left: 0px !important; margin-top: 0px !important; margin-right: 0px !important; margin-bottom: 0px !important; vertical-align: baseline !important; width: auto !important; background-position: -125px 0px !important; background-repeat: no-repeat no-repeat !important; ">03 83 58 41 28</span></span><span class="skype_pnh_right_span" style="background-attachment: scroll !important; background-color: transparent !important; background-image: url(chrome-extension://lifbcibllhkdhoafpjfnlhfpfgnpldfl/numbers_common_inactive_icon_set.gif) !important; border-color: initial !important; border-left-color: rgb(0, 0, 0) !important; border-left-style: none !important; border-left-width: 0px !important; border-top-color: rgb(0, 0, 0) !important; border-top-style: none !important; border-top-width: 0px !important; border-right-color: rgb(0, 0, 0) !important; border-right-style: none !important; border-right-width: 0px !important; border-bottom-color: rgb(0, 0, 0) !important; border-bottom-style: none !important; border-bottom-width: 0px !important; border-collapse: separate !important; bottom: auto !important; clear: none !important; clip: auto !important; cursor: pointer !important; direction: ltr !important; display: inline !important; float: none !important; font-style: normal !important; left: auto !important; letter-spacing: 0px !important; list-style-image: none !important; list-style-position: outside !important; list-style-type: disc !important; overflow-x: hidden !important; overflow-y: hidden !important; padding-left: 0px !important; padding-top: 0px !important; padding-right: 0px !important; padding-bottom: 0px !important; page-break-after: auto !important; page-break-before: auto !important; page-break-inside: auto !important; position: static !important; right: auto !important; table-layout: auto !important; text-align: left !important; text-decoration: none !important; text-indent: 0px !important; text-transform: none !important; top: auto !important; white-space: nowrap !important; word-spacing: normal !important; z-index: 0 !important; color: rgb(73, 83, 90) !important; font-family: Tahoma, Arial, Helvetica, sans-serif !important; font-size: 11px !important; font-weight: bold !important; height: 14px !important; line-height: 14px !important; margin-left: 0px !important; margin-top: 0px !important; margin-right: 0px !important; margin-bottom: 0px !important; vertical-align: baseline !important; width: 15px !important; background-position: -62px 0px !important; background-repeat: no-repeat no-repeat !important; ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span>&nbsp;</span>&nbsp;</span><br style="font-family: ''bookman old style'', ''new york'', times, serif; " />\r\n	<span style="font-family: ''bookman old style'', ''new york'', times, serif; ">Fax : 03 83 58 43 44&nbsp;</span><br />\r\n	<font face="Roman" size="2"><img alt="FEH-logo-fleche-haute.png" height="99" src="https://mail.google.com/mail/?ui=2&amp;ik=533df33891&amp;view=att&amp;th=13092de4f4940390&amp;attid=0.1&amp;disp=emb&amp;realattid=ii_13092dc52e98b6b4&amp;zw" title="FEH-logo-fleche-haute.png" width="112" /></font></p>\r\n', 'cgoffin@groupe-arcan.com; jacky.chef@promotech.fr; marie.point@live.fr; ; ', '2011-10-28 13:47:37', 'nima.dehlavi@mines.inpl-nancy.fr, Nicolas.De-Lichy@mines.inpl-nancy.fr, chris-eiji.mabrier@mines.inpl-nancy.fr', '', ''),
(22, 121, '', '[Forum Est-Horizon] Rappel : votre page de présentation', 'Bonjour,\r\n\r\nNous vous rappelons qu''il vous faut maintenant compléter votre fiche signalétique sur notre site internet www.est-horizon.com, via votre espace exposant.\r\n\r\nA partir des informations que vous aurez entrées, nous éditerons la page de présentation de votre entreprise qui apparaitra dans la brochure pour les visiteurs. Par la suite, nous vous enverrons un BAT pour validation.\r\n\r\nNous vous remercions de votre participation à cette 28ème édition du Forum Est-Horizon et restons bien sûr à votre disposition pour toute demande d''information.\r\n\r\nCordialement,', 'barbara.thomas@abylsen.com; cgoffin@groupe-arcan.com; christianmaillard@yahoo.fr; jacky.chef@promotech.fr; marie.point@live.fr; nicole.pascuttini@areva.com; pascale.cott@erdfdistribution.fr; sylvie.marcadella@fivesgroup.com; ', '2011-10-31 13:17:43', '', '', ''),
(25, 12, 'guillaume.dupuy@mines.inpl-nancy.fr', 'Règlement de la facture du Forum Est-Horizon', '<div>\r\n	<div>\r\n		<div>\r\n			<div>\r\n				<p>\r\n					Bonjour,</p>\r\n				Tout d&rsquo;abord, toute l&#39;&eacute;quipe du Forum Est-Horizon vous remercie d&#39;avoir particip&eacute; &agrave; notre forum jeudi 24 novembre 2011, et nous esp&eacute;rons qu&#39;il vous a plu.\r\n				<p>\r\n					Par ailleurs, nous n&rsquo;avons toujours pas re&ccedil;u le r&egrave;glement de la facture qui vous a &eacute;t&eacute; adress&eacute;e auparavant. Je vous rappelle que vous pouvez le faire soit par virement, soit par un ch&egrave;que envoy&eacute; au</p>\r\n				<p>\r\n					Forum Est-Horizon<br />\r\n					Parc de Saurupt<br />\r\n					54 042 Nancy Cedex.</p>\r\n				<p>\r\n					Dans le cas o&ugrave; vous souhaiteriez une nouvelle facture, nous pouvons vous la renvoyer par mail.</p>\r\n				<p>\r\n					Cordialement,</p>\r\n				<p>\r\n					Guillaume Dupuy,<br />\r\n					Tr&eacute;sorier du Forum Est-Horizon</p>\r\n				<p>\r\n					- -<br />\r\n					Forum Est-Horizon<br />\r\n					Parc de Saurupt<br />\r\n					54 042 Nancy Cedex<br />\r\n					T&eacute;l&eacute;phone personnel : 06 37 26 38 80<br />\r\n					Tel : 03 83 58 41 28<br />\r\n					Fax : 03 83 58 43 44</p>\r\n			</div>\r\n		</div>\r\n	</div>\r\n</div>\r\n', 'angela.loge@intech-grp.com; anthony.michel@marine.defense.gouv.fr; bertrand.malgras@gdfsuez.com; christianmaillard@yahoo.fr; cir.metz@gendarmerie.interieur.gouv.fr; communication@mines.inpl-nancy.fr; david.gisler@elca.ch; dbaumann@vinci-energies.com; eeva.hjelt@eurovia.com; elodie.gomes@banque-kolb.fr; elodie.roger@fr.ey.com; emmanuel.chassard@ensem.inpl-nancy.fr; gallet@essec.fr; jpaul.schoeser@pole-emploi.fr; marie.gabrielle.rouviere@heineken.fr; myriam.rousseau@logica.com; pascale.cott@erdfdistribution.fr; s.lerouge@pertuy-construction.fr; sarah.gledel@lu.pwc.com; sophie.bisserier@ifpen.fr; sophie.cannizzo@mazars.fr; sophie.delecroix@eiffage.com; sylvie.marcadella@fivesgroup.com; vfoutel@technip.com; virginie.zanin@schneider-electric.com; zoe.pataud@edf.fr; remi.boeglin@creditmutuel.fr; z.ba@esc-toulouse.fr; catherine.jungmann@icn-groupe.fr; ', '2011-12-05 12:01:42', '', '', ''),
(26, 26, 'forum@mines.inpl-nancy.fr', '[Forum Est-Horizon] Enquête date prochaine edition', '<p>\r\n	Bonjour,</p>\r\n<p>\r\n	Dans le but de satsifaire le maximum d&#39; exposants du Forum Est-Horizon, nous aimerions avoir votre avis sur la date du prochain &eacute;v&eacute;nement.</p>\r\n<p>\r\n	Pourriez vous r&eacute;pondre &agrave; ce mail en pr&eacute;cisant si vous &ecirc;tes favorable pour modifier la date du Forum Est-Horizon, habituellement pr&eacute;vu aux alentours du 24 novembre.</p>\r\n<p>\r\n	Si oui, pr&eacute;f&eacute;rez vous qu&#39;il ait lieu plus t&ocirc;t dans l&#39;ann&eacute;e (octobre par exemple), ou plus tard.</p>\r\n<p>\r\n	Vous trouverez en pi&egrave;ce jointe l&#39;enqu&ecirc;te de satisfaction de l&#39;&eacute;dition de cette ann&eacute;e si vous souhaitez nous donner un avis sur les autres aspects de l&#39;&eacute;dition du 24 novembre 2011 (environ 5 minutes pour la remplir), si vous ne l&#39;avez pas d&eacute;j&agrave; remplie.</p>\r\n<p>\r\n	Merci d&#39;avance</p>\r\n<p>\r\n	Cordialement,</p>\r\n<p>\r\n	Nicolas de Lichy<br />\r\n	Secr&eacute;taire G&eacute;n&eacute;ral</p>\r\n', 'angela.loge@intech-grp.com; anne.aubry@inria.fr; anthony.michel@marine.defense.gouv.fr; barbara.thomas@abylsen.com; bertrand.malgras@gdfsuez.com; cgoffin@groupe-arcan.com; christianmaillard@yahoo.fr; cir.metz@gendarmerie.interieur.gouv.fr; cjadeline@gmail.com; claire.oger@eulerhermes.com; communication@mines.inpl-nancy.fr; daniele.roussel@arcelormittal.com; david.gisler@elca.ch; dbaumann@vinci-energies.com; dboudemdane@amaris.com; eeva.hjelt@eurovia.com; elodie.roger@fr.ey.com; emmanuel.chassard@ensem.inpl-nancy.fr; hawa.konkobo@atos.net; jacky.chef@promotech.fr; jpaul.schoeser@pole-emploi.fr; latifa.varnerot@saint-gobain.com; magali.misbah@bnpparibas.com; marie.point@live.fr; nicole.pascuttini@areva.com; pascale.cott@erdfdistribution.fr; robert.s@sfeir.lu; sandrine.nourry@hilti.com; sarah.gledel@lu.pwc.com; sophie.bisserier@ifpen.fr; sophie.cannizzo@mazars.fr; sophie.delecroix@eiffage.com; sylvie.marcadella@fivesgroup.com; thomas.brun@capgemini.com; vfoutel@technip.com; virginie.zanin@schneider-electric.com; zoe.pataud@edf.fr; nima.dehlavi@mines.inpl-nancy.fr; ', '2011-12-07 09:35:51', '', '', '/homez.424/esthoriz/www/upload/newsletters/questionnaire_exposants_2011_mail.pdf');

-- --------------------------------------------------------

--
-- Structure de la table `news_cats`
--

CREATE TABLE IF NOT EXISTS `news_cats` (
  `cid` tinyint(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext CHARACTER SET latin1 NOT NULL,
  `shortname` tinytext NOT NULL,
  `parent` tinyint(4) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

--
-- Contenu de la table `news_cats`
--

INSERT INTO `news_cats` (`cid`, `name`, `shortname`, `parent`) VALUES
(14, 'Espace privé', 'private', 0),
(16, 'Accueil', 'home', 0);

-- --------------------------------------------------------

--
-- Structure de la table `news_cats_relations`
--

CREATE TABLE IF NOT EXISTS `news_cats_relations` (
  `news_id` mediumint(9) NOT NULL,
  `cat_id` tinyint(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `news_cats_relations`
--

INSERT INTO `news_cats_relations` (`news_id`, `cat_id`) VALUES
(6, 14),
(6, 16),
(7, 14),
(5, 14),
(5, 16),
(12, 16),
(12, 14),
(13, 14),
(13, 16);

-- --------------------------------------------------------

--
-- Structure de la table `pages`
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

--
-- Contenu de la table `pages`
--

INSERT INTO `pages` (`id`, `url`, `title`, `author`, `content`, `keywords`, `creation_time`, `edit_time`) VALUES
(18, 'plan-site', 'Plan du site', 'Dufau', '<ul>\r\n	<li>\r\n		Participer\r\n		<ul>\r\n			<li>\r\n				<a href="http://www.est-horizon.com/participer/exposer/">Exposer</a></li>\r\n			<li>\r\n				<a href="http://www.est-horizon.com/participer/visiter/">Visiter</a></li>\r\n			<li>\r\n				<a href="http://www.est-horizon.com/entreprise/inscription/">S&#39;inscrire</a></li>\r\n			<li>\r\n				<a href="http://www.est-horizon.com/user/connexion/">Se connecter</a></li>\r\n		</ul>\r\n	</li>\r\n	<li>\r\n		<a href="http://www.est-horizon.com/forum/programme/">Le Forum</a>\r\n		<ul>\r\n			<li>\r\n				<a href="http://www.est-horizon.com/forum/programme/">Programme</a></li>\r\n			<li>\r\n				<a href="http://www.est-horizon.com/forum/exposants/">Les exposants</a></li>\r\n			<li>\r\n				<a href="http://www.est-horizon.com/forum/ecoles/">Les &eacute;coles</a></li>\r\n			<li>\r\n				<a href="http://www.est-horizon.com/preforum/">Le Pr&eacute;-Forum</a></li>\r\n		</ul>\r\n	</li>\r\n	<li>\r\n		<a href="http://www.est-horizon.com/a-propos/association/">A propos</a>\r\n		<ul>\r\n			<li>\r\n				<a href="http://www.est-horizon.com/a-propos/association/">L&#39;association</a></li>\r\n			<li>\r\n				<a href="http://www.est-horizon.com/a-propos/medias/">M&eacute;dias</a></li>\r\n		</ul>\r\n	</li>\r\n	<li>\r\n		<a href="http://www.est-horizon.com/acces/">Acc&egrave;s</a></li>\r\n	<li>\r\n		<a href="http://www.est-horizon.com/contact/">Contact</a></li>\r\n</ul>\r\n', 'plan site, site map', 1316870754, 1316870754);

-- --------------------------------------------------------

--
-- Structure de la table `shoutbox`
--

CREATE TABLE IF NOT EXISTS `shoutbox` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `nickname` varchar(50) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `message` text NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=32 ;

--
-- Contenu de la table `shoutbox`
--

INSERT INTO `shoutbox` (`id`, `userid`, `nickname`, `date`, `message`) VALUES
(1, 12, 'Dufau', '2011-09-05 16:11:56', 'Ouverture du tableau de bord + shoutbox !'),
(2, 12, 'Dufau', '2011-09-10 11:05:09', '@Jean : il y avait effectivement un bug dans les étapes phoning, merci de me confirmer que c''est rectifié ;)'),
(3, 125, 'JM', '2011-09-12 10:12:37', 'Kikoulol'),
(4, 58, 'Slavik', '2011-09-12 14:06:53', '@Johan : pour mes entreprises le problème est rectifié.'),
(5, 25, 'Blatecky', '2011-09-13 08:06:27', 'Sfeir s''est réinscrite (contact en attente) alors qu''elle a déjà reservé son stand ^^'),
(6, 58, 'Slavik', '2011-09-13 09:57:46', '@julien : j''ai contacté sfeir ! Elle n''était pas sure d''être inscrite donc elle s''eest réinscrit ! tu peux supprimer une des inscription\r\n'),
(7, 12, 'Dufau', '2011-09-17 12:02:38', '@Jean: tu as répondu à Amadeus ou dois-je m''en charger ? Biz'),
(8, 58, 'Slavik', '2011-09-19 10:45:38', '@Johan : Non peux-tu t''en charger ?\r\n'),
(9, 121, 'Mabrier', '2011-09-22 09:56:13', 'zizi'),
(10, 12, 'Dufau', '2011-09-22 10:51:41', 'caca\r\n'),
(11, 26, 'De_Lichy', '2011-09-22 11:52:00', 'prout'),
(12, 72, 'Bouffier', '2011-09-22 12:04:08', 'Chris a un petit zizi'),
(13, 121, 'Mabrier', '2011-09-22 12:04:22', 'SEXE SEXE SEXE SEXE SEXE SEXE SEXE SEXE SEXE SEXE SEXE'),
(14, 26, 'De_Lichy', '2011-09-22 12:04:32', 'oui'),
(15, 27, 'Vanhove', '2011-09-22 12:05:11', 'Et les boloss\r\n'),
(16, 26, 'De_Lichy', '2011-09-22 12:49:45', 'et la Gouzou'),
(17, 25, 'Blatecky', '2011-09-23 10:59:04', 'lol\r\n'),
(18, 27, 'Vanhove', '2011-09-23 11:00:12', 'bizoux bizoux\r\n'),
(19, 72, 'Bouffier', '2011-09-23 11:06:27', 'tg vanhove !!!!'),
(20, 27, 'Vanhove', '2011-09-23 20:13:09', 'mr le boloss, on se calme\r\n'),
(21, 58, 'Slavik', '2011-09-24 10:40:05', 'pffff'),
(22, 120, 'Dupuy', '2011-09-25 16:08:00', 'j''aime le caca\r\n'),
(23, 58, 'Slavik', '2011-09-29 09:25:14', '@Johan/Julien : Flexi france s''est réinscrite !'),
(24, 26, 'De_Lichy', '2011-09-30 11:34:23', 'il me faudrait la facture pour Inria, avec 15% de réduction sur le total'),
(25, 26, 'De_Lichy', '2011-10-01 10:17:30', 'en fait c''est bon j''ai vu comment il fallait faire ; )'),
(26, 26, 'De_Lichy', '2011-10-04 14:50:52', 'test'),
(27, 12, 'Dufau', '2011-10-23 16:31:34', 'Avant de les valider, j''aimerais savoir pourquoi Kolb et IFP School se sont réinscrites ?'),
(29, 12, 'Dufau', '2011-11-01 17:33:27', '@Nima: c''est une blague le prez de l''UdL étudiant ? ^^'),
(30, 27, 'Vanhove', '2011-11-02 10:38:41', '@Johan:PRES = Pôle de recherche et d''enseignement supérieur (google is ur friend)'),
(31, 26, 'De_Lichy', '2011-11-02 15:43:26', 'I''m a (°qp°)');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(100) CHARACTER SET latin1 NOT NULL,
  `password` varchar(50) NOT NULL,
  `confirm` int(11) NOT NULL DEFAULT '0',
  `email` varchar(100) CHARACTER SET latin1 NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `groupe` int(4) NOT NULL,
  `access` text CHARACTER SET latin1 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(50) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=241 ;

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`id`, `nickname`, `password`, `confirm`, `email`, `firstname`, `lastname`, `groupe`, `access`, `date`, `last_activity`, `ip`) VALUES
(12, 'Dufau', '3e836920e79bd91969f7ed88dc3d796b0d06ce7f', 0, 'johan.dufau@mines.inpl-nancy.fr', 'Johan', 'Dufau', 5, 'all', '2008-01-20 15:33:18', '2011-12-28 20:23:10', ''),
(126, 'Ghadami', '71762e0368a2625c2efdccfac9033c3c493c8794', 0, 'armand.ghadami@mines.inpl-nancy.fr', '', '', 5, 'entreprise|0', '2011-09-01 17:03:51', '0000-00-00 00:00:00', ''),
(25, 'Blatecky', '5c682c2d1ec4073e277f9ba9f4bdf07e5794dabe', 0, 'julien.blatecky@mines.inpl-nancy.fr', '', '', 5, 'all', '2011-04-28 20:23:29', '2011-11-29 18:52:46', ''),
(26, 'De_Lichy', '738733a9edc4002f7f870358baf26e1336fcbe84', 0, 'nicolas.delichy@mines.inpl-nancy.fr', '', '', 5, 'all', '2011-04-28 20:36:08', '2011-12-07 09:13:44', ''),
(27, 'Vanhove', 'cca5020761d41326286f794165f27b580b8f15ee', 0, 'ludovic.vanhove@mines.inpl-nancy.fr', '', '', 5, 'all', '2011-04-28 21:03:16', '2011-12-25 11:44:28', ''),
(51, 'BDE_ensem', 'abef802e9474db5394c2ccd49bce67b7b0b5e427', 0, '', '', '', 6, 'fdh|0', '2011-05-16 12:46:05', '2011-11-01 21:23:08', ''),
(50, 'BDE_ensic', 'e2cfd16d1e58df0ca255aac1e5c3a24557a0d133', 0, '', '', '', 6, 'fdh|0', '2011-05-16 12:45:22', '0000-00-00 00:00:00', ''),
(49, 'BDE_geol', 'b7bad65c75eca9b6bab30f9653cf85ae50aecca2', 0, 'camille.fleuriault@ensg.inpl-nancy.fr', '', '', 6, 'fdh', '2011-05-16 12:44:44', '0000-00-00 00:00:00', ''),
(44, 'Trossat', 'eee8119d242b377e451d4aaeac7dc343e66b8311', 0, 'emeric.trossat@mines.inpl-nancy.fr', '', '', 5, 'cvtheque|0,entreprise|0,logistic|0', '2011-05-11 12:48:46', '2011-12-05 16:18:52', ''),
(52, 'BDE_esstin', 'c5d5900f2ee586a23f9b0cd31241f10136586f4b', 0, 'remi.gau@esstin.uhp-nancy.fr', '', '', 6, 'fdh', '2011-05-16 12:47:01', '0000-00-00 00:00:00', ''),
(53, 'BDE_icn', 'dd555b4ae2124377457326954f568226259759c0', 0, 'benedicte.lethielleux@myicn.fr', '', '', 6, 'fdh', '2011-05-16 12:48:04', '0000-00-00 00:00:00', ''),
(54, 'BDE_sciencepo', '289c93429484000e1fd620b6291c538932e5f794', 0, 'gui.denis@sciences-po.org', '', '', 6, 'fdh', '2011-05-16 12:49:20', '0000-00-00 00:00:00', ''),
(55, 'BDE_sfemmes', 'd0d5a7a89f3060b353f292eb62989a0ca92b0841', 0, 'gimie2125@hotmail.fr', '', '', 6, 'fdh', '2011-05-16 12:50:10', '0000-00-00 00:00:00', ''),
(56, 'BDE_kine', '2a11b927f603920e1f19a46463b0fddda50fef65', 0, 'minis._bizz9@hotmail.fr', '', '', 6, 'fdh', '2011-05-16 12:51:21', '0000-00-00 00:00:00', ''),
(57, 'BDE_fif', '6278d1b3fe3c300267e260f269fb6c1a6b395e12', 0, 'soraya.bennar@agroParisTech.fr', '', '', 6, 'fdh', '2011-05-16 12:52:18', '0000-00-00 00:00:00', ''),
(58, 'Slavik', '5e9bd3296e5bc1d29cca59a23c3cf87867c75f9a', 0, 'jean.slavik@mines.inpl-nancy.fr', '', '', 5, 'all', '2011-05-16 16:12:07', '2011-11-21 16:19:09', ''),
(59, 'Even', 'ab782d1121ec3da473654c3ffab61dfaf1a99cfe', 0, 'gregoire.even@mines.inpl-nancy.fr', '', '', 5, 'all', '2011-05-16 16:14:17', '2011-11-05 18:18:52', ''),
(62, 'FDH_forum', '1ddc6f9f55de2483f7dc4aea8bff3b5fc8d74e4b', 0, '', '', '', 0, 'fdh|0', '2011-05-19 17:52:55', '2011-11-02 18:47:27', ''),
(64, 'BDE_gsi', 'ce7dbd1a1ad101b5d9eac6d91c1c1f841d1e1631', 0, '', '', '', 6, 'fdh|0', '2011-05-23 15:14:00', '2011-12-13 21:03:33', ''),
(67, 'BDE_esial', '90a01446bfc24955132c09137b6e4a3a411c124e', 0, '', '', '', 6, 'fdh|0', '2011-05-23 16:28:59', '0000-00-00 00:00:00', ''),
(68, 'BDE_archi', 'f1287221d14bd0be12b0debcef84b426c11ce2e2', 0, '', '', '', 6, 'fdh|0', '2011-05-23 16:33:56', '0000-00-00 00:00:00', ''),
(69, 'BDE_ensaia', '397a2784ffcc1fa870646ec4b6be0e0c8cf750a8', 0, '', '', '', 6, 'fdh|0', '2011-05-26 10:50:36', '0000-00-00 00:00:00', ''),
(70, 'BDE_arts', 'dd9f509166ebdcb812967077402c472d0a796ca2', 0, '', '', '', 6, 'fdh|0', '2011-05-26 10:51:36', '0000-00-00 00:00:00', ''),
(71, 'BDE_eeigm', 'cce5f55e02bede4ec5f1c46b26484421757ac7c8', 0, '', '', '', 6, 'fdh|0', '2011-05-26 10:51:58', '0000-00-00 00:00:00', ''),
(72, 'Bouffier', '03b9a196ec05c3053deab8021200d2e9abbb315e', 0, 'marc.bouffier@mines.inpl-nancy.fr', '', '', 5, 'entreprise|1,logistic|0', '2011-05-26 10:52:47', '2011-11-30 16:13:14', ''),
(80, 'gallet@essec.fr', 'fd89c872d52e4d85006847a531fad0f8b2bae6e0', 0, 'gallet@essec.fr', '', '', 4, '', '2011-06-16 14:02:52', '2011-11-21 09:15:38', ''),
(77, 'Dehlavi', '3a4a16b0935e02199a9d82c9d15c33ffcbe89bc0', 0, 'nima.dehlavi@mines.inpl-nancy.fr', '', '', 5, 'all', '2011-06-09 08:21:07', '2011-11-28 17:51:54', ''),
(89, 'elodie.roger@fr.ey.com', '732d18e71a6837417bb852aed581cf2f89d58dbe', 0, 'elodie.roger@fr.ey.com', '', '', 4, '', '2011-07-11 12:24:53', '2011-11-08 09:57:22', ''),
(78, 'lgasser@deloitte.lu', '85ad80d4a62e8285b796d4c89dd95b8d3594275b', 1, 'lgasser@deloitte.lu', '', '', 4, '', '2011-06-10 14:31:32', '0000-00-00 00:00:00', ''),
(81, 'daniele.roussel@arcelormittal.com', 'd11a74d1662d72b280bd2e12591fc2bdf855ae22', 0, 'daniele.roussel@arcelormittal.com', '', '', 4, '', '2011-06-22 12:56:50', '2011-11-23 09:55:11', ''),
(82, 'nicole.pascuttini@areva.com', 'cd80b476b723d440d767385de69210cade9792cd', 0, 'nicole.pascuttini@areva.com', '', '', 4, '', '2011-06-23 08:05:53', '2011-10-11 15:07:29', ''),
(112, 'christianmaillard@yahoo.fr', 'b28de808c693ae9e23be5da977c1c9a6d22eb12b', 0, 'christianmaillard@yahoo.fr', '', '', 4, '', '2011-08-17 13:45:55', '2011-10-24 16:12:47', ''),
(84, 'sophie.delecroix@eiffage.com', '6975c5b7182bf34d2311abe9263e15ace1a30a92', 0, 'sophie.delecroix@eiffage.com', '', '', 4, '', '2011-06-30 12:29:00', '2011-11-18 17:52:58', ''),
(86, 'sophie.cannizzo@mazars.fr', 'a6697e7e326559e9c3482d7d9981c98499e4ab63', 0, 'sophie.cannizzo@mazars.fr', '', '', 4, '', '2011-07-05 13:00:39', '2011-10-27 11:48:48', ''),
(87, 'zoe.pataud@edf.fr', '1013b67428b2f5c67b1cd11d79ae1014ae662191', 0, 'zoe.pataud@edf.fr', '', '', 4, '', '2011-07-05 13:31:28', '2011-11-02 10:54:30', ''),
(119, 'Maier', '0a53f30e07b646e8c38829617bbf798510445b60', 0, 'mathias.maier@mines.inpl-nancy.fr', '', '', 5, 'entreprise|0,newsletter|0', '2011-08-29 18:08:19', '2011-11-21 15:12:05', ''),
(90, 'marie.point@live.fr', 'da9befbde94cfa387de9efc0b1a9b4e0fd8d2308', 0, 'marie.point@live.fr', '', '', 4, '', '2011-07-12 13:11:15', '0000-00-00 00:00:00', ''),
(91, 'hawa.konkobo@atos.net', '965aa0ca8755c34fd7d293aadb0a5b200a52fa35', 0, 'hawa.konkobo@atos.net', '', '', 4, '', '2011-07-13 14:40:39', '2011-11-23 16:11:29', ''),
(92, 'paul.mevel@kurtsalmon.com', 'c9607443d2633d1401800cc860ac244ea4d99f8c', 0, 'paul.mevel@kurtsalmon.com', '', '', 4, '', '2011-07-19 12:26:37', '0000-00-00 00:00:00', ''),
(93, 'thomas.brun@capgemini.com', '9315eab5a7edf509f989612953245216f882ed42', 0, 'thomas.brun@capgemini.com', '', '', 4, '', '2011-07-21 09:41:55', '2011-10-27 09:08:52', ''),
(94, 'virginie.zanin@schneider-electric.com', '3b2638ca934ce71085f3cc4ec2b5d660d209ae3d', 0, 'virginie.zanin@schneider-electric.com', '', '', 4, '', '2011-07-26 09:39:27', '2011-11-22 17:44:41', ''),
(95, 'cirfa.nancy@terre-net.defense.gouv.fr', 'e7208a76f99f5c30d06329d475adbf7a1a9602e0', 0, 'cirfa.nancy@terre-net.defense.gouv.fr', '', '', 4, '', '2011-07-27 07:57:35', '2011-09-28 14:12:37', ''),
(96, 'david.gisler@elca.ch', 'a0bc616380dc76eb8e65e3b01d228ad967a835bf', 0, 'david.gisler@elca.ch', '', '', 4, '', '2011-07-27 12:48:31', '2011-11-22 11:56:05', ''),
(97, 'delesalle@siege.colas.fr', '35b5140e53a98e748ce932a2ff82480cd98ec88f', 0, 'delesalle@siege.colas.fr', '', '', 4, '', '2011-07-29 14:28:42', '2011-11-18 15:50:31', ''),
(98, 'morgane.mariage@barilla.com', '5df56265098ffb925034c4f6e5f172c8a7bcce7d', 0, 'morgane.mariage@barilla.com', '', '', 4, '', '2011-07-29 16:42:19', '0000-00-00 00:00:00', ''),
(99, 'christine.simonetti@experis-france.fr', 'e4cca22343b63b1c3a3bc0e5651427c07b1584b7', 0, 'christine.simonetti@experis-france.fr', '', '', 4, '', '2011-08-01 15:09:57', '2011-11-23 09:03:57', ''),
(120, 'Dupuy', '1a5c47667a908f7c0d20e3436d908ba23a0a2332', 0, 'guillaume.dupuy@mines.inpl-nancy.fr', '', '', 5, 'all', '2011-08-29 19:23:57', '2011-12-06 11:55:34', ''),
(113, 'latifa.varnerot@saint-gobain.com', 'a0ce53f45cf4bd1cabdad0073ad3fc2bc29f6b4f', 0, 'Latifa.Varnerot@saint-gobain.com', '', '', 4, '', '2011-08-18 07:01:55', '2011-10-13 11:45:59', ''),
(103, 'magali.misbah@bnpparibas.com', 'ccd616740c1493eb2f93de01b70d4253e9dda595', 0, 'magali.misbah@bnpparibas.com', '', '', 4, '', '2011-08-09 11:03:00', '2011-10-18 10:21:19', ''),
(88, 'sylvie.marcadella@fivesgroup.com', 'd24ee692dc5f716e9db68bbc6b3467506cb16905', 0, 'sylvie.marcadella@fivesgroup.com', '', '', 4, '', '2011-07-06 07:41:28', '2011-11-23 12:28:02', ''),
(105, 'pegi@amazon.fr', 'ea669758f23242a3bf4730fd0dc2724a813bd1c0', 0, 'pegi@amazon.fr', '', '', 4, '', '2011-08-09 14:23:42', '2011-10-06 13:48:06', ''),
(106, 'sophie.bisserier@ifpen.fr', '5f50443bfe76f7279a8e0f2f0a98975cdbff38e9', 0, 'sophie.bisserier@ifpen.fr', '', '', 4, '', '2011-08-09 15:21:45', '2011-10-28 16:17:01', ''),
(107, 'eeva.hjelt@eurovia.com', '7efc096def29f226158d3e93a3460a2d39fd6304', 0, 'eeva.hjelt@eurovia.com', '', '', 4, '', '2011-08-10 12:56:48', '2011-11-23 09:35:18', ''),
(111, 'contact@mso-loisirs.fr', 'cda742d32da5f77685a2f329372fd1805421a68b', 0, 'contact@mso-loisirs.fr', '', '', 4, '', '2011-08-15 08:36:01', '0000-00-00 00:00:00', ''),
(139, 'cir.metz@gendarmerie.interieur.gouv.fr', '4aab3587e9e8b6a93ff33e8f5005abda64f9b842', 0, 'cir.metz@gendarmerie.interieur.gouv.fr', '', '', 4, '', '2011-09-21 14:56:54', '2011-09-22 06:50:52', ''),
(117, 'alexandra.seneque@accenture.com', '29525b11b8d8f7fcc694bdf70efa3efb89b92e93', 0, 'alexandra.seneque@accenture.com', '', '', 4, '', '2011-08-26 09:09:39', '2011-11-24 09:22:18', ''),
(128, 'anne.aubry@inria.fr', 'a533ba53d048ca435bd5fb5fa78e1ef7b01a8acd', 0, 'anne.aubry@inria.fr', '', '', 4, '', '2011-09-05 13:50:32', '2011-11-08 07:34:20', ''),
(121, 'Mabrier', '16eacdbc4f8b1de751628d7909b7f3b213c4243c', 0, 'chris-eiji.mabrier@mines.inpl-nancy.fr', '', '', 5, 'all', '2011-08-29 19:24:50', '2011-12-25 11:02:09', ''),
(122, 'Deny', '53577bb5df0ee9b6376e87f4896b6957a25d7a43', 0, 'angelique.deny@mines.inpl-nancy.fr', '', '', 5, 'entreprise|0', '2011-08-29 19:25:23', '2011-09-22 09:53:31', ''),
(123, 'Chevalier', '1645ba4a3d538cd4b62b15b9780a5fda03f8fa73', 0, 'guillaume.chevalier@mines.inpl-nancy.fr', '', '', 5, 'entreprise|0', '2011-08-29 19:26:25', '0000-00-00 00:00:00', ''),
(125, 'JM', 'f05d4bbb34788e8701e07cd3d21b98db007260f5', 0, 'jean-marie.john-mathews@mines.inpl-nancy.fr', '', '', 5, 'entreprise|0', '2011-08-29 19:27:02', '2011-11-07 21:57:24', ''),
(129, 's.lerouge@pertuy-construction.fr', '32ad53a88ec6a11d30de784ffbc12590fd8bc9d9', 0, 's.lerouge@pertuy-construction.fr', '', '', 4, '', '2011-09-07 16:02:23', '2011-10-20 11:13:08', ''),
(134, 'anthony.michel@marine.defense.gouv.fr', '1fd1b4516473c36c8fb30bbf7c4490fc20419a10', 0, 'anthony.michel@marine.defense.gouv.fr', '', '', 4, '', '2011-09-13 13:44:11', '0000-00-00 00:00:00', ''),
(131, 'marie.gabrielle.rouviere@heineken.fr', '2941a46407dfc67480e1b7cc3967720fb09c94db', 0, 'marie.gabrielle.rouviere@heineken.fr', '', '', 4, '', '2011-09-08 13:32:23', '2011-10-27 10:32:32', ''),
(132, 'robert.s@sfeir.lu', '2892e988414f72accc40b307ef1c8da85daa5284', 0, 'robert.s@sfeir.lu', '', '', 4, '', '2011-09-12 08:57:16', '2011-10-26 13:05:47', ''),
(133, 'jpaul.schoeser@pole-emploi.fr', '3efbf921bdde6aebde394f6c8dd41e356295807b', 0, 'jpaul.schoeser@pole-emploi.fr', '', '', 4, '', '2011-09-13 06:33:12', '2011-11-22 16:36:01', ''),
(135, 'lisa.gaucherot@bplc.banquepopulaire.fr', 'd123f0ea4ef671a7a51b2f2fb43294bde14a6d46', 0, 'lisa.gaucherot@bplc.banquepopulaire.fr', '', '', 4, '', '2011-09-15 07:12:59', '2011-11-14 10:04:31', ''),
(136, 'ICN', '95dbfeb54a6d4254ff81eb87e9f7aba789198fa1', 0, 'severine.reymann@icn-groupe.fr', '', '', 8, 'entreprise|2', '2011-09-16 14:50:56', '2011-12-14 10:39:35', ''),
(137, 'dbaumann@vinci-energies.com', 'e0eba4d68bc6ea65c419854322d20bb615cf57fb', 0, 'dbaumann@vinci-energies.com', '', '', 4, '', '2011-09-20 06:47:15', '2011-09-21 13:01:07', ''),
(155, 'catherine.mangin@creditmutuel.fr', '0ec1f87ee647a277a53bed1f7f714ae139d33099', 0, 'catherine.mangin@creditmutuel.fr', '', '', 4, '', '2011-10-09 17:55:14', '2011-11-07 08:54:53', ''),
(138, 'johandufau@gmail.com', 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3', 0, 'johandufau@gmail.com', '', '', 4, '', '2011-09-20 16:11:40', '2011-12-25 11:43:09', ''),
(153, 'cgoffin@groupe-arcan.com', '7ad7b1911c7b15d66baa96d7681dd0fcab5ae092', 0, 'cgoffin@groupe-arcan.com', '', '', 4, '', '2011-09-30 16:31:50', '2011-10-03 08:47:43', ''),
(145, 'unicef.nancy@unicef.fr', '4794a78077b5ac33793b9ec0910961bcb0c21ab9', 0, 'unicef.nancy@unicef.fr', '', '', 4, '', '2011-09-26 08:31:08', '0000-00-00 00:00:00', ''),
(143, 'reve.roy@mc2i.fr', '8fdb6853438feea7ebbbbc3e36a17f2a27ca929e', 0, 'reve.roy@mc2i.fr', '', '', 4, '', '2011-09-23 12:51:09', '2011-09-23 16:14:50', ''),
(144, 'pascale.cott@erdfdistribution.fr', '4551808a880ed8ebbafff0758940141b9c3a4e18', 0, 'pascale.cott@erdfdistribution.fr', '', '', 4, '', '2011-09-23 13:14:53', '2011-11-04 14:34:05', ''),
(147, 'ericlaclaverie@hotmail.fr', '9cf95dacd226dcf43da376cdb6cbba7035218921', 0, 'ericlaclaverie@hotmail.fr', '', '', 4, '', '2011-09-26 13:49:29', '0000-00-00 00:00:00', ''),
(176, 'emmanuel.chassard@ensem.inpl-nancy.fr', 'eae228e4fe386b1784118e6a88e457361a9841e0', 0, 'emmanuel.chassard@ensem.inpl-nancy.fr', '', '', 4, '', '2011-10-28 07:04:12', '2011-11-23 11:02:40', ''),
(151, 'vfoutel@technip.com', '97463d3ead67a69d50a5f77f6e1c4420643a377b', 0, 'vfoutel@technip.com', '', '', 4, '', '2011-09-29 08:42:14', '2011-11-14 09:11:00', ''),
(152, 'sarah.gledel@lu.pwc.com', 'ea3170f73fb3a830c87a095bee227587d04cee24', 0, 'sarah.gledel@lu.pwc.com', '', '', 4, '', '2011-09-29 14:50:21', '2011-10-28 10:15:17', ''),
(180, 'z.ba@esc-toulouse.fr', '8b789f5891cd0e893d49014c2e273432e1f5dc4f', 0, 'z.ba@esc-toulouse.fr', '', '', 4, '', '2011-11-03 13:01:48', '2011-11-03 13:02:23', ''),
(156, 'peggy.saintin@sca.com', '76a0d74b031f1735a29173c43289144453142a69', 0, 'peggy.saintin@sca.com', '', '', 4, '', '2011-10-10 12:57:32', '0000-00-00 00:00:00', ''),
(157, 'claire.oger@eulerhermes.com', '818d28506f47095ed8b325ee6cc85853ba5f5420', 0, 'claire.oger@eulerhermes.com', '', '', 4, '', '2011-10-10 14:17:18', '2011-11-16 10:01:25', ''),
(158, 'communication@mines.inpl-nancy.fr', '0ab4bf24284735623cf0191e037a2ae1cc5e1d44', 0, 'communication@mines.inpl-nancy.fr', '', '', 4, '', '2011-10-12 13:37:19', '2011-11-17 10:19:01', ''),
(159, 'elodie.gomes@banque-kolb.fr', 'bff80f76c69acf98208a6ed2bb44e8f7ff54e566', 0, 'elodie.gomes@banque-kolb.fr', '', '', 4, '', '2011-10-12 15:18:04', '2011-11-16 15:23:39', ''),
(160, 'angela.loge@intech-grp.com', 'faf9f18221b4b641d695b5290655739c53c95f56', 0, 'angela.loge@intech-grp.com', '', '', 4, '', '2011-10-13 11:38:38', '2011-10-24 09:03:03', ''),
(163, 'sandrine.nourry@hilti.com', '204d1b68ca70c70e17417076588df954f47da0da', 0, 'sandrine.nourry@hilti.com', '', '', 4, '', '2011-10-18 09:50:10', '2011-11-04 09:55:05', ''),
(164, 'dboudemdane@amaris.com', '3a87a95ad1636cd53e0ce347fb438993140960b9', 0, 'dboudemdane@amaris.com', '', '', 4, '', '2011-10-19 11:53:35', '2011-11-02 08:26:26', ''),
(165, 'myriam.rousseau@logica.com', 'dd867308226f9f903426f15c7aedd632b3c01245', 0, 'myriam.rousseau@logica.com', '', '', 4, '', '2011-10-21 09:15:50', '2011-10-21 09:51:04', ''),
(166, 'carbustama@hotmail.com', '161b816115dcd3b81bca7f975ec41b691b51d79e', 0, 'carbustama@hotmail.com', '', '', 4, '', '2011-10-21 10:38:35', '2011-10-28 12:19:38', ''),
(181, 'bertrand.malgras@gdfsuez.com', 'b65dfabac82ac0980167f07b04be31b855d43ee8', 0, 'bertrand.malgras@gdfsuez.com', '', '', 4, '', '2011-11-10 10:22:31', '2011-11-15 17:37:12', ''),
(179, 'jacky.chef@promotech.fr', '3899945a8bc22ee07aeab4806de4d0439f7bda82', 0, 'jacky.chef@promotech.fr', '', '', 4, '', '2011-10-31 10:34:31', '0000-00-00 00:00:00', ''),
(178, 'cjadeline@gmail.com', '9e858e757965b5cb57a5cc04292ae1f3cd82b1c9', 0, 'cjadeline@gmail.com', '', '', 4, '', '2011-10-28 18:23:52', '2011-11-20 06:01:55', ''),
(175, 'faure', '9e1f322efc36bf199427c3faa4781d4ae6e8383f', 0, 'bastien.faure@mines.inpl-nancy.fr', '', '', 5, 'all', '2011-10-25 13:35:48', '2011-11-09 11:20:36', ''),
(172, 'barbara.thomas@abylsen.com', 'bdc0126d7d253ddc84c40048eee4fd1fddeda2fa', 0, 'barbara.thomas@abylsen.com', '', '', 4, '', '2011-10-24 14:35:33', '2011-11-22 09:15:36', ''),
(197, 'pourtier.fall@gmail.com', '95dbfeb54a6d4254ff81eb87e9f7aba789198fa1', 0, 'pourtier.fall@gmail.com', 'Emilie', 'Pourtier', 9, '', '2011-11-21 09:05:03', '2011-11-22 12:34:39', '78.238.2.39'),
(198, 'julie.bluhm@ensaia.inpl-nancy.fr', 'bcd173ef18c97137956b984f01ab57a8b74ea683', 0, 'julie.bluhm@ensaia.inpl-nancy.fr', 'Julie', 'Bluhm', 9, '', '2011-11-21 09:09:18', '0000-00-00 00:00:00', '195.221.83.87'),
(199, 'laura.lebreton@ensaia.inpl-nancy.fr', '1f82c942befda29b6ed487a51da199f78fce7f05', 0, 'laura.lebreton@ensaia.inpl-nancy.fr', 'Laura', 'Lebreton', 9, '', '2011-11-21 09:12:24', '0000-00-00 00:00:00', '195.221.83.87'),
(200, 'bourguet.pauline@gmail.com', '5e2a2ae708527f22281034fbe10c99cb0ce3a21d', 0, 'bourguet.pauline@gmail.com', 'Pauline', 'Bourguet', 9, '', '2011-11-21 09:39:20', '2011-11-21 09:48:41', '195.221.83.87'),
(201, 'sophie.roman@ensgsi.inpl-nancy.fr', '97ffd0e3b29d8e1930be59634a78b68ccbcdfaca', 0, 'sophie.roman@ensgsi.inpl-nancy.fr', 'Sophie', 'Roman', 9, '', '2011-11-21 12:01:19', '0000-00-00 00:00:00', '83.196.74.194'),
(202, 'sibel.iyitutuncu@mines.inpl-nancy.fr', 'edc0a9720fce3203b1522562df85de010e08227a', 0, 'sibel.iyitutuncu@mines.inpl-nancy.fr', 'Sibel', 'Iyitutuncu', 9, '', '2011-11-21 12:21:09', '0000-00-00 00:00:00', '193.49.140.192'),
(203, 'lamiaa.ait-bellah@ensaia.inpl-nancy.fr', '58e29a96ff9e0858d6e55af40a3452520234b395', 0, 'lamiaa.ait-bellah@ensaia.inpl-nancy.fr', 'Lamiaa', 'Ait Bellah', 9, '', '2011-11-21 16:09:50', '0000-00-00 00:00:00', '77.201.129.156'),
(204, 'omar.ringa@gmail.com', 'e09447d7aaf632cac7ee6d27356b84b44cff1818', 0, 'omar.ringa@gmail.com', 'Omar', 'Ringa', 9, '', '2011-11-21 17:20:40', '2011-11-21 18:14:25', '193.49.162.12'),
(205, 'romain.degano@gmail.com', 'd9084296ede04d9b1252695d6321fff0124aa0ba', 0, 'romain.degano@gmail.com', 'Romain', 'Degano', 9, '', '2011-11-21 22:54:30', '2011-11-22 18:37:03', '2.3.207.17'),
(206, 'azeddine.idrissi@gmail.com', '206383e0d764e254e92ab5dd11007263d9dd9633', 0, 'azeddine.idrissi@gmail.com', 'Idrissi', 'Azeddine', 9, '', '2011-11-22 08:35:56', '0000-00-00 00:00:00', '86.66.186.178'),
(207, 'benoit.erbacher@mines-nancy.org', 'aa7bc40f2e215660624c432a5035e15a36ec1279', 0, 'benoit.erbacher@mines-nancy.org', 'Benoît', 'Erbacher', 9, '', '2011-11-22 14:45:07', '2011-11-22 14:58:31', '24.48.43.193'),
(208, 'id.mhamed.hicham@gmail.com', '55aa4858c1d017c2334e47a4db636e42e7694ffa', 0, 'id.mhamed.hicham@gmail.com', 'Hicham', 'Id Mhamed', 9, '', '2011-11-22 15:55:48', '2011-11-22 15:57:42', '194.214.218.108'),
(209, 'brenomcosta@hotmail.com', '04a0b30ca78be63efd719f1ba2595be18236480a', 0, 'brenomcosta@hotmail.com', 'Breno', 'Moreira Costa', 9, '', '2011-11-22 16:49:05', '0000-00-00 00:00:00', '193.49.162.12'),
(210, 'julie.huang@mines.inpl-nancy.fr', '8a01efeda99424bd45c45160ffa5db5a7f5b12e4', 0, 'julie.huang@mines.inpl-nancy.fr', 'Julie', 'Huang', 9, '', '2011-11-22 18:47:33', '0000-00-00 00:00:00', '89.224.148.250'),
(211, 'hugo.vioulac@mines.inpl-nancy.fr', 'f80c79e8995c1db9b69a04796d611794937958c3', 0, 'hugo.vioulac@mines.inpl-nancy.fr', 'Hugo', 'Vioulac', 9, '', '2011-11-22 18:53:53', '0000-00-00 00:00:00', '31.32.186.229'),
(212, 'braoudakis.yohan@gmail.com', '815dfcfd0c6673bdcb70288bc74f18c38afcea8a', 0, 'braoudakis.yohan@gmail.com', 'Yohan', 'Braoudakis', 9, '', '2011-11-22 19:03:55', '2011-11-23 22:55:01', '92.130.195.153'),
(213, 'rehane.ahamada@myicn.fr', '166dcac7719352b3e205761d0be097a64fe31cec', 0, 'rehane.ahamada@myicn.fr', 'Rehane', 'Ahamada', 9, '', '2011-11-22 20:02:30', '0000-00-00 00:00:00', '93.0.24.109'),
(214, 'wardharrioui@yahoo.fr', '6bd2f0375371e9482d2f257392ea56fcdae76147', 0, 'wardharrioui@yahoo.fr', 'Wared', 'Harrioui', 9, '', '2011-11-22 20:25:42', '2011-11-22 20:26:44', '80.185.224.202'),
(215, 'guillaume.lafont@mines.inpl-nancy.fr', '9d89be719a8b2bed3d6e38d71001924b202cc92b', 0, 'guillaume.lafont@mines.inpl-nancy.fr', 'Guillaume', 'Lafont', 9, '', '2011-11-22 20:51:51', '0000-00-00 00:00:00', '82.225.80.101'),
(216, 'garci.maroua@hotmail.fr', '162d610ac7d4000f9fc0305617a9becf2f6fd222', 0, 'garci.maroua@hotmail.fr', 'Garci', 'Maroua', 9, '', '2011-11-22 21:03:48', '0000-00-00 00:00:00', '193.49.162.6'),
(217, 'laureline.lebatard@mines.inpl-nancy.fr', '2ef2c28e65e7383fea95f017f2f08a2668cdb189', 0, 'laureline.lebatard@mines.inpl-nancy.fr', 'Lauréline', 'Lebatard', 9, '', '2011-11-22 21:22:53', '0000-00-00 00:00:00', '82.225.80.101'),
(218, 'jerome.leroux@mines.inpl-nancy.fr', '28964b4862579af2f29700d65617e7436acf5f37', 0, 'jerome.leroux@mines.inpl-nancy.fr', 'Jérome', 'Leroux', 9, '', '2011-11-22 21:40:57', '0000-00-00 00:00:00', '77.200.229.37'),
(219, 'salmane.lahdachi@mines.inpl-nancy.fr', '67b7124e079ecf1339cfbc2661ac2d9c39d2e673', 0, 'salmane.lahdachi@mines.inpl-nancy.fr', 'Salmane', 'Lahdachi', 9, '', '2011-11-22 21:47:07', '0000-00-00 00:00:00', '193.49.162.12'),
(220, 'vanessa.herault@mines.inpl-nancy.fr', '520efd113baea951667ab686b9cab457e247ae0f', 0, 'vanessa.herault@mines.inpl-nancy.fr', 'Vanessa', 'Herault', 9, '', '2011-11-22 22:27:15', '2011-11-24 09:17:57', '78.251.97.228'),
(221, 'sebastien.michel@mines.inpl-nancy.fr', '606488dcdf72bdaa9221d6c76bbf9fd57d76a028', 0, 'sebastien.michel@mines.inpl-nancy.fr', 'Sébastien', 'Michel', 9, '', '2011-11-23 04:59:22', '0000-00-00 00:00:00', '90.40.218.73'),
(222, 'sophia.benomar@mines.inpl-nancy.fr', '07c8ebed312dfe0dcbae6bf8c2f88e6a6147195f', 0, 'sophia.benomar@mines.inpl-nancy.fr', 'Sophia', 'Benomar', 9, '', '2011-11-23 10:40:37', '0000-00-00 00:00:00', '77.200.229.37'),
(223, 'hrv.walter@gmail.com', '6f4c57e022e5085ec6e368157589fc64151a1681', 0, 'hrv.walter@gmail.com', 'Hervé', 'Walter', 9, '', '2011-11-23 11:10:57', '0000-00-00 00:00:00', '86.70.1.54'),
(224, 'youyach@hotmail.com', '63e5b70d0c30cd78e27a987a3a6605afcb0e7bc1', 0, 'youyach@hotmail.com', 'Yousra', 'Choukrani', 9, '', '2011-11-23 12:16:49', '0000-00-00 00:00:00', '193.49.140.145'),
(225, 'jeremy.pageaux@mines.inpl-nancy.fr', '2d862f44e4847dc4501e90a700ac2cd7c0e7c696', 0, 'jeremy.pageaux@mines.inpl-nancy.fr', 'Jérémy', 'Pageaux', 9, '', '2011-11-23 12:58:02', '0000-00-00 00:00:00', '89.224.139.9'),
(226, 'senechal.elise@gmail.com', '1bd593229764163153a1c7f7977184e1580ec1ae', 0, 'senechal.elise@gmail.com', 'Elise', 'Senechal', 9, '', '2011-11-23 14:32:35', '0000-00-00 00:00:00', '31.34.31.37'),
(227, 'julien.lapiche@orange.fr', '0fba5c22724cbfe3a9ec1e1cb1d7b690e4ef57bb', 0, 'julien.lapiche@orange.fr', 'Julien', 'Lapiche', 9, '', '2011-11-23 15:20:11', '0000-00-00 00:00:00', '77.204.31.50'),
(228, 'douel@free.fr', 'e4242ee2cbf52cc49a50b7b5cf4d22a091677390', 0, 'douel@free.fr', 'Romain', 'Douel', 9, '', '2011-11-23 15:21:37', '2011-11-23 15:25:20', '2a01:e35:8a3f:89f0:4d82:df4:d127:5143'),
(229, 'erollot@gmail.com', 'a467a05d9f6f55e80afec795f99be456ade0f591', 0, 'erollot@gmail.com', 'Elodie', 'Rollot', 9, '', '2011-11-23 15:30:21', '0000-00-00 00:00:00', '62.34.94.159'),
(230, 'charline.renaudeau@gmail.com', 'f2dc697d032cc7d07f01edfa795e8e0575e8ffd3', 0, 'charline.renaudeau@gmail.com', 'Charline', 'Renaudeau', 9, '', '2011-11-23 16:19:34', '2011-11-23 16:20:35', '83.194.6.213'),
(231, 'lei.gao@mines.inpl-nancy.fr', 'e194901f0ed0aa6ddd99b6dab4283dc18e86334d', 0, 'lei.gao@mines.inpl-nancy.fr', 'Lei', 'Gao', 9, '', '2011-11-23 16:35:20', '2011-11-23 16:43:33', '193.49.162.12'),
(232, 'patrick.haddedou@mines.inpl-nancy.fr', 'da234c8cea9e28e26bd9ae68b90768dd2a7582a4', 0, 'patrick.haddedou@mines.inpl-nancy.fr', 'Patrick', 'Haddedou', 9, '', '2011-11-23 16:55:18', '0000-00-00 00:00:00', '86.72.148.235'),
(233, 'poindron.florent@hotmail.fr', 'aa50385870572314cff97e666aa0a6f01f162423', 0, 'poindron.florent@hotmail.fr', 'Florent', 'Poindron', 9, '', '2011-11-23 17:13:24', '2011-11-23 17:22:48', '85.170.118.220'),
(234, 'janas2000@gmail.com', 'a45232ed196740589e3620819ae1125ae47c8d0c', 0, 'janas2000@gmail.com', 'Nassim', 'Jamali', 9, '', '2011-11-23 17:25:27', '2011-11-23 17:29:58', '77.200.229.37'),
(235, 'alex.gapin@mines-nancy.org', '6172cc06905dfef5902d141881c8efb374239f35', 0, 'alex.gapin@mines-nancy.org', 'Alex', 'Gapin', 9, '', '2011-11-23 18:15:02', '0000-00-00 00:00:00', '84.97.155.79'),
(236, 'matthieu.borgraeve@mines.inpl-nancy.fr', 'dcd1b0894dc3461f86d22ec6983c2afe0793257c', 0, 'matthieu.borgraeve@mines.inpl-nancy.fr', 'Matthieu', 'Borgraeve', 9, '', '2011-11-23 18:29:11', '2011-11-23 18:31:56', '89.224.159.147'),
(237, 'eric.laclaverie@mines.inpl-nancy.fr', 'b893fd5116d63153d39b00079ca3193b56d169b8', 0, 'eric.laclaverie@mines.inpl-nancy.fr', 'Eric', 'Laclaverie', 9, '', '2011-11-23 20:04:03', '0000-00-00 00:00:00', '195.221.83.64'),
(238, 'werner.quentin@live.fr', '7e68a3a97801bb63efed84ee4693df8eb8d60c27', 0, 'werner.quentin@live.fr', 'Quentin', 'Werner', 9, '', '2011-11-23 20:42:56', '2011-11-23 20:43:57', '193.49.162.19'),
(239, 'yacine_tsa@live.fr', '2155a0a0127f21072d9f2ccbd3065d67e12dd333', 0, 'yacine_tsa@live.fr', 'Yacine', 'Tsalamlal', 9, '', '2011-11-23 21:06:44', '2011-11-23 21:11:54', '2a01:e35:8a4d:4a0:505b:5659:135a:a2f7'),
(240, 'sylvain.eckert@bbox.fr', '4b1c4fff14ee5054bcf2074f3fea4e652bf32553', 0, 'sylvain.eckert@bbox.fr', 'Sylvain', 'Eckert', 9, '', '2011-11-23 21:08:46', '2011-11-23 21:09:57', '89.87.202.42');

-- --------------------------------------------------------

--
-- Structure de la table `users_cats`
--

CREATE TABLE IF NOT EXISTS `users_cats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` mediumint(9) NOT NULL,
  `name` varchar(50) NOT NULL,
  `access` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Contenu de la table `users_cats`
--

INSERT INTO `users_cats` (`id`, `parent`, `name`, `access`) VALUES
(4, 0, 'Entreprise', ''),
(5, 0, 'Membre FEH 2011', 'entreprise|0'),
(6, 0, 'BDE', 'fdh'),
(8, 0, 'ICN', ''),
(9, 0, 'Eleve', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
