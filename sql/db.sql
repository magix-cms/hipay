CREATE TABLE IF NOT EXISTS `mc_plugins_hipay` (
  `idhipay` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `wsLogin` varchar(45) NOT NULL,
  `wsPassword` varchar(45) NOT NULL,
  `websiteId` varchar(45) NOT NULL,
  `customerIpAddress` varchar(20) NOT NULL,
  `signkey` varchar(45) DEFAULT NULL,
  `formaction` varchar(40) NOT NULL DEFAULT 'test',
  PRIMARY KEY (`idhipay`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;