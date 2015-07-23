CREATE TABLE IF NOT EXISTS `adf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `objecttype` varchar(32) NOT NULL,
  `objectid` int(11) NOT NULL,
  `cascade` int(11) NOT NULL,
  `contenttype_id` int(11) NOT NULL,
  `exclude` varchar(255) NOT NULL DEFAULT '',
  `identifier` varchar(32) NOT NULL DEFAULT '',
  `theme` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `adfcontent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objecttype` varchar(255) NOT NULL,
  `objectid` int(11) NOT NULL,
  `adf_id` int(11) NOT NULL,
  `content` mediumtext NOT NULL,
  `language` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `adminuser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `passwordformat` varchar(12) NOT NULL DEFAULT '',
  `adminuserrole_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `adminuserrole` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `config` text NOT NULL,
  `cmsnavigation` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `adminuserrole` (`id`, `name`, `config`, `cmsnavigation`) VALUES
(1, 'Administrator', 'a:61:{s:4:"ROOT";i:5;s:6:"pages/";i:5;s:10:"page/edit/";i:5;s:6:"posts/";i:5;s:10:"post/edit/";i:5;s:6:"media/";i:5;s:10:"media/add/";i:5;s:11:"media/edit/";i:5;s:10:"downloads/";i:5;s:13:"download/add/";i:5;s:14:"download/edit/";i:5;s:10:"siteusers/";i:5;s:20:"siteusers/viewusers/";i:5;s:30:"siteusers/viewusers/editusers/";i:5;s:21:"siteusers/viewgroups/";i:5;s:32:"siteusers/viewgroups/editgroups/";i:5;s:6:"admin/";i:5;s:12:"admin/users/";i:5;s:17:"admin/users/edit/";i:5;s:15:"admin/settings/";i:5;s:10:"analytics/";i:5;s:7:"addons/";i:5;s:19:"popup/select/image/";i:5;s:18:"popup/select/link/";i:5;s:19:"action-content-edit";i:5;s:20:"action-content-apply";i:5;s:22:"action-content-discard";i:5;s:31:"action-content-findSelectedLink";i:5;s:34:"action-content-prepareSelectedLink";i:5;s:18:"action-page-create";i:5;s:18:"action-page-delete";i:5;s:16:"action-page-move";i:5;s:18:"action-page-status";i:5;s:21:"action-page-adminTree";i:5;s:28:"action-page-adminTreeSession";i:5;s:18:"action-post-create";i:5;s:18:"action-post-delete";i:5;s:22:"action-post-savefolder";i:5;s:16:"action-post-move";i:5;s:18:"action-post-status";i:5;s:24:"action-post-loadByFolder";i:5;s:22:"action-post-folderTree";i:5;s:19:"action-media-create";i:5;s:19:"action-media-delete";i:5;s:23:"action-media-savefolder";i:5;s:17:"action-media-move";i:5;s:25:"action-media-loadByFolder";i:5;s:23:"action-media-folderTree";i:5;s:22:"action-download-create";i:5;s:22:"action-download-delete";i:5;s:26:"action-download-savefolder";i:5;s:22:"action-download-status";i:5;s:20:"action-download-move";i:5;s:28:"action-download-loadByFolder";i:5;s:26:"action-download-folderTree";i:5;s:33:"action-adminAuthentication-delete";i:5;s:25:"action-websiteUser-delete";i:5;s:30:"action-websiteUser-deleteGroup";i:5;s:25:"action-search-adminSearch";i:5;s:27:"action-websiteUser-activate";i:5;s:29:"action-websiteUser-deactivate";i:5;}', 'a:8:{i:0;s:5:"Pages";i:2;s:5:"Posts";i:3;s:5:"Media";i:4;s:9:"Downloads";i:5;s:10:"Site users";i:6;s:5:"Admin";i:7;s:9:"Analytics";i:8;s:7:"Add ons";}');

CREATE TABLE IF NOT EXISTS `analyticsvisitor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(32) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `browser` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `posttype_id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `category_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objecttype` varchar(255) NOT NULL,
  `objectid` int(11) NOT NULL,
  `contentcontainer_id` int(11) NOT NULL,
  `content` mediumtext NOT NULL,
  `language` varchar(4) NOT NULL,
  `editable` int(11) NOT NULL DEFAULT '1',
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `contentappend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objecttype` varchar(255) NOT NULL,
  `objectid` int(11) NOT NULL,
  `contentcontainer_id` int(11) NOT NULL,
  `content` mediumtext NOT NULL,
  `language` varchar(4) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `contentcontainer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contenttype_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `contenttype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `fields` text NOT NULL,
  `settings` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `contenttype` (`id`, `type`, `fields`, `settings`) VALUES
(1, 'Rich Text', '', '');

CREATE TABLE IF NOT EXISTS `datacache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar( 255 ) NOT NULL,
  `context` text NOT NULL,
  `data` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `download` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL,
  `isfolder` tinyint(4) NOT NULL DEFAULT '0',
  `parentid` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `uploaded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `search` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `imagesize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `mode` varchar(255) NOT NULL,
  `theme` varchar(255) NOT NULL DEFAULT '',
  `identifier` varchar(255) NOT NULL DEFAULT '',
  `settings` text DEFAULT NULL,
  `selectable` INT NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alt` varchar(255) NOT NULL,
  `isfolder` tinyint(4) NOT NULL DEFAULT '0',
  `parentid` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `type` varchar(32) NOT NULL,
  `pathmodifier` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `search` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL DEFAULT 'Default',
  `status` int(11) NOT NULL DEFAULT '0',
  `parentid` int(11) NOT NULL DEFAULT '0',
  `pageorder` int(11) NOT NULL DEFAULT '0',
  `deleted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `commencement` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expiry` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastupdated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `visibility` int(11) NOT NULL DEFAULT '1',
  `htmltitle` varchar(255) DEFAULT NULL,
  `metadescription` text,
  `metakeywords` text,
  `redirect` text,
  `ascendants` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL DEFAULT 'Default',
  `date` date NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `parentid` int(11) NOT NULL DEFAULT '0',
  `postorder` int(11) NOT NULL DEFAULT '0',
  `deleted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `commencement` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expiry` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastupdated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `visibility` int(11) NOT NULL DEFAULT '1',
  `htmltitle` varchar(255) NOT NULL DEFAULT '',
  `metadescription` text,
  `metakeywords` text,
  `redirect` text,
  `standfirst` text,
  `isfolder` int(11) NOT NULL DEFAULT '0',
  `tags` varchar(255) NOT NULL DEFAULT '',
  `media_id` int(11) NOT NULL DEFAULT '0',
  `posttype_id` int(11) NOT NULL DEFAULT '1',
  `eventstart` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `eventend` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `posttype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `posttype` (`id`, `name`) VALUES
(1, 'Post'),
(2, 'Event');

CREATE TABLE IF NOT EXISTS `route` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `route` varchar(255) NOT NULL,
  `primaryroute` int(11) NOT NULL,
  `objecttype` varchar(32) NOT NULL,
  `objectid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Finder` (`primaryroute`,`objecttype`,`objectid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `route` (`id`, `route`, `primaryroute`, `objecttype`, `objectid`) VALUES
(1, '/', 1, 'page', 1);

CREATE TABLE IF NOT EXISTS `setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

INSERT INTO `setting` (`id`, `name`, `value`) VALUES
(1, 'email', '<html xmlns="http://www.w3.org/1999/xhtml">\r\n<head>\r\n<title></title>\r\n<!-- hotmail fix start -->\r\n<style type="text/css">\r\n.ReadMsgBody\r\n{ width: 100%;}\r\n.ExternalClass\r\n{width: 100%;}\r\np {margin-bottom: 0em;}\r\na {color: #0073c9;}\r\n</style>\r\n<!-- hotmail fix end -->\r\n</head>\r\n<body style="background: #fff;">\r\n<table cellspacing="0" cellpadding="0" width="100%" border="0" style="width: 100%; background: #fff; font-family: arial, sans-serif">\r\n<tr>\r\n<td>\r\n<EMAILCONTENT>\r\n</td>\r\n</tr>\r\n</table>\r\n</body>\r\n</html>'),
(2, 'version', '3.4');

CREATE TABLE IF NOT EXISTS `systemlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objectType` varchar(255) NOT NULL,
  `objectID` int(11) NOT NULL,
  `event` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `websiteuser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL DEFAULT '',
  `forename` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `organisation` varchar(255) NOT NULL DEFAULT '',
  `jobtitle` VARCHAR(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `password` text NOT NULL,
  `passwordformat` varchar(12) NOT NULL,
  `address1` varchar(255) NOT NULL,
  `address2` varchar(255) NOT NULL DEFAULT '',
  `address3` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(255) NOT NULL,
  `region` varchar(255) NOT NULL,
  `postcode` varchar(16) NOT NULL,
  `country` varchar(32) NOT NULL DEFAULT '',
  `activation` varchar(255) NOT NULL DEFAULT '',
  `passwordrecovery` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `websiteusergroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `autoaddnewusers` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `websiteusergrouppermission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objecttype` varchar(255) NOT NULL,
  `objectid` int(11) NOT NULL,
  `websiteusergroup_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `websiteuser_websiteusergroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `websiteuser_id` int(11) NOT NULL,
  `websiteusergroup_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;