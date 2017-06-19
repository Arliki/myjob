-- MySQL dump 10.13  Distrib 5.5.53, for Win32 (AMD64)
--
-- Host: localhost    Database: brow
-- ------------------------------------------------------
-- Server version	5.5.53

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `about`
--

DROP TABLE IF EXISTS `about`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `about` (
  `content` text NOT NULL,
  `id` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `about`
--

LOCK TABLES `about` WRITE;
/*!40000 ALTER TABLE `about` DISABLE KEYS */;
INSERT INTO `about` VALUES ('&lt;p&gt;&lt;span style=&quot;font-family: &amp;quot;Lantinghei SC&amp;quot;, &amp;quot;microsoft yahei&amp;quot;, Tahoma, Helvetica, SimSun, sans-serif; font-size: 12px; text-indent: 24px; background-color: rgb(255, 255, 255);&quot;&gt;公司主要生产板式套房家具、实木家具、床垫、沙发、软床和定制家具、工程家具等系列产品，产品畅销全国，并远销欧美、东南亚多个国家和地区。公司主要生产板式套房家具、实木家具、床垫、沙发、软床和定制家具、工程家具等系列产品，产品畅销全国，并远销欧美、东南亚多个国家和地区。&lt;/span&gt;&lt;span style=&quot;font-family: &amp;quot;Lantinghei SC&amp;quot;, &amp;quot;microsoft yahei&amp;quot;, Tahoma, Helvetica, SimSun, sans-serif; font-size: 12px; text-indent: 24px; background-color: rgb(255, 255, 255);&quot;&gt;公司主要生产板式套房家具、实木家具、床垫、沙发、软床和定制家具、工程家具等系列产品，产品畅销全国，并远销欧美、东南亚多个国家和地区。公司主要生产板式套房家具、实木家具、床垫、沙发、软床和定制家具、工程家具等系列产品，产品畅销全国，并远销欧美、东南亚多个国家和地区。&lt;/span&gt;&lt;/p&gt;',1);
/*!40000 ALTER TABLE `about` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `id` varchar(10) NOT NULL,
  `passwd` varchar(34) NOT NULL,
  `power` varchar(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES ('123','250cf8b51c773f3f8dc8b4be867a9a02','2'),('admin','21232f297a57a5a743894a0e4a801fc3','1'),('news1','c7d81b6949462e2f1bb5fdf18dd1d006','2');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adminpower`
--

DROP TABLE IF EXISTS `adminpower`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adminpower` (
  `power` int(2) NOT NULL,
  `con` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`power`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adminpower`
--

LOCK TABLES `adminpower` WRITE;
/*!40000 ALTER TABLE `adminpower` DISABLE KEYS */;
INSERT INTO `adminpower` VALUES (1,'超级管理员'),(2,'新闻管理员');
/*!40000 ALTER TABLE `adminpower` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `copy`
--

DROP TABLE IF EXISTS `copy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `copy` (
  `id` int(2) NOT NULL,
  `name` varchar(100) NOT NULL,
  `add` varchar(300) NOT NULL,
  `icp` varchar(100) NOT NULL,
  `tel` varchar(50) DEFAULT NULL,
  `fax` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `copy`
--

LOCK TABLES `copy` WRITE;
/*!40000 ALTER TABLE `copy` DISABLE KEYS */;
INSERT INTO `copy` VALUES (1,'GREEN','河南省洛阳市XX家具有限公司','豫ICP备11013304号','86-28-66771818','11-01-20119211','kf@quanyou.com');
/*!40000 ALTER TABLE `copy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `firstf`
--

DROP TABLE IF EXISTS `firstf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firstf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(2) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `copy` varchar(255) DEFAULT NULL,
  `conner` text,
  `pic` varchar(500) DEFAULT NULL,
  `price` int(8) DEFAULT NULL,
  `pic_pic` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10014 DEFAULT CHARSET=utf8 COMMENT='首页下端';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firstf`
--

LOCK TABLES `firstf` WRITE;
/*!40000 ALTER TABLE `firstf` DISABLE KEYS */;
INSERT INTO `firstf` VALUES (1,'2','洛阳市洛龙区龙祥小区A座1室','河南省洛阳市洛龙区','123165465465418948941','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',800,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(2,'1','洛阳市洛龙区龙祥小区A座2室','河南省洛阳市洛龙区','154685132168156165','/toshop/Uploads/20170613/593f486cdfb10.jpg,/toshop/Uploads/20170613/593f486cc39d1.jpg,/toshop/Uploads/20170613/593f486ca41e2.jpg,/toshop/Uploads/20170613/593f486c87103.jpg,',800,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(3,'1','洛阳市洛龙区龙祥小区A座3室','河南省洛阳市洛龙区','45649816519841651','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',800,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(4,'2','洛阳市洛龙区龙祥小区A座4室','河南省洛阳市洛龙区','98498465198165498','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',500,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(5,'2','洛阳市洛龙区龙祥小区A座5室','河南省洛阳市洛龙区','98489416519841','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',500,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(6,'1','洛阳市洛龙区小区龙翔A坐7室','河南省洛阳市洛龙区','【粽享盛惠】仿古做旧工艺床，匠心雕凿老时光！5.20-5.30粽享置家特惠，自营商品购满6000立减500，享五包！更有奥克斯空调0元抽！去试手气','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',3800,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(7,'2','洛阳市洛龙区龙翔街B坐1室','河南省洛阳市洛龙区','轻简格调仿古床头柜，小巧精致！5.20-5.30粽享置家特惠，自营商品购满6000立减500，享五包！更有奥克斯空调0元抽！去试手气','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',1290,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(8,'1','洛阳市洛龙区龙翔街B坐2室','河南省洛阳市洛龙','轻快生活纯色布艺沙发，享自在！5.20-5.30粽享置家特惠，自营商品购满6000立减500，享五包！更有奥克斯空调0元抽！去试手气>>>','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',3219,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(9,'1','洛阳市洛龙区龙翔街B坐3室','河南省洛阳市洛龙','近5W买家良心推荐，上等白杨木框架更结实！5.20-5.30自营商品满6千减500享五包！还能0元抽玫瑰金iPhone7！更多优惠戳详情','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',4313,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(10,'2','洛阳市洛龙区龙翔街B坐4室','河南省洛阳市洛龙','可伸缩设计，乐享蜗居！5.20-5.30自营商品满6千减500享五包！更有劲爆床垫折扣！买俩8折，买仨7折！还有建材灯饰端午特惠！戳>>>','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',3200,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(11,'2','洛阳市洛龙区龙翔街B坐4室','河南省洛阳市洛龙','一胎的乐园，二胎的首选，进口芬兰松木环保更安心！5.20-30日自营商品满6000直减500元，享五包！更有iPhone7、奥克斯空调0元开抽！去看看GO>>>','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',5690,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(12,'2','洛阳市洛龙区龙翔街c坐1室','河南省洛阳市洛龙','端午粽享爆品惠，红包到店立减！5.15-5.30小户型多功能沙发领红包直省500元！集粽拿免单，红色iphone7、奥克斯空调0元抽，go>>>','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',1345,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(13,'2','洛阳市洛龙区龙翔街C坐3室','河南省洛阳市洛龙','进口芬兰松，高档油漆！5.20-5.30自营商品购满6千立减500，错过遗憾！更有0元抽iPhone7、奥克斯空调！戳>>>','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',1467,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(14,'1','洛阳市洛龙区龙翔街C坐5室','河南省洛阳市洛龙','坚实榆木，榫卯工艺！5.20-5.30自营商品购满2万立减2700，错过遗憾！更有0元抽iPhone7、奥克斯空调！戳>>>','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',5674,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(15,'1','洛阳市洛龙区龙祥小区A座6室','河南省洛阳市洛龙区','646516196132165984','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',789,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(16,'1','洛阳市洛龙区龙祥小区A座6室','洛阳市洛龙区龙祥小区A座6室','49865198651981','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',6565,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(17,'1','这是一个测试','同上','没有','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',666,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(18,'1','这是一个测试','这是一个测试','这是一个测试这是一个测试这是一个测试这是一个测试这是一个测试这是一个测试这是一个测试','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',333,'/toshop/Uploads/20170613/593f32fb063c0.jpg'),(19,'1','测试','测试','测试','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',168,'/toshop/Uploads/20170531/592eae40e0f1d.jpg'),(10010,'1','洛阳市洛龙区龙祥小区A座1室','河南省洛阳市洛龙区','首推产品001号','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',999,'/toshop/Uploads/20170531/592eae40e0f1d.jpg'),(10011,'2','洛阳市洛龙区龙祥小区A座2室','河南省洛阳市洛龙区','首推产品002号','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',998,'/toshop/Uploads/20170531/592eae40e0f1d.jpg'),(10012,'1','洛阳市洛龙区龙祥小区A座3室','河南省洛阳市洛龙区','首推产品003号','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,',997,'/toshop/Uploads/20170531/592eae40e0f1d.jpg'),(10013,'2','洛阳市洛龙区龙祥小区A座4室','河南省洛阳市洛龙区','首推产品004号','/toshop/Uploads/20170618/5945e23a1d008.jpg,/toshop/Uploads/20170618/5945e239f1e40.png,/toshop/Uploads/20170618/5945e239cf770.jpg,/toshop/Uploads/20170618/5945e239a43fe.jpg,',996,'/toshop/Uploads/20170618/5945e234c94a9.jpg');
/*!40000 ALTER TABLE `firstf` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `firstm`
--

DROP TABLE IF EXISTS `firstm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firstm` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `copy` varchar(255) DEFAULT NULL,
  `conner` text,
  `pic` varchar(500) DEFAULT '',
  `pic_pic` varchar(300) DEFAULT NULL,
  `type` int(2) DEFAULT NULL,
  `price` varchar(20) DEFAULT NULL,
  `time` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=10014 DEFAULT CHARSET=utf8 COMMENT='首页中间';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firstm`
--

LOCK TABLES `firstm` WRITE;
/*!40000 ALTER TABLE `firstm` DISABLE KEYS */;
INSERT INTO `firstm` VALUES (10010,'洛阳市洛龙区龙祥小区A座1室','河南省洛阳市洛龙区','首推产品001号','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,','/toshop/Uploads/20170531/592eae40e0f1d.jpg',1,'999','2017-5-8'),(10011,'洛阳市洛龙区龙祥小区A座2室','河南省洛阳市洛龙区','首推产品002号','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,','/toshop/Uploads/20170531/592eae40e0f1d.jpg',2,'998','2015-5-6'),(10012,'洛阳市洛龙区龙祥小区A座3室','河南省洛阳市洛龙区','首推产品003号','/toshop/Uploads/20170613/593f32fb95cc1.jpg,/toshop/Uploads/20170613/593f32fb614dd.jpg,/toshop/Uploads/20170613/593f32fb3d2b5.png,/toshop/Uploads/20170613/593f32fb063c0.jpg,','/toshop/Uploads/20170531/592eae40e0f1d.jpg',1,'997','2014-9-10'),(10013,'洛阳市洛龙区龙祥小区A座4室','河南省洛阳市洛龙区','首推产品004号','/toshop/Uploads/20170618/5945e23a1d008.jpg,/toshop/Uploads/20170618/5945e239f1e40.png,/toshop/Uploads/20170618/5945e239cf770.jpg,/toshop/Uploads/20170618/5945e239a43fe.jpg,','/toshop/Uploads/20170618/5945e234c94a9.jpg',2,'996','2017-06-18');
/*!40000 ALTER TABLE `firstm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ftype`
--

DROP TABLE IF EXISTS `ftype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ftype` (
  `type` int(11) NOT NULL AUTO_INCREMENT,
  `ftype` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ftype`
--

LOCK TABLES `ftype` WRITE;
/*!40000 ALTER TABLE `ftype` DISABLE KEYS */;
INSERT INTO `ftype` VALUES (1,'家庭家具'),(2,'办公家具');
/*!40000 ALTER TABLE `ftype` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ibox`
--

DROP TABLE IF EXISTS `ibox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibox` (
  `id` int(2) NOT NULL DEFAULT '0',
  `pic` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ibox`
--

LOCK TABLES `ibox` WRITE;
/*!40000 ALTER TABLE `ibox` DISABLE KEYS */;
INSERT INTO `ibox` VALUES (1,'/toshop/Uploads/20170608/59390daf0f258.png,/toshop/Uploads/20170608/59390daed003c.png,/toshop/Uploads/20170608/59390daea549a.jpg,/toshop/Uploads/20170608/59390dae86093.png,/toshop/Uploads/20170608/59390dae631f2.jpg,');
/*!40000 ALTER TABLE `ibox` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeid` char(9) DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `two` varchar(255) DEFAULT NULL,
  `time` varchar(20) DEFAULT NULL,
  `pic` varchar(500) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES (1,'202','叙利亚小镇疑遭化武袭击','习近平的“2017两会全记录”','','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(2,'201','大熊猫陪你过5.20','大熊猫陪你过5.20','2017/05/21','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(3,'201','大熊猫陪你过5.20','大熊猫陪你过5.20','2017/04/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(4,'202','叙利亚小镇疑遭化武袭击�','大熊猫陪你过5.20','2017/05/21','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(5,'202','叙利亚小镇疑遭化武袭击\0','大熊猫陪你过5.20','2017/05/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(6,'202','大熊猫陪你过5.20','大熊猫陪你过5.20','2017/05/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(7,'203','叙利亚小镇疑遭化武袭击\0','大熊猫陪你过5.20','2017/05/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(8,'201','叙利亚小镇疑遭化武袭击\0','大熊猫陪你过5.20','2017/05/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(9,'202','大熊猫陪你过5.20','大熊猫陪你过5.20','2017/05/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(10,'203','叙利亚小镇疑遭化武袭击\0','大熊猫陪你过5.20','2017/05/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(11,'203','叙利亚小镇疑遭化武袭击\0','大熊猫陪你过5.20','2017/05/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(12,'202','大熊猫陪你过5.20','大熊猫陪你过5.20','2017/05/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(13,'202','叙利亚小镇疑遭化武袭击\0','大熊猫陪你过5.20','2017/05/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(14,'203','大熊猫陪你过5.20','大熊猫陪你过5.20','2017/05/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(15,'203','大熊猫陪你过5.20','大熊猫陪你过5.20','2017/05/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(16,'203','大熊猫陪你过5.20','大熊猫陪你过5.20','2017/05/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(17,'202','叙利亚小镇疑遭化武袭击','大熊猫陪你过5.20','2017/05/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(18,'203','习近平同芬兰总统尼尼斯托举行会谈','大熊猫陪你过5.20','2017/05/20','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(19,'201','习近平同芬兰总统尼尼斯托举行会谈','习近平同芬兰总统尼尼斯托举行会谈','2013/05/02','/toshop/Uploads/20170608/59390a9fd4b4c.jpg','<p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">6月8日，央行网站显示，近日，中国人民银行、银监会、证监会、保监会、国家标准委等五部委联合发布“十三五”期间金融业标准化规划。规划全称为，《金融业标准化体系建设发展规划（2016-2020年）》（银发〔2017〕115号）（以下简称《规划》）。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909891125441.jpg\" title=\"1496909891125441.jpg\" alt=\"1496909891125441.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">《规划》明确要求，围绕统筹监管系统内重要性金融机构，统筹监管金融控股公司，和重要金融基础设施，统筹负责金融业综合统计，防范化解金融风险，加强重点标准研制和实施。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909940114494.png\" title=\"1496909940114494.png\" alt=\"1496909940114494.png\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\">一是建立新型金融业标准体系，全面覆盖金融产品与服务、金融基础设施、金融统计、金融监管与风险防控等领域；二是强化金融业标准实施，发挥政府、行业协会、认证机构、企业等各方面的作用；三是建立金融业标准监督评估体系，分类监督强制性标准和推荐性标准实施；四是持续推进金融国际标准化，在移动金融服务、非银行支付、数字货币等重点领域，加大对口专家排出力度，争取主导1-2项国际标准研制。</span></p><p style=\"text-align:center\"><span style=\"color: rgb(14, 14, 14); font-family: \"Microsoft YaHei\", 黑体; background-color: rgb(255, 255, 255);\"><img src=\"/ueup/image/20170608/1496909974377560.jpg\" title=\"1496909974377560.jpg\" alt=\"1496909974377560.jpg\" width=\"955\" height=\"368\"/></span></p><p style=\"text-indent: 2em;\"><br/></p>'),(20,'201','修正测试1','真的是测试','2017/06/17','/toshop/Uploads/20170617/59453c4dd40af.png','<p><em style=\"box-sizing: border-box;\">可以自定义排版图片和文字哦</em></p><p>自定义标题</p><p>段落格式</p><p>字体</p><p>字号</p><p><em style=\"box-sizing: border-box;\">可以自定义排版图片和文字哦</em></p><p>自定义标题</p><p>段落格式</p><p>字体</p><p>字号</p><p><em style=\"box-sizing: border-box;\">可以自定义排版图片和文字哦</em></p><p>自定义标题</p><p>段落格式</p><p>字体</p><p>字号</p><p><em style=\"box-sizing: border-box;\">可以自定义排版图片和文字哦</em></p><p>自定义标题</p><p>段落格式</p><p>字体</p><p>字号</p><p><br/></p>');
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sbox`
--

DROP TABLE IF EXISTS `sbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sbox` (
  `id` int(2) NOT NULL,
  `pic` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sbox`
--

LOCK TABLES `sbox` WRITE;
/*!40000 ALTER TABLE `sbox` DISABLE KEYS */;
INSERT INTO `sbox` VALUES (1,'/toshop/Uploads/20170614/5940e929b84ce.jpg,/toshop/Uploads/20170614/5940e9298e8cc.jpg,/toshop/Uploads/20170614/5940e929673db.jpg,/toshop/Uploads/20170614/5940e9293a510.jpg,');
/*!40000 ALTER TABLE `sbox` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `type`
--

DROP TABLE IF EXISTS `type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type` (
  `typeid` char(9) NOT NULL,
  `type` varchar(30) NOT NULL,
  PRIMARY KEY (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `type`
--

LOCK TABLES `type` WRITE;
/*!40000 ALTER TABLE `type` DISABLE KEYS */;
INSERT INTO `type` VALUES ('201','公司内部'),('202','媒体报道'),('203','国家要闻');
/*!40000 ALTER TABLE `type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `web`
--

DROP TABLE IF EXISTS `web`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `web` (
  `id` int(11) DEFAULT NULL,
  `value` char(4) NOT NULL,
  `con` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `web`
--

LOCK TABLES `web` WRITE;
/*!40000 ALTER TABLE `web` DISABLE KEYS */;
INSERT INTO `web` VALUES (0,'Y','站点因维护而暂停服务，将大约持续1小时以上。');
/*!40000 ALTER TABLE `web` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-06-19 20:32:52
