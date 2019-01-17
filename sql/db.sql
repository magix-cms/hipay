CREATE TABLE IF NOT EXISTS `mc_hipay` (
  `id_hipay` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `wsLogin` varchar(45) DEFAULT NULL,
  `wsPassword` varchar(45) DEFAULT NULL,
  `websiteId` varchar(45) DEFAULT NULL,
  `signkey` varchar(45) DEFAULT NULL,
  `formaction` varchar(40) NOT NULL DEFAULT 'test',
  `categoryId` smallint(3) UNSIGNED DEFAULT NULL,
  `direct` smallint(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_hipay`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `mc_admin_access` (`id_role`, `id_module`, `view`, `append`, `edit`, `del`, `action`)
  SELECT 1, m.id_module, 1, 1, 1, 1, 1 FROM mc_module as m WHERE name = 'hipay';