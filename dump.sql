-- MySQL dump 10.13  Distrib 5.7.29, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: copy1
-- ------------------------------------------------------
-- Server version	5.7.29-0ubuntu0.18.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE = @@TIME_ZONE */;
/*!40103 SET TIME_ZONE = '+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS = @@UNIQUE_CHECKS, UNIQUE_CHECKS = 0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0 */;
/*!40101 SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES = @@SQL_NOTES, SQL_NOTES = 0 */;

--
-- Table structure for table `compte`
--

DROP TABLE IF EXISTS `compte`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compte`
(
    `id`    int(11) NOT NULL AUTO_INCREMENT,
    `login` text    NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 5
  DEFAULT CHARSET = latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compte`
--

LOCK TABLES `compte` WRITE;
/*!40000 ALTER TABLE `compte`
    DISABLE KEYS */;
INSERT INTO `compte` (`id`, `login`)
VALUES (1, '5646832134'),
       (2, '9798165132'),
       (3, '8897984941'),
       (4, '0201216566');
/*!40000 ALTER TABLE `compte`
    ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facture`
--

DROP TABLE IF EXISTS `facture`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `facture`
(
    `id`      int(11) NOT NULL AUTO_INCREMENT,
    `compte`  int(11) NOT NULL,
    `montant` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `facture_compte_id_fk` (`compte`),
    CONSTRAINT `facture_compte_id_fk` FOREIGN KEY (`compte`) REFERENCES `compte` (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 14
  DEFAULT CHARSET = latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facture`
--

LOCK TABLES `facture` WRITE;
/*!40000 ALTER TABLE `facture`
    DISABLE KEYS */;
INSERT INTO `facture` (`id`, `compte`, `montant`)
VALUES (1, 3, 20),
       (2, 3, 15),
       (3, 1, 6),
       (4, 2, 14),
       (5, 3, 41),
       (6, 3, 3),
       (7, 4, 80),
       (8, 1, 30),
       (9, 1, 78),
       (10, 4, 34),
       (11, 4, 0),
       (12, 3, 99),
       (13, 2, 140);
/*!40000 ALTER TABLE `facture`
    ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facture_ko`
--

DROP TABLE IF EXISTS `facture_ko`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `facture_ko`
(
    `idFacture` int(11)                  NOT NULL,
    `etat`      enum ('rejete','annule') NOT NULL,
    `details`   text,
    PRIMARY KEY (`idFacture`),
    CONSTRAINT `facture_ko_facture_id_fk` FOREIGN KEY (`idFacture`) REFERENCES `facture` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facture_ko`
--

LOCK TABLES `facture_ko` WRITE;
/*!40000 ALTER TABLE `facture_ko`
    DISABLE KEYS */;
INSERT INTO `facture_ko` (`idFacture`, `etat`, `details`)
VALUES (1, 'rejete', 'toto'),
       (4, 'annule', 'abc'),
       (6, 'annule', 'def'),
       (8, 'rejete', 'ghi'),
       (10, 'annule', 'jkl'),
       (13, 'rejete', 'zzz');
/*!40000 ALTER TABLE `facture_ko`
    ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `identite`
--

DROP TABLE IF EXISTS `identite`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `identite`
(
    `id`     int(11) NOT NULL AUTO_INCREMENT,
    `compte` int(11) NOT NULL,
    `nom`    text    NOT NULL,
    `prenom` text    NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `identite_compte_uindex` (`compte`),
    CONSTRAINT `identite_compte_id_fk` FOREIGN KEY (`compte`) REFERENCES `compte` (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 5
  DEFAULT CHARSET = latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `identite`
--

LOCK TABLES `identite` WRITE;
/*!40000 ALTER TABLE `identite`
    DISABLE KEYS */;
INSERT INTO `identite` (`id`, `compte`, `nom`, `prenom`)
VALUES (1, 1, 'nct', 'b'),
       (2, 2, 'nct', '2'),
       (3, 3, 'nct', '3'),
       (4, 4, 'nct', '4');
/*!40000 ALTER TABLE `identite`
    ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE = @OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE = @OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES = @OLD_SQL_NOTES */;

-- Dump completed on 2020-02-06 15:17:47
