CREATE TABLE IF NOT EXISTS `ajaxmvc_example_one_table` (
 `id` int(6) NOT NULL AUTO_INCREMENT,
  `example_one_field` tinytext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

INSERT INTO `ajaxmvc_example_one_table` (`id`, `example_one_field`) VALUES ('2', 'example_one');