CREATE TABLE IF NOT EXISTS `mc_plugins_hipay` (
  `idhipay` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `mailhack` varchar(120) NOT NULL,
  `pwaccount` varchar(45) NOT NULL,
  `setaccount` varchar(45) NOT NULL,
  `setmarchantsiteid` varchar(45) NOT NULL,
  `mailcart` varchar(120) NOT NULL,
  `setcategory` smallint(3) unsigned NOT NULL,
  `signkey` varchar(45) NOT NULL,
  `formaction` varchar(40) NOT NULL DEFAULT 'test',
  PRIMARY KEY (`idhipay`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;