CREATE TABLE IF NOT EXISTS `messaging_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `path` varchar(256) NOT NULL,
  `link_id` varchar(45) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `uploader_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `link_id` (`link_id`),
  KEY `uploader_id` (`uploader_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `messaging_conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `author_id` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `attivita_id` int(11) DEFAULT NULL,
  `closed` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `attivita_id` (`attivita_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `messaging_conversations_tags` (
  `conversation_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  KEY `conversation_id` (`conversation_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
CREATE TABLE IF NOT EXISTS `messaging_conversations_users` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  KEY `conversation_id` (`conversation_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
CREATE TABLE IF NOT EXISTS `messaging_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `is_read` tinyint(4) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `content` text NOT NULL,
  `is_received` tinyint(4) NOT NULL DEFAULT '1',
  `is_first` tinyint(4) NOT NULL DEFAULT '1',
  `to_id` int(11) NOT NULL,
  `all_rcpts` VARCHAR(100) NULL,
  PRIMARY KEY (`id`),
  KEY `from_id` (`from_id`),
  KEY `conversation_id` (`conversation_id`),
  KEY `is_received` (`is_received`),
  KEY `to_id` (`to_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `messaging_messages_attachments` (
  `message_id` int(11) NOT NULL,
  `attachment_id` int(11) NOT NULL,
  KEY `message_id` (`message_id`),
  KEY `attachment_id` (`attachment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
CREATE TABLE IF NOT EXISTS `messaging_messagingtags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `color` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `messaging_messagingtag_usergroup_use` (
  `tag_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  KEY `tag_id` (`tag_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `messaging_messagingtag_usergroup_view` (
  `tag_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  KEY `tag_id` (`tag_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
