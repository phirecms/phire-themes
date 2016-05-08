--
-- Themes Module MySQL Database for Phire CMS 2.0
--

-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]themes` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `parent_id` int(16),
  `name` varchar(255) NOT NULL,
  `file` varchar(255),
  `folder` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `active` int(1) NOT NULL,
  `assets` text,
  `installed_on` datetime,
  `updated_on` datetime,
  PRIMARY KEY (`id`),
  INDEX `theme_name` (`name`),
  CONSTRAINT `fk_theme_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `[{prefix}]themes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10001;

-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 1;
