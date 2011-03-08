DROP TABLE IF EXISTS `LFW_CatalogItem`;
|
CREATE  TABLE IF NOT EXISTS `LFW_CatalogItem` (
  `catalogitem_id` binary(16) NOT NULL,
  `catalogitem_lovefilm_resource_id` varchar(200) DEFAULT NULL,
  `catalogitem_url` varchar(200) DEFAULT NULL,
  `catalogitem_title` varchar(200) DEFAULT NULL,
  `catalogitem_releasedate` datetime DEFAULT NULL,
  `catalogitem_updated` datetime DEFAULT NULL,
  `catalogitem_rating` double DEFAULT NULL,
  `catalogitem_imageurl` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`catalogitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
|
DROP TABLE IF EXISTS `LFW_PageAssignment`;
|
CREATE TABLE IF NOT EXISTS `LFW_PageAssignment` (
  `page_id` binary(16) NOT NULL,
  `catalogitem_id` binary(16) NOT NULL,
  `assignment_position` int(11) NOT NULL,
  `nofollow` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`page_id`,`catalogitem_id`),
  KEY `fk_pageassignment_has_page` (`page_id`),
  KEY `fk_pageassignment_has_catalogueitems` (`catalogitem_id`),
  CONSTRAINT `fk_pageassignment_has_catalogueitems` FOREIGN KEY (`catalogitem_id`) REFERENCES `LFW_CatalogItem` (`catalogitem_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
|
DROP TABLE IF EXISTS `LFW_Page`;
|
CREATE TABLE IF NOT EXISTS `LFW_Page` (
  `page_id` binary(16) NOT NULL,
  `page_datequeried` datetime DEFAULT NULL,
  `page_uri` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`page_id`),
  KEY `fk_page_has_pageassignment` (`page_id`),
  CONSTRAINT `fk_page_has_pageassignment` FOREIGN KEY (`page_id`) REFERENCES `LFW_PageAssignment` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
