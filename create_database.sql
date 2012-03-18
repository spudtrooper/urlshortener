SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `clicks` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `url_id` int(10) NOT NULL,
  `timestamp` int(32) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `referrer` varchar(512) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1317236 ;

CREATE TABLE IF NOT EXISTS `urls` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `long_url` varchar(1028) NOT NULL,
  `timestamp_added` int(32) NOT NULL,
  `clicks` int(10) NOT NULL,
  `category` varchar(255) NOT NULL,
  `url` varchar(1028) NOT NULL,
  `title` varchar(255) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `referrer` varchar(512) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=268882 ;