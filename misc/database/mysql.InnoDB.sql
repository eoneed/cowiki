#
#   Copyright (C) Daniel T. Gorski, <daniel.gorski@develnet.org>
#                 Further information on <http://www.develnet.org>
#
#   $Id: mysql.sql,v 1.9 2004/07/22 20:30:16 dtg Exp $
#
#   This file is part of coWiki. coWiki is free software under the terms of
#   the GNU General Public License (GPL). Read the LICENSE file. If you did
#   not receive a copy of the license and are not able to obtain it through
#   the internet, please send a note to <cowiki-license@develnet.org> so we
#   can mail you a copy immediately.
#

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `cowiki_comment` (
  `rec_tan` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_mod_id` int(10) unsigned DEFAULT NULL,
  `rec_mod_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_mod_host` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_state` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tree_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `replies` int(10) unsigned NOT NULL DEFAULT '0',
  `latest` datetime DEFAULT NULL,
  `subject` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `wikisubject` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `author_id` int(10) unsigned NOT NULL DEFAULT '0',
  `author_name` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author_email` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author_host` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `encoding` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` mediumtext COLLATE utf8_unicode_ci,
  `tenor` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notify` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `views` int(10) unsigned DEFAULT NULL,
  `lft` int(10) unsigned NOT NULL DEFAULT '0',
  `rgt` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_id`),
  KEY `rec_mod_id` (`rec_mod_id`),
  KEY `rec_state` (`rec_state`),
  KEY `node_id` (`node_id`),
  KEY `tree_id` (`tree_id`),
  KEY `created` (`created`),
  KEY `subject` (`subject`),
  KEY `wikisubject` (`wikisubject`),
  KEY `author_id` (`author_id`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `cowiki_group` (
  `rec_tan` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_mod_id` int(10) unsigned DEFAULT NULL,
  `rec_mod_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_mod_host` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_state` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`group_id`),
  KEY `rec_mod_id` (`rec_mod_id`),
  KEY `rec_state` (`rec_state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `cowiki_media` (
  `media_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`media_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `cowiki_node` (
  `rec_tan` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_mod_id` int(10) unsigned DEFAULT NULL,
  `rec_mod_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_mod_host` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_state` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `node_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tree_id` int(10) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_dir` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_index` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dont_index` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notify_user` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notify_group` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `mode` varchar(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `author_id` int(10) unsigned DEFAULT NULL,
  `revision` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `wikiname` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `metaphone` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `encoding` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` mediumtext COLLATE utf8_unicode_ci,
  `summary` mediumtext COLLATE utf8_unicode_ci,
  `keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `changelog` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comments` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `menu` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `foot` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `views` int(10) unsigned DEFAULT '0',
  `sort_order` int(10) unsigned DEFAULT '0',
  `node_spill_id` int(10) unsigned DEFAULT '0',
  `meta` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`node_id`),
  KEY `rec_mod_id` (`rec_mod_id`),
  KEY `rec_state` (`rec_state`),
  KEY `tree_id` (`tree_id`),
  KEY `parent_id` (`parent_id`),
  KEY `is_dir` (`is_dir`),
  KEY `is_index` (`is_index`),
  KEY `created` (`created`),
  KEY `author_id` (`author_id`),
  KEY `revision` (`revision`),
  KEY `name` (`name`),
  KEY `wikiname` (`wikiname`),
  KEY `metaphone` (`metaphone`),
  KEY `menu` (`menu`),
  KEY `foot` (`foot`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `cowiki_node_hist` (
  `node_hist_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rec_tan` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_mod_id` int(10) unsigned DEFAULT NULL,
  `rec_mod_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_mod_host` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_state` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `node_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tree_id` int(10) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_dir` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_index` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dont_index` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notify_user` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notify_group` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `mode` varchar(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `author_id` int(10) unsigned DEFAULT NULL,
  `revision` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `wikiname` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `metaphone` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `encoding` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` mediumtext COLLATE utf8_unicode_ci,
  `summary` mediumtext COLLATE utf8_unicode_ci,
  `keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `changelog` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comments` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `menu` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `foot` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `views` int(10) unsigned DEFAULT '0',
  `sort_order` int(10) unsigned DEFAULT '0',
  `node_spill_id` int(10) unsigned DEFAULT '0',
  `meta` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`node_hist_id`),
  KEY `rec_mod_id` (`rec_mod_id`),
  KEY `rec_state` (`rec_state`),
  KEY `node_id` (`node_id`),
  KEY `tree_id` (`tree_id`),
  KEY `created` (`created`),
  KEY `revision` (`revision`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `cowiki_node_media` (
  `node_id` int(10) unsigned NOT NULL DEFAULT '0',
  `media_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `node_id` (`node_id`),
  KEY `media_id` (`media_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `cowiki_node_ref` (
  `node_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ref_node_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `node_id` (`node_id`),
  KEY `ref_node_id` (`ref_node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `cowiki_shoutbox` (
  `rec_tan` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_mod_id` int(10) unsigned DEFAULT NULL,
  `rec_mod_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_mod_host` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_state` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shoutbox_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `poster_name` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` mediumtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`shoutbox_id`),
  KEY `rec_mod_id` (`rec_mod_id`),
  KEY `rec_state` (`rec_state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `cowiki_user` (
  `rec_tan` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_mod_id` int(10) unsigned DEFAULT NULL,
  `rec_mod_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_mod_host` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rec_state` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `guest_only` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `login` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `passwd` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `num_success` int(10) unsigned DEFAULT '0',
  `num_failure` int(10) unsigned DEFAULT '0',
  `locked` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locked_time` datetime DEFAULT '0000-00-00 00:00:00',
  `locked_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locked_host` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expires` datetime DEFAULT '0000-00-00 00:00:00',
  `meta` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `login` (`login`),
  KEY `rec_mod_id` (`rec_mod_id`),
  KEY `rec_state` (`rec_state`),
  KEY `group_id` (`group_id`),
  KEY `guest_only` (`guest_only`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `cowiki_user_group` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
