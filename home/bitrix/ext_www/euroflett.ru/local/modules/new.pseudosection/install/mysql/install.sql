CREATE TABLE IF NOT EXISTS `new_webprofy_pseudosection` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ACTIVE` char(1) NOT NULL default 'Y',
  `NAME` varchar(255) NOT NULL,
  `TITLE` varchar(255) NOT NULL,
  `DESCRIPTION` TEXT NULL,
  `TYPE` varchar(255) NOT NULL,
  `URL` varchar(255) NOT NULL,
  `PATH` varchar(255) NOT NULL,
  `SITE_ID` char(2) NULL,
  `IBLOCK_ID` int(11) NOT NULL DEFAULT '0',
  `ELEMENT_ID` int(11) NOT NULL DEFAULT '0',
  `GROUP_ID` int(11) NOT NULL DEFAULT '0',
  `SORT` int(11) NULL DEFAULT '500',
  `DATE_CREATE` datetime NOT NULL,
  `DATE_UPDATE` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
