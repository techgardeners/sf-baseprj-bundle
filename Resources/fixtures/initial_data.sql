-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.5.24-log - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL version:             7.0.0.4235
-- Date/time:                    2013-02-06 02:08:07
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for sfbaseprj
USE `sfbaseprj`;

-- Dumping data for table sfbaseprj.auth_user: ~0 rows (approximately)
/*!40000 ALTER TABLE `auth_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `auth_user` ENABLE KEYS */;

-- Dumping data for table sfbaseprj.geo_position: ~0 rows (approximately)
/*!40000 ALTER TABLE `geo_position` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_position` ENABLE KEYS */;

-- Dumping data for table sfbaseprj.language: ~0 rows (approximately)
/*!40000 ALTER TABLE `language` DISABLE KEYS */;
REPLACE INTO `language` (`id`, `label`, `ISO639`, `ISO3166`, `locale`, `enabled`) VALUES
    (1, 'Italiano', 'it', 'IT', 'it_IT', 1),
    (2, 'English', 'en', 'GB', 'en_GB', 1),
    (3, 'Espanol', 'es', 'ES', 'es_ES', 1),
    (4, 'Francese', 'fr', 'FR', 'fr_FR', 1),
    (5, 'Tedesco', 'de', 'DE', 'de_DE', 1);
/*!40000 ALTER TABLE `language` ENABLE KEYS */;

-- Dumping data for table sfbaseprj.language_trans: ~0 rows (approximately)
/*!40000 ALTER TABLE `language_trans` DISABLE KEYS */;
/*!40000 ALTER TABLE `language_trans` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
