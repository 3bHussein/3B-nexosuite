-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: general_trading_erp
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ec_accounting_export_batches`
--

DROP TABLE IF EXISTS `ec_accounting_export_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_accounting_export_batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_number` varchar(120) NOT NULL,
  `export_type` varchar(120) DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `row_count` int(11) DEFAULT 0,
  `total_debit` decimal(14,2) DEFAULT 0.00,
  `total_credit` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `batch_number` (`batch_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_accounting_export_batches`
--

LOCK TABLES `ec_accounting_export_batches` WRITE;
/*!40000 ALTER TABLE `ec_accounting_export_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_accounting_export_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_accounting_periods`
--

DROP TABLE IF EXISTS `ec_accounting_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_accounting_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fiscal_year_id` int(11) DEFAULT NULL,
  `period_name` varchar(120) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(30) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_accounting_periods`
--

LOCK TABLES `ec_accounting_periods` WRITE;
/*!40000 ALTER TABLE `ec_accounting_periods` DISABLE KEYS */;
INSERT INTO `ec_accounting_periods` VALUES (1,1,'January 2026','2026-01-01','2026-01-31','open','2026-06-12 11:21:09'),(2,1,'February 2026','2026-02-01','2026-02-28','open','2026-06-12 11:21:09'),(3,1,'March 2026','2026-03-01','2026-03-31','open','2026-06-12 11:21:09'),(4,1,'April 2026','2026-04-01','2026-04-30','open','2026-06-12 11:21:09'),(5,1,'May 2026','2026-05-01','2026-05-31','open','2026-06-12 11:21:09'),(6,1,'June 2026','2026-06-01','2026-06-30','open','2026-06-12 11:21:09'),(7,1,'July 2026','2026-07-01','2026-07-31','open','2026-06-12 11:21:09'),(8,1,'August 2026','2026-08-01','2026-08-31','open','2026-06-12 11:21:09'),(9,1,'September 2026','2026-09-01','2026-09-30','open','2026-06-12 11:21:09'),(10,1,'October 2026','2026-10-01','2026-10-31','open','2026-06-12 11:21:09'),(11,1,'November 2026','2026-11-01','2026-11-30','open','2026-06-12 11:21:09'),(12,1,'December 2026','2026-12-01','2026-12-31','open','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_accounting_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_accounts`
--

DROP TABLE IF EXISTS `ec_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_code` varchar(40) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_type` varchar(40) NOT NULL,
  `normal_balance` varchar(20) DEFAULT 'debit',
  `parent_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_code` (`account_code`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_accounts`
--

LOCK TABLES `ec_accounts` WRITE;
/*!40000 ALTER TABLE `ec_accounts` DISABLE KEYS */;
INSERT INTO `ec_accounts` VALUES (1,'1000','Cash & Bank','asset','debit',NULL,'Primary cash and banking control account',1,'2026-06-12 11:21:09'),(2,'1100','Accounts Receivable','asset','debit',NULL,'Customer receivables generated from invoices',1,'2026-06-12 11:21:09'),(3,'1200','Inventory Asset','asset','debit',NULL,'Stock asset control account for moving-average inventory valuation',1,'2026-06-12 11:21:09'),(4,'1300','VAT Input Recoverable','asset','debit',NULL,'Recoverable input tax on expenses and purchases',1,'2026-06-12 11:21:09'),(5,'1400','Intercompany Due From','asset','debit',NULL,'Receivable balance owed by affiliated entities after intercompany stock movement',1,'2026-06-12 11:21:09'),(6,'2000','Accounts Payable','liability','credit',NULL,'Supplier liabilities and unpaid expenses',1,'2026-06-12 11:21:09'),(7,'2200','Intercompany Due To','liability','credit',NULL,'Payable balance owed to affiliated entities after intercompany stock movement',1,'2026-06-12 11:21:09'),(8,'2100','VAT Output Payable','liability','credit',NULL,'Output tax collected on taxable sales',1,'2026-06-12 11:21:09'),(9,'3000','Retained Earnings','equity','credit',NULL,'Equity / accumulated earnings',1,'2026-06-12 11:21:09'),(10,'4000','Sales Revenue','revenue','credit',NULL,'Revenue posted from approved invoices',1,'2026-06-12 11:21:09'),(11,'5000','Cost of Goods Sold','expense','debit',NULL,'COGS placeholder for future inventory costing',1,'2026-06-12 11:21:09'),(12,'6100','Operating Expenses','expense','debit',NULL,'Operating expenses captured from finance module',1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_activity_log`
--

DROP TABLE IF EXISTS `ec_activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `actor_user_id` int(11) DEFAULT NULL,
  `module` varchar(80) DEFAULT NULL,
  `action` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `reference_type` varchar(80) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_activity_log`
--

LOCK TABLES `ec_activity_log` WRITE;
/*!40000 ALTER TABLE `ec_activity_log` DISABLE KEYS */;
INSERT INTO `ec_activity_log` VALUES (1,1,1,1,'System','installation','General Trading ERP & E-commerce installation completed.',NULL,NULL,'2026-06-12 11:21:09'),(2,1,1,1,'Quotation','seed','Sample quotation QTN-1001 created.','quotation',1,'2026-06-12 11:21:09'),(3,1,1,1,'Procurement','seed','Sample purchase order PO-1001 created.','purchase_order',1,'2026-06-12 11:21:09'),(4,1,1,1,'Finance','seed','Sample payment PAY-1001 recorded.','payment',1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ai_assistant_action_suggestions`
--

DROP TABLE IF EXISTS `ec_ai_assistant_action_suggestions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ai_assistant_action_suggestions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `suggestion_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `module` varchar(120) DEFAULT NULL,
  `suggestion_title` varchar(255) DEFAULT NULL,
  `suggestion_text` text DEFAULT NULL,
  `action_label` varchar(160) DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `priority` varchar(40) DEFAULT 'medium',
  `status` varchar(40) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `suggestion_number` (`suggestion_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ai_assistant_action_suggestions`
--

LOCK TABLES `ec_ai_assistant_action_suggestions` WRITE;
/*!40000 ALTER TABLE `ec_ai_assistant_action_suggestions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ai_assistant_action_suggestions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ai_assistant_messages`
--

DROP TABLE IF EXISTS `ec_ai_assistant_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ai_assistant_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ai_assistant_session_id` int(11) NOT NULL,
  `sender_type` varchar(40) DEFAULT 'user',
  `message_text` longtext DEFAULT NULL,
  `intent_key` varchar(120) DEFAULT NULL,
  `confidence_score` decimal(5,2) DEFAULT 0.00,
  `action_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ai_assistant_messages`
--

LOCK TABLES `ec_ai_assistant_messages` WRITE;
/*!40000 ALTER TABLE `ec_ai_assistant_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ai_assistant_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ai_assistant_playbooks`
--

DROP TABLE IF EXISTS `ec_ai_assistant_playbooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ai_assistant_playbooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playbook_code` varchar(120) NOT NULL,
  `playbook_name` varchar(255) DEFAULT NULL,
  `module` varchar(120) DEFAULT NULL,
  `trigger_phrase` varchar(255) DEFAULT NULL,
  `response_template` text DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `playbook_code` (`playbook_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ai_assistant_playbooks`
--

LOCK TABLES `ec_ai_assistant_playbooks` WRITE;
/*!40000 ALTER TABLE `ec_ai_assistant_playbooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ai_assistant_playbooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ai_assistant_sessions`
--

DROP TABLE IF EXISTS `ec_ai_assistant_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ai_assistant_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_number` varchar(120) NOT NULL,
  `session_title` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `module_context` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_number` (`session_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ai_assistant_sessions`
--

LOCK TABLES `ec_ai_assistant_sessions` WRITE;
/*!40000 ALTER TABLE `ec_ai_assistant_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ai_assistant_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ai_automation_run_items`
--

DROP TABLE IF EXISTS `ec_ai_automation_run_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ai_automation_run_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ai_automation_run_id` int(11) NOT NULL,
  `item_type` varchar(120) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_label` varchar(255) DEFAULT NULL,
  `score` decimal(8,2) DEFAULT 0.00,
  `result_status` varchar(40) DEFAULT 'processed',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ai_automation_run_items`
--

LOCK TABLES `ec_ai_automation_run_items` WRITE;
/*!40000 ALTER TABLE `ec_ai_automation_run_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ai_automation_run_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ai_automation_runs`
--

DROP TABLE IF EXISTS `ec_ai_automation_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ai_automation_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `run_number` varchar(120) NOT NULL,
  `run_type` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'completed',
  `items_scored` int(11) DEFAULT 0,
  `recommendations_created` int(11) DEFAULT 0,
  `alerts_created` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `run_number` (`run_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ai_automation_runs`
--

LOCK TABLES `ec_ai_automation_runs` WRITE;
/*!40000 ALTER TABLE `ec_ai_automation_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ai_automation_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ai_decision_recommendations`
--

DROP TABLE IF EXISTS `ec_ai_decision_recommendations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ai_decision_recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recommendation_number` varchar(120) NOT NULL,
  `source_type` varchar(120) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `module` varchar(120) DEFAULT NULL,
  `recommendation_title` varchar(255) DEFAULT NULL,
  `recommendation_text` text DEFAULT NULL,
  `confidence_score` decimal(8,2) DEFAULT 0.00,
  `impact_score` decimal(8,2) DEFAULT 0.00,
  `effort_score` decimal(8,2) DEFAULT 0.00,
  `priority` varchar(40) DEFAULT 'medium',
  `status` varchar(40) DEFAULT 'open',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `recommendation_number` (`recommendation_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ai_decision_recommendations`
--

LOCK TABLES `ec_ai_decision_recommendations` WRITE;
/*!40000 ALTER TABLE `ec_ai_decision_recommendations` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ai_decision_recommendations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ai_insight_cards`
--

DROP TABLE IF EXISTS `ec_ai_insight_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ai_insight_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_code` varchar(120) NOT NULL,
  `card_title` varchar(255) DEFAULT NULL,
  `card_group` varchar(120) DEFAULT 'General',
  `severity` varchar(40) DEFAULT 'info',
  `summary` text DEFAULT NULL,
  `recommendation` text DEFAULT NULL,
  `source_module` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `card_code` (`card_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ai_insight_cards`
--

LOCK TABLES `ec_ai_insight_cards` WRITE;
/*!40000 ALTER TABLE `ec_ai_insight_cards` DISABLE KEYS */;
INSERT INTO `ec_ai_insight_cards` VALUES (1,'AIC-SALES-001','Sales Follow-up Focus','Sales','info','Open quotations and opportunities need consistent follow-up.','Check pipeline and contact hot/warm leads first.','CRM','open','2026-06-12 11:21:09'),(2,'AIC-STOCK-001','Inventory Risk Watch','Inventory','warning','Low-stock items can block order fulfillment.','Run low-stock report and create reorder actions.','Inventory','open','2026-06-12 11:21:09'),(3,'AIC-FIN-001','Receivable Control','Finance','warning','Unpaid invoices may affect cash flow.','Review open receivables and send reminders.','Finance','open','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_ai_insight_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ai_recommendation_feedback`
--

DROP TABLE IF EXISTS `ec_ai_recommendation_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ai_recommendation_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ai_decision_recommendation_id` int(11) DEFAULT NULL,
  `recommendation_result_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `feedback_type` varchar(40) DEFAULT 'useful',
  `feedback_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ai_recommendation_feedback`
--

LOCK TABLES `ec_ai_recommendation_feedback` WRITE;
/*!40000 ALTER TABLE `ec_ai_recommendation_feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ai_recommendation_feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ai_risk_scores`
--

DROP TABLE IF EXISTS `ec_ai_risk_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ai_risk_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `score_number` varchar(120) NOT NULL,
  `model_code` varchar(120) DEFAULT NULL,
  `entity_type` varchar(120) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `entity_label` varchar(255) DEFAULT NULL,
  `module` varchar(120) DEFAULT NULL,
  `risk_score` decimal(8,2) DEFAULT 0.00,
  `risk_level` varchar(40) DEFAULT 'medium',
  `reason` text DEFAULT NULL,
  `recommended_action` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `score_number` (`score_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ai_risk_scores`
--

LOCK TABLES `ec_ai_risk_scores` WRITE;
/*!40000 ALTER TABLE `ec_ai_risk_scores` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ai_risk_scores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ai_scoring_models`
--

DROP TABLE IF EXISTS `ec_ai_scoring_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ai_scoring_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_code` varchar(120) NOT NULL,
  `model_name` varchar(255) NOT NULL,
  `model_type` varchar(120) DEFAULT 'risk',
  `module` varchar(120) DEFAULT 'General',
  `score_formula_json` longtext DEFAULT NULL,
  `threshold_low` decimal(8,2) DEFAULT 30.00,
  `threshold_medium` decimal(8,2) DEFAULT 60.00,
  `threshold_high` decimal(8,2) DEFAULT 80.00,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `model_code` (`model_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ai_scoring_models`
--

LOCK TABLES `ec_ai_scoring_models` WRITE;
/*!40000 ALTER TABLE `ec_ai_scoring_models` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ai_scoring_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_anomaly_detections`
--

DROP TABLE IF EXISTS `ec_anomaly_detections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_anomaly_detections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anomaly_number` varchar(120) NOT NULL,
  `anomaly_type` varchar(120) DEFAULT NULL,
  `module` varchar(120) DEFAULT NULL,
  `severity` varchar(40) DEFAULT 'medium',
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `baseline_value` decimal(14,2) DEFAULT 0.00,
  `observed_value` decimal(14,2) DEFAULT 0.00,
  `variance_value` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `anomaly_number` (`anomaly_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_anomaly_detections`
--

LOCK TABLES `ec_anomaly_detections` WRITE;
/*!40000 ALTER TABLE `ec_anomaly_detections` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_anomaly_detections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ap_aging_snapshots`
--

DROP TABLE IF EXISTS `ec_ap_aging_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ap_aging_snapshots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `snapshot_number` varchar(120) NOT NULL,
  `snapshot_date` date NOT NULL,
  `current_amount` decimal(14,2) DEFAULT 0.00,
  `days_1_30` decimal(14,2) DEFAULT 0.00,
  `days_31_60` decimal(14,2) DEFAULT 0.00,
  `days_61_90` decimal(14,2) DEFAULT 0.00,
  `days_over_90` decimal(14,2) DEFAULT 0.00,
  `total_amount` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'generated',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `snapshot_number` (`snapshot_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ap_aging_snapshots`
--

LOCK TABLES `ec_ap_aging_snapshots` WRITE;
/*!40000 ALTER TABLE `ec_ap_aging_snapshots` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ap_aging_snapshots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_api_access_logs`
--

DROP TABLE IF EXISTS `ec_api_access_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_api_access_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key_id` int(11) DEFAULT NULL,
  `endpoint` varchar(255) DEFAULT NULL,
  `method` varchar(20) DEFAULT NULL,
  `status_code` int(11) DEFAULT 0,
  `ip_address` varchar(80) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_api_access_logs`
--

LOCK TABLES `ec_api_access_logs` WRITE;
/*!40000 ALTER TABLE `ec_api_access_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_api_access_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_api_endpoint_catalog`
--

DROP TABLE IF EXISTS `ec_api_endpoint_catalog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_api_endpoint_catalog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `endpoint_code` varchar(120) NOT NULL,
  `endpoint_name` varchar(255) DEFAULT NULL,
  `http_method` varchar(20) DEFAULT 'GET',
  `route_path` varchar(255) DEFAULT NULL,
  `module` varchar(120) DEFAULT NULL,
  `required_scope` varchar(160) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `request_example` longtext DEFAULT NULL,
  `response_example` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `endpoint_code` (`endpoint_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_api_endpoint_catalog`
--

LOCK TABLES `ec_api_endpoint_catalog` WRITE;
/*!40000 ALTER TABLE `ec_api_endpoint_catalog` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_api_endpoint_catalog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_api_keys`
--

DROP TABLE IF EXISTS `ec_api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_name` varchar(180) NOT NULL,
  `key_prefix` varchar(40) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `scopes` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_api_prefix` (`key_prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_api_keys`
--

LOCK TABLES `ec_api_keys` WRITE;
/*!40000 ALTER TABLE `ec_api_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_api_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_api_marketplace_apps`
--

DROP TABLE IF EXISTS `ec_api_marketplace_apps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_api_marketplace_apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_code` varchar(120) NOT NULL,
  `app_name` varchar(255) DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `default_scopes` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'available',
  `doc_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_code` (`app_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_api_marketplace_apps`
--

LOCK TABLES `ec_api_marketplace_apps` WRITE;
/*!40000 ALTER TABLE `ec_api_marketplace_apps` DISABLE KEYS */;
INSERT INTO `ec_api_marketplace_apps` VALUES (1,'STORE-API','Storefront Commerce API','Commerce','Read products, categories, and publish order data for external sales channels.','read:products,read:orders,write:orders','available','/developer-docs.php','2026-06-12 11:21:09'),(2,'INVENTORY-API','Inventory Sync API','Inventory','Sync stock levels, low-stock alerts, and warehouse quantities.','read:inventory,write:inventory','available','/developer-docs.php','2026-06-12 11:21:09'),(3,'CRM-API','CRM Lead API','CRM','Create leads, update lead status, and connect landing pages or ad forms.','read:crm,write:crm','available','/developer-docs.php','2026-06-12 11:21:09'),(4,'REPORTING-API','Reporting API','Analytics','Pull saved report data, KPI snapshots, and management dashboard data.','read:reports,read:kpis','available','/developer-docs.php','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_api_marketplace_apps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_api_scope_policies`
--

DROP TABLE IF EXISTS `ec_api_scope_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_api_scope_policies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scope_key` varchar(160) NOT NULL,
  `scope_name` varchar(255) DEFAULT NULL,
  `module` varchar(120) DEFAULT NULL,
  `access_level` varchar(40) DEFAULT 'read',
  `description` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `scope_key` (`scope_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_api_scope_policies`
--

LOCK TABLES `ec_api_scope_policies` WRITE;
/*!40000 ALTER TABLE `ec_api_scope_policies` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_api_scope_policies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_api_usage_counters`
--

DROP TABLE IF EXISTS `ec_api_usage_counters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_api_usage_counters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `scope_key` varchar(160) DEFAULT NULL,
  `counter_date` date NOT NULL,
  `request_count` int(11) DEFAULT 0,
  `last_request_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_api_usage_counter` (`api_key_id`,`scope_key`,`counter_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_api_usage_counters`
--

LOCK TABLES `ec_api_usage_counters` WRITE;
/*!40000 ALTER TABLE `ec_api_usage_counters` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_api_usage_counters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_api_usage_limits`
--

DROP TABLE IF EXISTS `ec_api_usage_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_api_usage_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `limit_number` varchar(120) NOT NULL,
  `api_key_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `scope_key` varchar(160) DEFAULT NULL,
  `limit_window` varchar(40) DEFAULT 'daily',
  `request_limit` int(11) DEFAULT 1000,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `limit_number` (`limit_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_api_usage_limits`
--

LOCK TABLES `ec_api_usage_limits` WRITE;
/*!40000 ALTER TABLE `ec_api_usage_limits` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_api_usage_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_approval_logs`
--

DROP TABLE IF EXISTS `ec_approval_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_approval_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `approval_request_id` int(11) NOT NULL,
  `approval_request_step_id` int(11) DEFAULT NULL,
  `actor_user_id` int(11) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_approval_logs`
--

LOCK TABLES `ec_approval_logs` WRITE;
/*!40000 ALTER TABLE `ec_approval_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_approval_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_approval_request_steps`
--

DROP TABLE IF EXISTS `ec_approval_request_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_approval_request_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `approval_request_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL,
  `step_label` varchar(180) DEFAULT NULL,
  `approver_role_slug` varchar(160) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `decided_by` int(11) DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `decision_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_request_step` (`approval_request_id`,`step_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_approval_request_steps`
--

LOCK TABLES `ec_approval_request_steps` WRITE;
/*!40000 ALTER TABLE `ec_approval_request_steps` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_approval_request_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_approval_requests`
--

DROP TABLE IF EXISTS `ec_approval_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_approval_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_number` varchar(120) NOT NULL,
  `approval_rule_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `document_type` varchar(80) NOT NULL,
  `document_id` int(11) NOT NULL,
  `document_number` varchar(160) DEFAULT NULL,
  `action_key` varchar(80) NOT NULL,
  `request_amount` decimal(14,2) DEFAULT 0.00,
  `request_discount` decimal(14,2) DEFAULT 0.00,
  `maker_user_id` int(11) DEFAULT NULL,
  `current_step` int(11) DEFAULT 1,
  `status` varchar(50) DEFAULT 'pending',
  `submitted_at` datetime DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_number` (`request_number`),
  KEY `idx_approval_document` (`document_type`,`document_id`,`action_key`),
  KEY `idx_approval_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_approval_requests`
--

LOCK TABLES `ec_approval_requests` WRITE;
/*!40000 ALTER TABLE `ec_approval_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_approval_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_approval_rule_steps`
--

DROP TABLE IF EXISTS `ec_approval_rule_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_approval_rule_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `approval_rule_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL,
  `step_label` varchar(180) DEFAULT NULL,
  `approver_role_slug` varchar(160) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rule_step` (`approval_rule_id`,`step_number`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_approval_rule_steps`
--

LOCK TABLES `ec_approval_rule_steps` WRITE;
/*!40000 ALTER TABLE `ec_approval_rule_steps` DISABLE KEYS */;
INSERT INTO `ec_approval_rule_steps` VALUES (1,1,1,'Finance review','finance','2026-06-12 11:21:09'),(2,1,2,'ERP manager approval','erp-manager','2026-06-12 11:21:09'),(3,2,1,'Inventory or procurement review','inventory-procurement','2026-06-12 11:21:09'),(4,3,1,'Finance review','finance','2026-06-12 11:21:09'),(5,4,1,'Finance discount review','finance','2026-06-12 11:21:09'),(6,5,1,'Finance discount review','finance','2026-06-12 11:21:09'),(7,6,1,'Procurement review','inventory-procurement','2026-06-12 11:21:09'),(8,6,2,'ERP manager sign-off','erp-manager','2026-06-12 11:21:09'),(9,7,1,'Finance credit control','finance','2026-06-12 11:21:09'),(10,8,1,'Sales return review','sales-online-orders','2026-06-12 11:21:09'),(11,9,1,'Finance AP variance review','finance','2026-06-12 11:21:09'),(12,10,1,'Warranty / finance review','finance','2026-06-12 11:21:09'),(13,11,1,'Finance budget review','finance','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_approval_rule_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_approval_rules`
--

DROP TABLE IF EXISTS `ec_approval_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_approval_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_code` varchar(120) NOT NULL,
  `rule_name` varchar(255) NOT NULL,
  `document_type` varchar(80) NOT NULL,
  `action_key` varchar(80) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `min_amount` decimal(14,2) DEFAULT 0.00,
  `max_amount` decimal(14,2) DEFAULT NULL,
  `min_discount` decimal(14,2) DEFAULT 0.00,
  `max_discount` decimal(14,2) DEFAULT NULL,
  `approval_mode` varchar(40) DEFAULT 'sequential',
  `maker_checker` tinyint(1) DEFAULT 1,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_code` (`rule_code`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_approval_rules`
--

LOCK TABLES `ec_approval_rules` WRITE;
/*!40000 ALTER TABLE `ec_approval_rules` DISABLE KEYS */;
INSERT INTO `ec_approval_rules` VALUES (1,'APR-PO-10000','Purchase orders above AED 10,000','purchase_order','approve',NULL,NULL,10000.00,NULL,0.00,NULL,'sequential',1,1,'2026-06-12 11:21:09'),(2,'APR-TRF-ALL','Stock transfers require maker-checker approval','stock_transfer','approve',NULL,NULL,0.00,NULL,0.00,NULL,'sequential',1,1,'2026-06-12 11:21:09'),(3,'APR-EXP-1000','Expenses above AED 1,000','expense','approve',NULL,NULL,1000.00,NULL,0.00,NULL,'sequential',1,1,'2026-06-12 11:21:09'),(4,'APR-INV-DISC-500','Invoice discounts AED 500 or above','invoice','approve',NULL,NULL,0.00,NULL,500.00,NULL,'sequential',1,1,'2026-06-12 11:21:09'),(5,'APR-QTN-DISC-500','Quotation discounts AED 500 or above','quotation','accept',NULL,NULL,0.00,NULL,500.00,NULL,'sequential',1,1,'2026-06-12 11:21:09'),(6,'APR-REQ-5000','Purchase requisitions above AED 5,000','purchase_requisition','approve',NULL,NULL,5000.00,NULL,0.00,NULL,'sequential',1,1,'2026-06-12 11:21:09'),(7,'APR-SO-CREDIT','Sales order credit override','sales_order','credit_override',NULL,NULL,0.00,NULL,0.00,NULL,'sequential',1,1,'2026-06-12 11:21:09'),(8,'APR-RMA-1000','Customer returns above AED 1,000','return_rma','approve',NULL,NULL,1000.00,NULL,0.00,NULL,'sequential',1,1,'2026-06-12 11:21:09'),(9,'APR-SINV-100','Supplier invoice variance above AED 100','supplier_invoice','approve',NULL,NULL,0.00,NULL,100.00,NULL,'sequential',1,1,'2026-06-12 11:21:09'),(10,'APR-WCL-1000','Warranty claims above AED 1,000','warranty_claim','approve',NULL,NULL,1000.00,NULL,0.00,NULL,'sequential',1,1,'2026-06-12 11:21:09'),(11,'APR-BUDGET-OVERRIDE','Budget override requests','project','budget_override',NULL,NULL,0.00,NULL,0.00,NULL,'sequential',1,1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_approval_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ar_aging_snapshots`
--

DROP TABLE IF EXISTS `ec_ar_aging_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ar_aging_snapshots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `snapshot_number` varchar(120) NOT NULL,
  `snapshot_date` date NOT NULL,
  `current_amount` decimal(14,2) DEFAULT 0.00,
  `days_1_30` decimal(14,2) DEFAULT 0.00,
  `days_31_60` decimal(14,2) DEFAULT 0.00,
  `days_61_90` decimal(14,2) DEFAULT 0.00,
  `days_over_90` decimal(14,2) DEFAULT 0.00,
  `total_amount` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'generated',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `snapshot_number` (`snapshot_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ar_aging_snapshots`
--

LOCK TABLES `ec_ar_aging_snapshots` WRITE;
/*!40000 ALTER TABLE `ec_ar_aging_snapshots` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ar_aging_snapshots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_asset_qr_codes`
--

DROP TABLE IF EXISTS `ec_asset_qr_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_asset_qr_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `qr_number` varchar(120) NOT NULL,
  `customer_asset_id` int(11) DEFAULT NULL,
  `job_card_id` int(11) DEFAULT NULL,
  `qr_value` varchar(255) NOT NULL,
  `qr_url` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `qr_number` (`qr_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_asset_qr_codes`
--

LOCK TABLES `ec_asset_qr_codes` WRITE;
/*!40000 ALTER TABLE `ec_asset_qr_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_asset_qr_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_attendance_records`
--

DROP TABLE IF EXISTS `ec_attendance_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_attendance_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `work_date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `regular_hours` decimal(8,2) DEFAULT 0.00,
  `overtime_hours` decimal(8,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_employee_workday` (`employee_id`,`work_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_attendance_records`
--

LOCK TABLES `ec_attendance_records` WRITE;
/*!40000 ALTER TABLE `ec_attendance_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_attendance_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_audit_control_findings`
--

DROP TABLE IF EXISTS `ec_audit_control_findings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_audit_control_findings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `audit_control_id` int(11) NOT NULL,
  `test_date` date DEFAULT NULL,
  `result` varchar(40) DEFAULT 'passed',
  `severity` varchar(40) DEFAULT 'low',
  `finding_notes` text DEFAULT NULL,
  `remediation_status` varchar(40) DEFAULT 'open',
  `due_date` date DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_audit_control_findings`
--

LOCK TABLES `ec_audit_control_findings` WRITE;
/*!40000 ALTER TABLE `ec_audit_control_findings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_audit_control_findings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_audit_controls`
--

DROP TABLE IF EXISTS `ec_audit_controls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_audit_controls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `control_code` varchar(120) NOT NULL,
  `control_name` varchar(255) DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `risk_level` varchar(40) DEFAULT 'medium',
  `frequency` varchar(80) DEFAULT 'monthly',
  `owner_user_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `control_code` (`control_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_audit_controls`
--

LOCK TABLES `ec_audit_controls` WRITE;
/*!40000 ALTER TABLE `ec_audit_controls` DISABLE KEYS */;
INSERT INTO `ec_audit_controls` VALUES (1,'CTRL-BANK-REC','Monthly bank reconciliation review','Finance','high','monthly',NULL,'active','Bank reconciliation must be prepared and reviewed before period close.','2026-06-12 11:21:09'),(2,'CTRL-VAT-FILE','VAT return review before filing','Tax','high','quarterly',NULL,'active','VAT/tax return must be reconciled against invoices, expenses, and ledger balances.','2026-06-12 11:21:09'),(3,'CTRL-FA-DEP','Fixed asset depreciation review','Accounting','medium','monthly',NULL,'active','Depreciation run must be reviewed before financial close.','2026-06-12 11:21:09'),(4,'CTRL-PAYROLL','Payroll approval control','HR/Finance','high','monthly',NULL,'active','Payroll must be approved before posting and payment.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_audit_controls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_b2b_price_list_items`
--

DROP TABLE IF EXISTS `ec_b2b_price_list_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_b2b_price_list_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `b2b_price_list_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sku` varchar(120) DEFAULT NULL,
  `base_price` decimal(14,2) DEFAULT 0.00,
  `special_price` decimal(14,2) DEFAULT 0.00,
  `min_quantity` decimal(12,2) DEFAULT 1.00,
  `max_quantity` decimal(12,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_b2b_price_item` (`b2b_price_list_id`,`product_id`,`min_quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_b2b_price_list_items`
--

LOCK TABLES `ec_b2b_price_list_items` WRITE;
/*!40000 ALTER TABLE `ec_b2b_price_list_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_b2b_price_list_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_b2b_price_lists`
--

DROP TABLE IF EXISTS `ec_b2b_price_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_b2b_price_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `price_list_number` varchar(120) NOT NULL,
  `price_list_name` varchar(255) NOT NULL,
  `customer_type` varchar(40) DEFAULT 'b2b',
  `currency` varchar(20) DEFAULT 'AED',
  `discount_percent` decimal(8,2) DEFAULT 0.00,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_list_number` (`price_list_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_b2b_price_lists`
--

LOCK TABLES `ec_b2b_price_lists` WRITE;
/*!40000 ALTER TABLE `ec_b2b_price_lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_b2b_price_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_backup_jobs`
--

DROP TABLE IF EXISTS `ec_backup_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_backup_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_number` varchar(120) NOT NULL,
  `backup_type` varchar(80) DEFAULT 'database',
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT 0,
  `status` varchar(40) DEFAULT 'created',
  `created_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `backup_number` (`backup_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_backup_jobs`
--

LOCK TABLES `ec_backup_jobs` WRITE;
/*!40000 ALTER TABLE `ec_backup_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_backup_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bank_accounts`
--

DROP TABLE IF EXISTS `ec_bank_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bank_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_name` varchar(255) NOT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(120) DEFAULT NULL,
  `currency` varchar(20) DEFAULT 'AED',
  `cash_account_id` int(11) DEFAULT NULL,
  `opening_balance` decimal(14,2) DEFAULT 0.00,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bank_accounts`
--

LOCK TABLES `ec_bank_accounts` WRITE;
/*!40000 ALTER TABLE `ec_bank_accounts` DISABLE KEYS */;
INSERT INTO `ec_bank_accounts` VALUES (1,'Main Bank / Cash Control','Primary Bank','','AED',1,0.00,1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_bank_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bank_reconciliation_items`
--

DROP TABLE IF EXISTS `ec_bank_reconciliation_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bank_reconciliation_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_reconciliation_id` int(11) NOT NULL,
  `bank_statement_line_id` int(11) DEFAULT NULL,
  `matched_journal_line_id` int(11) DEFAULT NULL,
  `amount` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'unmatched',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bank_reconciliation_items`
--

LOCK TABLES `ec_bank_reconciliation_items` WRITE;
/*!40000 ALTER TABLE `ec_bank_reconciliation_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_bank_reconciliation_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bank_reconciliations`
--

DROP TABLE IF EXISTS `ec_bank_reconciliations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bank_reconciliations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reconciliation_number` varchar(120) NOT NULL,
  `bank_account_id` int(11) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `statement_ending_balance` decimal(14,2) DEFAULT 0.00,
  `book_ending_balance` decimal(14,2) DEFAULT 0.00,
  `variance` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'draft',
  `reconciled_by` int(11) DEFAULT NULL,
  `reconciled_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reconciliation_number` (`reconciliation_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bank_reconciliations`
--

LOCK TABLES `ec_bank_reconciliations` WRITE;
/*!40000 ALTER TABLE `ec_bank_reconciliations` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_bank_reconciliations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bank_statement_lines`
--

DROP TABLE IF EXISTS `ec_bank_statement_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bank_statement_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_account_id` int(11) NOT NULL,
  `statement_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `reference` varchar(120) DEFAULT NULL,
  `debit` decimal(14,2) DEFAULT 0.00,
  `credit` decimal(14,2) DEFAULT 0.00,
  `matched_journal_line_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'unmatched',
  `reconciled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bank_statement_lines`
--

LOCK TABLES `ec_bank_statement_lines` WRITE;
/*!40000 ALTER TABLE `ec_bank_statement_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_bank_statement_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_barcode_scan_logs`
--

DROP TABLE IF EXISTS `ec_barcode_scan_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_barcode_scan_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scan_value` varchar(255) NOT NULL,
  `scan_type` varchar(80) DEFAULT 'qr',
  `source_module` varchar(120) DEFAULT NULL,
  `reference_type` varchar(120) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `scanned_by` int(11) DEFAULT NULL,
  `scan_result` varchar(120) DEFAULT 'captured',
  `scanned_at` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_barcode_scan_logs`
--

LOCK TABLES `ec_barcode_scan_logs` WRITE;
/*!40000 ALTER TABLE `ec_barcode_scan_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_barcode_scan_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bi_dashboard_filter_presets`
--

DROP TABLE IF EXISTS `ec_bi_dashboard_filter_presets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bi_dashboard_filter_presets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `preset_number` varchar(120) NOT NULL,
  `preset_name` varchar(255) DEFAULT NULL,
  `dashboard_scope` varchar(120) DEFAULT 'executive',
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `filter_json` longtext DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `preset_number` (`preset_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bi_dashboard_filter_presets`
--

LOCK TABLES `ec_bi_dashboard_filter_presets` WRITE;
/*!40000 ALTER TABLE `ec_bi_dashboard_filter_presets` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_bi_dashboard_filter_presets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bi_dashboard_shares`
--

DROP TABLE IF EXISTS `ec_bi_dashboard_shares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bi_dashboard_shares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `share_number` varchar(120) NOT NULL,
  `management_dashboard_id` int(11) DEFAULT NULL,
  `shared_with_user_id` int(11) DEFAULT NULL,
  `shared_role` varchar(120) DEFAULT NULL,
  `permission_level` varchar(40) DEFAULT 'view',
  `expires_at` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `share_number` (`share_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bi_dashboard_shares`
--

LOCK TABLES `ec_bi_dashboard_shares` WRITE;
/*!40000 ALTER TABLE `ec_bi_dashboard_shares` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_bi_dashboard_shares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bi_kpi_alert_events`
--

DROP TABLE IF EXISTS `ec_bi_kpi_alert_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bi_kpi_alert_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bi_kpi_alert_rule_id` int(11) NOT NULL,
  `kpi_snapshot_id` int(11) DEFAULT NULL,
  `metric_value` decimal(14,2) DEFAULT 0.00,
  `threshold_value` decimal(14,2) DEFAULT 0.00,
  `severity` varchar(40) DEFAULT 'warning',
  `status` varchar(40) DEFAULT 'open',
  `event_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bi_kpi_alert_events`
--

LOCK TABLES `ec_bi_kpi_alert_events` WRITE;
/*!40000 ALTER TABLE `ec_bi_kpi_alert_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_bi_kpi_alert_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bi_kpi_alert_rules`
--

DROP TABLE IF EXISTS `ec_bi_kpi_alert_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bi_kpi_alert_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_number` varchar(120) NOT NULL,
  `report_kpi_id` int(11) NOT NULL,
  `condition_type` varchar(40) DEFAULT 'below_target',
  `threshold_value` decimal(14,2) DEFAULT 0.00,
  `severity` varchar(40) DEFAULT 'warning',
  `notify_roles` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `last_triggered_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_number` (`rule_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bi_kpi_alert_rules`
--

LOCK TABLES `ec_bi_kpi_alert_rules` WRITE;
/*!40000 ALTER TABLE `ec_bi_kpi_alert_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_bi_kpi_alert_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bi_metric_library`
--

DROP TABLE IF EXISTS `ec_bi_metric_library`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bi_metric_library` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metric_code` varchar(120) NOT NULL,
  `metric_name` varchar(255) NOT NULL,
  `metric_group` varchar(120) DEFAULT 'General',
  `metric_source` varchar(120) DEFAULT 'custom',
  `calculation_type` varchar(80) DEFAULT 'sum',
  `unit_label` varchar(40) DEFAULT '',
  `target_value` decimal(14,2) DEFAULT 0.00,
  `warning_value` decimal(14,2) DEFAULT 0.00,
  `filter_json` longtext DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `metric_code` (`metric_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bi_metric_library`
--

LOCK TABLES `ec_bi_metric_library` WRITE;
/*!40000 ALTER TABLE `ec_bi_metric_library` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_bi_metric_library` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bi_report_drilldowns`
--

DROP TABLE IF EXISTS `ec_bi_report_drilldowns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bi_report_drilldowns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `drilldown_number` varchar(120) NOT NULL,
  `source_type` varchar(120) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `drilldown_type` varchar(120) DEFAULT 'table',
  `title` varchar(255) DEFAULT NULL,
  `query_type` varchar(120) DEFAULT 'report_type',
  `config_json` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `drilldown_number` (`drilldown_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bi_report_drilldowns`
--

LOCK TABLES `ec_bi_report_drilldowns` WRITE;
/*!40000 ALTER TABLE `ec_bi_report_drilldowns` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_bi_report_drilldowns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bill_of_materials`
--

DROP TABLE IF EXISTS `ec_bill_of_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bill_of_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bom_number` varchar(120) NOT NULL,
  `bom_name` varchar(255) DEFAULT NULL,
  `finished_product_id` int(11) NOT NULL,
  `version_no` varchar(40) DEFAULT '1.0',
  `quantity` decimal(14,4) DEFAULT 1.0000,
  `status` varchar(40) DEFAULT 'draft',
  `routing_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `bom_number` (`bom_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bill_of_materials`
--

LOCK TABLES `ec_bill_of_materials` WRITE;
/*!40000 ALTER TABLE `ec_bill_of_materials` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_bill_of_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_blog_posts`
--

DROP TABLE IF EXISTS `ec_blog_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `status` varchar(30) DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_blog_posts`
--

LOCK TABLES `ec_blog_posts` WRITE;
/*!40000 ALTER TABLE `ec_blog_posts` DISABLE KEYS */;
INSERT INTO `ec_blog_posts` VALUES (1,'Welcome to the General Trading ERP Store','welcome-general-trading-erp-store','<h2>Welcome</h2><p>This edition connects retail ecommerce, trading quotes, supplier purchasing, and ERP reporting.</p>','A quick introduction to the general trading edition.','published','2026-06-12 11:21:09'),(2,'How Trade Quotes Become Revenue','trade-quotes-become-revenue','<h2>ERP Overview</h2><p>Manage customers, quotes, invoices, suppliers, procurement, and sales reporting from one system.</p>','Understand the trading ERP workflow.','published','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_blog_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bom_lines`
--

DROP TABLE IF EXISTS `ec_bom_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bom_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bill_of_material_id` int(11) NOT NULL,
  `component_product_id` int(11) NOT NULL,
  `component_type` varchar(80) DEFAULT 'material',
  `quantity` decimal(14,4) DEFAULT 1.0000,
  `wastage_percent` decimal(8,2) DEFAULT 0.00,
  `unit_cost` decimal(14,2) DEFAULT 0.00,
  `line_cost` decimal(14,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bom_lines`
--

LOCK TABLES `ec_bom_lines` WRITE;
/*!40000 ALTER TABLE `ec_bom_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_bom_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bookings`
--

DROP TABLE IF EXISTS `ec_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_number` varchar(80) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(80) DEFAULT NULL,
  `service_type` varchar(255) DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `booking_time` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(30) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_number` (`booking_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bookings`
--

LOCK TABLES `ec_bookings` WRITE;
/*!40000 ALTER TABLE `ec_bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_branches`
--

DROP TABLE IF EXISTS `ec_branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `branch_code` varchar(80) NOT NULL,
  `branch_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `is_head_office` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `branch_code` (`branch_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_branches`
--

LOCK TABLES `ec_branches` WRITE;
/*!40000 ALTER TABLE `ec_branches` DISABLE KEYS */;
INSERT INTO `ec_branches` VALUES (1,1,'BR-001','Head Office','','','','active',1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_budget_lines`
--

DROP TABLE IF EXISTS `ec_budget_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_budget_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `budget_version_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `period_label` varchar(120) DEFAULT NULL,
  `budget_amount` decimal(14,2) DEFAULT 0.00,
  `actual_amount` decimal(14,2) DEFAULT 0.00,
  `variance_amount` decimal(14,2) DEFAULT 0.00,
  `variance_percent` decimal(8,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_budget_lines`
--

LOCK TABLES `ec_budget_lines` WRITE;
/*!40000 ALTER TABLE `ec_budget_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_budget_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_budget_periods`
--

DROP TABLE IF EXISTS `ec_budget_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_budget_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `cost_center_id` int(11) NOT NULL,
  `fiscal_year` int(11) NOT NULL,
  `period_month` int(11) DEFAULT 0,
  `budget_amount` decimal(14,2) DEFAULT 0.00,
  `committed_amount` decimal(14,2) DEFAULT 0.00,
  `actual_amount` decimal(14,2) DEFAULT 0.00,
  `variance_amount` decimal(14,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_budget_period` (`cost_center_id`,`fiscal_year`,`period_month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_budget_periods`
--

LOCK TABLES `ec_budget_periods` WRITE;
/*!40000 ALTER TABLE `ec_budget_periods` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_budget_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_budget_versions`
--

DROP TABLE IF EXISTS `ec_budget_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_budget_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `budget_number` varchar(120) NOT NULL,
  `budget_name` varchar(255) DEFAULT NULL,
  `fiscal_year_id` int(11) DEFAULT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `status` varchar(40) DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `budget_number` (`budget_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_budget_versions`
--

LOCK TABLES `ec_budget_versions` WRITE;
/*!40000 ALTER TABLE `ec_budget_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_budget_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bulk_order_request_items`
--

DROP TABLE IF EXISTS `ec_bulk_order_request_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bulk_order_request_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bulk_order_request_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `sku` varchar(120) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 1.00,
  `unit_price` decimal(14,2) DEFAULT 0.00,
  `line_total` decimal(14,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bulk_order_request_items`
--

LOCK TABLES `ec_bulk_order_request_items` WRITE;
/*!40000 ALTER TABLE `ec_bulk_order_request_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_bulk_order_request_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_bulk_order_requests`
--

DROP TABLE IF EXISTS `ec_bulk_order_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_bulk_order_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bulk_request_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `required_date` date DEFAULT NULL,
  `delivery_location` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'new',
  `subtotal` decimal(14,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `converted_order_id` int(11) DEFAULT NULL,
  `converted_quotation_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `bulk_request_number` (`bulk_request_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_bulk_order_requests`
--

LOCK TABLES `ec_bulk_order_requests` WRITE;
/*!40000 ALTER TABLE `ec_bulk_order_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_bulk_order_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_campaign_members`
--

DROP TABLE IF EXISTS `ec_campaign_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_campaign_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marketing_campaign_id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'targeted',
  `response_status` varchar(80) DEFAULT 'none',
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_campaign_member` (`marketing_campaign_id`,`lead_id`,`customer_id`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_campaign_members`
--

LOCK TABLES `ec_campaign_members` WRITE;
/*!40000 ALTER TABLE `ec_campaign_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_campaign_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_cash_flow_forecast_lines`
--

DROP TABLE IF EXISTS `ec_cash_flow_forecast_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_cash_flow_forecast_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cash_flow_forecast_id` int(11) NOT NULL,
  `line_date` date DEFAULT NULL,
  `source_type` varchar(120) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `inflow` decimal(14,2) DEFAULT 0.00,
  `outflow` decimal(14,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_cash_flow_forecast_lines`
--

LOCK TABLES `ec_cash_flow_forecast_lines` WRITE;
/*!40000 ALTER TABLE `ec_cash_flow_forecast_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_cash_flow_forecast_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_cash_flow_forecasts`
--

DROP TABLE IF EXISTS `ec_cash_flow_forecasts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_cash_flow_forecasts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forecast_number` varchar(120) NOT NULL,
  `forecast_name` varchar(255) DEFAULT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `opening_cash` decimal(14,2) DEFAULT 0.00,
  `forecast_inflow` decimal(14,2) DEFAULT 0.00,
  `forecast_outflow` decimal(14,2) DEFAULT 0.00,
  `net_cash_flow` decimal(14,2) DEFAULT 0.00,
  `closing_cash` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'generated',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `forecast_number` (`forecast_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_cash_flow_forecasts`
--

LOCK TABLES `ec_cash_flow_forecasts` WRITE;
/*!40000 ALTER TABLE `ec_cash_flow_forecasts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_cash_flow_forecasts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_categories`
--

DROP TABLE IF EXISTS `ec_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_categories`
--

LOCK TABLES `ec_categories` WRITE;
/*!40000 ALTER TABLE `ec_categories` DISABLE KEYS */;
INSERT INTO `ec_categories` VALUES (1,'Office Supplies','office-supplies','Business and workplace essentials.',1,'2026-06-12 11:21:09'),(2,'Industrial Consumables','industrial-consumables','Trading items and operational supplies.',2,'2026-06-12 11:21:09'),(3,'Digital Services','digital-services','Online add-ons, plans, and service packages.',3,'2026-06-12 11:21:09'),(4,'Wholesale Bundles','wholesale-bundles','B2B packs and commercial offers.',4,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_client_onboarding_checklist_items`
--

DROP TABLE IF EXISTS `ec_client_onboarding_checklist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_client_onboarding_checklist_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_onboarding_checklist_id` int(11) NOT NULL,
  `phase` varchar(120) DEFAULT NULL,
  `item_title` varchar(255) DEFAULT NULL,
  `owner_role` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `completed_by` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_client_onboarding_checklist_items`
--

LOCK TABLES `ec_client_onboarding_checklist_items` WRITE;
/*!40000 ALTER TABLE `ec_client_onboarding_checklist_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_client_onboarding_checklist_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_client_onboarding_checklists`
--

DROP TABLE IF EXISTS `ec_client_onboarding_checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_client_onboarding_checklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `onboarding_number` varchar(120) NOT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `package_name` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `start_date` date DEFAULT NULL,
  `target_go_live_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `onboarding_number` (`onboarding_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_client_onboarding_checklists`
--

LOCK TABLES `ec_client_onboarding_checklists` WRITE;
/*!40000 ALTER TABLE `ec_client_onboarding_checklists` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_client_onboarding_checklists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_collection_tasks`
--

DROP TABLE IF EXISTS `ec_collection_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_collection_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_number` varchar(120) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `balance_due` decimal(14,2) DEFAULT 0.00,
  `due_date` date DEFAULT NULL,
  `priority` varchar(40) DEFAULT 'normal',
  `status` varchar(40) DEFAULT 'open',
  `assigned_to` int(11) DEFAULT NULL,
  `next_followup_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_number` (`task_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_collection_tasks`
--

LOCK TABLES `ec_collection_tasks` WRITE;
/*!40000 ALTER TABLE `ec_collection_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_collection_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_commercial_package_features`
--

DROP TABLE IF EXISTS `ec_commercial_package_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_commercial_package_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commercial_package_id` int(11) NOT NULL,
  `feature_group` varchar(160) DEFAULT NULL,
  `feature_name` varchar(255) DEFAULT NULL,
  `feature_description` text DEFAULT NULL,
  `included_limit` varchar(120) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_commercial_package_features`
--

LOCK TABLES `ec_commercial_package_features` WRITE;
/*!40000 ALTER TABLE `ec_commercial_package_features` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_commercial_package_features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_commercial_packages`
--

DROP TABLE IF EXISTS `ec_commercial_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_commercial_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_number` varchar(120) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `package_type` varchar(120) DEFAULT 'license',
  `target_customer` varchar(180) DEFAULT NULL,
  `billing_cycle` varchar(80) DEFAULT 'one_time',
  `base_price` decimal(12,2) DEFAULT 0.00,
  `currency` varchar(20) DEFAULT 'AED',
  `implementation_days` int(11) DEFAULT 0,
  `status` varchar(40) DEFAULT 'active',
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `package_number` (`package_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_commercial_packages`
--

LOCK TABLES `ec_commercial_packages` WRITE;
/*!40000 ALTER TABLE `ec_commercial_packages` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_commercial_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_commission_plans`
--

DROP TABLE IF EXISTS `ec_commission_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_commission_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_code` varchar(120) NOT NULL,
  `plan_name` varchar(255) DEFAULT NULL,
  `commission_type` varchar(80) DEFAULT 'invoice_percent',
  `commission_rate` decimal(8,2) DEFAULT 0.00,
  `threshold_amount` decimal(12,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `plan_code` (`plan_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_commission_plans`
--

LOCK TABLES `ec_commission_plans` WRITE;
/*!40000 ALTER TABLE `ec_commission_plans` DISABLE KEYS */;
INSERT INTO `ec_commission_plans` VALUES (1,'STD-SALES-2','Standard Sales Commission 2%','invoice_percent',2.00,0.00,'active','Default commission plan for paid invoices.','2026-06-12 11:21:09'),(2,'HIGH-VALUE-3','High Value Sales Commission 3%','invoice_percent',3.00,10000.00,'active','Higher commission for larger sales.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_commission_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_commission_records`
--

DROP TABLE IF EXISTS `ec_commission_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_commission_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commission_number` varchar(120) NOT NULL,
  `commission_plan_id` int(11) DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `sales_order_id` int(11) DEFAULT NULL,
  `sale_amount` decimal(12,2) DEFAULT 0.00,
  `commission_amount` decimal(12,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'earned',
  `period_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `commission_number` (`commission_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_commission_records`
--

LOCK TABLES `ec_commission_records` WRITE;
/*!40000 ALTER TABLE `ec_commission_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_commission_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_communication_automation_rules`
--

DROP TABLE IF EXISTS `ec_communication_automation_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_communication_automation_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_code` varchar(120) NOT NULL,
  `rule_name` varchar(255) DEFAULT NULL,
  `trigger_event` varchar(120) DEFAULT NULL,
  `channel` varchar(40) DEFAULT 'email',
  `template_key` varchar(120) DEFAULT NULL,
  `target_type` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `conditions_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_code` (`rule_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_communication_automation_rules`
--

LOCK TABLES `ec_communication_automation_rules` WRITE;
/*!40000 ALTER TABLE `ec_communication_automation_rules` DISABLE KEYS */;
INSERT INTO `ec_communication_automation_rules` VALUES (1,'COM-ORDER-UPDATE','Order status notification','order.updated','whatsapp','order_update','customer','active','{}','2026-06-12 11:21:09'),(2,'COM-QUOTE-FOLLOWUP','Quotation follow-up','quotation.sent','whatsapp','quotation_followup','lead','active','{}','2026-06-12 11:21:09'),(3,'COM-INVOICE-REMINDER','Invoice payment reminder','invoice.overdue','email','payment_reminder','customer','active','{}','2026-06-12 11:21:09'),(4,'COM-WEBHOOK-ORDER','Push order webhook','order.created','webhook','order.created','webhook','active','{}','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_communication_automation_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_communication_automation_runs`
--

DROP TABLE IF EXISTS `ec_communication_automation_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_communication_automation_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `communication_automation_rule_id` int(11) NOT NULL,
  `run_number` varchar(120) NOT NULL,
  `reference_type` varchar(120) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'completed',
  `channel` varchar(40) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `run_number` (`run_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_communication_automation_runs`
--

LOCK TABLES `ec_communication_automation_runs` WRITE;
/*!40000 ALTER TABLE `ec_communication_automation_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_communication_automation_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_companies`
--

DROP TABLE IF EXISTS `ec_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(80) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `legal_name` varchar(255) DEFAULT NULL,
  `tax_number` varchar(120) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `currency` varchar(20) DEFAULT 'AED',
  `timezone` varchar(120) DEFAULT 'Asia/Dubai',
  `address` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_code` (`company_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_companies`
--

LOCK TABLES `ec_companies` WRITE;
/*!40000 ALTER TABLE `ec_companies` DISABLE KEYS */;
INSERT INTO `ec_companies` VALUES (1,'CMP-001','General Trading ERP Store','General Trading ERP Store','','','','USD','Asia/Dubai','','active',1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_compliance_checklist_items`
--

DROP TABLE IF EXISTS `ec_compliance_checklist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_compliance_checklist_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compliance_checklist_id` int(11) NOT NULL,
  `item_title` varchar(255) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `require_evidence` tinyint(1) DEFAULT 0,
  `status` varchar(40) DEFAULT 'pending',
  `evidence_path` varchar(255) DEFAULT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_compliance_checklist_items`
--

LOCK TABLES `ec_compliance_checklist_items` WRITE;
/*!40000 ALTER TABLE `ec_compliance_checklist_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_compliance_checklist_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_compliance_checklists`
--

DROP TABLE IF EXISTS `ec_compliance_checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_compliance_checklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checklist_number` varchar(120) NOT NULL,
  `checklist_name` varchar(255) DEFAULT NULL,
  `compliance_area` varchar(120) DEFAULT 'General',
  `status` varchar(40) DEFAULT 'active',
  `owner_user_id` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `checklist_number` (`checklist_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_compliance_checklists`
--

LOCK TABLES `ec_compliance_checklists` WRITE;
/*!40000 ALTER TABLE `ec_compliance_checklists` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_compliance_checklists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_cost_centers`
--

DROP TABLE IF EXISTS `ec_cost_centers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_cost_centers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `cost_center_code` varchar(80) NOT NULL,
  `cost_center_name` varchar(255) NOT NULL,
  `manager_user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cost_center_code` (`cost_center_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_cost_centers`
--

LOCK TABLES `ec_cost_centers` WRITE;
/*!40000 ALTER TABLE `ec_cost_centers` DISABLE KEYS */;
INSERT INTO `ec_cost_centers` VALUES (1,1,1,'CC-SVC','Service Operations',NULL,NULL,'active','Default service and workshop cost center.','2026-06-12 11:21:09'),(2,1,1,'CC-PRJ','Projects',NULL,NULL,'active','Default project execution cost center.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_cost_centers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_credit_note_items`
--

DROP TABLE IF EXISTS `ec_credit_note_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_credit_note_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `credit_note_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_credit_note_items`
--

LOCK TABLES `ec_credit_note_items` WRITE;
/*!40000 ALTER TABLE `ec_credit_note_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_credit_note_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_credit_notes`
--

DROP TABLE IF EXISTS `ec_credit_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_credit_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `credit_note_number` varchar(80) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `tax` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `issue_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'draft',
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `credit_note_number` (`credit_note_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_credit_notes`
--

LOCK TABLES `ec_credit_notes` WRITE;
/*!40000 ALTER TABLE `ec_credit_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_credit_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_crm_automation_rules`
--

DROP TABLE IF EXISTS `ec_crm_automation_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_crm_automation_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_code` varchar(120) NOT NULL,
  `rule_name` varchar(255) DEFAULT NULL,
  `trigger_event` varchar(160) DEFAULT NULL,
  `condition_json` longtext DEFAULT NULL,
  `action_json` longtext DEFAULT NULL,
  `frequency` varchar(80) DEFAULT 'manual',
  `last_run_at` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_code` (`rule_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_crm_automation_rules`
--

LOCK TABLES `ec_crm_automation_rules` WRITE;
/*!40000 ALTER TABLE `ec_crm_automation_rules` DISABLE KEYS */;
INSERT INTO `ec_crm_automation_rules` VALUES (1,'CRM_FOLLOWUP_DUE','Notify sales users for overdue lead follow-ups','lead_followup_due','{\"next_follow_up\":\"due\"}','{\"notify_role\":\"sales-online-orders\"}','manual',NULL,'active','2026-06-12 11:21:09'),(2,'CRM_HIGH_VALUE_LEADS','Create opportunity for high-value leads','high_value_lead','{\"estimated_value_gte\":5000}','{\"create\":\"opportunity\"}','manual',NULL,'active','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_crm_automation_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_crm_automation_runs`
--

DROP TABLE IF EXISTS `ec_crm_automation_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_crm_automation_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `crm_automation_rule_id` int(11) DEFAULT NULL,
  `run_number` varchar(120) NOT NULL,
  `status` varchar(40) DEFAULT 'running',
  `records_checked` int(11) DEFAULT 0,
  `actions_created` int(11) DEFAULT 0,
  `summary` text DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `run_number` (`run_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_crm_automation_runs`
--

LOCK TABLES `ec_crm_automation_runs` WRITE;
/*!40000 ALTER TABLE `ec_crm_automation_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_crm_automation_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_crm_campaign_actions`
--

DROP TABLE IF EXISTS `ec_crm_campaign_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_crm_campaign_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action_number` varchar(120) NOT NULL,
  `marketing_campaign_id` int(11) NOT NULL,
  `campaign_member_id` int(11) DEFAULT NULL,
  `channel` varchar(80) DEFAULT 'email',
  `action_type` varchar(80) DEFAULT 'message',
  `subject` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'scheduled',
  `scheduled_at` datetime DEFAULT NULL,
  `executed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `action_number` (`action_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_crm_campaign_actions`
--

LOCK TABLES `ec_crm_campaign_actions` WRITE;
/*!40000 ALTER TABLE `ec_crm_campaign_actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_crm_campaign_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_crm_customer_touchpoints`
--

DROP TABLE IF EXISTS `ec_crm_customer_touchpoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_crm_customer_touchpoints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `touchpoint_number` varchar(120) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `opportunity_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `touchpoint_type` varchar(80) DEFAULT 'note',
  `subject` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `touchpoint_at` datetime DEFAULT NULL,
  `next_follow_up` date DEFAULT NULL,
  `outcome` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `touchpoint_number` (`touchpoint_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_crm_customer_touchpoints`
--

LOCK TABLES `ec_crm_customer_touchpoints` WRITE;
/*!40000 ALTER TABLE `ec_crm_customer_touchpoints` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_crm_customer_touchpoints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_crm_followup_tasks`
--

DROP TABLE IF EXISTS `ec_crm_followup_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_crm_followup_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_number` varchar(120) NOT NULL,
  `related_type` varchar(80) DEFAULT 'lead',
  `related_id` int(11) DEFAULT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `opportunity_id` int(11) DEFAULT NULL,
  `quotation_id` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `task_type` varchar(80) DEFAULT 'call',
  `priority` varchar(40) DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `completed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_number` (`task_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_crm_followup_tasks`
--

LOCK TABLES `ec_crm_followup_tasks` WRITE;
/*!40000 ALTER TABLE `ec_crm_followup_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_crm_followup_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_crm_leads`
--

DROP TABLE IF EXISTS `ec_crm_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_crm_leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `customer_type` varchar(30) DEFAULT 'b2c',
  `status` varchar(40) DEFAULT 'new',
  `source` varchar(120) DEFAULT NULL,
  `estimated_value` decimal(12,2) DEFAULT 0.00,
  `probability` int(11) DEFAULT 0,
  `next_follow_up` date DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `converted_customer_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_crm_leads`
--

LOCK TABLES `ec_crm_leads` WRITE;
/*!40000 ALTER TABLE `ec_crm_leads` DISABLE KEYS */;
INSERT INTO `ec_crm_leads` VALUES (1,1,1,'Office Refit Procurement','buyer@skylinetowers.example','+971500000033','Skyline Towers','b2b','qualified','Website Form',42000.00,66,'2026-06-14',2,NULL,'Requested furniture and stockroom product quotation.','2026-06-12 11:21:09'),(2,1,1,'Retail Maintenance Plan Enquiry','fatima.buyer@example.com','+971500000034','','b2c','contacted','Email',650.00,38,'2026-06-13',2,NULL,'Asked about digital support plan options.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_crm_leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_crm_quote_followups`
--

DROP TABLE IF EXISTS `ec_crm_quote_followups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_crm_quote_followups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `followup_number` varchar(120) NOT NULL,
  `quotation_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `quotation_total` decimal(14,2) DEFAULT 0.00,
  `followup_stage` varchar(80) DEFAULT 'first_followup',
  `next_follow_up` date DEFAULT NULL,
  `last_follow_up_at` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `followup_number` (`followup_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_crm_quote_followups`
--

LOCK TABLES `ec_crm_quote_followups` WRITE;
/*!40000 ALTER TABLE `ec_crm_quote_followups` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_crm_quote_followups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_crm_sales_forecasts`
--

DROP TABLE IF EXISTS `ec_crm_sales_forecasts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_crm_sales_forecasts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forecast_number` varchar(120) NOT NULL,
  `period_label` varchar(80) DEFAULT NULL,
  `pipeline_value` decimal(14,2) DEFAULT 0.00,
  `weighted_value` decimal(14,2) DEFAULT 0.00,
  `open_opportunities` int(11) DEFAULT 0,
  `expected_revenue` decimal(14,2) DEFAULT 0.00,
  `won_value` decimal(14,2) DEFAULT 0.00,
  `lost_value` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'calculated',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `forecast_number` (`forecast_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_crm_sales_forecasts`
--

LOCK TABLES `ec_crm_sales_forecasts` WRITE;
/*!40000 ALTER TABLE `ec_crm_sales_forecasts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_crm_sales_forecasts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_cron_runs`
--

DROP TABLE IF EXISTS `ec_cron_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_cron_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `run_key` varchar(120) DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'running',
  `summary` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_cron_runs`
--

LOCK TABLES `ec_cron_runs` WRITE;
/*!40000 ALTER TABLE `ec_cron_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_cron_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_currencies`
--

DROP TABLE IF EXISTS `ec_currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `rate_to_base` decimal(18,8) DEFAULT 1.00000000,
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_currencies`
--

LOCK TABLES `ec_currencies` WRITE;
/*!40000 ALTER TABLE `ec_currencies` DISABLE KEYS */;
INSERT INTO `ec_currencies` VALUES (1,'AED','UAE Dirham','AED ',1.00000000,0,1,0,'2026-06-01 15:32:31','2026-06-01 15:32:31'),(2,'EGP','Egyptian Pound','L.E ',0.07500000,0,1,1,'2026-06-01 15:32:31','2026-06-01 15:32:31'),(3,'USD','US Dollar','$ ',3.67250000,1,1,2,'2026-06-01 15:32:31','2026-06-01 15:32:31'),(4,'EUR','Euro','€ ',4.01250000,0,1,3,'2026-06-01 15:32:31','2026-06-01 15:32:31'),(5,'GBP','British Pound','£ ',4.72500000,0,1,4,'2026-06-01 15:32:31','2026-06-01 15:32:31'),(6,'SAR','Saudi Riyal','SAR ',0.97950000,0,1,5,'2026-06-01 15:32:31','2026-06-01 15:32:31');
/*!40000 ALTER TABLE `ec_currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_custom_pages`
--

DROP TABLE IF EXISTS `ec_custom_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_custom_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content_html` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `header_label` varchar(120) DEFAULT NULL,
  `show_in_header` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_custom_pages`
--

LOCK TABLES `ec_custom_pages` WRITE;
/*!40000 ALTER TABLE `ec_custom_pages` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_custom_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_customer_asset_documents`
--

DROP TABLE IF EXISTS `ec_customer_asset_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_customer_asset_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_asset_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `document_type` varchar(120) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `stored_path` varchar(255) DEFAULT NULL,
  `mime_type` varchar(160) DEFAULT NULL,
  `file_size` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_customer_asset_documents`
--

LOCK TABLES `ec_customer_asset_documents` WRITE;
/*!40000 ALTER TABLE `ec_customer_asset_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_customer_asset_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_customer_assets`
--

DROP TABLE IF EXISTS `ec_customer_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_customer_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `asset_type` varchar(80) DEFAULT 'vehicle',
  `asset_name` varchar(255) DEFAULT NULL,
  `make` varchar(120) DEFAULT NULL,
  `model` varchar(120) DEFAULT NULL,
  `year` varchar(20) DEFAULT NULL,
  `vin` varchar(120) DEFAULT NULL,
  `plate_number` varchar(80) DEFAULT NULL,
  `serial_number` varchar(160) DEFAULT NULL,
  `odometer` varchar(80) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_customer_assets`
--

LOCK TABLES `ec_customer_assets` WRITE;
/*!40000 ALTER TABLE `ec_customer_assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_customer_assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_customer_document_uploads`
--

DROP TABLE IF EXISTS `ec_customer_document_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_customer_document_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `reference_type` varchar(120) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `document_type` varchar(120) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `stored_path` varchar(255) DEFAULT NULL,
  `mime_type` varchar(160) DEFAULT NULL,
  `file_size` int(11) DEFAULT 0,
  `status` varchar(40) DEFAULT 'uploaded',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_customer_document_uploads`
--

LOCK TABLES `ec_customer_document_uploads` WRITE;
/*!40000 ALTER TABLE `ec_customer_document_uploads` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_customer_document_uploads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_customer_invoice_disputes`
--

DROP TABLE IF EXISTS `ec_customer_invoice_disputes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_customer_invoice_disputes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispute_number` varchar(120) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dispute_number` (`dispute_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_customer_invoice_disputes`
--

LOCK TABLES `ec_customer_invoice_disputes` WRITE;
/*!40000 ALTER TABLE `ec_customer_invoice_disputes` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_customer_invoice_disputes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_customer_payment_promises`
--

DROP TABLE IF EXISTS `ec_customer_payment_promises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_customer_payment_promises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promise_number` varchar(120) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `promised_amount` decimal(14,2) DEFAULT 0.00,
  `promised_date` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `promise_number` (`promise_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_customer_payment_promises`
--

LOCK TABLES `ec_customer_payment_promises` WRITE;
/*!40000 ALTER TABLE `ec_customer_payment_promises` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_customer_payment_promises` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_customer_portal_announcements`
--

DROP TABLE IF EXISTS `ec_customer_portal_announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_customer_portal_announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `audience` varchar(80) DEFAULT 'all',
  `status` varchar(40) DEFAULT 'published',
  `publish_from` datetime DEFAULT NULL,
  `publish_to` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_customer_portal_announcements`
--

LOCK TABLES `ec_customer_portal_announcements` WRITE;
/*!40000 ALTER TABLE `ec_customer_portal_announcements` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_customer_portal_announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_customer_portal_notifications`
--

DROP TABLE IF EXISTS `ec_customer_portal_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_customer_portal_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_customer_portal_notifications`
--

LOCK TABLES `ec_customer_portal_notifications` WRITE;
/*!40000 ALTER TABLE `ec_customer_portal_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_customer_portal_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_customer_price_rules`
--

DROP TABLE IF EXISTS `ec_customer_price_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_customer_price_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_number` varchar(120) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_type` varchar(40) DEFAULT NULL,
  `customer_group` varchar(120) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `rule_type` varchar(40) DEFAULT 'percent_discount',
  `rule_value` decimal(14,2) DEFAULT 0.00,
  `min_quantity` decimal(12,2) DEFAULT 1.00,
  `priority` int(11) DEFAULT 50,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_number` (`rule_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_customer_price_rules`
--

LOCK TABLES `ec_customer_price_rules` WRITE;
/*!40000 ALTER TABLE `ec_customer_price_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_customer_price_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_customer_segment_members`
--

DROP TABLE IF EXISTS `ec_customer_segment_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_customer_segment_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_segment_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `member_type` varchar(40) DEFAULT 'customer',
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_segment_member` (`customer_segment_id`,`customer_id`,`lead_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_customer_segment_members`
--

LOCK TABLES `ec_customer_segment_members` WRITE;
/*!40000 ALTER TABLE `ec_customer_segment_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_customer_segment_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_customer_segments`
--

DROP TABLE IF EXISTS `ec_customer_segments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_customer_segments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `segment_code` varchar(120) NOT NULL,
  `segment_name` varchar(255) DEFAULT NULL,
  `segment_type` varchar(80) DEFAULT 'dynamic',
  `criteria_json` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `member_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `segment_code` (`segment_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_customer_segments`
--

LOCK TABLES `ec_customer_segments` WRITE;
/*!40000 ALTER TABLE `ec_customer_segments` DISABLE KEYS */;
INSERT INTO `ec_customer_segments` VALUES (1,'SEG-B2B','B2B Customers','dynamic','{\"customer_type\":\"b2b\"}','active',0,'2026-06-12 11:21:09'),(2,'SEG-CREDIT-HOLD','Credit Hold Customers','dynamic','{\"credit_status\":\"hold\"}','active',0,'2026-06-12 11:21:09'),(3,'SEG-HOT-LEADS','Hot Leads','dynamic','{\"lead_score_gte\":70}','active',0,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_customer_segments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_customer_service_feedback`
--

DROP TABLE IF EXISTS `ec_customer_service_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_customer_service_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_service_request_id` int(11) DEFAULT NULL,
  `job_card_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT 0,
  `nps_score` int(11) DEFAULT 0,
  `feedback_text` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'submitted',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_customer_service_feedback`
--

LOCK TABLES `ec_customer_service_feedback` WRITE;
/*!40000 ALTER TABLE `ec_customer_service_feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_customer_service_feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_customer_service_request_messages`
--

DROP TABLE IF EXISTS `ec_customer_service_request_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_customer_service_request_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_service_request_id` int(11) NOT NULL,
  `sender_user_id` int(11) DEFAULT NULL,
  `sender_type` varchar(40) DEFAULT 'customer',
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_customer_service_request_messages`
--

LOCK TABLES `ec_customer_service_request_messages` WRITE;
/*!40000 ALTER TABLE `ec_customer_service_request_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_customer_service_request_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_customer_service_requests`
--

DROP TABLE IF EXISTS `ec_customer_service_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_customer_service_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_asset_id` int(11) DEFAULT NULL,
  `job_card_id` int(11) DEFAULT NULL,
  `service_type` varchar(160) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `preferred_time` time DEFAULT NULL,
  `priority` varchar(40) DEFAULT 'medium',
  `status` varchar(50) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_number` (`request_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_customer_service_requests`
--

LOCK TABLES `ec_customer_service_requests` WRITE;
/*!40000 ALTER TABLE `ec_customer_service_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_customer_service_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_customers`
--

DROP TABLE IF EXISTS `ec_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `customer_code` varchar(80) NOT NULL,
  `customer_type` varchar(30) DEFAULT 'b2c',
  `company_name` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `tax_number` varchar(120) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `credit_limit` decimal(12,2) DEFAULT 0.00,
  `payment_terms_days` int(11) DEFAULT 0,
  `credit_status` varchar(40) DEFAULT 'open',
  `credit_hold_reason` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_code` (`customer_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_customers`
--

LOCK TABLES `ec_customers` WRITE;
/*!40000 ALTER TABLE `ec_customers` DISABLE KEYS */;
INSERT INTO `ec_customers` VALUES (1,1,1,'CUS-0001','b2b','Crescent Trading LLC','Imran Qureshi','procurement@crescenttrading.example','+971500000031','TRN-400001','Deira, Dubai, UAE','Deira, Dubai, UAE',50000.00,30,'open',NULL,'active','2026-06-12 11:21:09'),(2,1,1,'CUS-0002','b2c','','Omar Saleh','omar.saleh@example.com','+971500000032','','Ajman, UAE','Ajman, UAE',0.00,0,'open',NULL,'active','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_dashboard_widget_preferences`
--

DROP TABLE IF EXISTS `ec_dashboard_widget_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_dashboard_widget_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `role_slug` varchar(160) DEFAULT NULL,
  `widget_key` varchar(160) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_enabled` tinyint(1) DEFAULT 1,
  `config_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_widget_pref` (`user_id`,`role_slug`,`widget_key`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_dashboard_widget_preferences`
--

LOCK TABLES `ec_dashboard_widget_preferences` WRITE;
/*!40000 ALTER TABLE `ec_dashboard_widget_preferences` DISABLE KEYS */;
INSERT INTO `ec_dashboard_widget_preferences` VALUES (1,NULL,'erp-manager','executive_kpis','Executive KPI Summary',10,1,'{}','2026-06-12 11:21:09'),(2,NULL,'erp-manager','approval_queue','Approval Queue',20,1,'{}','2026-06-12 11:21:09'),(3,NULL,'erp-manager','system_health','System Health',30,1,'{}','2026-06-12 11:21:09'),(4,NULL,'finance','cash_receivables','Cash & Receivables',10,1,'{}','2026-06-12 11:21:09'),(5,NULL,'finance','approval_queue','Approval Queue',20,1,'{}','2026-06-12 11:21:09'),(6,NULL,'inventory-procurement','inventory_alerts','Inventory Alerts',10,1,'{}','2026-06-12 11:21:09'),(7,NULL,'sales-online-orders','sales_pipeline','Sales Pipeline',10,1,'{}','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_dashboard_widget_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_data_export_logs`
--

DROP TABLE IF EXISTS `ec_data_export_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_data_export_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `export_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `module` varchar(120) DEFAULT NULL,
  `export_type` varchar(80) DEFAULT 'csv',
  `file_name` varchar(255) DEFAULT NULL,
  `row_count` int(11) DEFAULT 0,
  `filter_json` longtext DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'created',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `export_number` (`export_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_data_export_logs`
--

LOCK TABLES `ec_data_export_logs` WRITE;
/*!40000 ALTER TABLE `ec_data_export_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_data_export_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_data_import_jobs`
--

DROP TABLE IF EXISTS `ec_data_import_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_data_import_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `import_type` varchar(120) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'draft',
  `rows_total` int(11) DEFAULT 0,
  `rows_success` int(11) DEFAULT 0,
  `rows_failed` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `error_log` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_data_import_jobs`
--

LOCK TABLES `ec_data_import_jobs` WRITE;
/*!40000 ALTER TABLE `ec_data_import_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_data_import_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_data_import_rows`
--

DROP TABLE IF EXISTS `ec_data_import_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_data_import_rows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_import_job_id` int(11) NOT NULL,
  `row_number` int(11) DEFAULT 0,
  `status` varchar(40) DEFAULT 'pending',
  `source_json` longtext DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_data_import_rows`
--

LOCK TABLES `ec_data_import_rows` WRITE;
/*!40000 ALTER TABLE `ec_data_import_rows` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_data_import_rows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_debit_note_items`
--

DROP TABLE IF EXISTS `ec_debit_note_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_debit_note_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debit_note_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_debit_note_items`
--

LOCK TABLES `ec_debit_note_items` WRITE;
/*!40000 ALTER TABLE `ec_debit_note_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_debit_note_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_debit_notes`
--

DROP TABLE IF EXISTS `ec_debit_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_debit_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debit_note_number` varchar(80) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `expense_id` int(11) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `tax` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `issue_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'draft',
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `debit_note_number` (`debit_note_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_debit_notes`
--

LOCK TABLES `ec_debit_notes` WRITE;
/*!40000 ALTER TABLE `ec_debit_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_debit_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_decision_support_cases`
--

DROP TABLE IF EXISTS `ec_decision_support_cases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_decision_support_cases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_number` varchar(120) NOT NULL,
  `case_title` varchar(255) DEFAULT NULL,
  `module` varchar(120) DEFAULT NULL,
  `decision_question` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'draft',
  `selected_option_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `case_number` (`case_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_decision_support_cases`
--

LOCK TABLES `ec_decision_support_cases` WRITE;
/*!40000 ALTER TABLE `ec_decision_support_cases` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_decision_support_cases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_decision_support_options`
--

DROP TABLE IF EXISTS `ec_decision_support_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_decision_support_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `decision_support_case_id` int(11) NOT NULL,
  `option_title` varchar(255) DEFAULT NULL,
  `pros` text DEFAULT NULL,
  `cons` text DEFAULT NULL,
  `estimated_cost` decimal(14,2) DEFAULT 0.00,
  `estimated_benefit` decimal(14,2) DEFAULT 0.00,
  `risk_score` decimal(8,2) DEFAULT 0.00,
  `recommendation_score` decimal(8,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_decision_support_options`
--

LOCK TABLES `ec_decision_support_options` WRITE;
/*!40000 ALTER TABLE `ec_decision_support_options` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_decision_support_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_delivery_note_items`
--

DROP TABLE IF EXISTS `ec_delivery_note_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_delivery_note_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_note_id` int(11) NOT NULL,
  `sales_order_item_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity_delivered` decimal(12,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_delivery_note_items`
--

LOCK TABLES `ec_delivery_note_items` WRITE;
/*!40000 ALTER TABLE `ec_delivery_note_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_delivery_note_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_delivery_notes`
--

DROP TABLE IF EXISTS `ec_delivery_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_delivery_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `delivery_number` varchar(120) NOT NULL,
  `sales_order_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'draft',
  `dispatched_by` int(11) DEFAULT NULL,
  `delivered_by` int(11) DEFAULT NULL,
  `dispatched_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `delivery_number` (`delivery_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_delivery_notes`
--

LOCK TABLES `ec_delivery_notes` WRITE;
/*!40000 ALTER TABLE `ec_delivery_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_delivery_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_demo_credentials`
--

DROP TABLE IF EXISTS `ec_demo_credentials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_demo_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `credential_number` varchar(120) NOT NULL,
  `portal_name` varchar(160) DEFAULT NULL,
  `role_label` varchar(160) DEFAULT NULL,
  `login_url` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password_hint` varchar(255) DEFAULT NULL,
  `access_notes` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `credential_number` (`credential_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_demo_credentials`
--

LOCK TABLES `ec_demo_credentials` WRITE;
/*!40000 ALTER TABLE `ec_demo_credentials` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_demo_credentials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_deployment_checklist_items`
--

DROP TABLE IF EXISTS `ec_deployment_checklist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_deployment_checklist_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_key` varchar(160) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(120) DEFAULT NULL,
  `severity` varchar(40) DEFAULT 'medium',
  `status` varchar(40) DEFAULT 'open',
  `recommendation` text DEFAULT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_key` (`item_key`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_deployment_checklist_items`
--

LOCK TABLES `ec_deployment_checklist_items` WRITE;
/*!40000 ALTER TABLE `ec_deployment_checklist_items` DISABLE KEYS */;
INSERT INTO `ec_deployment_checklist_items` VALUES (1,'https_ssl','Enable HTTPS / SSL','Security','high','open','Point the production domain to HTTPS and update SHOP_URL to https://.',NULL,NULL,'2026-06-12 11:21:09'),(2,'disable_display_errors','Disable PHP display_errors','Security','high','open','Keep display_errors disabled and log errors server-side.',NULL,NULL,'2026-06-12 11:21:09'),(3,'backup_schedule','Configure backup schedule','Operations','high','open','Schedule database backups and verify restore procedure.',NULL,NULL,'2026-06-12 11:21:09'),(4,'cron_job','Configure cron runner','Automation','medium','open','Add a server cron hitting /admin/erp/cron-runner.php?token=YOUR_SECRET or run from UI.',NULL,NULL,'2026-06-12 11:21:09'),(5,'mail_delivery','Verify email delivery','Communication','medium','open','Configure SMTP/server email sending and test notification templates.',NULL,NULL,'2026-06-12 11:21:09'),(6,'file_permissions','Check file permissions','Security','high','open','Uploads/backups should be writable; config and PHP source should not be publicly editable.',NULL,NULL,'2026-06-12 11:21:09'),(7,'api_key_review','Review API keys','Integration','medium','open','Create scoped API keys only for approved integrations and rotate regularly.',NULL,NULL,'2026-06-12 11:21:09'),(8,'role_audit','Review employee roles','Access Control','medium','open','Confirm least-privilege roles and branch scopes for all employees.',NULL,NULL,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_deployment_checklist_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_digital_license_assignments`
--

DROP TABLE IF EXISTS `ec_digital_license_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_digital_license_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_number` varchar(120) NOT NULL,
  `digital_license_pool_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `assigned_to_name` varchar(255) DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'assigned',
  `delivery_status` varchar(40) DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `assignment_number` (`assignment_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_digital_license_assignments`
--

LOCK TABLES `ec_digital_license_assignments` WRITE;
/*!40000 ALTER TABLE `ec_digital_license_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_digital_license_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_digital_license_deliveries`
--

DROP TABLE IF EXISTS `ec_digital_license_deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_digital_license_deliveries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `digital_license_assignment_id` int(11) NOT NULL,
  `delivery_method` varchar(80) DEFAULT 'email',
  `recipient_email` varchar(255) DEFAULT NULL,
  `delivery_subject` varchar(255) DEFAULT NULL,
  `delivery_body` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'queued',
  `sent_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_digital_license_deliveries`
--

LOCK TABLES `ec_digital_license_deliveries` WRITE;
/*!40000 ALTER TABLE `ec_digital_license_deliveries` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_digital_license_deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_digital_license_pools`
--

DROP TABLE IF EXISTS `ec_digital_license_pools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_digital_license_pools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pool_number` varchar(120) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `pool_name` varchar(255) DEFAULT NULL,
  `license_type` varchar(120) DEFAULT 'software_key',
  `license_code` text DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `activation_link` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'available',
  `supplier_id` int(11) DEFAULT NULL,
  `cost_price` decimal(14,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `pool_number` (`pool_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_digital_license_pools`
--

LOCK TABLES `ec_digital_license_pools` WRITE;
/*!40000 ALTER TABLE `ec_digital_license_pools` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_digital_license_pools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_document_access_logs`
--

DROP TABLE IF EXISTS `ec_document_access_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_document_access_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_library_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `access_type` varchar(80) DEFAULT 'view',
  `ip_address` varchar(80) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_document_access_logs`
--

LOCK TABLES `ec_document_access_logs` WRITE;
/*!40000 ALTER TABLE `ec_document_access_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_document_access_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_document_approval_steps`
--

DROP TABLE IF EXISTS `ec_document_approval_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_document_approval_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_approval_id` int(11) NOT NULL,
  `step_number` int(11) DEFAULT 1,
  `approver_role` varchar(120) DEFAULT NULL,
  `approver_user_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'pending',
  `decision_notes` text DEFAULT NULL,
  `decided_by` int(11) DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_document_approval_steps`
--

LOCK TABLES `ec_document_approval_steps` WRITE;
/*!40000 ALTER TABLE `ec_document_approval_steps` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_document_approval_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_document_approvals`
--

DROP TABLE IF EXISTS `ec_document_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_document_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `approval_number` varchar(120) NOT NULL,
  `document_library_id` int(11) NOT NULL,
  `requested_by` int(11) DEFAULT NULL,
  `approval_type` varchar(120) DEFAULT 'document_review',
  `status` varchar(40) DEFAULT 'pending',
  `current_step` int(11) DEFAULT 1,
  `requested_at` datetime DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `approval_number` (`approval_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_document_approvals`
--

LOCK TABLES `ec_document_approvals` WRITE;
/*!40000 ALTER TABLE `ec_document_approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_document_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_document_attachments`
--

DROP TABLE IF EXISTS `ec_document_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_document_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `document_type` varchar(100) NOT NULL,
  `document_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `stored_path` varchar(255) NOT NULL,
  `mime_type` varchar(160) DEFAULT NULL,
  `file_size` int(11) DEFAULT 0,
  `uploaded_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_document_attachment` (`document_type`,`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_document_attachments`
--

LOCK TABLES `ec_document_attachments` WRITE;
/*!40000 ALTER TABLE `ec_document_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_document_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_document_categories`
--

DROP TABLE IF EXISTS `ec_document_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_document_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_code` varchar(120) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `module_key` varchar(120) DEFAULT 'general',
  `requires_expiry` tinyint(1) DEFAULT 0,
  `requires_approval` tinyint(1) DEFAULT 0,
  `default_retention_days` int(11) DEFAULT 0,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_code` (`category_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_document_categories`
--

LOCK TABLES `ec_document_categories` WRITE;
/*!40000 ALTER TABLE `ec_document_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_document_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_document_expiry_alerts`
--

DROP TABLE IF EXISTS `ec_document_expiry_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_document_expiry_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_number` varchar(120) NOT NULL,
  `document_library_id` int(11) NOT NULL,
  `alert_type` varchar(80) DEFAULT 'expiry',
  `days_before` int(11) DEFAULT 30,
  `alert_date` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `message` text DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `alert_number` (`alert_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_document_expiry_alerts`
--

LOCK TABLES `ec_document_expiry_alerts` WRITE;
/*!40000 ALTER TABLE `ec_document_expiry_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_document_expiry_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_document_folders`
--

DROP TABLE IF EXISTS `ec_document_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_document_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `folder_code` varchar(120) NOT NULL,
  `folder_name` varchar(255) NOT NULL,
  `folder_path` varchar(500) DEFAULT NULL,
  `module_key` varchar(120) DEFAULT 'general',
  `visibility` varchar(40) DEFAULT 'internal',
  `status` varchar(40) DEFAULT 'active',
  `sort_order` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `folder_code` (`folder_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_document_folders`
--

LOCK TABLES `ec_document_folders` WRITE;
/*!40000 ALTER TABLE `ec_document_folders` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_document_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_document_library`
--

DROP TABLE IF EXISTS `ec_document_library`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_document_library` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_number` varchar(120) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `folder_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `document_type` varchar(120) DEFAULT 'general',
  `module_key` varchar(120) DEFAULT 'general',
  `linked_entity_type` varchar(120) DEFAULT NULL,
  `linked_entity_id` int(11) DEFAULT NULL,
  `version_number` varchar(40) DEFAULT '1.0',
  `file_name` varchar(255) DEFAULT NULL,
  `stored_path` varchar(255) DEFAULT NULL,
  `mime_type` varchar(160) DEFAULT NULL,
  `file_size` int(11) DEFAULT 0,
  `expiry_date` date DEFAULT NULL,
  `review_date` date DEFAULT NULL,
  `confidentiality` varchar(40) DEFAULT 'internal',
  `approval_status` varchar(40) DEFAULT 'not_required',
  `status` varchar(40) DEFAULT 'active',
  `tags` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_number` (`document_number`),
  KEY `idx_dms_link` (`linked_entity_type`,`linked_entity_id`),
  KEY `idx_dms_expiry` (`expiry_date`),
  KEY `idx_dms_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_document_library`
--

LOCK TABLES `ec_document_library` WRITE;
/*!40000 ALTER TABLE `ec_document_library` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_document_library` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_document_linked_records`
--

DROP TABLE IF EXISTS `ec_document_linked_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_document_linked_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_library_id` int(11) NOT NULL,
  `module_key` varchar(120) DEFAULT NULL,
  `entity_type` varchar(120) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `entity_number` varchar(160) DEFAULT NULL,
  `link_notes` text DEFAULT NULL,
  `linked_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_dms_record_link` (`entity_type`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_document_linked_records`
--

LOCK TABLES `ec_document_linked_records` WRITE;
/*!40000 ALTER TABLE `ec_document_linked_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_document_linked_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_document_sequences`
--

DROP TABLE IF EXISTS `ec_document_sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_document_sequences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `document_type` varchar(80) NOT NULL,
  `prefix` varchar(120) NOT NULL,
  `next_number` int(11) DEFAULT 1,
  `padding` int(11) DEFAULT 5,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_doc_sequence_scope` (`company_id`,`branch_id`,`document_type`)
) ENGINE=InnoDB AUTO_INCREMENT=177 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_document_sequences`
--

LOCK TABLES `ec_document_sequences` WRITE;
/*!40000 ALTER TABLE `ec_document_sequences` DISABLE KEYS */;
INSERT INTO `ec_document_sequences` VALUES (1,1,1,'invoice','INV-BR-001',1,5,'active','2026-06-12 11:21:09'),(2,1,1,'quotation','QTN-BR-001',1,5,'active','2026-06-12 11:21:09'),(3,1,1,'purchase_order','PO-BR-001',1,5,'active','2026-06-12 11:21:09'),(4,1,1,'order','ORD-BR-001',1,5,'active','2026-06-12 11:21:09'),(5,1,1,'payment','PAY-BR-001',1,5,'active','2026-06-12 11:21:09'),(6,1,1,'journal','JRN-BR-001',1,5,'active','2026-06-12 11:21:09'),(7,1,1,'stock_transfer','TRF-BR-001',1,5,'active','2026-06-12 11:21:09'),(8,1,1,'intercompany_transaction','ICT-BR-001',1,5,'active','2026-06-12 11:21:09'),(9,1,1,'approval_request','APR-BR-001',1,5,'active','2026-06-12 11:21:09'),(10,1,1,'purchase_requisition','REQ-BR-001',1,5,'active','2026-06-12 11:21:09'),(11,1,1,'goods_receipt','GRN-BR-001',1,5,'active','2026-06-12 11:21:09'),(12,1,1,'supplier_invoice','SIN-BR-001',1,5,'active','2026-06-12 11:21:09'),(13,1,1,'sales_order','SO-BR-001',1,5,'active','2026-06-12 11:21:09'),(14,1,1,'delivery_note','DN-BR-001',1,5,'active','2026-06-12 11:21:09'),(15,1,1,'return_rma','RMA-BR-001',1,5,'active','2026-06-12 11:21:09'),(16,1,1,'job_card','JC-BR-001',1,5,'active','2026-06-12 11:21:09'),(17,1,1,'service_contract','AMC-BR-001',1,5,'active','2026-06-12 11:21:09'),(18,1,1,'warranty_claim','WCL-BR-001',1,5,'active','2026-06-12 11:21:09'),(19,1,1,'project','PRJ-BR-001',1,5,'active','2026-06-12 11:21:09'),(20,1,1,'rfq','RFQ-BR-001',1,5,'active','2026-06-12 11:21:09'),(21,1,1,'rfq_invitation','RFI-BR-001',1,5,'active','2026-06-12 11:21:09'),(22,1,1,'supplier_quote','SQ-BR-001',1,5,'active','2026-06-12 11:21:09'),(23,1,1,'procurement_tender','TND-BR-001',1,5,'active','2026-06-12 11:21:09'),(24,1,1,'workflow_run','WFR-BR-001',1,5,'active','2026-06-12 11:21:09'),(25,1,1,'sales_opportunity','OPP-BR-001',1,5,'active','2026-06-12 11:21:09'),(26,1,1,'marketing_campaign','CMP-BR-001',1,5,'active','2026-06-12 11:21:09'),(27,1,1,'crm_automation_run','CAR-BR-001',1,5,'active','2026-06-12 11:21:09'),(28,1,1,'payroll_period','PAYP-BR-001',1,5,'active','2026-06-12 11:21:09'),(29,1,1,'payroll_run','PAY-BR-001',1,5,'active','2026-06-12 11:21:09'),(30,1,1,'employee_expense_claim','EXPCL-BR-001',1,5,'active','2026-06-12 11:21:09'),(31,1,1,'commission_record','COM-BR-001',1,5,'active','2026-06-12 11:21:09'),(32,1,1,'performance_review','REV-BR-001',1,5,'active','2026-06-12 11:21:09'),(33,1,1,'financial_close','CLOSE-BR-001',1,5,'active','2026-06-12 11:21:09'),(34,1,1,'bank_reconciliation','BREC-BR-001',1,5,'active','2026-06-12 11:21:09'),(35,1,1,'fixed_asset','FA-BR-001',1,5,'active','2026-06-12 11:21:09'),(36,1,1,'tax_return','TAX-BR-001',1,5,'active','2026-06-12 11:21:09'),(37,1,1,'audit_control','CTRL-BR-001',1,5,'active','2026-06-12 11:21:09'),(38,1,1,'report_export','EXP-BR-001',1,5,'active','2026-06-12 11:21:09'),(39,1,1,'report_schedule','RSC-BR-001',1,5,'active','2026-06-12 11:21:09'),(40,1,1,'report_schedule_run','RSR-BR-001',1,5,'active','2026-06-12 11:21:09'),(41,1,1,'management_dashboard','MGMT-BR-001',1,5,'active','2026-06-12 11:21:09'),(42,1,1,'kpi_snapshot','KPI-BR-001',1,5,'active','2026-06-12 11:21:09'),(43,1,1,'webhook_event','WHE-BR-001',1,5,'active','2026-06-12 11:21:09'),(44,1,1,'webhook_subscription','WHS-BR-001',1,5,'active','2026-06-12 11:21:09'),(45,1,1,'integration_sync_job','SYNC-BR-001',1,5,'active','2026-06-12 11:21:09'),(46,1,1,'whatsapp_queue','WA-BR-001',1,5,'active','2026-06-12 11:21:09'),(47,1,1,'communication_automation_run','CARUN-BR-001',1,5,'active','2026-06-12 11:21:09'),(48,1,1,'field_dispatch','DISP-BR-001',1,5,'active','2026-06-12 11:21:09'),(49,1,1,'field_route','ROUTE-BR-001',1,5,'active','2026-06-12 11:21:09'),(50,1,1,'offline_job_card_draft','OFF-BR-001',1,5,'active','2026-06-12 11:21:09'),(51,1,1,'asset_qr_code','QR-BR-001',1,5,'active','2026-06-12 11:21:09'),(52,1,1,'customer_signoff','SIGN-BR-001',1,5,'active','2026-06-12 11:21:09'),(53,1,1,'ai_assistant_session','AIS-BR-001',1,5,'active','2026-06-12 11:21:09'),(54,1,1,'predictive_alert','PAL-BR-001',1,5,'active','2026-06-12 11:21:09'),(55,1,1,'recommendation_result','REC-BR-001',1,5,'active','2026-06-12 11:21:09'),(56,1,1,'anomaly_detection','ANM-BR-001',1,5,'active','2026-06-12 11:21:09'),(57,1,1,'decision_support_case','DSC-BR-001',1,5,'active','2026-06-12 11:21:09'),(58,1,1,'ai_insight_card','AIC-BR-001',1,5,'active','2026-06-12 11:21:09'),(59,1,1,'manufacturing_bom','BOM-BR-001',1,5,'active','2026-06-12 11:21:09'),(60,1,1,'manufacturing_work_order','MO-BR-001',1,5,'active','2026-06-12 11:21:09'),(61,1,1,'production_plan','PLAN-BR-001',1,5,'active','2026-06-12 11:21:09'),(62,1,1,'production_issue','ISSUE-BR-001',1,5,'active','2026-06-12 11:21:09'),(63,1,1,'production_receipt','RECEIPT-BR-001',1,5,'active','2026-06-12 11:21:09'),(64,1,1,'production_cost_rollup','COST-BR-001',1,5,'active','2026-06-12 11:21:09'),(65,1,1,'quality_check','QC-BR-001',1,5,'active','2026-06-12 11:21:09'),(66,1,1,'work_center','WC-BR-001',1,5,'active','2026-06-12 11:21:09'),(67,1,1,'warehouse_bin','BIN-BR-001',1,5,'active','2026-06-12 11:21:09'),(68,1,1,'inventory_lot','LOT-BR-001',1,5,'active','2026-06-12 11:21:09'),(69,1,1,'stock_count','COUNT-BR-001',1,5,'active','2026-06-12 11:21:09'),(70,1,1,'inventory_adjustment','ADJ-BR-001',1,5,'active','2026-06-12 11:21:09'),(71,1,1,'replenishment_suggestion','REP-BR-001',1,5,'active','2026-06-12 11:21:09'),(72,1,1,'picking_list','PICK-BR-001',1,5,'active','2026-06-12 11:21:09'),(73,1,1,'packing_slip','PACK-BR-001',1,5,'active','2026-06-12 11:21:09'),(74,1,1,'warehouse_dispatch','DISP-WH-BR-001',1,5,'active','2026-06-12 11:21:09'),(75,1,1,'supplier_onboarding','SON-BR-001',1,5,'active','2026-06-12 11:21:09'),(76,1,1,'supplier_scorecard','SSC-BR-001',1,5,'active','2026-06-12 11:21:09'),(77,1,1,'supplier_price_list','SPL-BR-001',1,5,'active','2026-06-12 11:21:09'),(78,1,1,'supplier_contract','SCON-BR-001',1,5,'active','2026-06-12 11:21:09'),(79,1,1,'procurement_award','AWD-BR-001',1,5,'active','2026-06-12 11:21:09'),(80,1,1,'rfq_quote_response','RQR-BR-001',1,5,'active','2026-06-12 11:21:09'),(81,1,1,'customer_invoice_dispute','DISP-BR-001',1,5,'active','2026-06-12 11:21:09'),(82,1,1,'customer_payment_promise','PROM-BR-001',1,5,'active','2026-06-12 11:21:09'),(83,1,1,'customer_service_request','CSR-BR-001',1,5,'active','2026-06-12 11:21:09'),(84,1,1,'crm_followup_task','CFT-BR-001',1,5,'active','2026-06-12 11:21:09'),(85,1,1,'crm_quote_followup','QFU-BR-001',1,5,'active','2026-06-12 11:21:09'),(86,1,1,'crm_sales_forecast','FCST-BR-001',1,5,'active','2026-06-12 11:21:09'),(87,1,1,'crm_touchpoint','TCH-BR-001',1,5,'active','2026-06-12 11:21:09'),(88,1,1,'crm_campaign_action','CAMP-ACT-BR-001',1,5,'active','2026-06-12 11:21:09'),(89,1,1,'employee_contract','ECON-BR-001',1,5,'active','2026-06-12 11:21:09'),(90,1,1,'employee_loan','ELOAN-BR-001',1,5,'active','2026-06-12 11:21:09'),(91,1,1,'employee_document','EDOC-BR-001',1,5,'active','2026-06-12 11:21:09'),(92,1,1,'employee_payslip','PAYSLIP-BR-001',1,5,'active','2026-06-12 11:21:09'),(93,1,1,'finance_automation_run','FAR-BR-001',1,5,'active','2026-06-12 11:21:09'),(94,1,1,'module_bundle','MBND-BR-001',1,5,'active','2026-06-12 11:21:09'),(95,1,1,'commercial_package','PKG-BR-001',1,5,'active','2026-06-12 11:21:09'),(96,1,1,'documentation_article','DOCART-BR-001',1,5,'active','2026-06-12 11:21:09'),(97,1,1,'documentation_asset','DOCAS-BR-001',1,5,'active','2026-06-12 11:21:09'),(98,1,1,'training_course','TRN-BR-001',1,5,'active','2026-06-12 11:21:09'),(99,1,1,'training_checklist','TRNCHK-BR-001',1,5,'active','2026-06-12 11:21:09'),(100,1,1,'demo_credential','DEMOCR-BR-001',1,5,'active','2026-06-12 11:21:09'),(101,1,1,'client_onboarding','ONB-BR-001',1,5,'active','2026-06-12 11:21:09'),(102,1,1,'feature_comparison','FCMP-BR-001',1,5,'active','2026-06-12 11:21:09'),(103,1,1,'sales_brochure','SBR-BR-001',1,5,'active','2026-06-12 11:21:09'),(104,1,1,'production_repair','PRUN-BR-001',1,5,'active','2026-06-12 11:21:09'),(105,1,1,'production_schema_check','SCHK-BR-001',1,5,'active','2026-06-12 11:21:09'),(106,1,1,'production_backup','PBACK-BR-001',1,5,'active','2026-06-12 11:21:09'),(107,1,1,'production_demo_batch','DEMO-BR-001',1,5,'active','2026-06-12 11:21:09'),(108,1,1,'production_installer_event','IEVT-BR-001',1,5,'active','2026-06-12 11:21:09'),(109,1,1,'production_release_checklist','REL-BR-001',1,5,'active','2026-06-12 11:21:09'),(110,1,1,'b2b_price_list','B2BPL-BR-001',1,5,'active','2026-06-12 11:21:09'),(111,1,1,'customer_price_rule','CPR-BR-001',1,5,'active','2026-06-12 11:21:09'),(112,1,1,'product_bundle','BNDL-BR-001',1,5,'active','2026-06-12 11:21:09'),(113,1,1,'digital_license_pool','DLIC-BR-001',1,5,'active','2026-06-12 11:21:09'),(114,1,1,'digital_license_assignment','DLAS-BR-001',1,5,'active','2026-06-12 11:21:09'),(115,1,1,'wishlist','WISH-BR-001',1,5,'active','2026-06-12 11:21:09'),(116,1,1,'comparison','COMP-BR-001',1,5,'active','2026-06-12 11:21:09'),(117,1,1,'quote_request','QRQ-BR-001',1,5,'active','2026-06-12 11:21:09'),(118,1,1,'bulk_order','BULK-BR-001',1,5,'active','2026-06-12 11:21:09'),(119,1,1,'ecommerce_discount_rule','EDISC-BR-001',1,5,'active','2026-06-12 11:21:09'),(120,1,1,'ecommerce_activity','EACT-BR-001',1,5,'active','2026-06-12 11:21:09'),(121,1,1,'document_library','DOC-BR-001',1,5,'active','2026-06-12 11:21:09'),(122,1,1,'document_folder','FLD-BR-001',1,5,'active','2026-06-12 11:21:09'),(123,1,1,'document_category','DCAT-BR-001',1,5,'active','2026-06-12 11:21:09'),(124,1,1,'document_approval','DAPP-BR-001',1,5,'active','2026-06-12 11:21:09'),(125,1,1,'document_expiry_alert','DEXP-BR-001',1,5,'active','2026-06-12 11:21:09'),(126,1,1,'pwa_asset','PWA-BR-001',1,5,'active','2026-06-12 11:21:09'),(127,1,1,'push_queue','PUSH-BR-001',1,5,'active','2026-06-12 11:21:09'),(128,1,1,'push_subscription','PUSHSUB-BR-001',1,5,'active','2026-06-12 11:21:09'),(129,1,1,'device_session','MOBDEV-BR-001',1,5,'active','2026-06-12 11:21:09'),(130,1,1,'mobile_install_event','MOBINS-BR-001',1,5,'active','2026-06-12 11:21:09'),(131,1,1,'mobile_sync','MSYNC-BR-001',1,5,'active','2026-06-12 11:21:09'),(132,1,1,'api_endpoint','APIEND-BR-001',1,5,'active','2026-06-12 11:21:09'),(133,1,1,'api_usage_limit','APILIM-BR-001',1,5,'active','2026-06-12 11:21:09'),(134,1,1,'webhook_template','WHTPL-BR-001',1,5,'active','2026-06-12 11:21:09'),(135,1,1,'webhook_retry','WHRTY-BR-001',1,5,'active','2026-06-12 11:21:09'),(136,1,1,'integration_mapping','IMAP-BR-001',1,5,'active','2026-06-12 11:21:09'),(137,1,1,'integration_error','IERR-BR-001',1,5,'active','2026-06-12 11:21:09'),(138,1,1,'integration_template','ICONN-BR-001',1,5,'active','2026-06-12 11:21:09'),(139,1,1,'accounting_export_batch','AEXP-BR-001',1,5,'active','2026-06-12 11:21:09'),(140,1,1,'marketplace_sync','MKTQ-BR-001',1,5,'active','2026-06-12 11:21:09'),(141,1,1,'saas_subscription_invoice','SUBINV-BR-001',1,5,'active','2026-06-12 11:21:09'),(142,1,1,'saas_subscription_payment','SUBPAY-BR-001',1,5,'active','2026-06-12 11:21:09'),(143,1,1,'trial_account','TRIAL-BR-001',1,5,'active','2026-06-12 11:21:09'),(144,1,1,'plan_change','PLNCHG-BR-001',1,5,'active','2026-06-12 11:21:09'),(145,1,1,'usage_enforcement','USG-BR-001',1,5,'active','2026-06-12 11:21:09'),(146,1,1,'tenant_domain','DOM-BR-001',1,5,'active','2026-06-12 11:21:09'),(147,1,1,'tenant_onboarding','ONB-BR-001',1,5,'active','2026-06-12 11:21:09'),(148,1,1,'security_event','SEV-BR-001',1,5,'active','2026-06-12 11:21:09'),(149,1,1,'login_session','LGS-BR-001',1,5,'active','2026-06-12 11:21:09'),(150,1,1,'permission_change','PCH-BR-001',1,5,'active','2026-06-12 11:21:09'),(151,1,1,'data_export','DEXP-BR-001',1,5,'active','2026-06-12 11:21:09'),(152,1,1,'sensitive_action','SAAP-BR-001',1,5,'active','2026-06-12 11:21:09'),(153,1,1,'ip_rule','IPR-BR-001',1,5,'active','2026-06-12 11:21:09'),(154,1,1,'compliance_checklist','COMP-BR-001',1,5,'active','2026-06-12 11:21:09'),(155,1,1,'workflow_builder_rule','WFB-BR-001',1,5,'active','2026-06-12 11:21:09'),(156,1,1,'workflow_builder_log','WFLOG-BR-001',1,5,'active','2026-06-12 11:21:09'),(157,1,1,'workflow_escalation','WFESC-BR-001',1,5,'active','2026-06-12 11:21:09'),(158,1,1,'ai_automation_run','AIRUN-BR-001',1,5,'active','2026-06-12 11:21:09'),(159,1,1,'ai_risk_score','RSK-BR-001',1,5,'active','2026-06-12 11:21:09'),(160,1,1,'ai_decision_recommendation','AIREC-BR-001',1,5,'active','2026-06-12 11:21:09'),(161,1,1,'ai_action_suggestion','ACTSUG-BR-001',1,5,'active','2026-06-12 11:21:09'),(162,1,1,'ai_playbook','AIPB-BR-001',1,5,'active','2026-06-12 11:21:09'),(163,1,1,'bi_metric','BIM-BR-001',1,5,'active','2026-06-12 11:21:09'),(164,1,1,'kpi_alert_rule','KAL-BR-001',1,5,'active','2026-06-12 11:21:09'),(165,1,1,'dashboard_filter','BIF-BR-001',1,5,'active','2026-06-12 11:21:09'),(166,1,1,'dashboard_share','BISHARE-BR-001',1,5,'active','2026-06-12 11:21:09'),(167,1,1,'report_drilldown','RDD-BR-001',1,5,'active','2026-06-12 11:21:09'),(168,1,1,'report_storyboard','STORY-BR-001',1,5,'active','2026-06-12 11:21:09'),(169,1,1,'recurring_journal','RJ-BR-001',1,5,'active','2026-06-12 11:21:09'),(170,1,1,'budget_version','BUD-BR-001',1,5,'active','2026-06-12 11:21:09'),(171,1,1,'cash_flow_forecast','CFF-BR-001',1,5,'active','2026-06-12 11:21:09'),(172,1,1,'ar_aging','ARAGE-BR-001',1,5,'active','2026-06-12 11:21:09'),(173,1,1,'ap_aging','APAGE-BR-001',1,5,'active','2026-06-12 11:21:09'),(174,1,1,'collection_task','COLL-BR-001',1,5,'active','2026-06-12 11:21:09'),(175,1,1,'supplier_payment_run','SPR-BR-001',1,5,'active','2026-06-12 11:21:09'),(176,1,1,'shift_template','SHIFT-BR-001',1,5,'active','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_document_sequences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_document_versions`
--

DROP TABLE IF EXISTS `ec_document_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_document_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_library_id` int(11) NOT NULL,
  `version_number` varchar(40) DEFAULT '1.0',
  `file_name` varchar(255) DEFAULT NULL,
  `stored_path` varchar(255) DEFAULT NULL,
  `mime_type` varchar(160) DEFAULT NULL,
  `file_size` int(11) DEFAULT 0,
  `change_summary` text DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'current',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_document_versions`
--

LOCK TABLES `ec_document_versions` WRITE;
/*!40000 ALTER TABLE `ec_document_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_document_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_documentation_articles`
--

DROP TABLE IF EXISTS `ec_documentation_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_documentation_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_number` varchar(120) NOT NULL,
  `title` varchar(255) NOT NULL,
  `doc_type` varchar(120) DEFAULT 'manual',
  `audience` varchar(120) DEFAULT 'admin',
  `module_key` varchar(120) DEFAULT 'general',
  `slug` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'published',
  `sort_order` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_number` (`article_number`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_documentation_articles`
--

LOCK TABLES `ec_documentation_articles` WRITE;
/*!40000 ALTER TABLE `ec_documentation_articles` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_documentation_articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_documentation_assets`
--

DROP TABLE IF EXISTS `ec_documentation_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_documentation_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_number` varchar(120) NOT NULL,
  `asset_title` varchar(255) DEFAULT NULL,
  `asset_type` varchar(120) DEFAULT 'guide',
  `file_path` varchar(255) DEFAULT NULL,
  `target_audience` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_number` (`asset_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_documentation_assets`
--

LOCK TABLES `ec_documentation_assets` WRITE;
/*!40000 ALTER TABLE `ec_documentation_assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_documentation_assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_downloads`
--

DROP TABLE IF EXISTS `ec_downloads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_downloads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` varchar(80) DEFAULT NULL,
  `download_count` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_downloads`
--

LOCK TABLES `ec_downloads` WRITE;
/*!40000 ALTER TABLE `ec_downloads` DISABLE KEYS */;
INSERT INTO `ec_downloads` VALUES (1,'Trade Buyer Catalogue','Upload a starter catalogue for trade customers.','trade-buyer-catalogue.pdf','1.1 MB',0,1,'2026-06-12 11:21:09'),(2,'Procurement Checklist','Upload the onboarding checklist file into uploads/downloads/.','procurement-checklist.pdf','680 KB',0,1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_downloads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ecommerce_activity_logs`
--

DROP TABLE IF EXISTS `ec_ecommerce_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ecommerce_activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_token` varchar(160) DEFAULT NULL,
  `activity_type` varchar(120) DEFAULT NULL,
  `entity_type` varchar(120) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `activity_number` (`activity_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ecommerce_activity_logs`
--

LOCK TABLES `ec_ecommerce_activity_logs` WRITE;
/*!40000 ALTER TABLE `ec_ecommerce_activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ecommerce_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ecommerce_discount_rules`
--

DROP TABLE IF EXISTS `ec_ecommerce_discount_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ecommerce_discount_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_number` varchar(120) NOT NULL,
  `rule_name` varchar(255) DEFAULT NULL,
  `rule_scope` varchar(80) DEFAULT 'cart',
  `rule_type` varchar(80) DEFAULT 'percent',
  `rule_value` decimal(14,2) DEFAULT 0.00,
  `min_subtotal` decimal(14,2) DEFAULT 0.00,
  `coupon_code` varchar(120) DEFAULT NULL,
  `customer_type` varchar(40) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_number` (`rule_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ecommerce_discount_rules`
--

LOCK TABLES `ec_ecommerce_discount_rules` WRITE;
/*!40000 ALTER TABLE `ec_ecommerce_discount_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ecommerce_discount_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_email_queue`
--

DROP TABLE IF EXISTS `ec_email_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_email_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_template_id` int(11) DEFAULT NULL,
  `recipient_email` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'queued',
  `attempts` int(11) DEFAULT 0,
  `last_error` text DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_email_queue`
--

LOCK TABLES `ec_email_queue` WRITE;
/*!40000 ALTER TABLE `ec_email_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_email_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_email_templates`
--

DROP TABLE IF EXISTS `ec_email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_key` varchar(160) NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_key` (`template_key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_email_templates`
--

LOCK TABLES `ec_email_templates` WRITE;
/*!40000 ALTER TABLE `ec_email_templates` DISABLE KEYS */;
INSERT INTO `ec_email_templates` VALUES (1,'approval_pending','Approval Pending','Approval required: {{request_number}}','Hello,\\n\\nApproval request {{request_number}} requires your review.\\n\\nDocument: {{document_type}}\\nAmount: {{amount}}\\n\\nOpen ERP Approval Center to review.','active','2026-06-12 11:21:09',NULL),(2,'order_confirmation','Order Confirmation','Your order {{order_number}} is received','Dear {{customer_name}},\\n\\nThank you. Your order {{order_number}} has been received.\\n\\nTotal: {{total}}\\n\\nRegards,\\n{{shop_name}}','active','2026-06-12 11:21:09',NULL),(3,'low_stock_alert','Low Stock Alert','Low stock: {{sku}}','Product {{sku}} - {{product_name}} is below the configured stock threshold.','active','2026-06-12 11:21:09',NULL),(4,'credit_hold_notice','Credit Hold Notice','Credit review required for {{customer_name}}','Customer {{customer_name}} requires credit review before order approval.','active','2026-06-12 11:21:09',NULL),(5,'backup_completed','Backup Completed','ERP backup {{backup_number}} completed','Database backup {{backup_number}} has been created successfully.','active','2026-06-12 11:21:09',NULL);
/*!40000 ALTER TABLE `ec_email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_employee_contracts`
--

DROP TABLE IF EXISTS `ec_employee_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_employee_contracts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_number` varchar(120) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `contract_type` varchar(120) DEFAULT 'full_time',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `basic_salary` decimal(14,2) DEFAULT 0.00,
  `allowance_total` decimal(14,2) DEFAULT 0.00,
  `working_hours_per_day` decimal(8,2) DEFAULT 8.00,
  `working_days_per_month` decimal(8,2) DEFAULT 26.00,
  `status` varchar(40) DEFAULT 'active',
  `document_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `contract_number` (`contract_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_employee_contracts`
--

LOCK TABLES `ec_employee_contracts` WRITE;
/*!40000 ALTER TABLE `ec_employee_contracts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_employee_contracts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_employee_documents`
--

DROP TABLE IF EXISTS `ec_employee_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_employee_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `document_type` varchar(160) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `stored_path` varchar(255) DEFAULT NULL,
  `mime_type` varchar(160) DEFAULT NULL,
  `file_size` int(11) DEFAULT 0,
  `expiry_date` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_employee_documents`
--

LOCK TABLES `ec_employee_documents` WRITE;
/*!40000 ALTER TABLE `ec_employee_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_employee_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_employee_expense_claim_items`
--

DROP TABLE IF EXISTS `ec_employee_expense_claim_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_employee_expense_claim_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_expense_claim_id` int(11) NOT NULL,
  `category` varchar(120) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT 0.00,
  `tax` decimal(12,2) DEFAULT 0.00,
  `receipt_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_employee_expense_claim_items`
--

LOCK TABLES `ec_employee_expense_claim_items` WRITE;
/*!40000 ALTER TABLE `ec_employee_expense_claim_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_employee_expense_claim_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_employee_expense_claims`
--

DROP TABLE IF EXISTS `ec_employee_expense_claims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_employee_expense_claims` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `claim_number` varchar(120) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `claim_date` date DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'draft',
  `approval_status` varchar(40) DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `claim_number` (`claim_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_employee_expense_claims`
--

LOCK TABLES `ec_employee_expense_claims` WRITE;
/*!40000 ALTER TABLE `ec_employee_expense_claims` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_employee_expense_claims` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_employee_goals`
--

DROP TABLE IF EXISTS `ec_employee_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_employee_goals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `goal_title` varchar(255) DEFAULT NULL,
  `target_metric` varchar(120) DEFAULT NULL,
  `target_value` decimal(12,2) DEFAULT 0.00,
  `actual_value` decimal(12,2) DEFAULT 0.00,
  `due_date` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_employee_goals`
--

LOCK TABLES `ec_employee_goals` WRITE;
/*!40000 ALTER TABLE `ec_employee_goals` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_employee_goals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_employee_leave_balances`
--

DROP TABLE IF EXISTS `ec_employee_leave_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_employee_leave_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `leave_type` varchar(120) DEFAULT NULL,
  `opening_balance` decimal(8,2) DEFAULT 0.00,
  `accrued_days` decimal(8,2) DEFAULT 0.00,
  `used_days` decimal(8,2) DEFAULT 0.00,
  `remaining_days` decimal(8,2) DEFAULT 0.00,
  `year_no` int(11) DEFAULT 0,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_emp_leave_year` (`employee_id`,`leave_type`,`year_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_employee_leave_balances`
--

LOCK TABLES `ec_employee_leave_balances` WRITE;
/*!40000 ALTER TABLE `ec_employee_leave_balances` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_employee_leave_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_employee_loan_repayments`
--

DROP TABLE IF EXISTS `ec_employee_loan_repayments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_employee_loan_repayments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_loan_id` int(11) NOT NULL,
  `payroll_run_id` int(11) DEFAULT NULL,
  `repayment_date` date DEFAULT NULL,
  `amount` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'posted',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_employee_loan_repayments`
--

LOCK TABLES `ec_employee_loan_repayments` WRITE;
/*!40000 ALTER TABLE `ec_employee_loan_repayments` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_employee_loan_repayments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_employee_loans`
--

DROP TABLE IF EXISTS `ec_employee_loans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_employee_loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_number` varchar(120) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `loan_type` varchar(120) DEFAULT 'advance',
  `principal_amount` decimal(14,2) DEFAULT 0.00,
  `installment_amount` decimal(14,2) DEFAULT 0.00,
  `balance_amount` decimal(14,2) DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `loan_number` (`loan_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_employee_loans`
--

LOCK TABLES `ec_employee_loans` WRITE;
/*!40000 ALTER TABLE `ec_employee_loans` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_employee_loans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_employee_payroll_documents`
--

DROP TABLE IF EXISTS `ec_employee_payroll_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_employee_payroll_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_run_item_id` int(11) DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `document_number` varchar(120) NOT NULL,
  `period_label` varchar(120) DEFAULT NULL,
  `gross_pay` decimal(14,2) DEFAULT 0.00,
  `net_pay` decimal(14,2) DEFAULT 0.00,
  `file_path` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_number` (`document_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_employee_payroll_documents`
--

LOCK TABLES `ec_employee_payroll_documents` WRITE;
/*!40000 ALTER TABLE `ec_employee_payroll_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_employee_payroll_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_employee_performance_reviews`
--

DROP TABLE IF EXISTS `ec_employee_performance_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_employee_performance_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `review_number` varchar(120) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `review_period` varchar(120) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `rating` decimal(4,2) DEFAULT 0.00,
  `goals_score` decimal(4,2) DEFAULT 0.00,
  `manager_comments` text DEFAULT NULL,
  `employee_comments` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `review_number` (`review_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_employee_performance_reviews`
--

LOCK TABLES `ec_employee_performance_reviews` WRITE;
/*!40000 ALTER TABLE `ec_employee_performance_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_employee_performance_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_employee_salary_components`
--

DROP TABLE IF EXISTS `ec_employee_salary_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_employee_salary_components` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `component_name` varchar(160) DEFAULT NULL,
  `component_type` varchar(80) DEFAULT 'allowance',
  `amount` decimal(14,2) DEFAULT 0.00,
  `taxable` tinyint(1) DEFAULT 0,
  `recurring` tinyint(1) DEFAULT 1,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_employee_salary_components`
--

LOCK TABLES `ec_employee_salary_components` WRITE;
/*!40000 ALTER TABLE `ec_employee_salary_components` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_employee_salary_components` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_employee_shift_assignments`
--

DROP TABLE IF EXISTS `ec_employee_shift_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_employee_shift_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `employee_shift_template_id` int(11) NOT NULL,
  `work_date` date NOT NULL,
  `status` varchar(40) DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_emp_shift_date` (`employee_id`,`work_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_employee_shift_assignments`
--

LOCK TABLES `ec_employee_shift_assignments` WRITE;
/*!40000 ALTER TABLE `ec_employee_shift_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_employee_shift_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_employee_shift_templates`
--

DROP TABLE IF EXISTS `ec_employee_shift_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_employee_shift_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shift_code` varchar(120) NOT NULL,
  `shift_name` varchar(255) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `break_minutes` int(11) DEFAULT 0,
  `standard_hours` decimal(8,2) DEFAULT 8.00,
  `status` varchar(40) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `shift_code` (`shift_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_employee_shift_templates`
--

LOCK TABLES `ec_employee_shift_templates` WRITE;
/*!40000 ALTER TABLE `ec_employee_shift_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_employee_shift_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_employees`
--

DROP TABLE IF EXISTS `ec_employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_code` varchar(80) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `position` varchar(120) DEFAULT NULL,
  `department` varchar(120) DEFAULT NULL,
  `salary` decimal(12,2) DEFAULT 0.00,
  `hire_date` date DEFAULT NULL,
  `status` varchar(30) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_code` (`employee_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_employees`
--

LOCK TABLES `ec_employees` WRITE;
/*!40000 ALTER TABLE `ec_employees` DISABLE KEYS */;
INSERT INTO `ec_employees` VALUES (1,'EMP-001','Admin','Manager','admin@ecommerce.local','','Operations Manager','Management',0.00,'2026-06-12','active','2026-06-12 11:21:09'),(2,'EMP-002','Sales','Agent','sales@ecommerce.local','','B2B Sales Executive','Sales',0.00,'2026-06-12','active','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_erp_roles`
--

DROP TABLE IF EXISTS `ec_erp_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_erp_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(160) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_erp_roles`
--

LOCK TABLES `ec_erp_roles` WRITE;
/*!40000 ALTER TABLE `ec_erp_roles` DISABLE KEYS */;
INSERT INTO `ec_erp_roles` VALUES (1,'ERP Manager','erp-manager','Full ERP access plus website sales tools','{\"access_erp\":true,\"dashboard\":true,\"manufacturing_dashboard\":true,\"bom_management\":true,\"work_orders\":true,\"production_planning\":true,\"material_issue\":true,\"production_receipts\":true,\"manufacturing_costing\":true,\"quality_checks\":true,\"work_centers\":true,\"ai_automation_dashboard\":true,\"decision_engine_2\":true,\"ai_risk_scoring\":true,\"smart_action_suggestions\":true,\"ai_assistant_2\":true,\"ai_assistant\":true,\"smart_search\":true,\"predictive_alerts\":true,\"recommendations\":true,\"decision_support\":true,\"anomaly_detection\":true,\"crm\":true,\"crm_advanced\":true,\"sales_pipeline\":true,\"marketing_campaigns\":true,\"lead_scoring\":true,\"customer_segments\":true,\"crm_automation\":true,\"sales_crm_dashboard\":true,\"sales_opportunities_2\":true,\"crm_followups\":true,\"quote_followups\":true,\"sales_forecast\":true,\"campaign_automation_2\":true,\"customers\":true,\"quotations\":true,\"invoices\":true,\"finance_automation_dashboard\":true,\"recurring_journals\":true,\"budgeting\":true,\"cash_flow_forecast\":true,\"ar_ap_aging\":true,\"supplier_payment_runs\":true,\"tax_automation_2\":true,\"finance\":true,\"accounting\":true,\"financial_close\":true,\"bank_reconciliation\":true,\"fixed_assets\":true,\"tax_filing\":true,\"audit_controls\":true,\"org_structure\":true,\"stock_transfers\":true,\"inventory_valuation\":true,\"intercompany\":true,\"consolidation\":true,\"approvals\":true,\"approval_rules\":true,\"procurement_dashboard\":true,\"supplier_onboarding\":true,\"supplier_scorecards\":true,\"supplier_price_lists\":true,\"supplier_contracts\":true,\"rfq_comparison\":true,\"purchase_requisitions\":true,\"goods_receipts\":true,\"supplier_invoices\":true,\"sales_orders\":true,\"delivery_notes\":true,\"returns_rma\":true,\"credit_control\":true,\"document_attachments\":true,\"cost_centers\":true,\"job_cards\":true,\"technician_timesheets\":true,\"service_contracts\":true,\"warranty_claims\":true,\"projects\":true,\"budget_control\":true,\"executive_bi\":true,\"bi_dashboard_2\":true,\"metric_library\":true,\"kpi_alerts\":true,\"report_drilldowns\":true,\"report_storyboards\":true,\"dataset_cache\":true,\"advanced_reporting\":true,\"kpi_builder\":true,\"scheduled_reports\":true,\"report_exports\":true,\"management_dashboards\":true,\"report_builder\":true,\"data_import_export\":true,\"notifications\":true,\"api_dashboard_2\":true,\"api_endpoint_catalog\":true,\"api_usage_limits\":true,\"webhook_builder_2\":true,\"integration_connectors_2\":true,\"integration_field_mappings\":true,\"integration_error_logs\":true,\"api_docs_2\":true,\"marketplace_sync\":true,\"api_keys\":true,\"api_marketplace\":true,\"integrations\":true,\"webhooks\":true,\"whatsapp_automation\":true,\"communication_automation\":true,\"developer_docs\":true,\"audit_trail\":true,\"security_compliance_dashboard\":true,\"login_session_monitor\":true,\"permission_change_history\":true,\"data_export_tracking\":true,\"sensitive_action_approvals\":true,\"security_policy_center\":true,\"b2b_price_lists\":true,\"customer_price_rules\":true,\"product_bundles\":true,\"digital_license_control\":true,\"wishlist_control\":true,\"product_comparison_control\":true,\"advanced_quote_requests\":true,\"module_bundle_manager\":true,\"documentation_center\":true,\"training_center\":true,\"demo_credentials\":true,\"commercial_packaging\":true,\"client_onboarding_checklist\":true,\"feature_comparison_sheet\":true,\"sales_brochure_builder\":true,\"handover_center\":true,\"production_hardening_dashboard\":true,\"system_repair_center\":true,\"database_migration_updater\":true,\"installer_health_check\":true,\"demo_data_manager\":true,\"permission_repair\":true,\"settings_repair\":true,\"table_column_checker\":true,\"production_error_log_viewer\":true,\"advanced_ecommerce_settings\":true,\"document_library_2\":true,\"document_folders\":true,\"document_versions\":true,\"document_approvals\":true,\"document_expiry_alerts\":true,\"document_access_logs\":true,\"compliance_checklists\":true,\"security_center\":true,\"backup_restore\":true,\"system_health\":true,\"error_logs\":true,\"cron_runner\":true,\"email_templates\":true,\"dashboard_widgets\":true,\"deployment_checklist\":true,\"migration_center\":true,\"saas_dashboard_2\":true,\"tenant_subscriptions_2\":true,\"tenant_billing\":true,\"trial_accounts\":true,\"plan_module_matrix\":true,\"usage_enforcement\":true,\"tenant_onboarding\":true,\"subscription_plans\":true,\"license_center\":true,\"update_center\":true,\"tenant_usage\":true,\"customer_portal_admin\":true,\"customer_portal_dashboard\":true,\"customer_documents\":true,\"customer_feedback\":true,\"customer_announcements\":true,\"customer_disputes\":true,\"vendor_portal_admin\":true,\"technician_portal\":true,\"pwa_settings\":true,\"mobile_app_readiness\":true,\"push_notifications\":true,\"mobile_shell\":true,\"mobile_offline_sync\":true,\"mobile_quick_actions_2\":true,\"mobile_erp\":true,\"field_dispatch\":true,\"field_service_routes\":true,\"offline_job_cards\":true,\"barcode_qr\":true,\"mobile_parts_usage\":true,\"customer_signoff\":true,\"technician_checklists\":true,\"rfq_management\":true,\"tender_management\":true,\"workflow_builder_2\":true,\"workflow_run_history_2\":true,\"workflow_approval_automation\":true,\"workflow_templates_2\":true,\"workflow_automation\":true,\"inventory\":true,\"warehouse_dashboard\":true,\"bin_locations\":true,\"lot_serial_tracking\":true,\"stock_counts\":true,\"inventory_adjustments\":true,\"picking_packing\":true,\"warehouse_dispatch\":true,\"replenishment\":true,\"suppliers\":true,\"purchase_orders\":true,\"hr_dashboard_2\":true,\"employee_contracts\":true,\"shift_scheduling\":true,\"leave_balances\":true,\"employee_loans\":true,\"employee_self_service_admin\":true,\"hr\":true,\"attendance\":true,\"payroll\":true,\"employee_expenses\":true,\"commissions\":true,\"performance_management\":true,\"reports\":true,\"activity_log\":true,\"online_sales_orders\":true,\"online_products\":true}',1,'2026-06-12 11:21:09'),(2,'Sales & Online Orders','sales-online-orders','CRM, quotations, sales/service orders, job cards, delivery notes, RMA, and website order access','{\"access_erp\":true,\"dashboard\":true,\"manufacturing_dashboard\":true,\"bom_management\":true,\"work_orders\":true,\"production_planning\":true,\"material_issue\":true,\"production_receipts\":true,\"manufacturing_costing\":true,\"quality_checks\":true,\"work_centers\":true,\"ai_automation_dashboard\":true,\"decision_engine_2\":true,\"ai_risk_scoring\":true,\"smart_action_suggestions\":true,\"ai_assistant_2\":true,\"ai_assistant\":true,\"smart_search\":true,\"predictive_alerts\":true,\"recommendations\":true,\"decision_support\":true,\"anomaly_detection\":true,\"crm\":true,\"crm_advanced\":true,\"sales_pipeline\":true,\"marketing_campaigns\":true,\"lead_scoring\":true,\"customer_segments\":true,\"crm_automation\":true,\"sales_crm_dashboard\":true,\"sales_opportunities_2\":true,\"crm_followups\":true,\"quote_followups\":true,\"sales_forecast\":true,\"campaign_automation_2\":true,\"customers\":true,\"quotations\":true,\"invoices\":true,\"finance_automation_dashboard\":false,\"recurring_journals\":false,\"budgeting\":false,\"cash_flow_forecast\":false,\"ar_ap_aging\":false,\"supplier_payment_runs\":false,\"tax_automation_2\":false,\"finance\":false,\"accounting\":false,\"financial_close\":false,\"bank_reconciliation\":false,\"fixed_assets\":false,\"tax_filing\":false,\"audit_controls\":false,\"org_structure\":false,\"stock_transfers\":false,\"inventory_valuation\":false,\"intercompany\":false,\"consolidation\":false,\"approvals\":false,\"approval_rules\":false,\"procurement_dashboard\":false,\"supplier_onboarding\":false,\"supplier_scorecards\":false,\"supplier_price_lists\":false,\"supplier_contracts\":false,\"rfq_comparison\":false,\"purchase_requisitions\":false,\"goods_receipts\":false,\"supplier_invoices\":false,\"sales_orders\":true,\"delivery_notes\":true,\"returns_rma\":true,\"credit_control\":false,\"document_attachments\":true,\"cost_centers\":false,\"job_cards\":true,\"technician_timesheets\":false,\"service_contracts\":true,\"warranty_claims\":true,\"projects\":false,\"budget_control\":false,\"executive_bi\":false,\"bi_dashboard_2\":false,\"metric_library\":false,\"kpi_alerts\":false,\"report_drilldowns\":false,\"report_storyboards\":false,\"dataset_cache\":false,\"advanced_reporting\":false,\"kpi_builder\":false,\"scheduled_reports\":false,\"report_exports\":false,\"management_dashboards\":false,\"report_builder\":true,\"data_import_export\":false,\"notifications\":true,\"api_dashboard_2\":false,\"api_endpoint_catalog\":false,\"api_usage_limits\":false,\"webhook_builder_2\":false,\"integration_connectors_2\":false,\"integration_field_mappings\":false,\"integration_error_logs\":false,\"api_docs_2\":false,\"marketplace_sync\":false,\"api_keys\":false,\"api_marketplace\":false,\"integrations\":false,\"webhooks\":false,\"whatsapp_automation\":true,\"communication_automation\":true,\"developer_docs\":true,\"audit_trail\":false,\"security_compliance_dashboard\":false,\"login_session_monitor\":false,\"permission_change_history\":false,\"data_export_tracking\":false,\"sensitive_action_approvals\":false,\"security_policy_center\":false,\"b2b_price_lists\":false,\"customer_price_rules\":false,\"product_bundles\":false,\"digital_license_control\":false,\"wishlist_control\":false,\"product_comparison_control\":false,\"advanced_quote_requests\":false,\"module_bundle_manager\":false,\"documentation_center\":false,\"training_center\":false,\"demo_credentials\":false,\"commercial_packaging\":false,\"client_onboarding_checklist\":false,\"feature_comparison_sheet\":false,\"sales_brochure_builder\":false,\"handover_center\":false,\"production_hardening_dashboard\":false,\"system_repair_center\":false,\"database_migration_updater\":false,\"installer_health_check\":false,\"demo_data_manager\":false,\"permission_repair\":false,\"settings_repair\":false,\"table_column_checker\":false,\"production_error_log_viewer\":false,\"advanced_ecommerce_settings\":false,\"document_library_2\":false,\"document_folders\":false,\"document_versions\":false,\"document_approvals\":false,\"document_expiry_alerts\":false,\"document_access_logs\":false,\"compliance_checklists\":false,\"security_center\":false,\"backup_restore\":false,\"system_health\":false,\"error_logs\":false,\"cron_runner\":false,\"email_templates\":false,\"dashboard_widgets\":false,\"deployment_checklist\":false,\"migration_center\":false,\"saas_dashboard_2\":false,\"tenant_subscriptions_2\":false,\"tenant_billing\":false,\"trial_accounts\":false,\"plan_module_matrix\":false,\"usage_enforcement\":false,\"tenant_onboarding\":false,\"subscription_plans\":false,\"license_center\":false,\"update_center\":false,\"tenant_usage\":false,\"customer_portal_admin\":true,\"customer_portal_dashboard\":true,\"customer_documents\":true,\"customer_feedback\":true,\"customer_announcements\":true,\"customer_disputes\":true,\"vendor_portal_admin\":false,\"technician_portal\":true,\"pwa_settings\":true,\"mobile_app_readiness\":true,\"push_notifications\":true,\"mobile_shell\":true,\"mobile_offline_sync\":true,\"mobile_quick_actions_2\":true,\"mobile_erp\":true,\"field_dispatch\":true,\"field_service_routes\":true,\"offline_job_cards\":true,\"barcode_qr\":true,\"mobile_parts_usage\":true,\"customer_signoff\":true,\"technician_checklists\":true,\"rfq_management\":false,\"tender_management\":false,\"workflow_builder_2\":false,\"workflow_run_history_2\":false,\"workflow_approval_automation\":false,\"workflow_templates_2\":false,\"workflow_automation\":false,\"inventory\":false,\"warehouse_dashboard\":false,\"bin_locations\":false,\"lot_serial_tracking\":false,\"stock_counts\":false,\"inventory_adjustments\":false,\"picking_packing\":false,\"warehouse_dispatch\":false,\"replenishment\":false,\"suppliers\":false,\"purchase_orders\":false,\"hr_dashboard_2\":false,\"employee_contracts\":false,\"shift_scheduling\":false,\"leave_balances\":false,\"employee_loans\":false,\"employee_self_service_admin\":false,\"hr\":false,\"attendance\":false,\"payroll\":false,\"employee_expenses\":false,\"commissions\":false,\"performance_management\":false,\"reports\":true,\"activity_log\":false,\"online_sales_orders\":true,\"online_products\":false}',1,'2026-06-12 11:21:09'),(3,'Inventory & Procurement','inventory-procurement','Requisitions, purchase orders, GRNs, stock, job-card parts, transfers, approvals, and valuation access','{\"access_erp\":true,\"dashboard\":true,\"manufacturing_dashboard\":false,\"bom_management\":false,\"work_orders\":false,\"production_planning\":false,\"material_issue\":false,\"production_receipts\":false,\"manufacturing_costing\":false,\"quality_checks\":false,\"work_centers\":false,\"ai_automation_dashboard\":false,\"decision_engine_2\":false,\"ai_risk_scoring\":false,\"smart_action_suggestions\":false,\"ai_assistant_2\":false,\"ai_assistant\":false,\"smart_search\":false,\"predictive_alerts\":false,\"recommendations\":false,\"decision_support\":false,\"anomaly_detection\":false,\"crm\":false,\"crm_advanced\":false,\"sales_pipeline\":false,\"marketing_campaigns\":false,\"lead_scoring\":false,\"customer_segments\":false,\"crm_automation\":false,\"sales_crm_dashboard\":false,\"sales_opportunities_2\":false,\"crm_followups\":false,\"quote_followups\":false,\"sales_forecast\":false,\"campaign_automation_2\":false,\"customers\":false,\"quotations\":false,\"invoices\":false,\"finance_automation_dashboard\":false,\"recurring_journals\":false,\"budgeting\":false,\"cash_flow_forecast\":false,\"ar_ap_aging\":false,\"supplier_payment_runs\":false,\"tax_automation_2\":false,\"finance\":false,\"accounting\":false,\"financial_close\":false,\"bank_reconciliation\":false,\"fixed_assets\":false,\"tax_filing\":false,\"audit_controls\":false,\"org_structure\":false,\"stock_transfers\":true,\"inventory_valuation\":true,\"intercompany\":false,\"consolidation\":false,\"approvals\":true,\"approval_rules\":false,\"procurement_dashboard\":true,\"supplier_onboarding\":true,\"supplier_scorecards\":true,\"supplier_price_lists\":true,\"supplier_contracts\":true,\"rfq_comparison\":true,\"purchase_requisitions\":true,\"goods_receipts\":true,\"supplier_invoices\":false,\"sales_orders\":false,\"delivery_notes\":false,\"returns_rma\":false,\"credit_control\":false,\"document_attachments\":true,\"cost_centers\":false,\"job_cards\":true,\"technician_timesheets\":false,\"service_contracts\":false,\"warranty_claims\":true,\"projects\":false,\"budget_control\":false,\"executive_bi\":false,\"bi_dashboard_2\":false,\"metric_library\":false,\"kpi_alerts\":false,\"report_drilldowns\":false,\"report_storyboards\":false,\"dataset_cache\":false,\"advanced_reporting\":false,\"kpi_builder\":false,\"scheduled_reports\":false,\"report_exports\":false,\"management_dashboards\":false,\"report_builder\":true,\"data_import_export\":true,\"notifications\":true,\"api_dashboard_2\":false,\"api_endpoint_catalog\":false,\"api_usage_limits\":false,\"webhook_builder_2\":false,\"integration_connectors_2\":false,\"integration_field_mappings\":false,\"integration_error_logs\":false,\"api_docs_2\":false,\"marketplace_sync\":false,\"api_keys\":false,\"api_marketplace\":false,\"integrations\":false,\"webhooks\":false,\"whatsapp_automation\":false,\"communication_automation\":false,\"developer_docs\":false,\"audit_trail\":false,\"security_compliance_dashboard\":false,\"login_session_monitor\":false,\"permission_change_history\":false,\"data_export_tracking\":false,\"sensitive_action_approvals\":false,\"security_policy_center\":false,\"b2b_price_lists\":false,\"customer_price_rules\":false,\"product_bundles\":false,\"digital_license_control\":false,\"wishlist_control\":false,\"product_comparison_control\":false,\"advanced_quote_requests\":false,\"module_bundle_manager\":false,\"documentation_center\":false,\"training_center\":false,\"demo_credentials\":false,\"commercial_packaging\":false,\"client_onboarding_checklist\":false,\"feature_comparison_sheet\":false,\"sales_brochure_builder\":false,\"handover_center\":false,\"production_hardening_dashboard\":false,\"system_repair_center\":false,\"database_migration_updater\":false,\"installer_health_check\":false,\"demo_data_manager\":false,\"permission_repair\":false,\"settings_repair\":false,\"table_column_checker\":false,\"production_error_log_viewer\":false,\"advanced_ecommerce_settings\":false,\"document_library_2\":false,\"document_folders\":false,\"document_versions\":false,\"document_approvals\":false,\"document_expiry_alerts\":false,\"document_access_logs\":false,\"compliance_checklists\":false,\"security_center\":false,\"backup_restore\":false,\"system_health\":false,\"error_logs\":false,\"cron_runner\":false,\"email_templates\":false,\"dashboard_widgets\":false,\"deployment_checklist\":false,\"migration_center\":false,\"saas_dashboard_2\":false,\"tenant_subscriptions_2\":false,\"tenant_billing\":false,\"trial_accounts\":false,\"plan_module_matrix\":false,\"usage_enforcement\":false,\"tenant_onboarding\":false,\"subscription_plans\":false,\"license_center\":false,\"update_center\":false,\"tenant_usage\":false,\"customer_portal_admin\":false,\"customer_portal_dashboard\":false,\"customer_documents\":false,\"customer_feedback\":false,\"customer_announcements\":false,\"customer_disputes\":false,\"vendor_portal_admin\":true,\"technician_portal\":true,\"pwa_settings\":false,\"mobile_app_readiness\":false,\"push_notifications\":false,\"mobile_shell\":false,\"mobile_offline_sync\":false,\"mobile_quick_actions_2\":false,\"mobile_erp\":true,\"field_dispatch\":true,\"field_service_routes\":true,\"offline_job_cards\":false,\"barcode_qr\":true,\"mobile_parts_usage\":true,\"customer_signoff\":false,\"technician_checklists\":true,\"rfq_management\":true,\"tender_management\":true,\"workflow_builder_2\":true,\"workflow_run_history_2\":true,\"workflow_approval_automation\":true,\"workflow_templates_2\":true,\"workflow_automation\":true,\"inventory\":true,\"warehouse_dashboard\":true,\"bin_locations\":true,\"lot_serial_tracking\":true,\"stock_counts\":true,\"inventory_adjustments\":true,\"picking_packing\":true,\"warehouse_dispatch\":true,\"replenishment\":true,\"suppliers\":true,\"purchase_orders\":true,\"hr_dashboard_2\":false,\"employee_contracts\":false,\"shift_scheduling\":false,\"leave_balances\":false,\"employee_loans\":false,\"employee_self_service_admin\":false,\"hr\":false,\"attendance\":false,\"payroll\":false,\"employee_expenses\":false,\"commissions\":false,\"performance_management\":false,\"reports\":true,\"activity_log\":false,\"online_sales_orders\":false,\"online_products\":false}',1,'2026-06-12 11:21:09'),(4,'Finance','finance','Finance, supplier invoice matching, credit control, project costing, budgets, approvals, receivables, invoices, and consolidation reports','{\"access_erp\":true,\"dashboard\":true,\"manufacturing_dashboard\":false,\"bom_management\":false,\"work_orders\":false,\"production_planning\":false,\"material_issue\":false,\"production_receipts\":false,\"manufacturing_costing\":false,\"quality_checks\":false,\"work_centers\":false,\"ai_automation_dashboard\":false,\"decision_engine_2\":false,\"ai_risk_scoring\":false,\"smart_action_suggestions\":false,\"ai_assistant_2\":false,\"ai_assistant\":false,\"smart_search\":false,\"predictive_alerts\":false,\"recommendations\":false,\"decision_support\":false,\"anomaly_detection\":false,\"crm\":false,\"crm_advanced\":false,\"sales_pipeline\":false,\"marketing_campaigns\":false,\"lead_scoring\":false,\"customer_segments\":false,\"crm_automation\":false,\"sales_crm_dashboard\":false,\"sales_opportunities_2\":false,\"crm_followups\":false,\"quote_followups\":false,\"sales_forecast\":false,\"campaign_automation_2\":false,\"customers\":false,\"quotations\":false,\"invoices\":true,\"finance_automation_dashboard\":true,\"recurring_journals\":true,\"budgeting\":true,\"cash_flow_forecast\":true,\"ar_ap_aging\":true,\"supplier_payment_runs\":true,\"tax_automation_2\":true,\"finance\":true,\"accounting\":true,\"financial_close\":true,\"bank_reconciliation\":true,\"fixed_assets\":true,\"tax_filing\":true,\"audit_controls\":true,\"org_structure\":false,\"stock_transfers\":false,\"inventory_valuation\":true,\"intercompany\":true,\"consolidation\":true,\"approvals\":true,\"approval_rules\":false,\"procurement_dashboard\":false,\"supplier_onboarding\":false,\"supplier_scorecards\":false,\"supplier_price_lists\":false,\"supplier_contracts\":false,\"rfq_comparison\":false,\"purchase_requisitions\":false,\"goods_receipts\":false,\"supplier_invoices\":true,\"sales_orders\":false,\"delivery_notes\":false,\"returns_rma\":false,\"credit_control\":true,\"document_attachments\":true,\"cost_centers\":true,\"job_cards\":false,\"technician_timesheets\":false,\"service_contracts\":false,\"warranty_claims\":false,\"projects\":true,\"budget_control\":true,\"executive_bi\":true,\"bi_dashboard_2\":true,\"metric_library\":true,\"kpi_alerts\":true,\"report_drilldowns\":true,\"report_storyboards\":true,\"dataset_cache\":true,\"advanced_reporting\":true,\"kpi_builder\":true,\"scheduled_reports\":true,\"report_exports\":true,\"management_dashboards\":true,\"report_builder\":true,\"data_import_export\":false,\"notifications\":true,\"api_dashboard_2\":false,\"api_endpoint_catalog\":false,\"api_usage_limits\":false,\"webhook_builder_2\":false,\"integration_connectors_2\":false,\"integration_field_mappings\":false,\"integration_error_logs\":false,\"api_docs_2\":false,\"marketplace_sync\":false,\"api_keys\":false,\"api_marketplace\":true,\"integrations\":true,\"webhooks\":true,\"whatsapp_automation\":false,\"communication_automation\":false,\"developer_docs\":true,\"audit_trail\":true,\"security_compliance_dashboard\":false,\"login_session_monitor\":false,\"permission_change_history\":false,\"data_export_tracking\":false,\"sensitive_action_approvals\":false,\"security_policy_center\":false,\"b2b_price_lists\":false,\"customer_price_rules\":false,\"product_bundles\":false,\"digital_license_control\":false,\"wishlist_control\":false,\"product_comparison_control\":false,\"advanced_quote_requests\":false,\"module_bundle_manager\":false,\"documentation_center\":false,\"training_center\":false,\"demo_credentials\":false,\"commercial_packaging\":false,\"client_onboarding_checklist\":false,\"feature_comparison_sheet\":false,\"sales_brochure_builder\":false,\"handover_center\":false,\"production_hardening_dashboard\":false,\"system_repair_center\":false,\"database_migration_updater\":false,\"installer_health_check\":false,\"demo_data_manager\":false,\"permission_repair\":false,\"settings_repair\":false,\"table_column_checker\":false,\"production_error_log_viewer\":false,\"advanced_ecommerce_settings\":false,\"document_library_2\":false,\"document_folders\":false,\"document_versions\":false,\"document_approvals\":false,\"document_expiry_alerts\":false,\"document_access_logs\":false,\"compliance_checklists\":false,\"security_center\":false,\"backup_restore\":false,\"system_health\":false,\"error_logs\":false,\"cron_runner\":false,\"email_templates\":false,\"dashboard_widgets\":false,\"deployment_checklist\":false,\"migration_center\":false,\"saas_dashboard_2\":false,\"tenant_subscriptions_2\":false,\"tenant_billing\":false,\"trial_accounts\":false,\"plan_module_matrix\":false,\"usage_enforcement\":false,\"tenant_onboarding\":false,\"subscription_plans\":false,\"license_center\":false,\"update_center\":false,\"tenant_usage\":false,\"customer_portal_admin\":false,\"customer_portal_dashboard\":false,\"customer_documents\":false,\"customer_feedback\":false,\"customer_announcements\":false,\"customer_disputes\":false,\"vendor_portal_admin\":false,\"technician_portal\":false,\"pwa_settings\":false,\"mobile_app_readiness\":false,\"push_notifications\":false,\"mobile_shell\":false,\"mobile_offline_sync\":false,\"mobile_quick_actions_2\":false,\"mobile_erp\":false,\"field_dispatch\":false,\"field_service_routes\":false,\"offline_job_cards\":false,\"barcode_qr\":false,\"mobile_parts_usage\":false,\"customer_signoff\":false,\"technician_checklists\":false,\"rfq_management\":false,\"tender_management\":false,\"workflow_builder_2\":false,\"workflow_run_history_2\":false,\"workflow_approval_automation\":false,\"workflow_templates_2\":false,\"workflow_automation\":false,\"inventory\":false,\"warehouse_dashboard\":false,\"bin_locations\":false,\"lot_serial_tracking\":false,\"stock_counts\":false,\"inventory_adjustments\":false,\"picking_packing\":false,\"warehouse_dispatch\":false,\"replenishment\":false,\"suppliers\":false,\"purchase_orders\":false,\"hr_dashboard_2\":false,\"employee_contracts\":false,\"shift_scheduling\":false,\"leave_balances\":false,\"employee_loans\":false,\"employee_self_service_admin\":false,\"hr\":false,\"attendance\":false,\"payroll\":false,\"employee_expenses\":false,\"commissions\":false,\"performance_management\":false,\"reports\":true,\"activity_log\":false,\"online_sales_orders\":false,\"online_products\":false}',1,'2026-06-12 11:21:09'),(5,'HR','hr','Employee and leave workflow access','{\"access_erp\":true,\"dashboard\":true,\"manufacturing_dashboard\":false,\"bom_management\":false,\"work_orders\":false,\"production_planning\":false,\"material_issue\":false,\"production_receipts\":false,\"manufacturing_costing\":false,\"quality_checks\":false,\"work_centers\":false,\"ai_automation_dashboard\":false,\"decision_engine_2\":false,\"ai_risk_scoring\":false,\"smart_action_suggestions\":false,\"ai_assistant_2\":false,\"ai_assistant\":false,\"smart_search\":false,\"predictive_alerts\":false,\"recommendations\":false,\"decision_support\":false,\"anomaly_detection\":false,\"crm\":false,\"crm_advanced\":false,\"sales_pipeline\":false,\"marketing_campaigns\":false,\"lead_scoring\":false,\"customer_segments\":false,\"crm_automation\":false,\"sales_crm_dashboard\":false,\"sales_opportunities_2\":false,\"crm_followups\":false,\"quote_followups\":false,\"sales_forecast\":false,\"campaign_automation_2\":false,\"customers\":false,\"quotations\":false,\"invoices\":false,\"finance_automation_dashboard\":false,\"recurring_journals\":false,\"budgeting\":false,\"cash_flow_forecast\":false,\"ar_ap_aging\":false,\"supplier_payment_runs\":false,\"tax_automation_2\":false,\"finance\":false,\"accounting\":false,\"financial_close\":false,\"bank_reconciliation\":false,\"fixed_assets\":false,\"tax_filing\":false,\"audit_controls\":false,\"org_structure\":false,\"stock_transfers\":false,\"inventory_valuation\":false,\"intercompany\":false,\"consolidation\":false,\"approvals\":false,\"approval_rules\":false,\"procurement_dashboard\":false,\"supplier_onboarding\":false,\"supplier_scorecards\":false,\"supplier_price_lists\":false,\"supplier_contracts\":false,\"rfq_comparison\":false,\"purchase_requisitions\":false,\"goods_receipts\":false,\"supplier_invoices\":false,\"sales_orders\":false,\"delivery_notes\":false,\"returns_rma\":false,\"credit_control\":false,\"document_attachments\":false,\"cost_centers\":false,\"job_cards\":false,\"technician_timesheets\":false,\"service_contracts\":false,\"warranty_claims\":false,\"projects\":false,\"budget_control\":false,\"executive_bi\":false,\"bi_dashboard_2\":false,\"metric_library\":false,\"kpi_alerts\":false,\"report_drilldowns\":false,\"report_storyboards\":false,\"dataset_cache\":false,\"advanced_reporting\":false,\"kpi_builder\":false,\"scheduled_reports\":false,\"report_exports\":false,\"management_dashboards\":false,\"report_builder\":true,\"data_import_export\":false,\"notifications\":true,\"api_dashboard_2\":false,\"api_endpoint_catalog\":false,\"api_usage_limits\":false,\"webhook_builder_2\":false,\"integration_connectors_2\":false,\"integration_field_mappings\":false,\"integration_error_logs\":false,\"api_docs_2\":false,\"marketplace_sync\":false,\"api_keys\":false,\"api_marketplace\":false,\"integrations\":false,\"webhooks\":false,\"whatsapp_automation\":false,\"communication_automation\":false,\"developer_docs\":false,\"audit_trail\":false,\"security_compliance_dashboard\":false,\"login_session_monitor\":false,\"permission_change_history\":false,\"data_export_tracking\":false,\"sensitive_action_approvals\":false,\"security_policy_center\":false,\"b2b_price_lists\":false,\"customer_price_rules\":false,\"product_bundles\":false,\"digital_license_control\":false,\"wishlist_control\":false,\"product_comparison_control\":false,\"advanced_quote_requests\":false,\"module_bundle_manager\":false,\"documentation_center\":false,\"training_center\":false,\"demo_credentials\":false,\"commercial_packaging\":false,\"client_onboarding_checklist\":false,\"feature_comparison_sheet\":false,\"sales_brochure_builder\":false,\"handover_center\":false,\"production_hardening_dashboard\":false,\"system_repair_center\":false,\"database_migration_updater\":false,\"installer_health_check\":false,\"demo_data_manager\":false,\"permission_repair\":false,\"settings_repair\":false,\"table_column_checker\":false,\"production_error_log_viewer\":false,\"advanced_ecommerce_settings\":false,\"document_library_2\":false,\"document_folders\":false,\"document_versions\":false,\"document_approvals\":false,\"document_expiry_alerts\":false,\"document_access_logs\":false,\"compliance_checklists\":false,\"security_center\":false,\"backup_restore\":false,\"system_health\":false,\"error_logs\":false,\"cron_runner\":false,\"email_templates\":false,\"dashboard_widgets\":false,\"deployment_checklist\":false,\"migration_center\":false,\"saas_dashboard_2\":false,\"tenant_subscriptions_2\":false,\"tenant_billing\":false,\"trial_accounts\":false,\"plan_module_matrix\":false,\"usage_enforcement\":false,\"tenant_onboarding\":false,\"subscription_plans\":false,\"license_center\":false,\"update_center\":false,\"tenant_usage\":false,\"customer_portal_admin\":false,\"customer_portal_dashboard\":false,\"customer_documents\":false,\"customer_feedback\":false,\"customer_announcements\":false,\"customer_disputes\":false,\"vendor_portal_admin\":false,\"technician_portal\":false,\"pwa_settings\":false,\"mobile_app_readiness\":false,\"push_notifications\":false,\"mobile_shell\":false,\"mobile_offline_sync\":false,\"mobile_quick_actions_2\":false,\"mobile_erp\":false,\"field_dispatch\":false,\"field_service_routes\":false,\"offline_job_cards\":false,\"barcode_qr\":false,\"mobile_parts_usage\":false,\"customer_signoff\":false,\"technician_checklists\":false,\"rfq_management\":false,\"tender_management\":false,\"workflow_builder_2\":false,\"workflow_run_history_2\":false,\"workflow_approval_automation\":false,\"workflow_templates_2\":false,\"workflow_automation\":false,\"inventory\":false,\"warehouse_dashboard\":false,\"bin_locations\":false,\"lot_serial_tracking\":false,\"stock_counts\":false,\"inventory_adjustments\":false,\"picking_packing\":false,\"warehouse_dispatch\":false,\"replenishment\":false,\"suppliers\":false,\"purchase_orders\":false,\"hr_dashboard_2\":true,\"employee_contracts\":true,\"shift_scheduling\":true,\"leave_balances\":true,\"employee_loans\":true,\"employee_self_service_admin\":true,\"hr\":true,\"attendance\":true,\"payroll\":true,\"employee_expenses\":true,\"commissions\":true,\"performance_management\":true,\"reports\":true,\"activity_log\":false,\"online_sales_orders\":false,\"online_products\":false}',1,'2026-06-12 11:21:09'),(6,'Developer Module Controller','module_bundle_developer','High-privilege developer-only access for Module Bundle Manager only.','{\"access_erp\":true,\"dashboard\":false,\"manufacturing_dashboard\":false,\"bom_management\":false,\"work_orders\":false,\"production_planning\":false,\"material_issue\":false,\"production_receipts\":false,\"manufacturing_costing\":false,\"quality_checks\":false,\"work_centers\":false,\"ai_automation_dashboard\":false,\"decision_engine_2\":false,\"ai_risk_scoring\":false,\"smart_action_suggestions\":false,\"ai_assistant_2\":false,\"ai_assistant\":false,\"smart_search\":false,\"predictive_alerts\":false,\"recommendations\":false,\"decision_support\":false,\"anomaly_detection\":false,\"crm\":false,\"crm_advanced\":false,\"sales_pipeline\":false,\"marketing_campaigns\":false,\"lead_scoring\":false,\"customer_segments\":false,\"crm_automation\":false,\"sales_crm_dashboard\":false,\"sales_opportunities_2\":false,\"crm_followups\":false,\"quote_followups\":false,\"sales_forecast\":false,\"campaign_automation_2\":false,\"customers\":false,\"quotations\":false,\"invoices\":false,\"finance_automation_dashboard\":false,\"recurring_journals\":false,\"budgeting\":false,\"cash_flow_forecast\":false,\"ar_ap_aging\":false,\"supplier_payment_runs\":false,\"tax_automation_2\":false,\"finance\":false,\"accounting\":false,\"financial_close\":false,\"bank_reconciliation\":false,\"fixed_assets\":false,\"tax_filing\":false,\"audit_controls\":false,\"org_structure\":false,\"stock_transfers\":false,\"inventory_valuation\":false,\"intercompany\":false,\"consolidation\":false,\"approvals\":false,\"approval_rules\":false,\"procurement_dashboard\":false,\"supplier_onboarding\":false,\"supplier_scorecards\":false,\"supplier_price_lists\":false,\"supplier_contracts\":false,\"rfq_comparison\":false,\"purchase_requisitions\":false,\"goods_receipts\":false,\"supplier_invoices\":false,\"sales_orders\":false,\"delivery_notes\":false,\"returns_rma\":false,\"credit_control\":false,\"document_attachments\":false,\"cost_centers\":false,\"job_cards\":false,\"technician_timesheets\":false,\"service_contracts\":false,\"warranty_claims\":false,\"projects\":false,\"budget_control\":false,\"executive_bi\":false,\"bi_dashboard_2\":false,\"metric_library\":false,\"kpi_alerts\":false,\"report_drilldowns\":false,\"report_storyboards\":false,\"dataset_cache\":false,\"advanced_reporting\":false,\"kpi_builder\":false,\"scheduled_reports\":false,\"report_exports\":false,\"management_dashboards\":false,\"report_builder\":false,\"data_import_export\":false,\"notifications\":false,\"api_dashboard_2\":false,\"api_endpoint_catalog\":false,\"api_usage_limits\":false,\"webhook_builder_2\":false,\"integration_connectors_2\":false,\"integration_field_mappings\":false,\"integration_error_logs\":false,\"api_docs_2\":false,\"marketplace_sync\":false,\"api_keys\":false,\"api_marketplace\":false,\"integrations\":false,\"webhooks\":false,\"whatsapp_automation\":false,\"communication_automation\":false,\"developer_docs\":false,\"audit_trail\":false,\"security_compliance_dashboard\":false,\"login_session_monitor\":false,\"permission_change_history\":false,\"data_export_tracking\":false,\"sensitive_action_approvals\":false,\"security_policy_center\":false,\"b2b_price_lists\":false,\"customer_price_rules\":false,\"product_bundles\":false,\"digital_license_control\":false,\"wishlist_control\":false,\"product_comparison_control\":false,\"advanced_quote_requests\":false,\"module_bundle_manager\":true,\"documentation_center\":false,\"training_center\":false,\"demo_credentials\":false,\"commercial_packaging\":false,\"client_onboarding_checklist\":false,\"feature_comparison_sheet\":false,\"sales_brochure_builder\":false,\"handover_center\":false,\"production_hardening_dashboard\":false,\"system_repair_center\":false,\"database_migration_updater\":false,\"installer_health_check\":false,\"demo_data_manager\":false,\"permission_repair\":false,\"settings_repair\":false,\"table_column_checker\":false,\"production_error_log_viewer\":false,\"advanced_ecommerce_settings\":false,\"document_library_2\":false,\"document_folders\":false,\"document_versions\":false,\"document_approvals\":false,\"document_expiry_alerts\":false,\"document_access_logs\":false,\"compliance_checklists\":false,\"security_center\":false,\"backup_restore\":false,\"system_health\":false,\"error_logs\":false,\"cron_runner\":false,\"email_templates\":false,\"dashboard_widgets\":false,\"deployment_checklist\":false,\"migration_center\":false,\"saas_dashboard_2\":false,\"tenant_subscriptions_2\":false,\"tenant_billing\":false,\"trial_accounts\":false,\"plan_module_matrix\":false,\"usage_enforcement\":false,\"tenant_onboarding\":false,\"subscription_plans\":false,\"license_center\":false,\"update_center\":false,\"tenant_usage\":false,\"customer_portal_admin\":false,\"customer_portal_dashboard\":false,\"customer_documents\":false,\"customer_feedback\":false,\"customer_announcements\":false,\"customer_disputes\":false,\"vendor_portal_admin\":false,\"technician_portal\":false,\"pwa_settings\":false,\"mobile_app_readiness\":false,\"push_notifications\":false,\"mobile_shell\":false,\"mobile_offline_sync\":false,\"mobile_quick_actions_2\":false,\"mobile_erp\":false,\"field_dispatch\":false,\"field_service_routes\":false,\"offline_job_cards\":false,\"barcode_qr\":false,\"mobile_parts_usage\":false,\"customer_signoff\":false,\"technician_checklists\":false,\"rfq_management\":false,\"tender_management\":false,\"workflow_builder_2\":false,\"workflow_run_history_2\":false,\"workflow_approval_automation\":false,\"workflow_templates_2\":false,\"workflow_automation\":false,\"inventory\":false,\"warehouse_dashboard\":false,\"bin_locations\":false,\"lot_serial_tracking\":false,\"stock_counts\":false,\"inventory_adjustments\":false,\"picking_packing\":false,\"warehouse_dispatch\":false,\"replenishment\":false,\"suppliers\":false,\"purchase_orders\":false,\"hr_dashboard_2\":false,\"employee_contracts\":false,\"shift_scheduling\":false,\"leave_balances\":false,\"employee_loans\":false,\"employee_self_service_admin\":false,\"hr\":false,\"attendance\":false,\"payroll\":false,\"employee_expenses\":false,\"commissions\":false,\"performance_management\":false,\"reports\":false,\"activity_log\":false,\"online_sales_orders\":false,\"online_products\":false}',1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_erp_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_expenses`
--

DROP TABLE IF EXISTS `ec_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `expense_number` varchar(80) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `vendor_name` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT 0.00,
  `tax` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `amount_paid` decimal(12,2) DEFAULT 0.00,
  `balance_due` decimal(12,2) DEFAULT 0.00,
  `payment_status` varchar(40) DEFAULT 'paid',
  `approval_status` varchar(50) DEFAULT 'not_required',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `expense_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `expense_number` (`expense_number`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_expenses`
--

LOCK TABLES `ec_expenses` WRITE;
/*!40000 ALTER TABLE `ec_expenses` DISABLE KEYS */;
INSERT INTO `ec_expenses` VALUES (1,1,1,1,'EXP-1001',NULL,'Logistics','Courier Partner',240.00,12.00,252.00,0.00,0.00,'paid','not_required',NULL,NULL,'2026-06-08',NULL,'Sample dispatch cost.','2026-06-12 11:21:09'),(2,1,1,1,'EXP-1002',NULL,'Software','Cloud Hosting',450.00,22.50,472.50,0.00,0.00,'paid','not_required',NULL,NULL,'2026-06-02',NULL,'Monthly application hosting.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_feature_comparison_items`
--

DROP TABLE IF EXISTS `ec_feature_comparison_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_feature_comparison_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_number` varchar(120) NOT NULL,
  `feature_area` varchar(160) DEFAULT NULL,
  `feature_name` varchar(255) DEFAULT NULL,
  `our_system` varchar(255) DEFAULT NULL,
  `sap_oracle_comparison` varchar(255) DEFAULT NULL,
  `business_value` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `sort_order` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_number` (`item_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_feature_comparison_items`
--

LOCK TABLES `ec_feature_comparison_items` WRITE;
/*!40000 ALTER TABLE `ec_feature_comparison_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_feature_comparison_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_field_customer_signoffs`
--

DROP TABLE IF EXISTS `ec_field_customer_signoffs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_field_customer_signoffs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `signoff_number` varchar(120) NOT NULL,
  `job_card_id` int(11) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(80) DEFAULT NULL,
  `signature_data` longtext DEFAULT NULL,
  `rating` int(11) DEFAULT 0,
  `status` varchar(40) DEFAULT 'signed',
  `signed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `signoff_number` (`signoff_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_field_customer_signoffs`
--

LOCK TABLES `ec_field_customer_signoffs` WRITE;
/*!40000 ALTER TABLE `ec_field_customer_signoffs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_field_customer_signoffs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_field_service_dispatches`
--

DROP TABLE IF EXISTS `ec_field_service_dispatches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_field_service_dispatches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatch_number` varchar(120) NOT NULL,
  `job_card_id` int(11) DEFAULT NULL,
  `technician_user_id` int(11) DEFAULT NULL,
  `dispatch_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `service_address` text DEFAULT NULL,
  `customer_contact` varchar(255) DEFAULT NULL,
  `gps_lat` decimal(12,8) DEFAULT NULL,
  `gps_lng` decimal(12,8) DEFAULT NULL,
  `priority` varchar(40) DEFAULT 'normal',
  `dispatch_status` varchar(40) DEFAULT 'scheduled',
  `created_by` int(11) DEFAULT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `enroute_at` datetime DEFAULT NULL,
  `arrived_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dispatch_number` (`dispatch_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_field_service_dispatches`
--

LOCK TABLES `ec_field_service_dispatches` WRITE;
/*!40000 ALTER TABLE `ec_field_service_dispatches` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_field_service_dispatches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_field_service_route_stops`
--

DROP TABLE IF EXISTS `ec_field_service_route_stops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_field_service_route_stops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_service_route_id` int(11) NOT NULL,
  `job_card_id` int(11) DEFAULT NULL,
  `field_service_dispatch_id` int(11) DEFAULT NULL,
  `stop_order` int(11) DEFAULT 0,
  `eta` time DEFAULT NULL,
  `status` varchar(40) DEFAULT 'planned',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_field_service_route_stops`
--

LOCK TABLES `ec_field_service_route_stops` WRITE;
/*!40000 ALTER TABLE `ec_field_service_route_stops` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_field_service_route_stops` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_field_service_routes`
--

DROP TABLE IF EXISTS `ec_field_service_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_field_service_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `route_number` varchar(120) NOT NULL,
  `technician_user_id` int(11) DEFAULT NULL,
  `route_date` date DEFAULT NULL,
  `route_name` varchar(255) DEFAULT NULL,
  `start_location` varchar(255) DEFAULT NULL,
  `end_location` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'planned',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `route_number` (`route_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_field_service_routes`
--

LOCK TABLES `ec_field_service_routes` WRITE;
/*!40000 ALTER TABLE `ec_field_service_routes` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_field_service_routes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_finance_automation_rules`
--

DROP TABLE IF EXISTS `ec_finance_automation_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_finance_automation_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_code` varchar(120) NOT NULL,
  `rule_name` varchar(255) DEFAULT NULL,
  `rule_type` varchar(120) DEFAULT NULL,
  `trigger_type` varchar(120) DEFAULT 'manual',
  `status` varchar(40) DEFAULT 'active',
  `last_run_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_code` (`rule_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_finance_automation_rules`
--

LOCK TABLES `ec_finance_automation_rules` WRITE;
/*!40000 ALTER TABLE `ec_finance_automation_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_finance_automation_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_finance_automation_runs`
--

DROP TABLE IF EXISTS `ec_finance_automation_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_finance_automation_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `run_number` varchar(120) NOT NULL,
  `finance_automation_rule_id` int(11) DEFAULT NULL,
  `run_type` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'completed',
  `items_processed` int(11) DEFAULT 0,
  `total_amount` decimal(14,2) DEFAULT 0.00,
  `run_notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `run_number` (`run_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_finance_automation_runs`
--

LOCK TABLES `ec_finance_automation_runs` WRITE;
/*!40000 ALTER TABLE `ec_finance_automation_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_finance_automation_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_financial_close_periods`
--

DROP TABLE IF EXISTS `ec_financial_close_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_financial_close_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `close_number` varchar(120) NOT NULL,
  `period_name` varchar(255) DEFAULT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `status` varchar(40) DEFAULT 'open',
  `owner_user_id` int(11) DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `close_number` (`close_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_financial_close_periods`
--

LOCK TABLES `ec_financial_close_periods` WRITE;
/*!40000 ALTER TABLE `ec_financial_close_periods` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_financial_close_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_financial_close_tasks`
--

DROP TABLE IF EXISTS `ec_financial_close_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_financial_close_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `financial_close_period_id` int(11) NOT NULL,
  `task_key` varchar(160) DEFAULT NULL,
  `task_name` varchar(255) DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `assigned_to` int(11) DEFAULT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `evidence_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_financial_close_tasks`
--

LOCK TABLES `ec_financial_close_tasks` WRITE;
/*!40000 ALTER TABLE `ec_financial_close_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_financial_close_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_fiscal_years`
--

DROP TABLE IF EXISTS `ec_fiscal_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_fiscal_years` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(30) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_fiscal_years`
--

LOCK TABLES `ec_fiscal_years` WRITE;
/*!40000 ALTER TABLE `ec_fiscal_years` DISABLE KEYS */;
INSERT INTO `ec_fiscal_years` VALUES (1,'FY 2026','2026-01-01','2026-12-31','open','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_fiscal_years` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_fixed_asset_depreciation`
--

DROP TABLE IF EXISTS `ec_fixed_asset_depreciation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_fixed_asset_depreciation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fixed_asset_id` int(11) NOT NULL,
  `period_date` date NOT NULL,
  `depreciation_amount` decimal(14,2) DEFAULT 0.00,
  `accumulated_depreciation` decimal(14,2) DEFAULT 0.00,
  `book_value` decimal(14,2) DEFAULT 0.00,
  `journal_entry_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'calculated',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_asset_period` (`fixed_asset_id`,`period_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_fixed_asset_depreciation`
--

LOCK TABLES `ec_fixed_asset_depreciation` WRITE;
/*!40000 ALTER TABLE `ec_fixed_asset_depreciation` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_fixed_asset_depreciation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_fixed_assets`
--

DROP TABLE IF EXISTS `ec_fixed_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_fixed_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_number` varchar(120) NOT NULL,
  `asset_name` varchar(255) DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(14,2) DEFAULT 0.00,
  `salvage_value` decimal(14,2) DEFAULT 0.00,
  `useful_life_months` int(11) DEFAULT 36,
  `accumulated_depreciation` decimal(14,2) DEFAULT 0.00,
  `book_value` decimal(14,2) DEFAULT 0.00,
  `asset_account_id` int(11) DEFAULT NULL,
  `accumulated_depreciation_account_id` int(11) DEFAULT NULL,
  `depreciation_expense_account_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_number` (`asset_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_fixed_assets`
--

LOCK TABLES `ec_fixed_assets` WRITE;
/*!40000 ALTER TABLE `ec_fixed_assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_fixed_assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_goods_receipt_items`
--

DROP TABLE IF EXISTS `ec_goods_receipt_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_goods_receipt_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_receipt_id` int(11) NOT NULL,
  `purchase_order_item_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity_received` decimal(12,2) DEFAULT 0.00,
  `accepted_quantity` decimal(12,2) DEFAULT 0.00,
  `rejected_quantity` decimal(12,2) DEFAULT 0.00,
  `unit_cost` decimal(12,2) DEFAULT 0.00,
  `line_total` decimal(14,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_goods_receipt_items`
--

LOCK TABLES `ec_goods_receipt_items` WRITE;
/*!40000 ALTER TABLE `ec_goods_receipt_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_goods_receipt_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_goods_receipts`
--

DROP TABLE IF EXISTS `ec_goods_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_goods_receipts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `grn_number` varchar(120) NOT NULL,
  `purchase_order_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `receipt_date` date DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'posted',
  `total_value` decimal(14,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `grn_number` (`grn_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_goods_receipts`
--

LOCK TABLES `ec_goods_receipts` WRITE;
/*!40000 ALTER TABLE `ec_goods_receipts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_goods_receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_in_transit_stock`
--

DROP TABLE IF EXISTS `ec_in_transit_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_in_transit_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_transfer_id` int(11) NOT NULL,
  `stock_transfer_item_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `from_company_id` int(11) DEFAULT NULL,
  `from_branch_id` int(11) DEFAULT NULL,
  `from_warehouse_id` int(11) DEFAULT NULL,
  `from_location_id` int(11) DEFAULT NULL,
  `to_company_id` int(11) DEFAULT NULL,
  `to_branch_id` int(11) DEFAULT NULL,
  `to_warehouse_id` int(11) DEFAULT NULL,
  `to_location_id` int(11) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 0.00,
  `received_quantity` decimal(12,2) DEFAULT 0.00,
  `unit_cost` decimal(12,2) DEFAULT 0.00,
  `total_value` decimal(14,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'in_transit',
  `dispatched_at` datetime DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_in_transit_stock`
--

LOCK TABLES `ec_in_transit_stock` WRITE;
/*!40000 ALTER TABLE `ec_in_transit_stock` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_in_transit_stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_integration_apps`
--

DROP TABLE IF EXISTS `ec_integration_apps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_integration_apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_code` varchar(120) NOT NULL,
  `app_name` varchar(255) DEFAULT NULL,
  `provider` varchar(120) DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `auth_type` varchar(80) DEFAULT 'api_key',
  `base_url` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'available',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_code` (`app_code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_integration_apps`
--

LOCK TABLES `ec_integration_apps` WRITE;
/*!40000 ALTER TABLE `ec_integration_apps` DISABLE KEYS */;
INSERT INTO `ec_integration_apps` VALUES (1,'WHATSAPP-CLOUD','WhatsApp Cloud API','Meta','Messaging','bearer_token','https://graph.facebook.com/','available','Send customer WhatsApp notifications and follow-up messages.','2026-06-12 11:21:09'),(2,'ZAPIER','Zapier Webhooks','Zapier','Automation','webhook','https://hooks.zapier.com/','available','Push orders, leads, and invoice events to Zapier.','2026-06-12 11:21:09'),(3,'MAKE','Make.com Webhooks','Make','Automation','webhook','https://hook.eu1.make.com/','available','Push operational events to Make scenarios.','2026-06-12 11:21:09'),(4,'GOOGLE-SHEETS','Google Sheets Export','Google','Productivity','oauth','https://sheets.googleapis.com/','available','Send report exports and inventory files to Google Sheets.','2026-06-12 11:21:09'),(5,'WOOCOMMERCE','WooCommerce Channel','WooCommerce','Commerce','api_key','https://example.com/wp-json/wc/v3/','available','Sync external WooCommerce products/orders.','2026-06-12 11:21:09'),(6,'ACCOUNTING-CONNECTOR','Accounting Connector','Generic','Finance','api_key','https://api.example.com/','available','Push invoices and payments to external accounting systems.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_integration_apps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_integration_connections`
--

DROP TABLE IF EXISTS `ec_integration_connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_integration_connections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `integration_app_id` int(11) NOT NULL,
  `connection_name` varchar(255) DEFAULT NULL,
  `environment` varchar(40) DEFAULT 'production',
  `status` varchar(40) DEFAULT 'inactive',
  `credentials_json` longtext DEFAULT NULL,
  `settings_json` longtext DEFAULT NULL,
  `last_test_at` datetime DEFAULT NULL,
  `last_sync_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_integration_connections`
--

LOCK TABLES `ec_integration_connections` WRITE;
/*!40000 ALTER TABLE `ec_integration_connections` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_integration_connections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_integration_connector_templates`
--

DROP TABLE IF EXISTS `ec_integration_connector_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_integration_connector_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `connector_code` varchar(120) NOT NULL,
  `connector_name` varchar(255) DEFAULT NULL,
  `provider` varchar(120) DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `auth_type` varchar(80) DEFAULT 'api_key',
  `base_url` varchar(255) DEFAULT NULL,
  `supported_events` text DEFAULT NULL,
  `default_scopes` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'available',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `connector_code` (`connector_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_integration_connector_templates`
--

LOCK TABLES `ec_integration_connector_templates` WRITE;
/*!40000 ALTER TABLE `ec_integration_connector_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_integration_connector_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_integration_error_logs`
--

DROP TABLE IF EXISTS `ec_integration_error_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_integration_error_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `error_number` varchar(120) NOT NULL,
  `integration_connection_id` int(11) DEFAULT NULL,
  `integration_sync_job_id` int(11) DEFAULT NULL,
  `error_type` varchar(120) DEFAULT NULL,
  `severity` varchar(40) DEFAULT 'warning',
  `reference_type` varchar(120) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `payload_json` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `error_number` (`error_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_integration_error_logs`
--

LOCK TABLES `ec_integration_error_logs` WRITE;
/*!40000 ALTER TABLE `ec_integration_error_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_integration_error_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_integration_field_mappings`
--

DROP TABLE IF EXISTS `ec_integration_field_mappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_integration_field_mappings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mapping_number` varchar(120) NOT NULL,
  `integration_connection_id` int(11) DEFAULT NULL,
  `source_module` varchar(120) DEFAULT NULL,
  `target_module` varchar(120) DEFAULT NULL,
  `source_field` varchar(160) DEFAULT NULL,
  `target_field` varchar(160) DEFAULT NULL,
  `transform_rule` varchar(255) DEFAULT NULL,
  `required_field` tinyint(1) DEFAULT 0,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `mapping_number` (`mapping_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_integration_field_mappings`
--

LOCK TABLES `ec_integration_field_mappings` WRITE;
/*!40000 ALTER TABLE `ec_integration_field_mappings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_integration_field_mappings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_integration_sync_jobs`
--

DROP TABLE IF EXISTS `ec_integration_sync_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_integration_sync_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `integration_connection_id` int(11) NOT NULL,
  `job_number` varchar(120) NOT NULL,
  `sync_type` varchar(120) DEFAULT NULL,
  `direction` varchar(40) DEFAULT 'outbound',
  `status` varchar(40) DEFAULT 'queued',
  `records_total` int(11) DEFAULT 0,
  `records_success` int(11) DEFAULT 0,
  `records_failed` int(11) DEFAULT 0,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `job_number` (`job_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_integration_sync_jobs`
--

LOCK TABLES `ec_integration_sync_jobs` WRITE;
/*!40000 ALTER TABLE `ec_integration_sync_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_integration_sync_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_intercompany_transaction_items`
--

DROP TABLE IF EXISTS `ec_intercompany_transaction_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_intercompany_transaction_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `intercompany_transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(12,2) DEFAULT 0.00,
  `unit_cost` decimal(12,2) DEFAULT 0.00,
  `total_value` decimal(14,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_intercompany_transaction_items`
--

LOCK TABLES `ec_intercompany_transaction_items` WRITE;
/*!40000 ALTER TABLE `ec_intercompany_transaction_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_intercompany_transaction_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_intercompany_transactions`
--

DROP TABLE IF EXISTS `ec_intercompany_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_intercompany_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_number` varchar(120) NOT NULL,
  `stock_transfer_id` int(11) DEFAULT NULL,
  `from_company_id` int(11) DEFAULT NULL,
  `from_branch_id` int(11) DEFAULT NULL,
  `to_company_id` int(11) DEFAULT NULL,
  `to_branch_id` int(11) DEFAULT NULL,
  `total_value` decimal(14,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'in_transit',
  `source_journal_id` int(11) DEFAULT NULL,
  `destination_journal_id` int(11) DEFAULT NULL,
  `recognized_at` datetime DEFAULT NULL,
  `settled_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_number` (`transaction_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_intercompany_transactions`
--

LOCK TABLES `ec_intercompany_transactions` WRITE;
/*!40000 ALTER TABLE `ec_intercompany_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_intercompany_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_inventory`
--

DROP TABLE IF EXISTS `ec_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `sku` varchar(120) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 0.00,
  `reorder_level` decimal(12,2) DEFAULT 5.00,
  `location` varchar(120) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_inventory`
--

LOCK TABLES `ec_inventory` WRITE;
/*!40000 ALTER TABLE `ec_inventory` DISABLE KEYS */;
INSERT INTO `ec_inventory` VALUES (1,1,'TRADE-CHAIR-01',28.00,5.00,'Main Store','2026-06-12 11:21:09'),(2,2,'TRADE-PRINTER-01',16.00,5.00,'Main Store','2026-06-12 11:21:09'),(3,3,'TRADE-BUNDLE-01',12.00,5.00,'Main Store','2026-06-12 11:21:09'),(4,4,'TRADE-PLAN-01',999.00,150.00,'Digital','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_inventory_adjustment_lines`
--

DROP TABLE IF EXISTS `ec_inventory_adjustment_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_inventory_adjustment_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_adjustment_request_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `adjustment_qty` decimal(14,4) DEFAULT 0.0000,
  `unit_cost` decimal(14,2) DEFAULT 0.00,
  `total_value` decimal(14,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_inventory_adjustment_lines`
--

LOCK TABLES `ec_inventory_adjustment_lines` WRITE;
/*!40000 ALTER TABLE `ec_inventory_adjustment_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_inventory_adjustment_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_inventory_adjustment_requests`
--

DROP TABLE IF EXISTS `ec_inventory_adjustment_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_inventory_adjustment_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adjustment_number` varchar(120) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  `bin_id` int(11) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'draft',
  `requested_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `requested_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `adjustment_number` (`adjustment_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_inventory_adjustment_requests`
--

LOCK TABLES `ec_inventory_adjustment_requests` WRITE;
/*!40000 ALTER TABLE `ec_inventory_adjustment_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_inventory_adjustment_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_inventory_lots`
--

DROP TABLE IF EXISTS `ec_inventory_lots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_inventory_lots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lot_number` varchar(120) NOT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `bin_id` int(11) DEFAULT NULL,
  `quantity` decimal(14,4) DEFAULT 0.0000,
  `manufacture_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `lot_number` (`lot_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_inventory_lots`
--

LOCK TABLES `ec_inventory_lots` WRITE;
/*!40000 ALTER TABLE `ec_inventory_lots` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_inventory_lots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_inventory_movements`
--

DROP TABLE IF EXISTS `ec_inventory_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_inventory_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `movement_type` varchar(40) NOT NULL,
  `quantity` decimal(12,2) DEFAULT 0.00,
  `reference_type` varchar(80) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_inventory_movements`
--

LOCK TABLES `ec_inventory_movements` WRITE;
/*!40000 ALTER TABLE `ec_inventory_movements` DISABLE KEYS */;
INSERT INTO `ec_inventory_movements` VALUES (1,1,1,1,1,1,'opening',28.00,'system',0,'Opening stock balance','2026-06-12 11:21:09'),(2,1,1,1,1,2,'opening',16.00,'system',0,'Opening stock balance','2026-06-12 11:21:09'),(3,1,1,1,1,3,'opening',12.00,'system',0,'Opening stock balance','2026-06-12 11:21:09'),(4,1,1,1,1,4,'opening',999.00,'system',0,'Opening stock balance','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_inventory_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_inventory_serial_numbers`
--

DROP TABLE IF EXISTS `ec_inventory_serial_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_inventory_serial_numbers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(160) NOT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `bin_id` int(11) DEFAULT NULL,
  `lot_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'available',
  `reference_type` varchar(120) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number` (`serial_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_inventory_serial_numbers`
--

LOCK TABLES `ec_inventory_serial_numbers` WRITE;
/*!40000 ALTER TABLE `ec_inventory_serial_numbers` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_inventory_serial_numbers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_inventory_valuation_entries`
--

DROP TABLE IF EXISTS `ec_inventory_valuation_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_inventory_valuation_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `movement_type` varchar(80) DEFAULT NULL,
  `quantity_delta` decimal(12,2) DEFAULT 0.00,
  `unit_cost` decimal(12,2) DEFAULT 0.00,
  `value_delta` decimal(14,2) DEFAULT 0.00,
  `resulting_quantity` decimal(12,2) DEFAULT 0.00,
  `resulting_value` decimal(14,2) DEFAULT 0.00,
  `reference_type` varchar(80) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_inventory_valuation_entries`
--

LOCK TABLES `ec_inventory_valuation_entries` WRITE;
/*!40000 ALTER TABLE `ec_inventory_valuation_entries` DISABLE KEYS */;
INSERT INTO `ec_inventory_valuation_entries` VALUES (1,1,1,1,1,1,'opening',28.00,419.40,11743.20,28.00,11743.20,'system',0,'Opening stock valued from demo default cost ratio.','2026-06-12 11:21:09'),(2,1,1,1,1,2,'opening',16.00,719.40,11510.40,16.00,11510.40,'system',0,'Opening stock valued from demo default cost ratio.','2026-06-12 11:21:09'),(3,1,1,1,1,3,'opening',12.00,1139.40,13672.80,12.00,13672.80,'system',0,'Opening stock valued from demo default cost ratio.','2026-06-12 11:21:09'),(4,1,1,1,1,4,'opening',999.00,149.40,149250.60,999.00,149250.60,'system',0,'Opening stock valued from demo default cost ratio.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_inventory_valuation_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_invoice_items`
--

DROP TABLE IF EXISTS `ec_invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `item_type` varchar(40) DEFAULT 'product',
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 1.00,
  `unit_price` decimal(12,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_invoice_items`
--

LOCK TABLES `ec_invoice_items` WRITE;
/*!40000 ALTER TABLE `ec_invoice_items` DISABLE KEYS */;
INSERT INTO `ec_invoice_items` VALUES (1,1,'product',1,'Ergonomic Office Chair',1.00,699.00,5.00,699.00,'2026-06-12 11:21:09'),(2,1,'product',2,'Warehouse Label Printer',2.00,1199.00,5.00,2398.00,'2026-06-12 11:21:09'),(3,2,'product',3,'B2B Procurement Bundle',1.00,1899.00,5.00,1899.00,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_invoices`
--

DROP TABLE IF EXISTS `ec_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(80) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_type` varchar(30) DEFAULT 'b2c',
  `billing_address` text DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `discount` decimal(12,2) DEFAULT 0.00,
  `tax` decimal(12,2) DEFAULT 0.00,
  `shipping` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `amount_paid` decimal(12,2) DEFAULT 0.00,
  `credit_amount` decimal(12,2) DEFAULT 0.00,
  `balance_due` decimal(12,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'draft',
  `sales_channel` varchar(40) DEFAULT 'erp',
  `source_order_id` int(11) DEFAULT NULL,
  `source_sales_order_id` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_invoices`
--

LOCK TABLES `ec_invoices` WRITE;
/*!40000 ALTER TABLE `ec_invoices` DISABLE KEYS */;
INSERT INTO `ec_invoices` VALUES (1,1,1,1,'INV-1001',1,'Crescent Trading LLC','procurement@crescenttrading.example','b2b','Deira, Dubai, UAE',3097.00,154.85,147.11,0.00,3089.26,1235.70,0.00,1853.56,'partial','erp',NULL,NULL,'2026-07-02','2026-06-07 13:21:09',NULL,'Starter B2B invoice with partial collection.','2026-06-12 11:21:09'),(2,1,1,1,'INV-1002',2,'Omar Saleh','omar.saleh@example.com','b2c','Ajman, UAE',1899.00,0.00,94.95,0.00,1993.95,0.00,0.00,1993.95,'approved','erp',NULL,NULL,'2026-06-19','2026-06-10 13:21:09',NULL,'Retail customer invoice awaiting collection.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_ip_access_rules`
--

DROP TABLE IF EXISTS `ec_ip_access_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_ip_access_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_number` varchar(120) NOT NULL,
  `rule_type` varchar(40) DEFAULT 'allow',
  `ip_address` varchar(80) DEFAULT NULL,
  `cidr_range` varchar(80) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_number` (`rule_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_ip_access_rules`
--

LOCK TABLES `ec_ip_access_rules` WRITE;
/*!40000 ALTER TABLE `ec_ip_access_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_ip_access_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_job_card_labor`
--

DROP TABLE IF EXISTS `ec_job_card_labor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_job_card_labor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_card_id` int(11) NOT NULL,
  `technician_user_id` int(11) DEFAULT NULL,
  `task_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `estimated_hours` decimal(10,2) DEFAULT 0.00,
  `actual_hours` decimal(10,2) DEFAULT 0.00,
  `hourly_rate` decimal(12,2) DEFAULT 0.00,
  `line_total` decimal(14,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_job_card_labor`
--

LOCK TABLES `ec_job_card_labor` WRITE;
/*!40000 ALTER TABLE `ec_job_card_labor` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_job_card_labor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_job_card_parts`
--

DROP TABLE IF EXISTS `ec_job_card_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_job_card_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_card_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity_planned` decimal(12,2) DEFAULT 0.00,
  `quantity_used` decimal(12,2) DEFAULT 0.00,
  `unit_price` decimal(12,2) DEFAULT 0.00,
  `unit_cost` decimal(12,2) DEFAULT 0.00,
  `line_total` decimal(14,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'planned',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_job_card_parts`
--

LOCK TABLES `ec_job_card_parts` WRITE;
/*!40000 ALTER TABLE `ec_job_card_parts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_job_card_parts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_job_cards`
--

DROP TABLE IF EXISTS `ec_job_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_job_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `cost_center_id` int(11) DEFAULT NULL,
  `job_card_number` varchar(120) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(80) DEFAULT NULL,
  `asset_type` varchar(80) DEFAULT 'vehicle',
  `vehicle_make` varchar(120) DEFAULT NULL,
  `vehicle_model` varchar(120) DEFAULT NULL,
  `vehicle_year` varchar(20) DEFAULT NULL,
  `vin` varchar(120) DEFAULT NULL,
  `plate_number` varchar(80) DEFAULT NULL,
  `odometer` varchar(80) DEFAULT NULL,
  `service_advisor_user_id` int(11) DEFAULT NULL,
  `technician_user_id` int(11) DEFAULT NULL,
  `source_quotation_id` int(11) DEFAULT NULL,
  `source_sales_order_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'draft',
  `priority` varchar(40) DEFAULT 'normal',
  `opened_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `invoiced_at` datetime DEFAULT NULL,
  `parts_issued_at` datetime DEFAULT NULL,
  `labor_total` decimal(14,2) DEFAULT 0.00,
  `parts_total` decimal(14,2) DEFAULT 0.00,
  `estimated_total` decimal(14,2) DEFAULT 0.00,
  `actual_cost` decimal(14,2) DEFAULT 0.00,
  `invoice_id` int(11) DEFAULT NULL,
  `complaint` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `recommendation` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `job_card_number` (`job_card_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_job_cards`
--

LOCK TABLES `ec_job_cards` WRITE;
/*!40000 ALTER TABLE `ec_job_cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_job_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_journal_entries`
--

DROP TABLE IF EXISTS `ec_journal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_journal_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `journal_number` varchar(80) NOT NULL,
  `entry_date` date NOT NULL,
  `reference_type` varchar(80) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `memo` text DEFAULT NULL,
  `status` varchar(30) DEFAULT 'draft',
  `total_debit` decimal(14,2) DEFAULT 0.00,
  `total_credit` decimal(14,2) DEFAULT 0.00,
  `posted_at` datetime DEFAULT NULL,
  `reversed_entry_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `journal_number` (`journal_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_journal_entries`
--

LOCK TABLES `ec_journal_entries` WRITE;
/*!40000 ALTER TABLE `ec_journal_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_journal_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_journal_lines`
--

DROP TABLE IF EXISTS `ec_journal_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_journal_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `journal_entry_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `debit` decimal(14,2) DEFAULT 0.00,
  `credit` decimal(14,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_journal_lines`
--

LOCK TABLES `ec_journal_lines` WRITE;
/*!40000 ALTER TABLE `ec_journal_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_journal_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_kpi_snapshots`
--

DROP TABLE IF EXISTS `ec_kpi_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_kpi_snapshots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_kpi_id` int(11) NOT NULL,
  `snapshot_date` date NOT NULL,
  `metric_value` decimal(14,2) DEFAULT 0.00,
  `target_value` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'ok',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kpi_snapshot` (`report_kpi_id`,`snapshot_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_kpi_snapshots`
--

LOCK TABLES `ec_kpi_snapshots` WRITE;
/*!40000 ALTER TABLE `ec_kpi_snapshots` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_kpi_snapshots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_lead_score_events`
--

DROP TABLE IF EXISTS `ec_lead_score_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_lead_score_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL,
  `lead_score_rule_id` int(11) DEFAULT NULL,
  `score_delta` int(11) DEFAULT 0,
  `total_score` int(11) DEFAULT 0,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_lead_score_events`
--

LOCK TABLES `ec_lead_score_events` WRITE;
/*!40000 ALTER TABLE `ec_lead_score_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_lead_score_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_lead_score_rules`
--

DROP TABLE IF EXISTS `ec_lead_score_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_lead_score_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_code` varchar(120) NOT NULL,
  `rule_name` varchar(255) DEFAULT NULL,
  `condition_key` varchar(160) DEFAULT NULL,
  `score_value` int(11) DEFAULT 0,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_code` (`rule_code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_lead_score_rules`
--

LOCK TABLES `ec_lead_score_rules` WRITE;
/*!40000 ALTER TABLE `ec_lead_score_rules` DISABLE KEYS */;
INSERT INTO `ec_lead_score_rules` VALUES (1,'HAS_EMAIL','Lead has email','has_email',10,'active','2026-06-12 11:21:09'),(2,'HAS_PHONE','Lead has phone','has_phone',10,'active','2026-06-12 11:21:09'),(3,'HIGH_VALUE','Estimated value above configured threshold','high_value',25,'active','2026-06-12 11:21:09'),(4,'HIGH_PROBABILITY','Probability above 60%','high_probability',20,'active','2026-06-12 11:21:09'),(5,'B2B_LEAD','B2B/company lead','b2b_lead',15,'active','2026-06-12 11:21:09'),(6,'FOLLOWUP_DUE','Follow-up date is due','followup_due',10,'active','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_lead_score_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_leave_requests`
--

DROP TABLE IF EXISTS `ec_leave_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_leave_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) DEFAULT NULL,
  `leave_type` varchar(80) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(30) DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `decision_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_leave_requests`
--

LOCK TABLES `ec_leave_requests` WRITE;
/*!40000 ALTER TABLE `ec_leave_requests` DISABLE KEYS */;
INSERT INTO `ec_leave_requests` VALUES (1,2,'Annual Leave','2026-06-22','2026-06-24','Sample pending leave request.','pending',NULL,NULL,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_leave_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_license_audit_logs`
--

DROP TABLE IF EXISTS `ec_license_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_license_audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(120) NOT NULL,
  `severity` varchar(30) NOT NULL DEFAULT 'info',
  `message` text DEFAULT NULL,
  `context` longtext DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_license_audit_logs`
--

LOCK TABLES `ec_license_audit_logs` WRITE;
/*!40000 ALTER TABLE `ec_license_audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_license_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_license_keys`
--

DROP TABLE IF EXISTS `ec_license_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_license_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `license_key_hash` varchar(255) NOT NULL,
  `license_prefix` varchar(40) DEFAULT NULL,
  `license_name` varchar(255) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `subscription_plan_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `issued_to` varchar(255) DEFAULT NULL,
  `issued_email` varchar(255) DEFAULT NULL,
  `domain_name` varchar(255) DEFAULT NULL,
  `max_users` int(11) DEFAULT 0,
  `max_branches` int(11) DEFAULT 0,
  `modules_json` longtext DEFAULT NULL,
  `issued_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `last_validated_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_key_hash` (`license_key_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_license_keys`
--

LOCK TABLES `ec_license_keys` WRITE;
/*!40000 ALTER TABLE `ec_license_keys` DISABLE KEYS */;
INSERT INTO `ec_license_keys` VALUES (1,'$2y$10$8Pz4zBGQswgm3CeUlVI5h.j2TjMc41.XKHnoNumhZEZZaOSFPfAaK','ECOM-5D011232-','Default Local License',1,2,'active','General Trading ERP Store','','localhost',25,5,'[\"commerce\",\"accounting\",\"inventory\",\"approvals\",\"three_way_match\",\"service_operations\",\"projects_budget\",\"bi_reporting\",\"api_access\",\"saas_controls\"]','2026-06-12 14:21:09','2027-06-12 14:21:09',NULL,'Plain license generated at install: ECOM-5D011232-7E955CBD-F475BB80','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_license_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_license_settings`
--

DROP TABLE IF EXISTS `ec_license_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_license_settings` (
  `setting_key` varchar(190) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_license_settings`
--

LOCK TABLES `ec_license_settings` WRITE;
/*!40000 ALTER TABLE `ec_license_settings` DISABLE KEYS */;
INSERT INTO `ec_license_settings` VALUES ('license_activated_at','','2026-06-12 11:21:09'),('license_activation_code','','2026-06-12 11:21:09'),('license_alert_email','','2026-06-12 11:21:09'),('license_alert_email_enabled','0','2026-06-12 11:21:09'),('license_allowed_modules_json','[]','2026-06-12 11:21:09'),('license_bind_domain','1','2026-06-12 11:21:09'),('license_bind_fingerprint','1','2026-06-12 11:21:09'),('license_core_integrity_hash','1ab938db6e6cfed998c443ab8bdabe8af1321f69c1795f57604241ff8c1b9a21','2026-06-12 11:21:18'),('license_enforcement_mode','enforce','2026-06-12 11:21:09'),('license_grace_days','14','2026-06-12 11:21:09'),('license_heartbeat_enabled','0','2026-06-12 11:21:09'),('license_heartbeat_interval_hours','12','2026-06-12 11:21:09'),('license_installation_uid','758f6f4518bc4203ea69c13b8c7c9d22','2026-06-12 11:21:09'),('license_integrity_enabled','1','2026-06-12 11:21:09'),('license_integrity_manifest_json','','2026-06-12 11:21:09'),('license_last_heartbeat_at','','2026-06-12 11:21:09'),('license_last_heartbeat_message','','2026-06-12 11:21:09'),('license_last_heartbeat_status','never','2026-06-12 11:21:09'),('license_last_remote_heartbeat_at','','2026-06-12 11:21:09'),('license_last_remote_heartbeat_status','never','2026-06-12 11:21:09'),('license_last_tamper_warning','','2026-06-12 11:21:09'),('license_last_validated_at','','2026-06-12 11:21:09'),('license_limits','{\"products\":5,\"categories\":5,\"customers\":5,\"orders\":5,\"users\":3,\"branches\":1,\"warehouses\":1,\"invoices\":10}','2026-06-12 11:21:09'),('license_modules','[\"products\",\"categories\",\"customers\",\"orders\",\"inventory_basic\"]','2026-06-12 11:21:09'),('license_note','Trial mode allows limited records. Activation requires a signed license bound to this installation and can enforce plan limits, heartbeat and read-only lock.','2026-06-12 11:21:09'),('license_payload_json','','2026-06-12 11:21:09'),('license_plan','trial','2026-06-12 11:21:09'),('license_readonly_when_invalid','0','2026-06-12 11:21:09'),('license_remote_limits_json','{}','2026-06-12 11:21:09'),('license_remote_status','active','2026-06-12 11:21:09'),('license_server_url','','2026-06-12 11:21:09'),('license_signature_hash','','2026-06-12 11:21:09'),('license_status','trial','2026-06-12 11:21:09'),('license_trial_baseline_json','{\"products\":0,\"categories\":0,\"customers\":0,\"orders\":0}','2026-06-12 11:21:09'),('license_trial_branch_limit','1','2026-06-12 11:21:09'),('license_trial_category_limit','10','2026-06-12 11:21:09'),('license_trial_customer_limit','10','2026-06-12 11:21:09'),('license_trial_invoice_limit','10','2026-06-12 11:21:09'),('license_trial_order_limit','10','2026-06-12 11:21:09'),('license_trial_product_limit','10','2026-06-12 11:21:09'),('license_trial_record_limit','5','2026-06-12 11:21:09'),('license_trial_user_limit','10','2026-06-12 11:21:09'),('license_trial_warehouse_limit','1','2026-06-12 11:21:09'),('license_watermark_created_at','2026-06-12 15:21:12','2026-06-12 11:21:12'),('license_watermark_id','WM-3F5DE3DE93FE649049BC56D8','2026-06-12 11:21:12');
/*!40000 ALTER TABLE `ec_license_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_license_validation_logs`
--

DROP TABLE IF EXISTS `ec_license_validation_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_license_validation_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `license_key_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT NULL,
  `domain_name` varchar(255) DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_license_validation_logs`
--

LOCK TABLES `ec_license_validation_logs` WRITE;
/*!40000 ALTER TABLE `ec_license_validation_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_license_validation_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_login_session_logs`
--

DROP TABLE IF EXISTS `ec_login_session_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_login_session_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `login_status` varchar(40) DEFAULT 'success',
  `ip_address` varchar(80) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `login_at` datetime DEFAULT NULL,
  `logout_at` datetime DEFAULT NULL,
  `last_activity_at` datetime DEFAULT NULL,
  `session_hash` varchar(255) DEFAULT NULL,
  `risk_score` decimal(8,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_number` (`session_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_login_session_logs`
--

LOCK TABLES `ec_login_session_logs` WRITE;
/*!40000 ALTER TABLE `ec_login_session_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_login_session_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_management_dashboard_widgets`
--

DROP TABLE IF EXISTS `ec_management_dashboard_widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_management_dashboard_widgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `management_dashboard_id` int(11) NOT NULL,
  `widget_key` varchar(120) DEFAULT NULL,
  `widget_title` varchar(255) DEFAULT NULL,
  `widget_type` varchar(80) DEFAULT 'kpi',
  `source_key` varchar(120) DEFAULT NULL,
  `position_x` int(11) DEFAULT 0,
  `position_y` int(11) DEFAULT 0,
  `width_units` int(11) DEFAULT 3,
  `height_units` int(11) DEFAULT 1,
  `config_json` longtext DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_management_dashboard_widgets`
--

LOCK TABLES `ec_management_dashboard_widgets` WRITE;
/*!40000 ALTER TABLE `ec_management_dashboard_widgets` DISABLE KEYS */;
INSERT INTO `ec_management_dashboard_widgets` VALUES (1,1,'rev_mtd','Revenue MTD','kpi','revenue_mtd',0,0,3,1,'{}',1,'2026-06-12 11:21:09'),(2,1,'orders_mtd','Orders MTD','kpi','orders_mtd',3,0,3,1,'{}',1,'2026-06-12 11:21:09'),(3,1,'open_ar','Open Receivables','kpi','open_ar',6,0,3,1,'{}',1,'2026-06-12 11:21:09'),(4,1,'low_stock','Low Stock','kpi','low_stock',9,0,3,1,'{}',1,'2026-06-12 11:21:09'),(5,1,'pipeline','Pipeline Forecast','kpi','open_pipeline',0,1,6,1,'{}',1,'2026-06-12 11:21:09'),(6,1,'open_approvals','Open Approvals','kpi','open_approvals',6,1,6,1,'{}',1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_management_dashboard_widgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_management_dashboards`
--

DROP TABLE IF EXISTS `ec_management_dashboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_management_dashboards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dashboard_code` varchar(120) NOT NULL,
  `dashboard_name` varchar(255) DEFAULT NULL,
  `dashboard_type` varchar(80) DEFAULT 'management',
  `visibility` varchar(40) DEFAULT 'private',
  `owner_user_id` int(11) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dashboard_code` (`dashboard_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_management_dashboards`
--

LOCK TABLES `ec_management_dashboards` WRITE;
/*!40000 ALTER TABLE `ec_management_dashboards` DISABLE KEYS */;
INSERT INTO `ec_management_dashboards` VALUES (1,'MGMT-BR-001-00001','Executive Management Dashboard','executive','public',NULL,1,1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_management_dashboards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_manufacturing_quality_checks`
--

DROP TABLE IF EXISTS `ec_manufacturing_quality_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_manufacturing_quality_checks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quality_number` varchar(120) NOT NULL,
  `manufacturing_work_order_id` int(11) NOT NULL,
  `check_type` varchar(120) DEFAULT 'final',
  `result` varchar(40) DEFAULT 'pending',
  `checked_quantity` decimal(14,4) DEFAULT 0.0000,
  `passed_quantity` decimal(14,4) DEFAULT 0.0000,
  `failed_quantity` decimal(14,4) DEFAULT 0.0000,
  `checked_by` int(11) DEFAULT NULL,
  `checked_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `quality_number` (`quality_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_manufacturing_quality_checks`
--

LOCK TABLES `ec_manufacturing_quality_checks` WRITE;
/*!40000 ALTER TABLE `ec_manufacturing_quality_checks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_manufacturing_quality_checks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_manufacturing_routing_steps`
--

DROP TABLE IF EXISTS `ec_manufacturing_routing_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_manufacturing_routing_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manufacturing_routing_id` int(11) NOT NULL,
  `step_number` int(11) DEFAULT 10,
  `work_center_id` int(11) DEFAULT NULL,
  `operation_name` varchar(255) DEFAULT NULL,
  `standard_minutes` decimal(10,2) DEFAULT 0.00,
  `labor_rate` decimal(14,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_manufacturing_routing_steps`
--

LOCK TABLES `ec_manufacturing_routing_steps` WRITE;
/*!40000 ALTER TABLE `ec_manufacturing_routing_steps` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_manufacturing_routing_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_manufacturing_routings`
--

DROP TABLE IF EXISTS `ec_manufacturing_routings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_manufacturing_routings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `routing_code` varchar(120) NOT NULL,
  `routing_name` varchar(255) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `routing_code` (`routing_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_manufacturing_routings`
--

LOCK TABLES `ec_manufacturing_routings` WRITE;
/*!40000 ALTER TABLE `ec_manufacturing_routings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_manufacturing_routings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_manufacturing_work_centers`
--

DROP TABLE IF EXISTS `ec_manufacturing_work_centers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_manufacturing_work_centers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `center_code` varchar(120) NOT NULL,
  `center_name` varchar(255) DEFAULT NULL,
  `center_type` varchar(120) DEFAULT 'general',
  `hourly_rate` decimal(14,2) DEFAULT 0.00,
  `capacity_hours_per_day` decimal(10,2) DEFAULT 8.00,
  `status` varchar(40) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `center_code` (`center_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_manufacturing_work_centers`
--

LOCK TABLES `ec_manufacturing_work_centers` WRITE;
/*!40000 ALTER TABLE `ec_manufacturing_work_centers` DISABLE KEYS */;
INSERT INTO `ec_manufacturing_work_centers` VALUES (1,'WC-ASSEMBLY','Assembly Bench','assembly',75.00,8.00,'active','General assembly and fitting work center.','2026-06-12 11:21:09'),(2,'WC-TESTING','Testing & Quality','quality',90.00,8.00,'active','Testing, inspection, calibration, and quality verification.','2026-06-12 11:21:09'),(3,'WC-PACKING','Packing Station','packing',45.00,8.00,'active','Packing, labeling, and dispatch preparation.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_manufacturing_work_centers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_manufacturing_work_orders`
--

DROP TABLE IF EXISTS `ec_manufacturing_work_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_manufacturing_work_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `work_order_number` varchar(120) NOT NULL,
  `production_plan_id` int(11) DEFAULT NULL,
  `bill_of_material_id` int(11) DEFAULT NULL,
  `finished_product_id` int(11) NOT NULL,
  `planned_quantity` decimal(14,4) DEFAULT 1.0000,
  `completed_quantity` decimal(14,4) DEFAULT 0.0000,
  `scrap_quantity` decimal(14,4) DEFAULT 0.0000,
  `planned_start` date DEFAULT NULL,
  `planned_finish` date DEFAULT NULL,
  `actual_start` datetime DEFAULT NULL,
  `actual_finish` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'planned',
  `priority` varchar(40) DEFAULT 'normal',
  `estimated_cost` decimal(14,2) DEFAULT 0.00,
  `actual_cost` decimal(14,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `work_order_number` (`work_order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_manufacturing_work_orders`
--

LOCK TABLES `ec_manufacturing_work_orders` WRITE;
/*!40000 ALTER TABLE `ec_manufacturing_work_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_manufacturing_work_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_marketing_campaigns`
--

DROP TABLE IF EXISTS `ec_marketing_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_marketing_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `campaign_code` varchar(120) NOT NULL,
  `campaign_name` varchar(255) NOT NULL,
  `campaign_type` varchar(80) DEFAULT 'email',
  `status` varchar(50) DEFAULT 'draft',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget_amount` decimal(14,2) DEFAULT 0.00,
  `expected_revenue` decimal(14,2) DEFAULT 0.00,
  `actual_cost` decimal(14,2) DEFAULT 0.00,
  `generated_leads` int(11) DEFAULT 0,
  `converted_leads` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `campaign_code` (`campaign_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_marketing_campaigns`
--

LOCK TABLES `ec_marketing_campaigns` WRITE;
/*!40000 ALTER TABLE `ec_marketing_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_marketing_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_marketplace_sync_queue`
--

DROP TABLE IF EXISTS `ec_marketplace_sync_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_marketplace_sync_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_number` varchar(120) NOT NULL,
  `marketplace` varchar(120) DEFAULT NULL,
  `entity_type` varchar(120) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `direction` varchar(40) DEFAULT 'outbound',
  `payload_json` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'queued',
  `attempt_count` int(11) DEFAULT 0,
  `last_attempt_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `queue_number` (`queue_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_marketplace_sync_queue`
--

LOCK TABLES `ec_marketplace_sync_queue` WRITE;
/*!40000 ALTER TABLE `ec_marketplace_sync_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_marketplace_sync_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_migration_history`
--

DROP TABLE IF EXISTS `ec_migration_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_migration_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration_key` varchar(180) NOT NULL,
  `version_label` varchar(180) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `installed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration_key` (`migration_key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_migration_history`
--

LOCK TABLES `ec_migration_history` WRITE;
/*!40000 ALTER TABLE `ec_migration_history` DISABLE KEYS */;
INSERT INTO `ec_migration_history` VALUES (1,'priority7_security_hardening','29.0.0','Fresh installer includes security hardening, backups, health checks, cron runner, email templates, dashboard widgets, deployment checklist, and migration center.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_migration_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_mobile_app_install_events`
--

DROP TABLE IF EXISTS `ec_mobile_app_install_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_mobile_app_install_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `device_id` varchar(180) DEFAULT NULL,
  `event_type` varchar(80) DEFAULT 'install_prompt',
  `platform` varchar(80) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'captured',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_number` (`event_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_mobile_app_install_events`
--

LOCK TABLES `ec_mobile_app_install_events` WRITE;
/*!40000 ALTER TABLE `ec_mobile_app_install_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_mobile_app_install_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_mobile_device_sessions`
--

DROP TABLE IF EXISTS `ec_mobile_device_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_mobile_device_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `device_id` varchar(180) DEFAULT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `platform` varchar(80) DEFAULT NULL,
  `app_version` varchar(80) DEFAULT NULL,
  `is_pwa_installed` tinyint(1) DEFAULT 0,
  `last_seen_at` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `ip_address` varchar(80) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_number` (`device_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_mobile_device_sessions`
--

LOCK TABLES `ec_mobile_device_sessions` WRITE;
/*!40000 ALTER TABLE `ec_mobile_device_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_mobile_device_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_mobile_offline_sync_logs`
--

DROP TABLE IF EXISTS `ec_mobile_offline_sync_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_mobile_offline_sync_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile_offline_sync_queue_id` int(11) DEFAULT NULL,
  `sync_status` varchar(40) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_mobile_offline_sync_logs`
--

LOCK TABLES `ec_mobile_offline_sync_logs` WRITE;
/*!40000 ALTER TABLE `ec_mobile_offline_sync_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_mobile_offline_sync_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_mobile_offline_sync_queue`
--

DROP TABLE IF EXISTS `ec_mobile_offline_sync_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_mobile_offline_sync_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `device_id` varchar(180) DEFAULT NULL,
  `entity_type` varchar(120) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `operation` varchar(80) DEFAULT 'upsert',
  `payload_json` longtext DEFAULT NULL,
  `sync_status` varchar(40) DEFAULT 'pending',
  `retry_count` int(11) DEFAULT 0,
  `last_attempt_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sync_number` (`sync_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_mobile_offline_sync_queue`
--

LOCK TABLES `ec_mobile_offline_sync_queue` WRITE;
/*!40000 ALTER TABLE `ec_mobile_offline_sync_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_mobile_offline_sync_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_mobile_parts_usage`
--

DROP TABLE IF EXISTS `ec_mobile_parts_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_mobile_parts_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_card_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `technician_user_id` int(11) DEFAULT NULL,
  `barcode_scan_log_id` int(11) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 1.00,
  `unit_cost` decimal(12,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'used',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_mobile_parts_usage`
--

LOCK TABLES `ec_mobile_parts_usage` WRITE;
/*!40000 ALTER TABLE `ec_mobile_parts_usage` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_mobile_parts_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_mobile_quick_actions`
--

DROP TABLE IF EXISTS `ec_mobile_quick_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_mobile_quick_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_slug` varchar(160) DEFAULT NULL,
  `action_key` varchar(160) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `icon_class` varchar(80) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_mobile_quick_actions`
--

LOCK TABLES `ec_mobile_quick_actions` WRITE;
/*!40000 ALTER TABLE `ec_mobile_quick_actions` DISABLE KEYS */;
INSERT INTO `ec_mobile_quick_actions` VALUES (1,'sales-online-orders','dispatch','Dispatch Board','/admin/erp/field-dispatch.php','bi-truck',10,1,'2026-06-12 11:21:09'),(2,'sales-online-orders','tech_mobile','Technician Mobile','/admin/erp/technician-mobile.php','bi-phone',20,1,'2026-06-12 11:21:09'),(3,'sales-online-orders','scan_qr','Scan QR / Barcode','/admin/erp/barcode-qr.php','bi-qr-code-scan',30,1,'2026-06-12 11:21:09'),(4,'inventory-procurement','mobile_parts','Mobile Parts Usage','/admin/erp/technician-mobile.php','bi-box-seam',10,1,'2026-06-12 11:21:09'),(5,'inventory-procurement','scan_qr','Scan Stock QR','/admin/erp/barcode-qr.php','bi-upc-scan',20,1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_mobile_quick_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_mobile_time_entries`
--

DROP TABLE IF EXISTS `ec_mobile_time_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_mobile_time_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_card_id` int(11) DEFAULT NULL,
  `technician_user_id` int(11) DEFAULT NULL,
  `timer_start` datetime DEFAULT NULL,
  `timer_end` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT 0,
  `status` varchar(40) DEFAULT 'running',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_mobile_time_entries`
--

LOCK TABLES `ec_mobile_time_entries` WRITE;
/*!40000 ALTER TABLE `ec_mobile_time_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_mobile_time_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_module_bundle_selection_items`
--

DROP TABLE IF EXISTS `ec_module_bundle_selection_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_module_bundle_selection_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_bundle_selection_id` int(11) NOT NULL,
  `module_code` varchar(180) DEFAULT NULL,
  `module_label` varchar(255) DEFAULT NULL,
  `priority_code` varchar(120) DEFAULT NULL,
  `priority_label` varchar(255) DEFAULT NULL,
  `module_group` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'enabled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_module_bundle_selection_items`
--

LOCK TABLES `ec_module_bundle_selection_items` WRITE;
/*!40000 ALTER TABLE `ec_module_bundle_selection_items` DISABLE KEYS */;
INSERT INTO `ec_module_bundle_selection_items` VALUES (1,1,'website_sales','Website Sales Only','Website Sales','Only public website sales tools: products, categories, online orders, bookings, homepage, content and settings','Website Sales','enabled','2026-06-12 11:21:09'),(2,1,'website_storefront','Website + Storefront','Core','Core website, products, cart and checkout','Commerce Core','enabled','2026-06-12 11:21:09'),(3,1,'homepage_cms','Homepage Builder + CMS','Frontend','Homepage, header/footer, blog and downloads management','Commerce Core','enabled','2026-06-12 11:21:09'),(4,1,'accounting_finance','Accounting & Finance','P01/P13/P24','Accounting core, finance automation, tax, bank and fixed assets','Finance','enabled','2026-06-12 11:21:09'),(5,1,'multicompany_inventory','Multi-company, Branch & Warehouse','P02/P19','Companies, branches, warehouses, transfers and advanced inventory','Operations','enabled','2026-06-12 11:21:09'),(6,1,'approval_workflow','Approvals & Internal Controls','P03/P27','Maker-checker, approval rules and workflow automation','Controls','enabled','2026-06-12 11:21:09'),(7,1,'sales_operations','Sales Orders, Fulfillment & RMA','P04','Sales orders, delivery notes, returns and credit control','Sales','enabled','2026-06-12 11:21:09'),(8,1,'service_projects','Service, Projects, Warranty & AMC','P05/P16','Job cards, service operations, warranty, technician and field workflows','Service','enabled','2026-06-12 11:21:09'),(9,1,'security_deployment','Security, Backup & Deployment','P07/P28/P34','Security, audit, repair center, production readiness and hardening','Security','enabled','2026-06-12 11:21:09'),(10,1,'seo_frontend_settings','SEO, Frontend Settings & Robots','P08/P09','SEO controls, robots.txt, page settings and mobile-friendly storefront','Marketing','enabled','2026-06-12 11:21:09'),(11,1,'rfq_tender','RFQ, Tender & Quote Workflow','P10','RFQ, tender, supplier quote comparison and award controls','Procurement','enabled','2026-06-12 11:21:09'),(12,1,'crm_pipeline','CRM, Pipeline & Campaigns','P11/P22','Leads, opportunities, campaigns, follow-ups and sales automation','Sales','enabled','2026-06-12 11:21:09'),(13,1,'hr_payroll','HR, Payroll & Employee Self-Service','P12/P23','Employees, attendance, leave, payroll, commissions and ESS portal','HR','enabled','2026-06-12 11:21:09'),(14,1,'reporting_bi','Reporting, BI & KPI Dashboards','P14/P25','Management reports, KPI builder, executive dashboards and scheduled exports','Analytics','enabled','2026-06-12 11:21:09'),(15,1,'api_integrations','API, Webhooks & Integrations','P15/P30','API catalog, webhooks, connector templates, marketplace and accounting sync','Integrations','enabled','2026-06-12 11:21:09'),(16,1,'ai_decision','AI Assistant & Decision Engine','P17/P26','Smart search, alerts, recommendations, decision support and assistant workflows','AI','enabled','2026-06-12 11:21:09'),(17,1,'manufacturing_bom','Manufacturing, BOM & Work Orders','P18','BOM, work orders, material issue, production receipt and costing','Manufacturing','enabled','2026-06-12 11:21:09'),(18,1,'procurement_supplier_portal','Procurement + Supplier Portal','P20','Supplier onboarding, scorecards, contracts, procurement and vendor portal','Procurement','enabled','2026-06-12 11:21:09'),(19,1,'customer_portal','Customer Portal 2.0','P21','Customer dashboard, assets, service requests, invoices, documents and payments','Portal','enabled','2026-06-12 11:21:09'),(20,1,'saas_subscription','SaaS, Multi-Tenant & Subscription','P29','Tenant plans, subscriptions, billing, domains and usage enforcement','SaaS','enabled','2026-06-12 11:21:09'),(21,1,'mobile_pwa','Mobile App / PWA Readiness','P31','Mobile shell, PWA settings, offline sync and push notification foundation','Mobile','enabled','2026-06-12 11:21:09'),(22,1,'document_management','Document Management System','P32','Document library, folders, versions, approvals, expiry and access logs','Documents','enabled','2026-06-12 11:21:09'),(23,1,'advanced_ecommerce','Advanced E-commerce 2.0','P33','B2B pricing, bundles, digital licenses, wishlists, compare, quote and bulk order','Commerce Advanced','enabled','2026-06-12 11:21:09'),(24,1,'documentation_training','Documentation, Training & Commercial Packaging','P35','Documentation center, training, demo credentials, packages and handover','Commercial','enabled','2026-06-12 11:21:09'),(25,1,'egypt_tax_authority','Egypt Tax Authority Readiness','V58','Egypt e-invoice, e-receipt and local tax compliance roadmap','Localization','enabled','2026-06-12 11:21:09'),(26,1,'subscription_billing','Subscription Billing Engine','V58','SaaS billing, renewals, plan limits and recurring invoices','SaaS','enabled','2026-06-12 11:21:09'),(27,1,'partner_reseller_portal','Partner / Reseller Portal','V58','Channel partners, referrals, reseller deals and commission tracking','Commercial','enabled','2026-06-12 11:21:09'),(28,1,'customer_success','Customer Success Dashboard','V58','Onboarding, adoption, health score, support and renewal workflow','Commercial','enabled','2026-06-12 11:21:09'),(29,1,'executive_kpi_cockpit','Executive KPI Cockpit','V58','Owner dashboard for revenue, cash, customers, pipeline and operations','Analytics','enabled','2026-06-12 11:21:09'),(30,1,'whatsapp_business','WhatsApp Business Integration','V58','Customer messaging, quotation follow-up, payment reminders and support notifications','Integrations','enabled','2026-06-12 11:21:09'),(31,1,'ai_forecasting','AI Forecasting','V58','Sales, cash flow, demand and renewal prediction roadmap','AI','enabled','2026-06-12 11:21:09'),(32,1,'ai_inventory_planning','AI Inventory Planning','V58','Reorder suggestions, slow-moving stock and demand planning roadmap','AI','enabled','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_module_bundle_selection_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_module_bundle_selections`
--

DROP TABLE IF EXISTS `ec_module_bundle_selections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_module_bundle_selections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bundle_number` varchar(120) NOT NULL,
  `business_type` varchar(120) DEFAULT NULL,
  `bundle_key` varchar(160) DEFAULT NULL,
  `bundle_label` varchar(255) DEFAULT NULL,
  `selected_modules_json` longtext DEFAULT NULL,
  `selected_priorities_json` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `bundle_number` (`bundle_number`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_module_bundle_selections`
--

LOCK TABLES `ec_module_bundle_selections` WRITE;
/*!40000 ALTER TABLE `ec_module_bundle_selections` DISABLE KEYS */;
INSERT INTO `ec_module_bundle_selections` VALUES (1,'MBND-20260612132109','general_trading','enterprise_full','Full Enterprise ERP Bundle','[\"website_sales\",\"website_storefront\",\"homepage_cms\",\"accounting_finance\",\"multicompany_inventory\",\"approval_workflow\",\"sales_operations\",\"service_projects\",\"security_deployment\",\"seo_frontend_settings\",\"rfq_tender\",\"crm_pipeline\",\"hr_payroll\",\"reporting_bi\",\"api_integrations\",\"ai_decision\",\"manufacturing_bom\",\"procurement_supplier_portal\",\"customer_portal\",\"saas_subscription\",\"mobile_pwa\",\"document_management\",\"advanced_ecommerce\",\"documentation_training\",\"egypt_tax_authority\",\"subscription_billing\",\"partner_reseller_portal\",\"customer_success\",\"executive_kpi_cockpit\",\"whatsapp_business\",\"ai_forecasting\",\"ai_inventory_planning\"]','[\"Website Sales\",\"Core\",\"Frontend\",\"P01/P13/P24\",\"P02/P19\",\"P03/P27\",\"P04\",\"P05/P16\",\"P07/P28/P34\",\"P08/P09\",\"P10\",\"P11/P22\",\"P12/P23\",\"P14/P25\",\"P15/P30\",\"P17/P26\",\"P18\",\"P20\",\"P21\",\"P29\",\"P31\",\"P32\",\"P33\",\"P35\",\"V58\"]','active',1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_module_bundle_selections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_module_entitlements`
--

DROP TABLE IF EXISTS `ec_module_entitlements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_module_entitlements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `license_key_id` int(11) DEFAULT NULL,
  `module_key` varchar(160) NOT NULL,
  `module_name` varchar(255) DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `limit_value` varchar(120) DEFAULT NULL,
  `source` varchar(80) DEFAULT 'license',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_module_company` (`company_id`,`module_key`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_module_entitlements`
--

LOCK TABLES `ec_module_entitlements` WRITE;
/*!40000 ALTER TABLE `ec_module_entitlements` DISABLE KEYS */;
INSERT INTO `ec_module_entitlements` VALUES (1,1,1,'commerce','B2B/B2C Commerce',1,'','license','2026-06-12 11:21:09'),(2,1,1,'accounting','Accounting Core',1,'','license','2026-06-12 11:21:09'),(3,1,1,'inventory','Inventory & Warehouses',1,'','license','2026-06-12 11:21:09'),(4,1,1,'approvals','Approval Workflow',1,'','license','2026-06-12 11:21:09'),(5,1,1,'three_way_match','Supplier Invoice 3-Way Match',1,'','license','2026-06-12 11:21:09'),(6,1,1,'service_operations','Job Cards & Service Operations',1,'','license','2026-06-12 11:21:09'),(7,1,1,'projects_budget','Projects & Budget Control',1,'','license','2026-06-12 11:21:09'),(8,1,1,'bi_reporting','BI & Report Builder',1,'','license','2026-06-12 11:21:09'),(9,1,1,'api_access','API Access',1,'','license','2026-06-12 11:21:09'),(10,1,1,'saas_controls','SaaS Licensing Controls',1,'','license','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_module_entitlements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_newsletter`
--

DROP TABLE IF EXISTS `ec_newsletter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_newsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `status` varchar(40) DEFAULT 'subscribed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_newsletter`
--

LOCK TABLES `ec_newsletter` WRITE;
/*!40000 ALTER TABLE `ec_newsletter` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_newsletter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_notification_events`
--

DROP TABLE IF EXISTS `ec_notification_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_notification_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_key` varchar(160) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `module` varchar(120) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `default_severity` varchar(40) DEFAULT 'info',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_notification_event` (`event_key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_notification_events`
--

LOCK TABLES `ec_notification_events` WRITE;
/*!40000 ALTER TABLE `ec_notification_events` DISABLE KEYS */;
INSERT INTO `ec_notification_events` VALUES (1,'approval_pending','Approval Pending','Approvals',1,'warning','2026-06-12 11:21:09'),(2,'low_stock','Low Stock Alert','Inventory',1,'warning','2026-06-12 11:21:09'),(3,'budget_threshold','Budget Threshold Warning','Budgets',1,'danger','2026-06-12 11:21:09'),(4,'credit_hold','Customer Credit Hold','Credit Control',1,'danger','2026-06-12 11:21:09'),(5,'supplier_invoice_variance','Supplier Invoice Variance','Accounts Payable',1,'warning','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_notification_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_notifications`
--

DROP TABLE IF EXISTS `ec_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role_slug` varchar(160) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `severity` varchar(40) DEFAULT 'info',
  `link_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notification_user` (`user_id`,`is_read`),
  KEY `idx_notification_role` (`role_slug`,`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_notifications`
--

LOCK TABLES `ec_notifications` WRITE;
/*!40000 ALTER TABLE `ec_notifications` DISABLE KEYS */;
INSERT INTO `ec_notifications` VALUES (1,1,1,1,NULL,'ERP BI layer installed','Priority 6 analytics, notifications, import/export, and API controls are ready.','success','http://localhost/admin/erp/executive-dashboard.php',0,NULL,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_offline_job_card_drafts`
--

DROP TABLE IF EXISTS `ec_offline_job_card_drafts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_offline_job_card_drafts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `draft_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `job_card_id` int(11) DEFAULT NULL,
  `device_id` varchar(180) DEFAULT NULL,
  `draft_payload` longtext DEFAULT NULL,
  `sync_status` varchar(40) DEFAULT 'pending',
  `last_sync_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `draft_number` (`draft_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_offline_job_card_drafts`
--

LOCK TABLES `ec_offline_job_card_drafts` WRITE;
/*!40000 ALTER TABLE `ec_offline_job_card_drafts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_offline_job_card_drafts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_order_items`
--

DROP TABLE IF EXISTS `ec_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_order_items`
--

LOCK TABLES `ec_order_items` WRITE;
/*!40000 ALTER TABLE `ec_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_orders`
--

DROP TABLE IF EXISTS `ec_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `order_number` varchar(80) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(80) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `discount` decimal(12,2) DEFAULT 0.00,
  `shipping_cost` decimal(12,2) DEFAULT 0.00,
  `tax` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `payment_method` varchar(80) DEFAULT NULL,
  `payment_status` varchar(40) DEFAULT 'pending',
  `order_status` varchar(40) DEFAULT 'pending',
  `inventory_reserved` tinyint(1) DEFAULT 0,
  `stock_released` tinyint(1) DEFAULT 0,
  `erp_invoice_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_orders`
--

LOCK TABLES `ec_orders` WRITE;
/*!40000 ALTER TABLE `ec_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_packing_slip_items`
--

DROP TABLE IF EXISTS `ec_packing_slip_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_packing_slip_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `packing_slip_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `packed_qty` decimal(14,4) DEFAULT 0.0000,
  `package_label` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_packing_slip_items`
--

LOCK TABLES `ec_packing_slip_items` WRITE;
/*!40000 ALTER TABLE `ec_packing_slip_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_packing_slip_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_packing_slips`
--

DROP TABLE IF EXISTS `ec_packing_slips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_packing_slips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `packing_number` varchar(120) NOT NULL,
  `picking_list_id` int(11) DEFAULT NULL,
  `source_type` varchar(120) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'draft',
  `packed_by` int(11) DEFAULT NULL,
  `packed_at` datetime DEFAULT NULL,
  `package_count` int(11) DEFAULT 1,
  `weight_kg` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `packing_number` (`packing_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_packing_slips`
--

LOCK TABLES `ec_packing_slips` WRITE;
/*!40000 ALTER TABLE `ec_packing_slips` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_packing_slips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_password_policy_rules`
--

DROP TABLE IF EXISTS `ec_password_policy_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_password_policy_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_key` varchar(120) NOT NULL,
  `rule_name` varchar(255) DEFAULT NULL,
  `rule_value` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_key` (`rule_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_password_policy_rules`
--

LOCK TABLES `ec_password_policy_rules` WRITE;
/*!40000 ALTER TABLE `ec_password_policy_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_password_policy_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_payments`
--

DROP TABLE IF EXISTS `ec_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `payment_number` varchar(80) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `bank_account_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT 0.00,
  `method` varchar(80) DEFAULT NULL,
  `reference` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'received',
  `paid_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_number` (`payment_number`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_payments`
--

LOCK TABLES `ec_payments` WRITE;
/*!40000 ALTER TABLE `ec_payments` DISABLE KEYS */;
INSERT INTO `ec_payments` VALUES (1,1,1,'PAY-1001',1,1,NULL,1235.70,'Bank Transfer','BANK-REF-001','received','2026-06-09 13:21:09','Advance payment received.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_payroll_periods`
--

DROP TABLE IF EXISTS `ec_payroll_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_payroll_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `period_number` varchar(120) NOT NULL,
  `period_name` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(40) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `period_number` (`period_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_payroll_periods`
--

LOCK TABLES `ec_payroll_periods` WRITE;
/*!40000 ALTER TABLE `ec_payroll_periods` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_payroll_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_payroll_run_items`
--

DROP TABLE IF EXISTS `ec_payroll_run_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_payroll_run_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_run_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `basic_salary` decimal(12,2) DEFAULT 0.00,
  `allowances` decimal(12,2) DEFAULT 0.00,
  `overtime_amount` decimal(12,2) DEFAULT 0.00,
  `commission_amount` decimal(12,2) DEFAULT 0.00,
  `expense_reimbursement` decimal(12,2) DEFAULT 0.00,
  `deductions` decimal(12,2) DEFAULT 0.00,
  `gross_pay` decimal(12,2) DEFAULT 0.00,
  `net_pay` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_payroll_run_items`
--

LOCK TABLES `ec_payroll_run_items` WRITE;
/*!40000 ALTER TABLE `ec_payroll_run_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_payroll_run_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_payroll_runs`
--

DROP TABLE IF EXISTS `ec_payroll_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_payroll_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_number` varchar(120) NOT NULL,
  `payroll_period_id` int(11) NOT NULL,
  `status` varchar(40) DEFAULT 'draft',
  `gross_total` decimal(14,2) DEFAULT 0.00,
  `deductions_total` decimal(14,2) DEFAULT 0.00,
  `net_total` decimal(14,2) DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_number` (`payroll_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_payroll_runs`
--

LOCK TABLES `ec_payroll_runs` WRITE;
/*!40000 ALTER TABLE `ec_payroll_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_payroll_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_permission_change_history`
--

DROP TABLE IF EXISTS `ec_permission_change_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_permission_change_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `change_number` varchar(120) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `change_type` varchar(120) DEFAULT NULL,
  `old_permissions` longtext DEFAULT NULL,
  `new_permissions` longtext DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `change_number` (`change_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_permission_change_history`
--

LOCK TABLES `ec_permission_change_history` WRITE;
/*!40000 ALTER TABLE `ec_permission_change_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_permission_change_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_picking_list_items`
--

DROP TABLE IF EXISTS `ec_picking_list_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_picking_list_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `picking_list_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `required_qty` decimal(14,4) DEFAULT 0.0000,
  `picked_qty` decimal(14,4) DEFAULT 0.0000,
  `bin_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_picking_list_items`
--

LOCK TABLES `ec_picking_list_items` WRITE;
/*!40000 ALTER TABLE `ec_picking_list_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_picking_list_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_picking_lists`
--

DROP TABLE IF EXISTS `ec_picking_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_picking_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `picking_number` varchar(120) NOT NULL,
  `source_type` varchar(120) DEFAULT 'order',
  `source_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) NOT NULL,
  `status` varchar(40) DEFAULT 'draft',
  `assigned_to` int(11) DEFAULT NULL,
  `picked_by` int(11) DEFAULT NULL,
  `picked_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `picking_number` (`picking_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_picking_lists`
--

LOCK TABLES `ec_picking_lists` WRITE;
/*!40000 ALTER TABLE `ec_picking_lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_picking_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_plan_features`
--

DROP TABLE IF EXISTS `ec_plan_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_plan_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscription_plan_id` int(11) NOT NULL,
  `feature_key` varchar(160) NOT NULL,
  `feature_name` varchar(255) DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `limit_value` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plan_feature` (`subscription_plan_id`,`feature_key`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_plan_features`
--

LOCK TABLES `ec_plan_features` WRITE;
/*!40000 ALTER TABLE `ec_plan_features` DISABLE KEYS */;
INSERT INTO `ec_plan_features` VALUES (1,1,'commerce','B2B/B2C Commerce',1,'','2026-06-12 11:21:09'),(2,1,'accounting','Accounting Core',1,'','2026-06-12 11:21:09'),(3,1,'inventory','Inventory & Warehouses',1,'','2026-06-12 11:21:09'),(4,1,'approvals','Approval Workflow',1,'','2026-06-12 11:21:09'),(5,1,'three_way_match','Supplier Invoice 3-Way Match',0,'','2026-06-12 11:21:09'),(6,1,'service_operations','Job Cards & Service Operations',0,'','2026-06-12 11:21:09'),(7,1,'projects_budget','Projects & Budget Control',0,'','2026-06-12 11:21:09'),(8,1,'bi_reporting','BI & Report Builder',1,'','2026-06-12 11:21:09'),(9,1,'api_access','API Access',0,'','2026-06-12 11:21:09'),(10,1,'saas_controls','SaaS Licensing Controls',0,'','2026-06-12 11:21:09'),(11,2,'commerce','B2B/B2C Commerce',1,'','2026-06-12 11:21:09'),(12,2,'accounting','Accounting Core',1,'','2026-06-12 11:21:09'),(13,2,'inventory','Inventory & Warehouses',1,'','2026-06-12 11:21:09'),(14,2,'approvals','Approval Workflow',1,'','2026-06-12 11:21:09'),(15,2,'three_way_match','Supplier Invoice 3-Way Match',1,'','2026-06-12 11:21:09'),(16,2,'service_operations','Job Cards & Service Operations',1,'','2026-06-12 11:21:09'),(17,2,'projects_budget','Projects & Budget Control',1,'','2026-06-12 11:21:09'),(18,2,'bi_reporting','BI & Report Builder',1,'','2026-06-12 11:21:09'),(19,2,'api_access','API Access',1,'','2026-06-12 11:21:09'),(20,2,'saas_controls','SaaS Licensing Controls',0,'','2026-06-12 11:21:09'),(21,3,'commerce','B2B/B2C Commerce',1,'','2026-06-12 11:21:09'),(22,3,'accounting','Accounting Core',1,'','2026-06-12 11:21:09'),(23,3,'inventory','Inventory & Warehouses',1,'','2026-06-12 11:21:09'),(24,3,'approvals','Approval Workflow',1,'','2026-06-12 11:21:09'),(25,3,'three_way_match','Supplier Invoice 3-Way Match',1,'','2026-06-12 11:21:09'),(26,3,'service_operations','Job Cards & Service Operations',1,'','2026-06-12 11:21:09'),(27,3,'projects_budget','Projects & Budget Control',1,'','2026-06-12 11:21:09'),(28,3,'bi_reporting','BI & Report Builder',1,'','2026-06-12 11:21:09'),(29,3,'api_access','API Access',1,'','2026-06-12 11:21:09'),(30,3,'saas_controls','SaaS Licensing Controls',1,'','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_plan_features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_predictive_alerts`
--

DROP TABLE IF EXISTS `ec_predictive_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_predictive_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_number` varchar(120) NOT NULL,
  `alert_type` varchar(120) DEFAULT NULL,
  `severity` varchar(40) DEFAULT 'medium',
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `recommended_action` text DEFAULT NULL,
  `source_module` varchar(120) DEFAULT NULL,
  `reference_type` varchar(120) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `score` decimal(8,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alert_number` (`alert_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_predictive_alerts`
--

LOCK TABLES `ec_predictive_alerts` WRITE;
/*!40000 ALTER TABLE `ec_predictive_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_predictive_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_procurement_award_decisions`
--

DROP TABLE IF EXISTS `ec_procurement_award_decisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_procurement_award_decisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `award_number` varchar(120) NOT NULL,
  `rfq_id` int(11) NOT NULL,
  `rfq_supplier_quote_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `decision_reason` text DEFAULT NULL,
  `total_score` decimal(12,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'awarded',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `award_number` (`award_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_procurement_award_decisions`
--

LOCK TABLES `ec_procurement_award_decisions` WRITE;
/*!40000 ALTER TABLE `ec_procurement_award_decisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_procurement_award_decisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_procurement_tender_items`
--

DROP TABLE IF EXISTS `ec_procurement_tender_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_procurement_tender_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `procurement_tender_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 1.00,
  `target_unit_cost` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_procurement_tender_items`
--

LOCK TABLES `ec_procurement_tender_items` WRITE;
/*!40000 ALTER TABLE `ec_procurement_tender_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_procurement_tender_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_procurement_tenders`
--

DROP TABLE IF EXISTS `ec_procurement_tenders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_procurement_tenders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `tender_number` varchar(120) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `publish_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'draft',
  `estimated_value` decimal(14,2) DEFAULT 0.00,
  `awarded_supplier_id` int(11) DEFAULT NULL,
  `awarded_rfq_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tender_number` (`tender_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_procurement_tenders`
--

LOCK TABLES `ec_procurement_tenders` WRITE;
/*!40000 ALTER TABLE `ec_procurement_tenders` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_procurement_tenders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_product_bundle_items`
--

DROP TABLE IF EXISTS `ec_product_bundle_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_product_bundle_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_bundle_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(12,2) DEFAULT 1.00,
  `unit_price` decimal(14,2) DEFAULT 0.00,
  `line_total` decimal(14,2) DEFAULT 0.00,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_product_bundle_items`
--

LOCK TABLES `ec_product_bundle_items` WRITE;
/*!40000 ALTER TABLE `ec_product_bundle_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_product_bundle_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_product_bundles`
--

DROP TABLE IF EXISTS `ec_product_bundles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_product_bundles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bundle_number` varchar(120) NOT NULL,
  `bundle_name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `bundle_price` decimal(14,2) DEFAULT 0.00,
  `compare_price` decimal(14,2) DEFAULT 0.00,
  `total_cost` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'active',
  `featured` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `bundle_number` (`bundle_number`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_product_bundles`
--

LOCK TABLES `ec_product_bundles` WRITE;
/*!40000 ALTER TABLE `ec_product_bundles` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_product_bundles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_product_comparison_items`
--

DROP TABLE IF EXISTS `ec_product_comparison_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_product_comparison_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_comparison_session_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_compare_item` (`product_comparison_session_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_product_comparison_items`
--

LOCK TABLES `ec_product_comparison_items` WRITE;
/*!40000 ALTER TABLE `ec_product_comparison_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_product_comparison_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_product_comparison_sessions`
--

DROP TABLE IF EXISTS `ec_product_comparison_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_product_comparison_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compare_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_token` varchar(160) DEFAULT NULL,
  `compare_name` varchar(255) DEFAULT 'Product Compare',
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `compare_number` (`compare_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_product_comparison_sessions`
--

LOCK TABLES `ec_product_comparison_sessions` WRITE;
/*!40000 ALTER TABLE `ec_product_comparison_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_product_comparison_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_production_cost_rollups`
--

DROP TABLE IF EXISTS `ec_production_cost_rollups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_production_cost_rollups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rollup_number` varchar(120) NOT NULL,
  `bill_of_material_id` int(11) DEFAULT NULL,
  `manufacturing_work_order_id` int(11) DEFAULT NULL,
  `material_cost` decimal(14,2) DEFAULT 0.00,
  `labor_cost` decimal(14,2) DEFAULT 0.00,
  `overhead_cost` decimal(14,2) DEFAULT 0.00,
  `total_cost` decimal(14,2) DEFAULT 0.00,
  `unit_cost` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'calculated',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rollup_number` (`rollup_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_production_cost_rollups`
--

LOCK TABLES `ec_production_cost_rollups` WRITE;
/*!40000 ALTER TABLE `ec_production_cost_rollups` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_production_cost_rollups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_production_demo_data_batches`
--

DROP TABLE IF EXISTS `ec_production_demo_data_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_production_demo_data_batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_number` varchar(120) NOT NULL,
  `batch_name` varchar(255) DEFAULT NULL,
  `batch_type` varchar(120) DEFAULT 'starter',
  `status` varchar(40) DEFAULT 'created',
  `records_created` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `batch_number` (`batch_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_production_demo_data_batches`
--

LOCK TABLES `ec_production_demo_data_batches` WRITE;
/*!40000 ALTER TABLE `ec_production_demo_data_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_production_demo_data_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_production_demo_data_items`
--

DROP TABLE IF EXISTS `ec_production_demo_data_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_production_demo_data_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `production_demo_data_batch_id` int(11) DEFAULT NULL,
  `entity_type` varchar(120) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `entity_label` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'created',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_production_demo_data_items`
--

LOCK TABLES `ec_production_demo_data_items` WRITE;
/*!40000 ALTER TABLE `ec_production_demo_data_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_production_demo_data_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_production_installer_events`
--

DROP TABLE IF EXISTS `ec_production_installer_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_production_installer_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_number` varchar(120) NOT NULL,
  `event_type` varchar(120) DEFAULT NULL,
  `severity` varchar(40) DEFAULT 'info',
  `status` varchar(40) DEFAULT 'open',
  `message` text DEFAULT NULL,
  `context_json` longtext DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_number` (`event_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_production_installer_events`
--

LOCK TABLES `ec_production_installer_events` WRITE;
/*!40000 ALTER TABLE `ec_production_installer_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_production_installer_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_production_material_issues`
--

DROP TABLE IF EXISTS `ec_production_material_issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_production_material_issues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `issue_number` varchar(120) NOT NULL,
  `manufacturing_work_order_id` int(11) NOT NULL,
  `component_product_id` int(11) NOT NULL,
  `quantity` decimal(14,4) DEFAULT 0.0000,
  `unit_cost` decimal(14,2) DEFAULT 0.00,
  `total_cost` decimal(14,2) DEFAULT 0.00,
  `issued_by` int(11) DEFAULT NULL,
  `issued_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `issue_number` (`issue_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_production_material_issues`
--

LOCK TABLES `ec_production_material_issues` WRITE;
/*!40000 ALTER TABLE `ec_production_material_issues` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_production_material_issues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_production_output_receipts`
--

DROP TABLE IF EXISTS `ec_production_output_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_production_output_receipts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `receipt_number` varchar(120) NOT NULL,
  `manufacturing_work_order_id` int(11) NOT NULL,
  `finished_product_id` int(11) NOT NULL,
  `quantity` decimal(14,4) DEFAULT 0.0000,
  `scrap_quantity` decimal(14,4) DEFAULT 0.0000,
  `unit_cost` decimal(14,2) DEFAULT 0.00,
  `total_cost` decimal(14,2) DEFAULT 0.00,
  `received_by` int(11) DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `receipt_number` (`receipt_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_production_output_receipts`
--

LOCK TABLES `ec_production_output_receipts` WRITE;
/*!40000 ALTER TABLE `ec_production_output_receipts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_production_output_receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_production_plans`
--

DROP TABLE IF EXISTS `ec_production_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_production_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_number` varchar(120) NOT NULL,
  `plan_name` varchar(255) DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `plan_number` (`plan_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_production_plans`
--

LOCK TABLES `ec_production_plans` WRITE;
/*!40000 ALTER TABLE `ec_production_plans` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_production_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_production_release_checklist_items`
--

DROP TABLE IF EXISTS `ec_production_release_checklist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_production_release_checklist_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `production_release_checklist_id` int(11) NOT NULL,
  `item_key` varchar(160) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `severity` varchar(40) DEFAULT 'medium',
  `status` varchar(40) DEFAULT 'open',
  `recommendation` text DEFAULT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_production_release_checklist_items`
--

LOCK TABLES `ec_production_release_checklist_items` WRITE;
/*!40000 ALTER TABLE `ec_production_release_checklist_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_production_release_checklist_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_production_release_checklists`
--

DROP TABLE IF EXISTS `ec_production_release_checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_production_release_checklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checklist_number` varchar(120) NOT NULL,
  `release_name` varchar(255) DEFAULT NULL,
  `release_version` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `checklist_number` (`checklist_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_production_release_checklists`
--

LOCK TABLES `ec_production_release_checklists` WRITE;
/*!40000 ALTER TABLE `ec_production_release_checklists` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_production_release_checklists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_production_repair_items`
--

DROP TABLE IF EXISTS `ec_production_repair_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_production_repair_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `production_repair_run_id` int(11) DEFAULT NULL,
  `item_key` varchar(160) DEFAULT NULL,
  `item_type` varchar(120) DEFAULT NULL,
  `severity` varchar(40) DEFAULT 'info',
  `status` varchar(40) DEFAULT 'open',
  `description` text DEFAULT NULL,
  `recommendation` text DEFAULT NULL,
  `repair_action` varchar(255) DEFAULT NULL,
  `repaired_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_production_repair_items`
--

LOCK TABLES `ec_production_repair_items` WRITE;
/*!40000 ALTER TABLE `ec_production_repair_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_production_repair_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_production_repair_runs`
--

DROP TABLE IF EXISTS `ec_production_repair_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_production_repair_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `run_number` varchar(120) NOT NULL,
  `run_type` varchar(120) DEFAULT 'health_scan',
  `status` varchar(40) DEFAULT 'running',
  `items_checked` int(11) DEFAULT 0,
  `issues_found` int(11) DEFAULT 0,
  `repairs_applied` int(11) DEFAULT 0,
  `summary` text DEFAULT NULL,
  `started_by` int(11) DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `run_number` (`run_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_production_repair_runs`
--

LOCK TABLES `ec_production_repair_runs` WRITE;
/*!40000 ALTER TABLE `ec_production_repair_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_production_repair_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_production_schema_check_items`
--

DROP TABLE IF EXISTS `ec_production_schema_check_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_production_schema_check_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `production_schema_check_id` int(11) DEFAULT NULL,
  `table_name` varchar(180) DEFAULT NULL,
  `column_name` varchar(180) DEFAULT NULL,
  `item_type` varchar(80) DEFAULT 'table',
  `expected_definition` text DEFAULT NULL,
  `exists_flag` tinyint(1) DEFAULT 0,
  `status` varchar(40) DEFAULT 'ok',
  `repair_sql` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_production_schema_check_items`
--

LOCK TABLES `ec_production_schema_check_items` WRITE;
/*!40000 ALTER TABLE `ec_production_schema_check_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_production_schema_check_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_production_schema_checks`
--

DROP TABLE IF EXISTS `ec_production_schema_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_production_schema_checks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `check_number` varchar(120) NOT NULL,
  `check_type` varchar(120) DEFAULT 'full_schema',
  `status` varchar(40) DEFAULT 'completed',
  `tables_checked` int(11) DEFAULT 0,
  `columns_checked` int(11) DEFAULT 0,
  `missing_tables` int(11) DEFAULT 0,
  `missing_columns` int(11) DEFAULT 0,
  `started_by` int(11) DEFAULT NULL,
  `checked_at` datetime DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `check_number` (`check_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_production_schema_checks`
--

LOCK TABLES `ec_production_schema_checks` WRITE;
/*!40000 ALTER TABLE `ec_production_schema_checks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_production_schema_checks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_production_upgrade_backups`
--

DROP TABLE IF EXISTS `ec_production_upgrade_backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_production_upgrade_backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_number` varchar(120) NOT NULL,
  `backup_type` varchar(80) DEFAULT 'pre_upgrade',
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT 0,
  `status` varchar(40) DEFAULT 'created',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `backup_number` (`backup_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_production_upgrade_backups`
--

LOCK TABLES `ec_production_upgrade_backups` WRITE;
/*!40000 ALTER TABLE `ec_production_upgrade_backups` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_production_upgrade_backups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_products`
--

DROP TABLE IF EXISTS `ec_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `name_ar` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `description_ar` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `short_description_ar` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `compare_price` decimal(12,2) DEFAULT NULL,
  `cost_price` decimal(12,2) DEFAULT 0.00,
  `average_cost` decimal(12,2) DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `sku` varchar(120) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `badge` varchar(80) DEFAULT NULL,
  `warranty` varchar(120) DEFAULT NULL,
  `warranty_ar` varchar(120) DEFAULT NULL,
  `specifications` longtext DEFAULT NULL,
  `specifications_ar` longtext DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `downloadable` tinyint(1) DEFAULT 0,
  `download_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_products`
--

LOCK TABLES `ec_products` WRITE;
/*!40000 ALTER TABLE `ec_products` DISABLE KEYS */;
INSERT INTO `ec_products` VALUES (1,1,'Ergonomic Office Chair',NULL,'ergonomic-office-chair','Configurable retail and B2B trading product for catalogue display, quotations, and stock-managed sales.',NULL,'Comfort-focused office chair for teams and individual buyers.',NULL,699.00,849.00,419.40,419.40,28,'TRADE-CHAIR-01','','','Trade Pick','12-Month Warranty',NULL,'Category: Office supplies\nChannel: B2B/B2C\nStock type: Physical',NULL,1,1,0,'','2026-06-12 11:21:09'),(2,2,'Warehouse Label Printer',NULL,'warehouse-label-printer','Practical commercial device for logistics, trade buyers, and procurement-oriented storefronts.',NULL,'Business label printer for stock rooms and dispatch desks.',NULL,1199.00,1399.00,719.40,719.40,16,'TRADE-PRINTER-01','','','Operations Pick','12-Month Warranty',NULL,'Use case: Warehouse operations\nCustomer: Business buyers\nStock: Physical device',NULL,1,1,0,'','2026-06-12 11:21:09'),(3,3,'B2B Procurement Bundle',NULL,'b2b-procurement-bundle','Bundle example for quote-heavy business sales and commercial landing-page merchandising.',NULL,'Multi-item trade bundle for corporate buyers.',NULL,1899.00,2199.00,1139.40,1139.40,12,'TRADE-BUNDLE-01','','','Bulk Offer','Support Included',NULL,'Format: Bundle\nSales: Quotations and checkout\nStock: Physical bundle',NULL,0,1,0,'','2026-06-12 11:21:09'),(4,4,'Digital Maintenance Plan',NULL,'digital-maintenance-plan','Digital service plan example for non-physical ecommerce workflows.',NULL,'Downloadable service and support plan.',NULL,249.00,299.00,149.40,149.40,999,'TRADE-PLAN-01','','','Digital Add-on','Service Coverage',NULL,'Delivery: Digital\nAudience: Business/consumer\nAccess: Customer downloads',NULL,1,1,1,'maintenance-plan.pdf','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_project_cost_entries`
--

DROP TABLE IF EXISTS `ec_project_cost_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_project_cost_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `cost_center_id` int(11) DEFAULT NULL,
  `reference_type` varchar(100) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `cost_category` varchar(120) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(14,2) DEFAULT 0.00,
  `entry_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_project_cost_entries`
--

LOCK TABLES `ec_project_cost_entries` WRITE;
/*!40000 ALTER TABLE `ec_project_cost_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_project_cost_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_projects`
--

DROP TABLE IF EXISTS `ec_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `cost_center_id` int(11) DEFAULT NULL,
  `project_number` varchar(120) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `project_manager_user_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget_amount` decimal(14,2) DEFAULT 0.00,
  `revenue_amount` decimal(14,2) DEFAULT 0.00,
  `cost_amount` decimal(14,2) DEFAULT 0.00,
  `margin_amount` decimal(14,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'planning',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_number` (`project_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_projects`
--

LOCK TABLES `ec_projects` WRITE;
/*!40000 ALTER TABLE `ec_projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_purchase_order_items`
--

DROP TABLE IF EXISTS `ec_purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_purchase_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 1.00,
  `received_quantity` decimal(12,2) DEFAULT 0.00,
  `unit_cost` decimal(12,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_purchase_order_items`
--

LOCK TABLES `ec_purchase_order_items` WRITE;
/*!40000 ALTER TABLE `ec_purchase_order_items` DISABLE KEYS */;
INSERT INTO `ec_purchase_order_items` VALUES (1,1,3,'B2B Procurement Bundle',6.00,0.00,1177.38,5.00,7064.28,'2026-06-12 11:21:09'),(2,1,1,'Ergonomic Office Chair',2.00,0.00,335.52,5.00,671.04,'2026-06-12 11:21:09'),(3,2,2,'Warehouse Label Printer',10.00,0.00,659.45,5.00,6594.50,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_purchase_orders`
--

DROP TABLE IF EXISTS `ec_purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_purchase_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `source_requisition_id` int(11) DEFAULT NULL,
  `po_number` varchar(80) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `expected_date` date DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `tax` decimal(12,2) DEFAULT 0.00,
  `shipping` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'draft',
  `approved_at` datetime DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_number` (`po_number`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_purchase_orders`
--

LOCK TABLES `ec_purchase_orders` WRITE;
/*!40000 ALTER TABLE `ec_purchase_orders` DISABLE KEYS */;
INSERT INTO `ec_purchase_orders` VALUES (1,1,1,1,NULL,'PO-1001',1,'Regional Distribution Hub','2026-06-10','2026-06-20',7735.32,386.77,100.00,8222.09,'approved','2026-06-10 13:21:09',NULL,'Approved replenishment order for fast-moving products.','2026-06-12 11:21:09'),(2,1,1,1,NULL,'PO-1002',2,'Office Supply Partners','2026-06-12','2026-06-24',6594.50,329.73,0.00,6924.23,'draft',NULL,NULL,'Draft purchase order for replenishment.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_purchase_requisition_items`
--

DROP TABLE IF EXISTS `ec_purchase_requisition_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_purchase_requisition_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_requisition_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 1.00,
  `estimated_unit_cost` decimal(12,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_purchase_requisition_items`
--

LOCK TABLES `ec_purchase_requisition_items` WRITE;
/*!40000 ALTER TABLE `ec_purchase_requisition_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_purchase_requisition_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_purchase_requisitions`
--

DROP TABLE IF EXISTS `ec_purchase_requisitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_purchase_requisitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `requisition_number` varchar(120) NOT NULL,
  `requested_by_user_id` int(11) DEFAULT NULL,
  `department` varchar(180) DEFAULT NULL,
  `required_date` date DEFAULT NULL,
  `justification` text DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `tax` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'draft',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `converted_po_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `requisition_number` (`requisition_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_purchase_requisitions`
--

LOCK TABLES `ec_purchase_requisitions` WRITE;
/*!40000 ALTER TABLE `ec_purchase_requisitions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_purchase_requisitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_push_notification_logs`
--

DROP TABLE IF EXISTS `ec_push_notification_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_push_notification_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `push_notification_queue_id` int(11) DEFAULT NULL,
  `push_notification_subscription_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `delivery_status` varchar(40) DEFAULT 'simulated',
  `response_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_push_notification_logs`
--

LOCK TABLES `ec_push_notification_logs` WRITE;
/*!40000 ALTER TABLE `ec_push_notification_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_push_notification_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_push_notification_queue`
--

DROP TABLE IF EXISTS `ec_push_notification_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_push_notification_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role_slug` varchar(160) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `priority` varchar(40) DEFAULT 'normal',
  `status` varchar(40) DEFAULT 'queued',
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `queue_number` (`queue_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_push_notification_queue`
--

LOCK TABLES `ec_push_notification_queue` WRITE;
/*!40000 ALTER TABLE `ec_push_notification_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_push_notification_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_push_notification_subscriptions`
--

DROP TABLE IF EXISTS `ec_push_notification_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_push_notification_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscription_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `device_id` varchar(180) DEFAULT NULL,
  `endpoint_url` text DEFAULT NULL,
  `p256dh_key` text DEFAULT NULL,
  `auth_key` text DEFAULT NULL,
  `platform` varchar(80) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `last_seen_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscription_number` (`subscription_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_push_notification_subscriptions`
--

LOCK TABLES `ec_push_notification_subscriptions` WRITE;
/*!40000 ALTER TABLE `ec_push_notification_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_push_notification_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_pwa_app_shortcuts`
--

DROP TABLE IF EXISTS `ec_pwa_app_shortcuts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_pwa_app_shortcuts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shortcut_key` varchar(160) NOT NULL,
  `shortcut_name` varchar(255) DEFAULT NULL,
  `shortcut_url` varchar(255) DEFAULT NULL,
  `icon_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_enabled` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `shortcut_key` (`shortcut_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_pwa_app_shortcuts`
--

LOCK TABLES `ec_pwa_app_shortcuts` WRITE;
/*!40000 ALTER TABLE `ec_pwa_app_shortcuts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_pwa_app_shortcuts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_pwa_cache_assets`
--

DROP TABLE IF EXISTS `ec_pwa_cache_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_pwa_cache_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_number` varchar(120) NOT NULL,
  `asset_url` varchar(255) DEFAULT NULL,
  `asset_type` varchar(80) DEFAULT 'page',
  `cache_strategy` varchar(80) DEFAULT 'stale_while_revalidate',
  `is_required` tinyint(1) DEFAULT 0,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_number` (`asset_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_pwa_cache_assets`
--

LOCK TABLES `ec_pwa_cache_assets` WRITE;
/*!40000 ALTER TABLE `ec_pwa_cache_assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_pwa_cache_assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_pwa_settings`
--

DROP TABLE IF EXISTS `ec_pwa_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_pwa_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(160) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `setting_group` varchar(120) DEFAULT 'pwa',
  `description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_pwa_settings`
--

LOCK TABLES `ec_pwa_settings` WRITE;
/*!40000 ALTER TABLE `ec_pwa_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_pwa_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_quotation_items`
--

DROP TABLE IF EXISTS `ec_quotation_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_quotation_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quotation_id` int(11) NOT NULL,
  `item_type` varchar(40) DEFAULT 'product',
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 1.00,
  `unit_price` decimal(12,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_quotation_items`
--

LOCK TABLES `ec_quotation_items` WRITE;
/*!40000 ALTER TABLE `ec_quotation_items` DISABLE KEYS */;
INSERT INTO `ec_quotation_items` VALUES (1,1,'product',1,'Ergonomic Office Chair',1.00,699.00,5.00,699.00,'2026-06-12 11:21:09'),(2,1,'product',2,'Warehouse Label Printer',3.00,1199.00,5.00,3597.00,'2026-06-12 11:21:09'),(3,2,'product',3,'B2B Procurement Bundle',1.00,1899.00,5.00,1899.00,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_quotation_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_quotations`
--

DROP TABLE IF EXISTS `ec_quotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_quotations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `quotation_number` varchar(80) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_type` varchar(30) DEFAULT 'b2c',
  `billing_address` text DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `discount` decimal(12,2) DEFAULT 0.00,
  `tax` decimal(12,2) DEFAULT 0.00,
  `shipping` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'draft',
  `valid_until` date DEFAULT NULL,
  `converted_invoice_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotation_number` (`quotation_number`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_quotations`
--

LOCK TABLES `ec_quotations` WRITE;
/*!40000 ALTER TABLE `ec_quotations` DISABLE KEYS */;
INSERT INTO `ec_quotations` VALUES (1,1,1,1,'QTN-1001',1,'Crescent Trading LLC','procurement@crescenttrading.example','b2b','Deira, Dubai, UAE',4296.00,214.80,204.06,0.00,4285.26,'sent','2026-06-22',NULL,'Sample B2B quotation awaiting customer decision.','2026-06-12 11:21:09'),(2,1,1,1,'QTN-1002',2,'Omar Saleh','omar.saleh@example.com','b2c','Ajman, UAE',1899.00,0.00,94.95,0.00,1993.95,'accepted','2026-06-17',NULL,'Accepted retail quotation ready for invoice conversion.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_quotations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_quote_request_items`
--

DROP TABLE IF EXISTS `ec_quote_request_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_quote_request_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quote_request_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `sku` varchar(120) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 1.00,
  `target_price` decimal(14,2) DEFAULT 0.00,
  `unit_price` decimal(14,2) DEFAULT 0.00,
  `line_total` decimal(14,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_quote_request_items`
--

LOCK TABLES `ec_quote_request_items` WRITE;
/*!40000 ALTER TABLE `ec_quote_request_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_quote_request_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_quote_requests_2`
--

DROP TABLE IF EXISTS `ec_quote_requests_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_quote_requests_2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quote_request_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_type` varchar(40) DEFAULT 'b2b',
  `company_name` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `source` varchar(120) DEFAULT 'store_request_quote',
  `subtotal` decimal(14,2) DEFAULT 0.00,
  `estimated_total` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'new',
  `priority` varchar(40) DEFAULT 'normal',
  `notes` text DEFAULT NULL,
  `converted_quotation_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `quote_request_number` (`quote_request_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_quote_requests_2`
--

LOCK TABLES `ec_quote_requests_2` WRITE;
/*!40000 ALTER TABLE `ec_quote_requests_2` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_quote_requests_2` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_recommendation_results`
--

DROP TABLE IF EXISTS `ec_recommendation_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_recommendation_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recommendation_number` varchar(120) NOT NULL,
  `recommendation_rule_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `recommendation_text` text DEFAULT NULL,
  `impact_score` decimal(8,2) DEFAULT 0.00,
  `effort_score` decimal(8,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'open',
  `reference_type` varchar(120) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `recommendation_number` (`recommendation_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_recommendation_results`
--

LOCK TABLES `ec_recommendation_results` WRITE;
/*!40000 ALTER TABLE `ec_recommendation_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_recommendation_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_recommendation_rules`
--

DROP TABLE IF EXISTS `ec_recommendation_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_recommendation_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_code` varchar(120) NOT NULL,
  `rule_name` varchar(255) DEFAULT NULL,
  `module` varchar(120) DEFAULT NULL,
  `trigger_metric` varchar(120) DEFAULT NULL,
  `condition_json` longtext DEFAULT NULL,
  `recommendation_template` text DEFAULT NULL,
  `priority` int(11) DEFAULT 100,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_code` (`rule_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_recommendation_rules`
--

LOCK TABLES `ec_recommendation_rules` WRITE;
/*!40000 ALTER TABLE `ec_recommendation_rules` DISABLE KEYS */;
INSERT INTO `ec_recommendation_rules` VALUES (1,'REC-LOW-STOCK','Low Stock Reorder Recommendation','Inventory','low_stock','{\"stock_lte\":3}','Review low-stock products and create purchase order or stock transfer.',10,'active','2026-06-12 11:21:09'),(2,'REC-OPEN-AR','Receivable Collection Recommendation','Finance','open_ar','{\"balance_gt\":0}','Prioritize follow-up for overdue invoices and high-balance customers.',20,'active','2026-06-12 11:21:09'),(3,'REC-HOT-LEADS','Hot Lead Conversion Recommendation','CRM','hot_leads','{\"lead_score_gte\":70}','Create opportunity and follow up hot leads within 24 hours.',30,'active','2026-06-12 11:21:09'),(4,'REC-SERVICE-DELAY','Service Delay Recommendation','Service','open_jobs','{\"age_days_gt\":3}','Review delayed job cards and assign technician dispatch priority.',40,'active','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_recommendation_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_recurring_journal_template_lines`
--

DROP TABLE IF EXISTS `ec_recurring_journal_template_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_recurring_journal_template_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recurring_journal_template_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `debit` decimal(14,2) DEFAULT 0.00,
  `credit` decimal(14,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_recurring_journal_template_lines`
--

LOCK TABLES `ec_recurring_journal_template_lines` WRITE;
/*!40000 ALTER TABLE `ec_recurring_journal_template_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_recurring_journal_template_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_recurring_journal_templates`
--

DROP TABLE IF EXISTS `ec_recurring_journal_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_recurring_journal_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_number` varchar(120) NOT NULL,
  `template_name` varchar(255) DEFAULT NULL,
  `frequency` varchar(80) DEFAULT 'monthly',
  `next_run_date` date DEFAULT NULL,
  `last_run_date` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `memo` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_number` (`template_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_recurring_journal_templates`
--

LOCK TABLES `ec_recurring_journal_templates` WRITE;
/*!40000 ALTER TABLE `ec_recurring_journal_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_recurring_journal_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_replenishment_rules`
--

DROP TABLE IF EXISTS `ec_replenishment_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_replenishment_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_code` varchar(120) NOT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `min_qty` decimal(14,4) DEFAULT 0.0000,
  `max_qty` decimal(14,4) DEFAULT 0.0000,
  `reorder_qty` decimal(14,4) DEFAULT 0.0000,
  `preferred_supplier_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_code` (`rule_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_replenishment_rules`
--

LOCK TABLES `ec_replenishment_rules` WRITE;
/*!40000 ALTER TABLE `ec_replenishment_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_replenishment_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_replenishment_suggestions`
--

DROP TABLE IF EXISTS `ec_replenishment_suggestions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_replenishment_suggestions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `suggestion_number` varchar(120) NOT NULL,
  `replenishment_rule_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `current_qty` decimal(14,4) DEFAULT 0.0000,
  `recommended_qty` decimal(14,4) DEFAULT 0.0000,
  `source_type` varchar(80) DEFAULT 'rule',
  `status` varchar(40) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `suggestion_number` (`suggestion_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_replenishment_suggestions`
--

LOCK TABLES `ec_replenishment_suggestions` WRITE;
/*!40000 ALTER TABLE `ec_replenishment_suggestions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_replenishment_suggestions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_report_dataset_cache`
--

DROP TABLE IF EXISTS `ec_report_dataset_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_report_dataset_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cache_key` varchar(180) NOT NULL,
  `report_type` varchar(120) DEFAULT NULL,
  `filter_hash` varchar(120) DEFAULT NULL,
  `row_count` int(11) DEFAULT 0,
  `data_json` longtext DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cache_key` (`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_report_dataset_cache`
--

LOCK TABLES `ec_report_dataset_cache` WRITE;
/*!40000 ALTER TABLE `ec_report_dataset_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_report_dataset_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_report_export_files`
--

DROP TABLE IF EXISTS `ec_report_export_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_report_export_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `saved_report_id` int(11) DEFAULT NULL,
  `report_type` varchar(120) DEFAULT NULL,
  `export_number` varchar(120) NOT NULL,
  `format` varchar(40) DEFAULT 'csv',
  `file_name` varchar(255) DEFAULT NULL,
  `row_count` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `export_number` (`export_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_report_export_files`
--

LOCK TABLES `ec_report_export_files` WRITE;
/*!40000 ALTER TABLE `ec_report_export_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_report_export_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_report_kpis`
--

DROP TABLE IF EXISTS `ec_report_kpis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_report_kpis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kpi_code` varchar(120) NOT NULL,
  `kpi_name` varchar(255) NOT NULL,
  `kpi_group` varchar(120) DEFAULT 'General',
  `metric_source` varchar(120) NOT NULL,
  `calculation_type` varchar(80) DEFAULT 'sum',
  `target_value` decimal(14,2) DEFAULT 0.00,
  `warning_value` decimal(14,2) DEFAULT 0.00,
  `unit_label` varchar(40) DEFAULT '',
  `sort_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kpi_code` (`kpi_code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_report_kpis`
--

LOCK TABLES `ec_report_kpis` WRITE;
/*!40000 ALTER TABLE `ec_report_kpis` DISABLE KEYS */;
INSERT INTO `ec_report_kpis` VALUES (1,'REV-MTD','Month-to-Date Revenue','Sales','revenue_mtd','sum',100000.00,50000.00,'AED',10,1,'2026-06-12 11:21:09'),(2,'ORDERS-MTD','Month-to-Date Orders','Sales','orders_mtd','count',100.00,25.00,'orders',20,1,'2026-06-12 11:21:09'),(3,'OPEN-AR','Open Receivables','Finance','open_ar','sum',0.00,50000.00,'AED',30,1,'2026-06-12 11:21:09'),(4,'LOW-STOCK','Low Stock Items','Inventory','low_stock','count',0.00,10.00,'items',40,1,'2026-06-12 11:21:09'),(5,'OPEN-APPROVALS','Open Approvals','Governance','open_approvals','count',0.00,10.00,'requests',50,1,'2026-06-12 11:21:09'),(6,'PIPELINE','Open Sales Pipeline','CRM','open_pipeline','sum',200000.00,75000.00,'AED',60,1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_report_kpis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_report_runs`
--

DROP TABLE IF EXISTS `ec_report_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_report_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `saved_report_id` int(11) DEFAULT NULL,
  `run_by` int(11) DEFAULT NULL,
  `run_type` varchar(40) DEFAULT 'manual',
  `status` varchar(40) DEFAULT 'completed',
  `row_count` int(11) DEFAULT 0,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_report_runs`
--

LOCK TABLES `ec_report_runs` WRITE;
/*!40000 ALTER TABLE `ec_report_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_report_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_report_schedule_runs`
--

DROP TABLE IF EXISTS `ec_report_schedule_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_report_schedule_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_schedule_id` int(11) NOT NULL,
  `run_number` varchar(120) NOT NULL,
  `status` varchar(40) DEFAULT 'queued',
  `row_count` int(11) DEFAULT 0,
  `export_path` varchar(255) DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `run_number` (`run_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_report_schedule_runs`
--

LOCK TABLES `ec_report_schedule_runs` WRITE;
/*!40000 ALTER TABLE `ec_report_schedule_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_report_schedule_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_report_schedules`
--

DROP TABLE IF EXISTS `ec_report_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_report_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `saved_report_id` int(11) NOT NULL,
  `schedule_code` varchar(120) NOT NULL,
  `schedule_name` varchar(255) DEFAULT NULL,
  `frequency` varchar(40) DEFAULT 'weekly',
  `format` varchar(40) DEFAULT 'csv',
  `recipient_emails` text DEFAULT NULL,
  `next_run_at` datetime DEFAULT NULL,
  `last_run_at` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedule_code` (`schedule_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_report_schedules`
--

LOCK TABLES `ec_report_schedules` WRITE;
/*!40000 ALTER TABLE `ec_report_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_report_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_report_storyboard_slides`
--

DROP TABLE IF EXISTS `ec_report_storyboard_slides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_report_storyboard_slides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_storyboard_id` int(11) NOT NULL,
  `slide_title` varchar(255) DEFAULT NULL,
  `slide_type` varchar(120) DEFAULT 'kpi',
  `reference_type` varchar(120) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_report_storyboard_slides`
--

LOCK TABLES `ec_report_storyboard_slides` WRITE;
/*!40000 ALTER TABLE `ec_report_storyboard_slides` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_report_storyboard_slides` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_report_storyboards`
--

DROP TABLE IF EXISTS `ec_report_storyboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_report_storyboards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `storyboard_number` varchar(120) NOT NULL,
  `storyboard_title` varchar(255) DEFAULT NULL,
  `audience` varchar(120) DEFAULT 'management',
  `status` varchar(40) DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `storyboard_number` (`storyboard_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_report_storyboards`
--

LOCK TABLES `ec_report_storyboards` WRITE;
/*!40000 ALTER TABLE `ec_report_storyboards` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_report_storyboards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_report_subscriptions`
--

DROP TABLE IF EXISTS `ec_report_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_report_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `saved_report_id` int(11) DEFAULT NULL,
  `report_schedule_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `frequency` varchar(40) DEFAULT 'weekly',
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_report_subscriptions`
--

LOCK TABLES `ec_report_subscriptions` WRITE;
/*!40000 ALTER TABLE `ec_report_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_report_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_return_rma_items`
--

DROP TABLE IF EXISTS `ec_return_rma_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_return_rma_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_rma_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 0.00,
  `unit_price` decimal(12,2) DEFAULT 0.00,
  `condition_status` varchar(80) DEFAULT 'uninspected',
  `disposition` varchar(80) DEFAULT 'restock',
  `line_total` decimal(14,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_return_rma_items`
--

LOCK TABLES `ec_return_rma_items` WRITE;
/*!40000 ALTER TABLE `ec_return_rma_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_return_rma_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_returns_rma`
--

DROP TABLE IF EXISTS `ec_returns_rma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_returns_rma` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `rma_number` varchar(120) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `sales_order_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `delivery_note_id` int(11) DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `total_value` decimal(14,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'draft',
  `approval_status` varchar(50) DEFAULT 'not_required',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `credit_note_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rma_number` (`rma_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_returns_rma`
--

LOCK TABLES `ec_returns_rma` WRITE;
/*!40000 ALTER TABLE `ec_returns_rma` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_returns_rma` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_rfq_items`
--

DROP TABLE IF EXISTS `ec_rfq_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_rfq_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfq_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 1.00,
  `target_unit_cost` decimal(12,2) DEFAULT 0.00,
  `required_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_rfq_items`
--

LOCK TABLES `ec_rfq_items` WRITE;
/*!40000 ALTER TABLE `ec_rfq_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_rfq_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_rfq_supplier_invitations`
--

DROP TABLE IF EXISTS `ec_rfq_supplier_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_rfq_supplier_invitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfq_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `invitation_number` varchar(120) NOT NULL,
  `status` varchar(50) DEFAULT 'invited',
  `sent_at` datetime DEFAULT NULL,
  `viewed_at` datetime DEFAULT NULL,
  `responded_at` datetime DEFAULT NULL,
  `declined_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invitation_number` (`invitation_number`),
  UNIQUE KEY `uq_rfq_supplier` (`rfq_id`,`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_rfq_supplier_invitations`
--

LOCK TABLES `ec_rfq_supplier_invitations` WRITE;
/*!40000 ALTER TABLE `ec_rfq_supplier_invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_rfq_supplier_invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_rfq_supplier_quote_items`
--

DROP TABLE IF EXISTS `ec_rfq_supplier_quote_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_rfq_supplier_quote_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfq_supplier_quote_id` int(11) NOT NULL,
  `rfq_item_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 1.00,
  `unit_price` decimal(12,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(14,2) DEFAULT 0.00,
  `delivery_days` int(11) DEFAULT 0,
  `compliance_status` varchar(50) DEFAULT 'compliant',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_rfq_supplier_quote_items`
--

LOCK TABLES `ec_rfq_supplier_quote_items` WRITE;
/*!40000 ALTER TABLE `ec_rfq_supplier_quote_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_rfq_supplier_quote_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_rfq_supplier_quotes`
--

DROP TABLE IF EXISTS `ec_rfq_supplier_quotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_rfq_supplier_quotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfq_id` int(11) NOT NULL,
  `rfq_supplier_invitation_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) NOT NULL,
  `response_number` varchar(120) NOT NULL,
  `quote_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(14,2) DEFAULT 0.00,
  `tax` decimal(14,2) DEFAULT 0.00,
  `shipping` decimal(14,2) DEFAULT 0.00,
  `total_amount` decimal(14,2) DEFAULT 0.00,
  `delivery_days` int(11) DEFAULT 0,
  `payment_terms` varchar(180) DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'submitted',
  `rank_score` decimal(12,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `response_number` (`response_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_rfq_supplier_quotes`
--

LOCK TABLES `ec_rfq_supplier_quotes` WRITE;
/*!40000 ALTER TABLE `ec_rfq_supplier_quotes` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_rfq_supplier_quotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_rfqs`
--

DROP TABLE IF EXISTS `ec_rfqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_rfqs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `rfq_number` varchar(120) NOT NULL,
  `source_requisition_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `awarded_supplier_id` int(11) DEFAULT NULL,
  `awarded_quote_id` int(11) DEFAULT NULL,
  `converted_po_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rfq_number` (`rfq_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_rfqs`
--

LOCK TABLES `ec_rfqs` WRITE;
/*!40000 ALTER TABLE `ec_rfqs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_rfqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_saas_module_catalog`
--

DROP TABLE IF EXISTS `ec_saas_module_catalog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_saas_module_catalog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_key` varchar(160) NOT NULL,
  `module_name` varchar(255) DEFAULT NULL,
  `module_group` varchar(120) DEFAULT 'Core',
  `description` text DEFAULT NULL,
  `default_enabled` tinyint(1) DEFAULT 1,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_key` (`module_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_saas_module_catalog`
--

LOCK TABLES `ec_saas_module_catalog` WRITE;
/*!40000 ALTER TABLE `ec_saas_module_catalog` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_saas_module_catalog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_saas_onboarding_tasks`
--

DROP TABLE IF EXISTS `ec_saas_onboarding_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_saas_onboarding_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_number` varchar(120) NOT NULL,
  `company_id` int(11) NOT NULL,
  `task_title` varchar(255) DEFAULT NULL,
  `task_type` varchar(120) DEFAULT 'setup',
  `status` varchar(40) DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_number` (`task_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_saas_onboarding_tasks`
--

LOCK TABLES `ec_saas_onboarding_tasks` WRITE;
/*!40000 ALTER TABLE `ec_saas_onboarding_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_saas_onboarding_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_saas_plan_change_requests`
--

DROP TABLE IF EXISTS `ec_saas_plan_change_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_saas_plan_change_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_number` varchar(120) NOT NULL,
  `company_id` int(11) NOT NULL,
  `current_plan_id` int(11) DEFAULT NULL,
  `requested_plan_id` int(11) DEFAULT NULL,
  `change_type` varchar(40) DEFAULT 'upgrade',
  `status` varchar(40) DEFAULT 'pending',
  `requested_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `requested_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_number` (`request_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_saas_plan_change_requests`
--

LOCK TABLES `ec_saas_plan_change_requests` WRITE;
/*!40000 ALTER TABLE `ec_saas_plan_change_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_saas_plan_change_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_saas_plan_modules`
--

DROP TABLE IF EXISTS `ec_saas_plan_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_saas_plan_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscription_plan_id` int(11) NOT NULL,
  `module_key` varchar(160) NOT NULL,
  `module_name` varchar(255) DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `included_limit` varchar(120) DEFAULT NULL,
  `overage_price` decimal(12,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_saas_plan_module` (`subscription_plan_id`,`module_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_saas_plan_modules`
--

LOCK TABLES `ec_saas_plan_modules` WRITE;
/*!40000 ALTER TABLE `ec_saas_plan_modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_saas_plan_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_saas_subscription_invoices`
--

DROP TABLE IF EXISTS `ec_saas_subscription_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_saas_subscription_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(120) NOT NULL,
  `company_id` int(11) NOT NULL,
  `tenant_subscription_id` int(11) DEFAULT NULL,
  `subscription_plan_id` int(11) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `billing_period_start` date DEFAULT NULL,
  `billing_period_end` date DEFAULT NULL,
  `subtotal` decimal(14,2) DEFAULT 0.00,
  `tax_amount` decimal(14,2) DEFAULT 0.00,
  `total` decimal(14,2) DEFAULT 0.00,
  `balance_due` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_saas_subscription_invoices`
--

LOCK TABLES `ec_saas_subscription_invoices` WRITE;
/*!40000 ALTER TABLE `ec_saas_subscription_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_saas_subscription_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_saas_subscription_payments`
--

DROP TABLE IF EXISTS `ec_saas_subscription_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_saas_subscription_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_number` varchar(120) NOT NULL,
  `saas_subscription_invoice_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `amount` decimal(14,2) DEFAULT 0.00,
  `payment_method` varchar(120) DEFAULT NULL,
  `payment_reference` varchar(180) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'received',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_number` (`payment_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_saas_subscription_payments`
--

LOCK TABLES `ec_saas_subscription_payments` WRITE;
/*!40000 ALTER TABLE `ec_saas_subscription_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_saas_subscription_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_saas_tenant_domains`
--

DROP TABLE IF EXISTS `ec_saas_tenant_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_saas_tenant_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_number` varchar(120) NOT NULL,
  `company_id` int(11) NOT NULL,
  `domain_name` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `status` varchar(40) DEFAULT 'pending',
  `verified_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_number` (`domain_number`),
  UNIQUE KEY `uq_saas_domain` (`domain_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_saas_tenant_domains`
--

LOCK TABLES `ec_saas_tenant_domains` WRITE;
/*!40000 ALTER TABLE `ec_saas_tenant_domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_saas_tenant_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_saas_trial_accounts`
--

DROP TABLE IF EXISTS `ec_saas_trial_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_saas_trial_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trial_number` varchar(120) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `subscription_plan_id` int(11) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(80) DEFAULT NULL,
  `trial_start` date DEFAULT NULL,
  `trial_end` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `converted_subscription_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `trial_number` (`trial_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_saas_trial_accounts`
--

LOCK TABLES `ec_saas_trial_accounts` WRITE;
/*!40000 ALTER TABLE `ec_saas_trial_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_saas_trial_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_saas_usage_enforcement_logs`
--

DROP TABLE IF EXISTS `ec_saas_usage_enforcement_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_saas_usage_enforcement_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enforcement_number` varchar(120) NOT NULL,
  `company_id` int(11) NOT NULL,
  `tenant_subscription_id` int(11) DEFAULT NULL,
  `module_key` varchar(160) DEFAULT NULL,
  `metric_key` varchar(120) DEFAULT NULL,
  `current_value` decimal(14,2) DEFAULT 0.00,
  `limit_value` decimal(14,2) DEFAULT 0.00,
  `severity` varchar(40) DEFAULT 'warning',
  `status` varchar(40) DEFAULT 'open',
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `enforcement_number` (`enforcement_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_saas_usage_enforcement_logs`
--

LOCK TABLES `ec_saas_usage_enforcement_logs` WRITE;
/*!40000 ALTER TABLE `ec_saas_usage_enforcement_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_saas_usage_enforcement_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_sales_brochure_sections`
--

DROP TABLE IF EXISTS `ec_sales_brochure_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_sales_brochure_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_number` varchar(120) NOT NULL,
  `section_title` varchar(255) DEFAULT NULL,
  `section_type` varchar(120) DEFAULT 'brochure',
  `headline` varchar(255) DEFAULT NULL,
  `body_text` longtext DEFAULT NULL,
  `cta_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_number` (`section_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_sales_brochure_sections`
--

LOCK TABLES `ec_sales_brochure_sections` WRITE;
/*!40000 ALTER TABLE `ec_sales_brochure_sections` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_sales_brochure_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_sales_opportunities`
--

DROP TABLE IF EXISTS `ec_sales_opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_sales_opportunities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `opportunity_number` varchar(120) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `stage_id` int(11) DEFAULT NULL,
  `owner_user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `source` varchar(120) DEFAULT NULL,
  `value_amount` decimal(14,2) DEFAULT 0.00,
  `probability` int(11) DEFAULT 0,
  `weighted_value` decimal(14,2) DEFAULT 0.00,
  `expected_close_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'open',
  `last_activity_at` datetime DEFAULT NULL,
  `next_follow_up` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `opportunity_number` (`opportunity_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_sales_opportunities`
--

LOCK TABLES `ec_sales_opportunities` WRITE;
/*!40000 ALTER TABLE `ec_sales_opportunities` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_sales_opportunities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_sales_opportunity_activities`
--

DROP TABLE IF EXISTS `ec_sales_opportunity_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_sales_opportunity_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sales_opportunity_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(80) DEFAULT 'note',
  `subject` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `activity_date` datetime DEFAULT NULL,
  `next_follow_up` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_sales_opportunity_activities`
--

LOCK TABLES `ec_sales_opportunity_activities` WRITE;
/*!40000 ALTER TABLE `ec_sales_opportunity_activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_sales_opportunity_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_sales_order_items`
--

DROP TABLE IF EXISTS `ec_sales_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_sales_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sales_order_id` int(11) NOT NULL,
  `item_type` varchar(40) DEFAULT 'product',
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 1.00,
  `unit_price` decimal(12,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(14,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_sales_order_items`
--

LOCK TABLES `ec_sales_order_items` WRITE;
/*!40000 ALTER TABLE `ec_sales_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_sales_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_sales_orders`
--

DROP TABLE IF EXISTS `ec_sales_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_sales_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `sales_order_number` varchar(120) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_type` varchar(30) DEFAULT 'b2c',
  `billing_address` text DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(14,2) DEFAULT 0.00,
  `discount` decimal(14,2) DEFAULT 0.00,
  `tax` decimal(14,2) DEFAULT 0.00,
  `shipping` decimal(14,2) DEFAULT 0.00,
  `total` decimal(14,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'draft',
  `credit_check_status` varchar(50) DEFAULT 'not_required',
  `credit_override_by` int(11) DEFAULT NULL,
  `credit_override_at` datetime DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `converted_invoice_id` int(11) DEFAULT NULL,
  `delivery_note_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_order_number` (`sales_order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_sales_orders`
--

LOCK TABLES `ec_sales_orders` WRITE;
/*!40000 ALTER TABLE `ec_sales_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_sales_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_sales_pipeline_stages`
--

DROP TABLE IF EXISTS `ec_sales_pipeline_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_sales_pipeline_stages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stage_key` varchar(120) NOT NULL,
  `stage_name` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `probability` int(11) DEFAULT 0,
  `is_won` tinyint(1) DEFAULT 0,
  `is_lost` tinyint(1) DEFAULT 0,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `stage_key` (`stage_key`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_sales_pipeline_stages`
--

LOCK TABLES `ec_sales_pipeline_stages` WRITE;
/*!40000 ALTER TABLE `ec_sales_pipeline_stages` DISABLE KEYS */;
INSERT INTO `ec_sales_pipeline_stages` VALUES (1,'new','New Lead',10,10,0,0,'active','2026-06-12 11:21:09'),(2,'qualified','Qualified',20,30,0,0,'active','2026-06-12 11:21:09'),(3,'proposal','Proposal Sent',30,50,0,0,'active','2026-06-12 11:21:09'),(4,'negotiation','Negotiation',40,70,0,0,'active','2026-06-12 11:21:09'),(5,'won','Won',50,100,1,0,'active','2026-06-12 11:21:09'),(6,'lost','Lost',60,0,0,1,'active','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_sales_pipeline_stages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_saved_reports`
--

DROP TABLE IF EXISTS `ec_saved_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_saved_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_code` varchar(120) NOT NULL,
  `report_name` varchar(255) NOT NULL,
  `report_type` varchar(120) NOT NULL,
  `config_json` longtext DEFAULT NULL,
  `visibility` varchar(40) DEFAULT 'private',
  `owner_user_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_code` (`report_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_saved_reports`
--

LOCK TABLES `ec_saved_reports` WRITE;
/*!40000 ALTER TABLE `ec_saved_reports` DISABLE KEYS */;
INSERT INTO `ec_saved_reports` VALUES (1,'RPT-SALES-PIPELINE','Sales Pipeline & Order Performance','sales_pipeline','{}','public',1,1,1,1,'2026-06-12 11:21:09'),(2,'RPT-INVENTORY-VALUE','Inventory Valuation & Low Stock','inventory_value','{}','public',1,1,1,1,'2026-06-12 11:21:09'),(3,'RPT-AP-MATCHING','Supplier Invoice Match Variance','ap_matching','{}','public',1,1,1,1,'2026-06-12 11:21:09'),(4,'RPT-PROJECT-MARGIN','Project Margin & Budget Usage','project_margin','{}','public',1,1,1,1,'2026-06-12 11:21:09'),(5,'RPT-SERVICE-PROFIT','Service Job Card Profitability','service_profit','{}','public',1,1,1,1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_saved_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_security_events`
--

DROP TABLE IF EXISTS `ec_security_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_security_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `event_type` varchar(120) NOT NULL,
  `severity` varchar(40) DEFAULT 'info',
  `ip_address` varchar(80) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_security_event_type` (`event_type`),
  KEY `idx_security_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_security_events`
--

LOCK TABLES `ec_security_events` WRITE;
/*!40000 ALTER TABLE `ec_security_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_security_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_sensitive_action_approvals`
--

DROP TABLE IF EXISTS `ec_sensitive_action_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_sensitive_action_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `approval_number` varchar(120) NOT NULL,
  `requested_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `action_key` varchar(160) DEFAULT NULL,
  `module` varchar(120) DEFAULT NULL,
  `reference_type` varchar(120) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `risk_level` varchar(40) DEFAULT 'medium',
  `status` varchar(40) DEFAULT 'pending',
  `requested_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `approval_number` (`approval_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_sensitive_action_approvals`
--

LOCK TABLES `ec_sensitive_action_approvals` WRITE;
/*!40000 ALTER TABLE `ec_sensitive_action_approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_sensitive_action_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_service_contract_visits`
--

DROP TABLE IF EXISTS `ec_service_contract_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_service_contract_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_contract_id` int(11) NOT NULL,
  `job_card_id` int(11) DEFAULT NULL,
  `visit_date` date DEFAULT NULL,
  `visit_type` varchar(120) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'planned',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_service_contract_visits`
--

LOCK TABLES `ec_service_contract_visits` WRITE;
/*!40000 ALTER TABLE `ec_service_contract_visits` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_service_contract_visits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_service_contracts`
--

DROP TABLE IF EXISTS `ec_service_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_service_contracts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `cost_center_id` int(11) DEFAULT NULL,
  `contract_number` varchar(120) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `contract_type` varchar(80) DEFAULT 'AMC',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `contract_value` decimal(14,2) DEFAULT 0.00,
  `billing_frequency` varchar(80) DEFAULT 'monthly',
  `next_billing_date` date DEFAULT NULL,
  `visits_included` int(11) DEFAULT 0,
  `visits_used` int(11) DEFAULT 0,
  `response_sla_hours` int(11) DEFAULT 24,
  `status` varchar(50) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `contract_number` (`contract_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_service_contracts`
--

LOCK TABLES `ec_service_contracts` WRITE;
/*!40000 ALTER TABLE `ec_service_contracts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_service_contracts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_services`
--

DROP TABLE IF EXISTS `ec_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) DEFAULT 0.00,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_services`
--

LOCK TABLES `ec_services` WRITE;
/*!40000 ALTER TABLE `ec_services` DISABLE KEYS */;
INSERT INTO `ec_services` VALUES (1,'Trade Account Setup','trade-account-setup','Commercial onboarding for B2B customer accounts.',199.00,1,'2026-06-12 11:21:09'),(2,'Procurement Consultation','procurement-consultation','Needs analysis for business buyers and bulk sourcing.',299.00,1,'2026-06-12 11:21:09'),(3,'Stock Planning Review','stock-planning-review','Operational guidance for replenishment and reorder planning.',349.00,1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_settings`
--

DROP TABLE IF EXISTS `ec_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_settings` (
  `key_name` varchar(120) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_settings`
--

LOCK TABLES `ec_settings` WRITE;
/*!40000 ALTER TABLE `ec_settings` DISABLE KEYS */;
INSERT INTO `ec_settings` VALUES ('accounting_export_batch_prefix','AEXP'),('advanced_ecommerce_enabled','1'),('ai_action_suggestion_prefix','ACTSUG'),('ai_assistant_enabled','1'),('ai_assistant_mode','rules_based'),('ai_auto_create_action_suggestions','1'),('ai_automation_run_prefix','AIRUN'),('ai_decision_recommendation_prefix','AIREC'),('ai_default_confidence_threshold','65'),('ai_default_risk_threshold','70'),('ai_playbook_prefix','AIPB'),('ai_risk_score_prefix','RSK'),('anomaly_detection_enabled','1'),('ap_aging_prefix','APAGE'),('api_default_daily_limit','1000'),('api_endpoint_prefix','APIEND'),('api_key_default_expiry_days','365'),('api_marketplace_enabled','1'),('api_rate_limit_enabled','1'),('api_usage_limit_prefix','APILIM'),('ar_aging_prefix','ARAGE'),('asset_qr_prefix','QR'),('audit_control_prefix','CTRL'),('auto_suspend_expired_trials','0'),('b2b_price_list_prefix','B2BPL'),('b2b_pricing_enabled','1'),('backup_before_upgrade_enabled','1'),('backup_directory','backups'),('backup_retention_days','14'),('bank_reconciliation_prefix','BREC'),('bi_default_date_range_days','30'),('bi_metric_prefix','BIM'),('booking_page_title','Book a Service'),('booking_service_type_label','Service Type'),('booking_service_types','Remote Installation\nDiagnostic Software Support\nVehicle Diagnostic Consultation\nAccount / Download Support'),('booking_submit_label','Submit Booking'),('budget_prefix','BUD'),('budget_variance_warning_percent','10'),('budget_warning_threshold_percent','80'),('bulk_order_enabled','1'),('bulk_order_prefix','BULK'),('bundle_builder_enabled','1'),('business_label','General Trading ERP & E-commerce'),('business_type','general_trading'),('cash_flow_forecast_prefix','CFF'),('catalog_intro','Search, filter, compare stock, and route high-value buyers into quotations when online checkout is not enough.'),('catalog_search','enabled'),('catalog_title','Browse a configurable product catalogue for trading and retail workflows.'),('category_section_intro','General-purpose product departments suitable for distribution, trading, and service-led commerce.'),('checkout_pay_later_enabled','1'),('checkout_pay_later_label','Pay Later'),('checkout_pay_later_note','Submit the order now and our team will confirm payment later.'),('checkout_payment_provider','paypal_me'),('client_onboarding_prefix','ONB'),('collection_task_days_overdue','7'),('collection_task_prefix','COLL'),('commercial_default_currency','AED'),('commercial_default_implementation_days','7'),('commercial_package_prefix','PKG'),('commission_prefix','COM'),('comparison_enabled','1'),('comparison_prefix','COMP'),('compliance_checklist_prefix','COMP'),('contact_address',''),('contact_details_enabled','1'),('contact_email',''),('contact_hours','Monday to Saturday, 9:00 AM - 6:00 PM'),('contact_map_url',''),('contact_page_intro','Send us your requirement and our team will contact you shortly.'),('contact_page_title','Contact Us'),('contact_phone',''),('contact_whatsapp',''),('credit_note_prefix','CNO'),('crm_auto_followup_days','3'),('crm_automation_enabled','1'),('crm_campaign_action_prefix','CAMP-ACT'),('crm_campaign_code_prefix','CMP'),('crm_followup_task_prefix','CFT'),('crm_forecast_probability_floor','10'),('crm_lead_score_hot_threshold','70'),('crm_lead_score_warm_threshold','40'),('crm_quote_followup_days','3'),('crm_quote_followup_prefix','QFU'),('crm_sales_forecast_prefix','FCST'),('crm_task_due_days','1'),('crm_touchpoint_prefix','TCH'),('cron_secret','7125acdd9ec06c74f3af322d'),('currency_rate_aed','1'),('currency_rate_egp','13.20'),('currency_rate_eur','0.2520'),('currency_rate_usd','0.2723'),('customer_credit_block_when_exceeded','1'),('customer_credit_include_open_sales_orders','1'),('customer_dashboard_invoice_disputes_enabled','0'),('customer_dashboard_payment_promises_enabled','0'),('customer_invoice_dispute_prefix','DISP'),('customer_payment_promise_prefix','PROM'),('customer_portal_announcements_enabled','1'),('customer_portal_documents_enabled','1'),('customer_portal_enabled','1'),('customer_portal_feedback_enabled','1'),('customer_portal_payment_promises_enabled','1'),('customer_prefix','CUS'),('customer_price_rule_prefix','CPR'),('customer_signoff_prefix','SIGN'),('dashboard_filter_prefix','BIF'),('dashboard_share_prefix','BISHARE'),('dashboard_widget_refresh_seconds','300'),('data_export_prefix','DEXP'),('data_export_tracking_enabled','1'),('database_encrypted_backup_prefix','ERPENC'),('database_encryption_algorithm','AES-256-GCM'),('database_encryption_tools_enabled','1'),('dataset_cache_ttl_minutes','60'),('debit_note_prefix','DNO'),('decision_support_scoring','1'),('default_annual_leave_days','30'),('default_b2b_discount_percent','5'),('default_branch_id','1'),('default_company_id','1'),('default_display_currency','AED'),('default_labor_hourly_rate','150'),('default_location_id','1'),('default_subscription_plan','GROWTH'),('default_technician_hourly_cost','45'),('default_trial_days','14'),('default_warehouse_id','1'),('demo_credential_prefix','DEMOCR'),('demo_data_manager_enabled','1'),('developer_docs_public_enabled','1'),('device_session_prefix','MOBDEV'),('digital_license_assignment_prefix','DLAS'),('digital_license_delivery_enabled','1'),('digital_license_pool_prefix','DLIC'),('document_access_logging_enabled','1'),('document_allowed_extensions','pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,txt,csv,zip'),('document_approval_prefix','DAPP'),('document_category_prefix','DCAT'),('document_default_expiry_alert_days','30'),('document_expiry_alert_prefix','DEXP'),('document_folder_prefix','FLD'),('document_library_prefix','DOC'),('document_max_upload_mb','25'),('document_require_approval_default','0'),('document_versioning_enabled','1'),('documentation_article_prefix','DOCART'),('documentation_asset_prefix','DOCAS'),('documentation_export_format','print_pdf'),('ecommerce_activity_prefix','EACT'),('ecommerce_discount_rule_prefix','EDISC'),('email_from_address',''),('email_from_name','General Trading ERP Store'),('employee_contract_prefix','ECON'),('employee_document_prefix','EDOC'),('employee_expense_prefix','EXPCL'),('employee_loan_prefix','ELOAN'),('employee_payslip_prefix','PAYSLIP'),('employee_self_service_enabled','1'),('enabled_modules_json','[\"website_sales\",\"website_storefront\",\"homepage_cms\",\"accounting_finance\",\"multicompany_inventory\",\"approval_workflow\",\"sales_operations\",\"service_projects\",\"security_deployment\",\"seo_frontend_settings\",\"rfq_tender\",\"crm_pipeline\",\"hr_payroll\",\"reporting_bi\",\"api_integrations\",\"ai_decision\",\"manufacturing_bom\",\"procurement_supplier_portal\",\"customer_portal\",\"saas_subscription\",\"mobile_pwa\",\"document_management\",\"advanced_ecommerce\",\"documentation_training\",\"egypt_tax_authority\",\"subscription_billing\",\"partner_reseller_portal\",\"customer_success\",\"executive_kpi_cockpit\",\"whatsapp_business\",\"ai_forecasting\",\"ai_inventory_planning\"]'),('enabled_priorities_json','[\"Website Sales\",\"Core\",\"Frontend\",\"P01/P13/P24\",\"P02/P19\",\"P03/P27\",\"P04\",\"P05/P16\",\"P07/P28/P34\",\"P08/P09\",\"P10\",\"P11/P22\",\"P12/P23\",\"P14/P25\",\"P15/P30\",\"P17/P26\",\"P18\",\"P20\",\"P21\",\"P29\",\"P31\",\"P32\",\"P33\",\"P35\",\"V58\"]'),('erp_mode','b2b_b2c'),('executive_dashboard_margin_warning_percent','15'),('feature_comparison_prefix','FCMP'),('field_default_dispatch_status','scheduled'),('field_dispatch_prefix','DISP'),('field_route_prefix','ROUTE'),('finance_automation_run_prefix','FAR'),('finance_forecast_days','30'),('financial_close_auto_tasks','1'),('financial_close_prefix','CLOSE'),('fixed_asset_default_depreciation_method','straight_line'),('fixed_asset_default_life_months','36'),('fixed_asset_prefix','FA'),('footer_about_enabled','0'),('footer_about_text','Commerce frontend, B2B quote journey, customer checkout, and ERP-linked stock and sales operations.'),('footer_bottom_note','Built as an ERP-connected commerce suite.'),('footer_bottom_note_enabled','0'),('footer_brand_mark','YS'),('footer_brand_name','Your Store Name'),('footer_contact_title','Contact'),('footer_hide_booking_for_guest','1'),('footer_hide_quote_for_guest','1'),('footer_newsletter_button','Subscribe'),('footer_newsletter_enabled','1'),('footer_newsletter_eyebrow','Commercial updates'),('footer_newsletter_placeholder','Business email address'),('footer_newsletter_text','Use the newsletter form for campaigns, product releases, and procurement promotions.'),('footer_newsletter_title','Get product launches, B2B offers, and service updates.'),('footer_pill_1','ERP'),('footer_pill_2','B2B'),('footer_pill_3','B2C'),('footer_pill_4','Inventory'),('footer_pills_enabled','0'),('footer_shop_links_enabled','1'),('free_shipping_threshold','500'),('frontend_security_block_copy','0'),('frontend_security_block_devtools_keys','1'),('frontend_security_block_image_drag','1'),('frontend_security_block_text_select','0'),('frontend_security_block_view_source','1'),('frontend_security_devtools_overlay','0'),('frontend_security_disable_right_click','1'),('frontend_security_enabled','0'),('frontend_security_noscript_warning','0'),('frontend_security_warning_message','This action is disabled for website security.'),('header_b2b_enquiry_enabled','0'),('header_b2b_enquiry_label','B2B Enquiry'),('header_b2b_enquiry_url','/contact.php'),('header_book_support_label','Book Support'),('header_book_support_url','/booking.php'),('header_brand_mark','YS'),('header_brand_name','Your Store Name'),('header_brand_tagline','Diagnostic Software Store'),('header_request_quote_label','Contact Us'),('header_request_quote_url','/contact.php'),('header_utility_enabled','0'),('header_utility_primary','ERP-connected B2B + B2C commerce'),('header_utility_secondary','Quote, invoice, stock, sales in one system'),('health_disk_warning_mb','512'),('homepage_b2b_eyebrow','B2B path'),('homepage_b2b_primary_label','Request Commercial Quote'),('homepage_b2b_primary_url','/contact.php'),('homepage_b2b_secondary_label','View Services'),('homepage_b2b_secondary_url','/services/index.php'),('homepage_b2b_text','The frontend should not only sell. It should send qualified business buyers into ERP quotations and sales operations.'),('homepage_b2b_title','Turn larger enquiries into quotes, invoices, and account records.'),('homepage_b2c_bullet_1','Searchable catalogue'),('homepage_b2c_bullet_2','Stronger product detail UX'),('homepage_b2c_bullet_3','Cart and checkout structure'),('homepage_b2c_bullet_4','Customer dashboard and downloads'),('homepage_b2c_cta_label','Browse Catalogue'),('homepage_b2c_cta_url','/products/index.php'),('homepage_b2c_eyebrow','B2C path'),('homepage_b2c_title','Make quick retail buying easier.'),('homepage_categories_cta_label','View catalogue'),('homepage_categories_cta_url','/products/index.php'),('homepage_categories_description','General-purpose product departments suitable for distribution, trading, and service-led commerce.'),('homepage_categories_enabled','1'),('homepage_categories_eyebrow','Shop by department'),('homepage_categories_title','Browse high-intent product categories'),('homepage_commercial_split_enabled','0'),('homepage_featured_cta_label','Shop all'),('homepage_featured_cta_url','/products/index.php'),('homepage_featured_description','Sharper product hierarchy: category, SKU, stock, price, detail link, and a premium card layout.'),('homepage_featured_eyebrow','Featured catalogue'),('homepage_featured_limit','8'),('homepage_featured_products_enabled','1'),('homepage_featured_title','Products designed to look more sellable'),('homepage_hero_enabled','1'),('homepage_hero_image',''),('homepage_hero_primary_label','Shop Products'),('homepage_hero_primary_url','/products/index.php'),('homepage_hero_secondary_label','Request Trade Quote'),('homepage_hero_secondary_url','/contact.php'),('homepage_hero_showcase_label','Featured Workflow'),('homepage_hero_showcase_text','Commerce actions become operational records.'),('homepage_hero_showcase_title','Storefront → Cart → ERP Invoice'),('homepage_hero_trust_1','Product catalogue'),('homepage_hero_trust_2','ERP-linked sales'),('homepage_hero_trust_3','Procurement ready'),('homepage_intro','Use a flexible catalogue for products and service add-ons while keeping ecommerce orders, B2B quotes, inventory, supplier purchases, and finance reporting connected.'),('homepage_kicker','Trading commerce + ERP workflow'),('homepage_new_arrivals_enabled','1'),('homepage_new_description','A marketplace-style discovery row that keeps the homepage dynamic.'),('homepage_new_eyebrow','New arrivals'),('homepage_new_limit','4'),('homepage_new_title','Freshly added products'),('homepage_promo_1_icon','bi-headset'),('homepage_promo_1_text','Quote-heavy product businesses stay conversion-ready.'),('homepage_promo_1_title','Sales Support'),('homepage_promo_2_icon','bi-truck'),('homepage_promo_2_text','Purchasing and stock flows remain visible.'),('homepage_promo_2_title','Inventory-Aware'),('homepage_promo_3_icon','bi-diagram-3'),('homepage_promo_3_text','One frontend for retail and company enquiries.'),('homepage_promo_3_title','B2B + B2C'),('homepage_promo_4_icon','bi-bar-chart'),('homepage_promo_4_text','Orders feed finance and operations.'),('homepage_promo_4_title','ERP Reporting'),('homepage_promo_ribbon_enabled','1'),('homepage_services_cta_label','All services'),('homepage_services_cta_url','/services/index.php'),('homepage_services_description','Useful for onboarding, remote support, installations, setup, and B2B implementation services.'),('homepage_services_enabled','1'),('homepage_services_eyebrow','Service commerce'),('homepage_services_limit','3'),('homepage_services_title','Sell products and services from the same business storefront'),('homepage_title','Run retail sales, B2B quotations, and procurement from one adaptable business platform.'),('homepage_trust_1_icon','bi-boxes'),('homepage_trust_1_text','Categories, cards, search, filters, and detail pages built for ecommerce presentation.'),('homepage_trust_1_title','Catalogue-ready'),('homepage_trust_2_icon','bi-clipboard2-data'),('homepage_trust_2_text','Orders, invoices, stock, and operations stay aligned through the backend workflow.'),('homepage_trust_2_title','ERP-aware'),('homepage_trust_3_icon','bi-building-check'),('homepage_trust_3_text','Quote-first and enquiry-led sales are visible beside checkout-led retail journeys.'),('homepage_trust_3_title','B2B-friendly'),('homepage_trust_4_icon','bi-phone'),('homepage_trust_4_text','Header, cards, sections, and product pages scale across desktop and mobile.'),('homepage_trust_4_title','Responsive'),('homepage_trust_grid_enabled','1'),('installer_rollback_enabled','1'),('installer_version','57.5.1 Multi-Business ERP Commerce Suite - PayPal.Me EcuWarrior + Multi-Entity Limits'),('integration_error_prefix','IERR'),('integration_mapping_prefix','IMAP'),('integration_payload_logging_enabled','1'),('integration_sync_enabled','1'),('integration_template_prefix','ICONN'),('intercompany_auto_journals','1'),('inventory_adjustment_prefix','ADJ'),('inventory_default_cost_ratio','0.60'),('inventory_lot_prefix','LOT'),('inventory_valuation_method','moving_average'),('invoice_prefix','INV'),('ip_access_control_enabled','0'),('ip_rule_prefix','IPR'),('journal_prefix','JRN'),('kpi_alert_rule_prefix','KAL'),('kpi_snapshot_auto_enabled','1'),('license_activated_at',''),('license_activation_code',''),('license_alert_email',''),('license_alert_email_enabled','0'),('license_allowed_modules_json','[]'),('license_bind_domain','1'),('license_bind_fingerprint','1'),('license_core_integrity_hash','1ab938db6e6cfed998c443ab8bdabe8af1321f69c1795f57604241ff8c1b9a21'),('license_enforcement_mode','enforce'),('license_grace_days','14'),('license_heartbeat_enabled','0'),('license_heartbeat_interval_hours','12'),('license_installation_uid','758f6f4518bc4203ea69c13b8c7c9d22'),('license_integrity_enabled','1'),('license_integrity_manifest_json',''),('license_last_heartbeat_at',''),('license_last_heartbeat_message',''),('license_last_heartbeat_status','never'),('license_last_remote_heartbeat_at',''),('license_last_remote_heartbeat_status','never'),('license_last_tamper_warning',''),('license_last_validated_at',''),('license_limits','{\"products\":5,\"categories\":5,\"customers\":5,\"orders\":5,\"users\":3,\"branches\":1,\"warehouses\":1,\"invoices\":10}'),('license_modules','[\"products\",\"categories\",\"customers\",\"orders\",\"inventory_basic\"]'),('license_note','Trial mode allows limited records. Activation requires a signed license bound to this installation and can enforce plan limits, heartbeat and read-only lock.'),('license_payload_json',''),('license_plan','trial'),('license_readonly_when_invalid','0'),('license_remote_limits_json','{}'),('license_remote_status','active'),('license_server_url',''),('license_signature_hash',''),('license_status','trial'),('license_trial_baseline_json','{\"products\":0,\"categories\":0,\"customers\":0,\"orders\":0}'),('license_trial_branch_limit','1'),('license_trial_category_limit','10'),('license_trial_customer_limit','10'),('license_trial_invoice_limit','10'),('license_trial_order_limit','10'),('license_trial_product_limit','10'),('license_trial_record_limit','5'),('license_trial_user_limit','10'),('license_trial_warehouse_limit','1'),('license_watermark_created_at','2026-06-12 15:21:12'),('license_watermark_id','WM-3F5DE3DE93FE649049BC56D8'),('login_max_attempts','5'),('login_session_prefix','LGS'),('maintenance_mode_enabled','0'),('management_dashboard_default','MGMT'),('manufacturing_auto_stock_update','1'),('manufacturing_bom_prefix','BOM'),('manufacturing_overhead_percent','10'),('manufacturing_work_order_prefix','MO'),('marketplace_sync_prefix','MKTQ'),('mobile_app_version','1.0.0'),('mobile_cache_version','v1'),('mobile_drawer_pill_1','ERP'),('mobile_drawer_pill_2','B2B'),('mobile_drawer_pill_3','B2C'),('mobile_drawer_pill_4','Inventory'),('mobile_drawer_pills_enabled','0'),('mobile_erp_enabled','1'),('mobile_install_event_prefix','MOBINS'),('mobile_install_prompt_enabled','1'),('mobile_offline_enabled','1'),('mobile_offline_mode_enabled','1'),('mobile_sync_prefix','MSYNC'),('mobile_sync_retry_limit','3'),('module_bundle_customer_notes','Selected modules control the sidebar and direct ERP access.'),('module_bundle_developer_email','3b@me.com'),('module_bundle_developer_only','1'),('module_bundle_enforcement_enabled','1'),('module_bundle_hide_disabled_sidebar','1'),('module_bundle_prefix','MBND'),('module_bundle_selection_locked','0'),('notification_auto_generate_enabled','1'),('offline_draft_prefix','OFF'),('packing_prefix','PACK'),('password_min_length','8'),('password_require_number','1'),('password_require_uppercase','1'),('payment_prefix','PAY'),('paypal_business_email','paypal.me/EcuWarrior'),('paypal_currency','USD'),('paypal_live_url','https://paypal.me/EcuWarrior'),('paypal_me_link','https://paypal.me/EcuWarrior'),('paypal_mode','live'),('paypal_sandbox_url','https://paypal.me/EcuWarrior'),('payroll_auto_deduct_employee_loans','1'),('payroll_auto_include_approved_expenses','1'),('payroll_auto_include_commissions','1'),('payroll_default_working_days','30'),('payroll_overtime_hour_rate','25'),('payroll_period_prefix','PAYP'),('payroll_run_prefix','PAY'),('performance_review_prefix','REV'),('permission_change_prefix','PCH'),('picking_prefix','PICK'),('plan_change_prefix','PLNCHG'),('portal_service_request_prefix','CSR'),('predictive_alert_score_threshold','60'),('procurement_auto_po_status','approved'),('procurement_award_prefix','AWD'),('product_bundle_prefix','BNDL'),('product_page_commercial_note_1_text','Customers can buy ready products through a clean cart and checkout journey.'),('product_page_commercial_note_1_title','Direct online purchase'),('product_page_commercial_note_2_text','Digital files, order history, invoices, and resources can be accessed from the customer account.'),('product_page_commercial_note_2_title','Customer account access'),('product_page_commercial_note_3_text','High-value products can still connect to support, contact, and quotation workflows when needed.'),('product_page_commercial_note_3_title','Support-led selling'),('product_page_commercial_notes_enabled','0'),('product_page_commercial_notes_title','Commercial Notes'),('product_page_slogan_enabled','1'),('product_page_slogan_icon','bi-bag-check'),('product_page_slogan_text','Browse products, add to cart, pay online, and access your digital resources from one clean customer account.'),('product_page_slogan_title','E-commerce made simple for every customer.'),('product_page_trust_1_icon','bi-building'),('product_page_trust_1_text','Company pricing path'),('product_page_trust_2_icon','bi-receipt'),('product_page_trust_2_text','ERP invoice workflow'),('product_page_trust_3_icon','bi-headset'),('product_page_trust_3_text','Sales support CTA'),('product_page_trust_enabled','0'),('production_backup_prefix','PBACK'),('production_cost_rollup_prefix','COST'),('production_demo_batch_prefix','DEMO'),('production_health_min_score','85'),('production_installer_event_prefix','IEVT'),('production_issue_prefix','ISSUE'),('production_mode_enabled','0'),('production_plan_prefix','PLAN'),('production_receipt_prefix','RECEIPT'),('production_release_checklist_prefix','REL'),('production_repair_prefix','PRUN'),('production_schema_check_prefix','SCHK'),('purchase_order_prefix','PO'),('push_notifications_enabled','1'),('push_queue_prefix','PUSH'),('push_vapid_private_key',''),('push_vapid_public_key',''),('pwa_app_name','General Trading ERP Store ERP'),('pwa_asset_prefix','PWA'),('pwa_background_color','#ffffff'),('pwa_display_mode','standalone'),('pwa_offline_page','/offline.php'),('pwa_service_worker_enabled','1'),('pwa_short_name','ERP'),('pwa_start_url','/mobile/index.php'),('pwa_theme_color','#0f172a'),('qr_public_lookup_enabled','0'),('quality_check_prefix','QC'),('quotation_prefix','QTN'),('quote_cta','Request Trade Quote'),('quote_request_prefix','QRQ'),('recommendation_auto_generate','1'),('recurring_journal_prefix','RJ'),('repair_center_enabled','1'),('replenishment_prefix','REP'),('report_drilldown_prefix','RDD'),('report_export_max_rows','5000'),('report_export_prefix','EXP'),('report_schedule_enabled','1'),('report_schedule_prefix','RSC'),('report_storyboard_prefix','STORY'),('request_quote_enabled','1'),('rfq_auto_close_on_award','1'),('rfq_invitation_prefix','RFI'),('rfq_min_supplier_count','3'),('rfq_prefix','RFQ'),('rfq_quote_response_prefix','RQR'),('saas_mode_enabled','0'),('saas_subscription_invoice_prefix','SUBINV'),('saas_subscription_payment_prefix','SUBPAY'),('saas_tax_rate_percent','5'),('sales_brochure_prefix','SBR'),('sales_opportunity_prefix','OPP'),('schema_checker_enabled','1'),('security_event_prefix','SEV'),('selected_business_type','general_trading'),('selected_module_bundle','enterprise_full'),('selected_module_bundle_label','Full Enterprise ERP Bundle'),('sensitive_action_approval_enabled','1'),('sensitive_action_prefix','SAAP'),('seo_blog_canonical',''),('seo_blog_description','Read articles, updates, and commercial insights.'),('seo_blog_keywords','blog, insights, articles'),('seo_blog_robots','index,follow'),('seo_blog_title','Insights | General Trading ERP Store'),('seo_booking_canonical',''),('seo_booking_description','Book a service, consultation, or support request.'),('seo_booking_keywords','booking, support, appointment'),('seo_booking_robots','index,follow'),('seo_booking_title','Book Support | General Trading ERP Store'),('seo_cart_canonical',''),('seo_cart_description','Review selected items before checkout.'),('seo_cart_keywords',''),('seo_cart_robots','noindex,nofollow'),('seo_cart_title','Cart | General Trading ERP Store'),('seo_checkout_canonical',''),('seo_checkout_description','Complete your checkout securely.'),('seo_checkout_keywords',''),('seo_checkout_robots','noindex,nofollow'),('seo_checkout_title','Checkout | General Trading ERP Store'),('seo_contact_canonical',''),('seo_contact_description','Contact the team for product enquiries, quotes, and business support.'),('seo_contact_keywords','contact, quotation, enquiry'),('seo_contact_robots','index,follow'),('seo_contact_title','Contact | General Trading ERP Store'),('seo_default_description','Explore products, services, downloads, and B2B/B2C business solutions from General Trading ERP Store.'),('seo_default_keywords','ecommerce, ERP, B2B, B2C, products, services'),('seo_default_og_image',''),('seo_default_robots','index,follow'),('seo_default_title_suffix','General Trading ERP Store'),('seo_downloads_canonical',''),('seo_downloads_description','View available downloads, documents, and customer resources.'),('seo_downloads_keywords','downloads, documents, resources'),('seo_downloads_robots','index,follow'),('seo_downloads_title','Downloads | General Trading ERP Store'),('seo_home_canonical',''),('seo_home_description','Browse products, business services, and ERP-connected commerce workflows.'),('seo_home_keywords','homepage, ecommerce, ERP commerce'),('seo_home_robots','index,follow'),('seo_home_title','General Trading ERP Store | E-commerce ERP Commerce Suite'),('seo_login_canonical',''),('seo_login_description','Customer and employee login area.'),('seo_login_keywords',''),('seo_login_robots','noindex,nofollow'),('seo_login_title','Login | General Trading ERP Store'),('seo_products_canonical',''),('seo_products_description','Browse the product catalogue, categories, prices, and availability.'),('seo_products_keywords','products, catalogue, online store'),('seo_products_robots','index,follow'),('seo_products_title','Products | General Trading ERP Store'),('seo_register_canonical',''),('seo_register_description','Create a customer account.'),('seo_register_keywords',''),('seo_register_robots','noindex,nofollow'),('seo_register_title','Register | General Trading ERP Store'),('seo_robots_txt','User-agent: *\\nAllow: /\\nDisallow: /admin/\\nDisallow: /user/\\nDisallow: /employee/\\nDisallow: /checkout.php\\nDisallow: /payment.php'),('seo_services_canonical',''),('seo_services_description','Explore bookable services and business support options.'),('seo_services_keywords','services, booking, support'),('seo_services_robots','index,follow'),('seo_services_title','Services | General Trading ERP Store'),('session_timeout_minutes','120'),('shift_template_prefix','SHIFT'),('shipping_cost','0'),('shop_address',''),('shop_email',''),('shop_name','General Trading ERP Store'),('shop_phone',''),('site_default_language','en'),('site_direction','ltr'),('site_language','both'),('site_language_mode','both'),('site_translation_enabled','1'),('smart_search_auto_index','1'),('stock_count_prefix','COUNT'),('supplier_contract_prefix','SCON'),('supplier_invoice_match_tolerance','1.00'),('supplier_min_approved_score','70'),('supplier_onboarding_prefix','SON'),('supplier_payment_run_prefix','SPR'),('supplier_prefix','SUP'),('supplier_price_list_prefix','SPL'),('supplier_quote_prefix','SQ'),('supplier_scorecard_prefix','SSC'),('system_error_logging_enabled','1'),('tax_default_rate','5'),('tax_rate','5'),('tax_return_prefix','TAX'),('technician_portal_enabled','1'),('tenant_domain_prefix','DOM'),('tenant_onboarding_prefix','ONB'),('tender_prefix','TND'),('training_checklist_prefix','TRNCHK'),('training_course_prefix','TRN'),('training_default_duration_minutes','60'),('translation_manual_json','{}'),('trial_account_prefix','TRIAL'),('two_factor_foundation_enabled','0'),('update_channel','stable'),('upgrade_mode_enabled','0'),('upgrade_safe_mode_enabled','1'),('usage_enforcement_enabled','1'),('usage_enforcement_prefix','USG'),('vendor_portal_enabled','1'),('vendor_quote_response_prefix','VQR'),('warehouse_auto_reserve_on_pick','1'),('warehouse_auto_stock_out_on_dispatch','1'),('warehouse_bin_prefix','BIN'),('warehouse_dispatch_prefix','DISP-WH'),('webhook_max_retries','3'),('webhook_retry_limit','3'),('webhook_retry_minutes','15'),('webhook_retry_prefix','WHRTY'),('webhook_signing_enabled','1'),('webhook_template_prefix','WHTPL'),('website_brand_name','Your Store Name'),('website_checkout_mode','erp_linked'),('website_permission_config_json',''),('website_permission_denied_message','Please login first or contact us to access this feature.'),('website_permission_enforcement_enabled','1'),('website_permission_require_active_module','0'),('website_sales_only_mode','0'),('whatsapp_default_country_code','+971'),('whatsapp_provider','manual'),('wishlist_enabled','1'),('wishlist_prefix','WISH'),('work_center_prefix','WC'),('workflow_approval_escalation_days','2'),('workflow_auto_low_stock_rfq','0'),('workflow_auto_requisition_rfq','0'),('workflow_automation_enabled','1'),('workflow_builder_enabled','1'),('workflow_builder_log_prefix','WFLOG'),('workflow_builder_rule_prefix','WFB'),('workflow_default_task_due_days','2'),('workflow_escalation_prefix','WFESC'),('workflow_overdue_invoice_days','7');
/*!40000 ALTER TABLE `ec_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_smart_search_index`
--

DROP TABLE IF EXISTS `ec_smart_search_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_smart_search_index` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(120) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `keywords` text DEFAULT NULL,
  `url_path` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `last_indexed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FULLTEXT KEY `ft_search` (`title`,`summary`,`keywords`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_smart_search_index`
--

LOCK TABLES `ec_smart_search_index` WRITE;
/*!40000 ALTER TABLE `ec_smart_search_index` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_smart_search_index` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_stock_count_lines`
--

DROP TABLE IF EXISTS `ec_stock_count_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_stock_count_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_count_session_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `system_qty` decimal(14,4) DEFAULT 0.0000,
  `counted_qty` decimal(14,4) DEFAULT 0.0000,
  `variance_qty` decimal(14,4) DEFAULT 0.0000,
  `unit_cost` decimal(14,2) DEFAULT 0.00,
  `variance_value` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'counted',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_stock_count_lines`
--

LOCK TABLES `ec_stock_count_lines` WRITE;
/*!40000 ALTER TABLE `ec_stock_count_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_stock_count_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_stock_count_sessions`
--

DROP TABLE IF EXISTS `ec_stock_count_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_stock_count_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `count_number` varchar(120) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  `bin_id` int(11) DEFAULT NULL,
  `count_type` varchar(80) DEFAULT 'cycle',
  `status` varchar(40) DEFAULT 'draft',
  `started_by` int(11) DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `count_number` (`count_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_stock_count_sessions`
--

LOCK TABLES `ec_stock_count_sessions` WRITE;
/*!40000 ALTER TABLE `ec_stock_count_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_stock_count_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_stock_transfer_items`
--

DROP TABLE IF EXISTS `ec_stock_transfer_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_stock_transfer_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_transfer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(12,2) DEFAULT 0.00,
  `received_quantity` decimal(12,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_stock_transfer_items`
--

LOCK TABLES `ec_stock_transfer_items` WRITE;
/*!40000 ALTER TABLE `ec_stock_transfer_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_stock_transfer_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_stock_transfers`
--

DROP TABLE IF EXISTS `ec_stock_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_stock_transfers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transfer_number` varchar(120) NOT NULL,
  `from_company_id` int(11) DEFAULT NULL,
  `from_branch_id` int(11) DEFAULT NULL,
  `from_warehouse_id` int(11) DEFAULT NULL,
  `from_location_id` int(11) DEFAULT NULL,
  `to_company_id` int(11) DEFAULT NULL,
  `to_branch_id` int(11) DEFAULT NULL,
  `to_warehouse_id` int(11) DEFAULT NULL,
  `to_location_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'draft',
  `requested_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `dispatched_by` int(11) DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `requested_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `dispatched_at` datetime DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transfer_number` (`transfer_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_stock_transfers`
--

LOCK TABLES `ec_stock_transfers` WRITE;
/*!40000 ALTER TABLE `ec_stock_transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_stock_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_subscription_plans`
--

DROP TABLE IF EXISTS `ec_subscription_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_subscription_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_code` varchar(120) NOT NULL,
  `plan_name` varchar(255) NOT NULL,
  `billing_cycle` varchar(40) DEFAULT 'monthly',
  `monthly_price` decimal(12,2) DEFAULT 0.00,
  `yearly_price` decimal(12,2) DEFAULT 0.00,
  `user_limit` int(11) DEFAULT 5,
  `branch_limit` int(11) DEFAULT 1,
  `warehouse_limit` int(11) DEFAULT 1,
  `product_limit` int(11) DEFAULT 1000,
  `storage_limit_mb` int(11) DEFAULT 1024,
  `api_call_limit_monthly` int(11) DEFAULT 10000,
  `status` varchar(40) DEFAULT 'active',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `plan_code` (`plan_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_subscription_plans`
--

LOCK TABLES `ec_subscription_plans` WRITE;
/*!40000 ALTER TABLE `ec_subscription_plans` DISABLE KEYS */;
INSERT INTO `ec_subscription_plans` VALUES (1,'STARTER','Starter ERP','monthly',199.00,1990.00,5,1,1,1000,1024,5000,'active','Entry plan for small teams starting with ERP-commerce operations.','2026-06-12 11:21:09'),(2,'GROWTH','Growth ERP','monthly',499.00,4990.00,25,5,5,10000,10240,50000,'active','Recommended plan for growing B2B/B2C companies with multi-branch operations.','2026-06-12 11:21:09'),(3,'ENTERPRISE','Enterprise ERP','monthly',1499.00,14990.00,250,50,100,250000,102400,500000,'active','Enterprise plan for larger groups requiring advanced controls and integrations.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_subscription_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_supplier_contract_milestones`
--

DROP TABLE IF EXISTS `ec_supplier_contract_milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_supplier_contract_milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_contract_id` int(11) NOT NULL,
  `milestone_title` varchar(255) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `amount` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_supplier_contract_milestones`
--

LOCK TABLES `ec_supplier_contract_milestones` WRITE;
/*!40000 ALTER TABLE `ec_supplier_contract_milestones` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_supplier_contract_milestones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_supplier_contracts`
--

DROP TABLE IF EXISTS `ec_supplier_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_supplier_contracts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_number` varchar(120) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `contract_title` varchar(255) DEFAULT NULL,
  `contract_type` varchar(120) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `contract_value` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'draft',
  `renewal_notice_days` int(11) DEFAULT 30,
  `document_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `contract_number` (`contract_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_supplier_contracts`
--

LOCK TABLES `ec_supplier_contracts` WRITE;
/*!40000 ALTER TABLE `ec_supplier_contracts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_supplier_contracts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_supplier_invoice_items`
--

DROP TABLE IF EXISTS `ec_supplier_invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_supplier_invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_invoice_id` int(11) NOT NULL,
  `purchase_order_item_id` int(11) DEFAULT NULL,
  `goods_receipt_item_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 0.00,
  `matched_quantity` decimal(12,2) DEFAULT 0.00,
  `unit_cost` decimal(12,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `line_total` decimal(14,2) DEFAULT 0.00,
  `variance_amount` decimal(14,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_supplier_invoice_items`
--

LOCK TABLES `ec_supplier_invoice_items` WRITE;
/*!40000 ALTER TABLE `ec_supplier_invoice_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_supplier_invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_supplier_invoices`
--

DROP TABLE IF EXISTS `ec_supplier_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_supplier_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `internal_number` varchar(120) NOT NULL,
  `supplier_invoice_number` varchar(160) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `purchase_order_id` int(11) DEFAULT NULL,
  `goods_receipt_id` int(11) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(14,2) DEFAULT 0.00,
  `tax` decimal(14,2) DEFAULT 0.00,
  `total` decimal(14,2) DEFAULT 0.00,
  `matched_total` decimal(14,2) DEFAULT 0.00,
  `difference_amount` decimal(14,2) DEFAULT 0.00,
  `match_status` varchar(50) DEFAULT 'pending',
  `approval_status` varchar(50) DEFAULT 'not_required',
  `status` varchar(50) DEFAULT 'draft',
  `posted_journal_id` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `internal_number` (`internal_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_supplier_invoices`
--

LOCK TABLES `ec_supplier_invoices` WRITE;
/*!40000 ALTER TABLE `ec_supplier_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_supplier_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_supplier_onboarding_requests`
--

DROP TABLE IF EXISTS `ec_supplier_onboarding_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_supplier_onboarding_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `onboarding_number` varchar(120) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `tax_number` varchar(120) DEFAULT NULL,
  `category` varchar(160) DEFAULT NULL,
  `risk_level` varchar(40) DEFAULT 'medium',
  `status` varchar(40) DEFAULT 'draft',
  `submitted_at` datetime DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `onboarding_number` (`onboarding_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_supplier_onboarding_requests`
--

LOCK TABLES `ec_supplier_onboarding_requests` WRITE;
/*!40000 ALTER TABLE `ec_supplier_onboarding_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_supplier_onboarding_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_supplier_onboarding_steps`
--

DROP TABLE IF EXISTS `ec_supplier_onboarding_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_supplier_onboarding_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_onboarding_request_id` int(11) NOT NULL,
  `step_name` varchar(255) DEFAULT NULL,
  `step_type` varchar(120) DEFAULT 'document',
  `required_flag` tinyint(1) DEFAULT 1,
  `status` varchar(40) DEFAULT 'pending',
  `completed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_supplier_onboarding_steps`
--

LOCK TABLES `ec_supplier_onboarding_steps` WRITE;
/*!40000 ALTER TABLE `ec_supplier_onboarding_steps` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_supplier_onboarding_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_supplier_payment_run_items`
--

DROP TABLE IF EXISTS `ec_supplier_payment_run_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_supplier_payment_run_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_payment_run_id` int(11) NOT NULL,
  `expense_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `vendor_name` varchar(255) DEFAULT NULL,
  `expense_number` varchar(120) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `amount_due` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_supplier_payment_run_items`
--

LOCK TABLES `ec_supplier_payment_run_items` WRITE;
/*!40000 ALTER TABLE `ec_supplier_payment_run_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_supplier_payment_run_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_supplier_payment_runs`
--

DROP TABLE IF EXISTS `ec_supplier_payment_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_supplier_payment_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_run_number` varchar(120) NOT NULL,
  `run_date` date NOT NULL,
  `date_to` date NOT NULL,
  `status` varchar(40) DEFAULT 'draft',
  `total_amount` decimal(14,2) DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_run_number` (`payment_run_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_supplier_payment_runs`
--

LOCK TABLES `ec_supplier_payment_runs` WRITE;
/*!40000 ALTER TABLE `ec_supplier_payment_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_supplier_payment_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_supplier_price_list_items`
--

DROP TABLE IF EXISTS `ec_supplier_price_list_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_supplier_price_list_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_price_list_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `supplier_sku` varchar(160) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `unit_cost` decimal(14,2) DEFAULT 0.00,
  `moq` decimal(14,4) DEFAULT 1.0000,
  `lead_time_days` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_supplier_price_list_items`
--

LOCK TABLES `ec_supplier_price_list_items` WRITE;
/*!40000 ALTER TABLE `ec_supplier_price_list_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_supplier_price_list_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_supplier_price_lists`
--

DROP TABLE IF EXISTS `ec_supplier_price_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_supplier_price_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `price_list_number` varchar(120) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `price_list_name` varchar(255) DEFAULT NULL,
  `currency` varchar(20) DEFAULT 'AED',
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_list_number` (`price_list_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_supplier_price_lists`
--

LOCK TABLES `ec_supplier_price_lists` WRITE;
/*!40000 ALTER TABLE `ec_supplier_price_lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_supplier_price_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_supplier_scorecards`
--

DROP TABLE IF EXISTS `ec_supplier_scorecards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_supplier_scorecards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scorecard_number` varchar(120) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `period_label` varchar(80) DEFAULT NULL,
  `quality_score` decimal(8,2) DEFAULT 0.00,
  `delivery_score` decimal(8,2) DEFAULT 0.00,
  `price_score` decimal(8,2) DEFAULT 0.00,
  `response_score` decimal(8,2) DEFAULT 0.00,
  `compliance_score` decimal(8,2) DEFAULT 0.00,
  `total_score` decimal(8,2) DEFAULT 0.00,
  `rating` varchar(40) DEFAULT 'C',
  `status` varchar(40) DEFAULT 'published',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `scorecard_number` (`scorecard_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_supplier_scorecards`
--

LOCK TABLES `ec_supplier_scorecards` WRITE;
/*!40000 ALTER TABLE `ec_supplier_scorecards` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_supplier_scorecards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_supplier_user_access`
--

DROP TABLE IF EXISTS `ec_supplier_user_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_supplier_user_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_label` varchar(120) DEFAULT 'Vendor User',
  `can_view_pos` tinyint(1) DEFAULT 1,
  `can_view_invoices` tinyint(1) DEFAULT 1,
  `can_upload_documents` tinyint(1) DEFAULT 1,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_supplier_user` (`supplier_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_supplier_user_access`
--

LOCK TABLES `ec_supplier_user_access` WRITE;
/*!40000 ALTER TABLE `ec_supplier_user_access` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_supplier_user_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_suppliers`
--

DROP TABLE IF EXISTS `ec_suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `supplier_code` varchar(80) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `tax_number` varchar(120) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `payment_terms_days` int(11) DEFAULT 0,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_code` (`supplier_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_suppliers`
--

LOCK TABLES `ec_suppliers` WRITE;
/*!40000 ALTER TABLE `ec_suppliers` DISABLE KEYS */;
INSERT INTO `ec_suppliers` VALUES (1,1,1,'SUP-0001','Regional Distribution Hub','Hassan Nadeem','sales@regionalhub.example','+971500000035','TRN-SUP-301','Jebel Ali, Dubai, UAE',30,'active','2026-06-12 11:21:09'),(2,1,1,'SUP-0002','Office Supply Partners','Mina Joseph','orders@officesupplypartners.example','+971500000036','TRN-SUP-302','Sharjah, UAE',15,'active','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_support_tickets`
--

DROP TABLE IF EXISTS `ec_support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_support_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_number` varchar(80) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `priority` varchar(40) DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_number` (`ticket_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_support_tickets`
--

LOCK TABLES `ec_support_tickets` WRITE;
/*!40000 ALTER TABLE `ec_support_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_support_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_suspicious_activity_events`
--

DROP TABLE IF EXISTS `ec_suspicious_activity_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_suspicious_activity_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_type` varchar(120) DEFAULT NULL,
  `severity` varchar(40) DEFAULT 'warning',
  `ip_address` varchar(80) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `risk_score` decimal(8,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_number` (`event_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_suspicious_activity_events`
--

LOCK TABLES `ec_suspicious_activity_events` WRITE;
/*!40000 ALTER TABLE `ec_suspicious_activity_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_suspicious_activity_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_system_error_logs`
--

DROP TABLE IF EXISTS `ec_system_error_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_system_error_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `severity` varchar(40) DEFAULT 'error',
  `message` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `line_number` int(11) DEFAULT NULL,
  `context_json` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_system_error_logs`
--

LOCK TABLES `ec_system_error_logs` WRITE;
/*!40000 ALTER TABLE `ec_system_error_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_system_error_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_system_health_checks`
--

DROP TABLE IF EXISTS `ec_system_health_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_system_health_checks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `check_key` varchar(120) NOT NULL,
  `check_label` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'ok',
  `value_text` varchar(255) DEFAULT NULL,
  `recommendation` text DEFAULT NULL,
  `checked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_health_key` (`check_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_system_health_checks`
--

LOCK TABLES `ec_system_health_checks` WRITE;
/*!40000 ALTER TABLE `ec_system_health_checks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_system_health_checks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_system_updates`
--

DROP TABLE IF EXISTS `ec_system_updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_system_updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `update_code` varchar(120) NOT NULL,
  `channel_id` int(11) DEFAULT NULL,
  `version_label` varchar(120) DEFAULT NULL,
  `release_title` varchar(255) DEFAULT NULL,
  `release_notes` longtext DEFAULT NULL,
  `package_url` varchar(255) DEFAULT NULL,
  `checksum` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'available',
  `is_required` tinyint(1) DEFAULT 0,
  `installed_by` int(11) DEFAULT NULL,
  `installed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `update_code` (`update_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_system_updates`
--

LOCK TABLES `ec_system_updates` WRITE;
/*!40000 ALTER TABLE `ec_system_updates` DISABLE KEYS */;
INSERT INTO `ec_system_updates` VALUES (1,'P8-30-0-0',1,'30.0.0','Priority 8 SaaS Readiness','Adds subscription plans, license center, module entitlements, update center, and upgrade mode controls.','','','installed',0,NULL,NULL,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_system_updates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_tax_return_lines`
--

DROP TABLE IF EXISTS `ec_tax_return_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_tax_return_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_return_id` int(11) NOT NULL,
  `source_type` varchar(80) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `source_number` varchar(120) DEFAULT NULL,
  `taxable_amount` decimal(14,2) DEFAULT 0.00,
  `tax_amount` decimal(14,2) DEFAULT 0.00,
  `direction` varchar(20) DEFAULT 'output',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_tax_return_lines`
--

LOCK TABLES `ec_tax_return_lines` WRITE;
/*!40000 ALTER TABLE `ec_tax_return_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_tax_return_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_tax_returns`
--

DROP TABLE IF EXISTS `ec_tax_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_tax_returns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_number` varchar(120) NOT NULL,
  `period_name` varchar(255) DEFAULT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `output_tax` decimal(14,2) DEFAULT 0.00,
  `input_tax` decimal(14,2) DEFAULT 0.00,
  `net_tax` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'draft',
  `filed_at` datetime DEFAULT NULL,
  `filing_reference` varchar(180) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `return_number` (`return_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_tax_returns`
--

LOCK TABLES `ec_tax_returns` WRITE;
/*!40000 ALTER TABLE `ec_tax_returns` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_tax_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_technician_checklist_items`
--

DROP TABLE IF EXISTS `ec_technician_checklist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_technician_checklist_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `technician_checklist_id` int(11) NOT NULL,
  `item_text` varchar(255) DEFAULT NULL,
  `item_type` varchar(80) DEFAULT 'checkbox',
  `is_required` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_technician_checklist_items`
--

LOCK TABLES `ec_technician_checklist_items` WRITE;
/*!40000 ALTER TABLE `ec_technician_checklist_items` DISABLE KEYS */;
INSERT INTO `ec_technician_checklist_items` VALUES (1,1,'Confirm customer vehicle/equipment details','checkbox',1,10,'2026-06-12 11:21:09'),(2,1,'Scan asset QR or enter VIN/serial number','checkbox',1,20,'2026-06-12 11:21:09'),(3,1,'Take before-service notes/photos reference','text',1,30,'2026-06-12 11:21:09'),(4,1,'Complete diagnosis / installation procedure','checkbox',1,40,'2026-06-12 11:21:09'),(5,1,'Record used parts from mobile screen','checkbox',0,50,'2026-06-12 11:21:09'),(6,1,'Capture customer sign-off','signature',1,60,'2026-06-12 11:21:09'),(7,2,'Confirm complaint and symptoms','text',1,10,'2026-06-12 11:21:09'),(8,2,'Run diagnostic scan and save reference','checkbox',1,20,'2026-06-12 11:21:09'),(9,2,'Record DTC / finding summary','text',1,30,'2026-06-12 11:21:09'),(10,2,'Share recommendation with customer','checkbox',1,40,'2026-06-12 11:21:09'),(11,2,'Capture sign-off / next action','signature',1,50,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_technician_checklist_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_technician_checklists`
--

DROP TABLE IF EXISTS `ec_technician_checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_technician_checklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checklist_code` varchar(120) NOT NULL,
  `checklist_name` varchar(255) DEFAULT NULL,
  `job_type` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `checklist_code` (`checklist_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_technician_checklists`
--

LOCK TABLES `ec_technician_checklists` WRITE;
/*!40000 ALTER TABLE `ec_technician_checklists` DISABLE KEYS */;
INSERT INTO `ec_technician_checklists` VALUES (1,'MOBILE-INSTALL','Mobile Installation Checklist','installation','active','2026-06-12 11:21:09'),(2,'MOBILE-DIAG','Mobile Diagnostic Checklist','diagnostic','active','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_technician_checklists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_technician_portal_notes`
--

DROP TABLE IF EXISTS `ec_technician_portal_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_technician_portal_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_card_id` int(11) DEFAULT NULL,
  `technician_user_id` int(11) DEFAULT NULL,
  `note_type` varchar(80) DEFAULT 'progress',
  `note` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_technician_portal_notes`
--

LOCK TABLES `ec_technician_portal_notes` WRITE;
/*!40000 ALTER TABLE `ec_technician_portal_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_technician_portal_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_technician_timesheets`
--

DROP TABLE IF EXISTS `ec_technician_timesheets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_technician_timesheets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `cost_center_id` int(11) DEFAULT NULL,
  `technician_user_id` int(11) NOT NULL,
  `job_card_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `work_date` date DEFAULT NULL,
  `hours` decimal(10,2) DEFAULT 0.00,
  `billable` tinyint(1) DEFAULT 1,
  `hourly_cost` decimal(12,2) DEFAULT 0.00,
  `cost_amount` decimal(14,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'draft',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_technician_timesheets`
--

LOCK TABLES `ec_technician_timesheets` WRITE;
/*!40000 ALTER TABLE `ec_technician_timesheets` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_technician_timesheets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_tenant_subscriptions`
--

DROP TABLE IF EXISTS `ec_tenant_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_tenant_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `subscription_plan_id` int(11) DEFAULT NULL,
  `subscription_number` varchar(120) NOT NULL,
  `status` varchar(40) DEFAULT 'trial',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `next_billing_date` date DEFAULT NULL,
  `billing_cycle` varchar(40) DEFAULT 'monthly',
  `amount` decimal(12,2) DEFAULT 0.00,
  `auto_renew` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscription_number` (`subscription_number`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_tenant_subscriptions`
--

LOCK TABLES `ec_tenant_subscriptions` WRITE;
/*!40000 ALTER TABLE `ec_tenant_subscriptions` DISABLE KEYS */;
INSERT INTO `ec_tenant_subscriptions` VALUES (1,1,2,'SUB-20260612-001','active','2026-06-12','2027-06-12','2026-07-12','monthly',499.00,1,'Default subscription created during installation.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_tenant_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_tenant_usage_snapshots`
--

DROP TABLE IF EXISTS `ec_tenant_usage_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_tenant_usage_snapshots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `snapshot_date` date NOT NULL,
  `user_count` int(11) DEFAULT 0,
  `branch_count` int(11) DEFAULT 0,
  `warehouse_count` int(11) DEFAULT 0,
  `product_count` int(11) DEFAULT 0,
  `storage_used_mb` decimal(12,2) DEFAULT 0.00,
  `api_calls_month` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tenant_usage_date` (`company_id`,`snapshot_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_tenant_usage_snapshots`
--

LOCK TABLES `ec_tenant_usage_snapshots` WRITE;
/*!40000 ALTER TABLE `ec_tenant_usage_snapshots` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_tenant_usage_snapshots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_training_checklist_items`
--

DROP TABLE IF EXISTS `ec_training_checklist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_training_checklist_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `training_checklist_id` int(11) NOT NULL,
  `item_title` varchar(255) DEFAULT NULL,
  `item_type` varchar(120) DEFAULT 'training',
  `status` varchar(40) DEFAULT 'open',
  `notes` text DEFAULT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_training_checklist_items`
--

LOCK TABLES `ec_training_checklist_items` WRITE;
/*!40000 ALTER TABLE `ec_training_checklist_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_training_checklist_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_training_checklists`
--

DROP TABLE IF EXISTS `ec_training_checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_training_checklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checklist_number` varchar(120) NOT NULL,
  `checklist_title` varchar(255) NOT NULL,
  `audience` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `checklist_number` (`checklist_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_training_checklists`
--

LOCK TABLES `ec_training_checklists` WRITE;
/*!40000 ALTER TABLE `ec_training_checklists` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_training_checklists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_training_courses`
--

DROP TABLE IF EXISTS `ec_training_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_training_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_number` varchar(120) NOT NULL,
  `course_title` varchar(255) NOT NULL,
  `course_type` varchar(120) DEFAULT 'admin_training',
  `audience` varchar(120) DEFAULT 'staff',
  `duration_minutes` int(11) DEFAULT 60,
  `status` varchar(40) DEFAULT 'active',
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_number` (`course_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_training_courses`
--

LOCK TABLES `ec_training_courses` WRITE;
/*!40000 ALTER TABLE `ec_training_courses` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_training_courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_training_lessons`
--

DROP TABLE IF EXISTS `ec_training_lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_training_lessons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `training_course_id` int(11) NOT NULL,
  `lesson_title` varchar(255) NOT NULL,
  `lesson_type` varchar(120) DEFAULT 'lesson',
  `lesson_content` longtext DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT 15,
  `sort_order` int(11) DEFAULT 0,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_training_lessons`
--

LOCK TABLES `ec_training_lessons` WRITE;
/*!40000 ALTER TABLE `ec_training_lessons` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_training_lessons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_two_factor_auth_secrets`
--

DROP TABLE IF EXISTS `ec_two_factor_auth_secrets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_two_factor_auth_secrets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `secret_label` varchar(160) DEFAULT 'Authenticator App',
  `secret_hash` varchar(255) DEFAULT NULL,
  `backup_codes_hash` longtext DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 0,
  `last_verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_2fa_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_two_factor_auth_secrets`
--

LOCK TABLES `ec_two_factor_auth_secrets` WRITE;
/*!40000 ALTER TABLE `ec_two_factor_auth_secrets` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_two_factor_auth_secrets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_update_channels`
--

DROP TABLE IF EXISTS `ec_update_channels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_update_channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_code` varchar(120) NOT NULL,
  `channel_name` varchar(255) DEFAULT NULL,
  `stability` varchar(40) DEFAULT 'stable',
  `status` varchar(40) DEFAULT 'active',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `channel_code` (`channel_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_update_channels`
--

LOCK TABLES `ec_update_channels` WRITE;
/*!40000 ALTER TABLE `ec_update_channels` DISABLE KEYS */;
INSERT INTO `ec_update_channels` VALUES (1,'stable','Stable Releases','stable','active','Recommended channel for production companies.','2026-06-12 11:21:09'),(2,'beta','Beta Releases','beta','active','Early access channel for testing new ERP modules.','2026-06-12 11:21:09'),(3,'lts','Long Term Support','lts','active','Conservative update channel for highly controlled environments.','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_update_channels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_upgrade_mode_events`
--

DROP TABLE IF EXISTS `ec_upgrade_mode_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_upgrade_mode_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `from_version` varchar(120) DEFAULT NULL,
  `to_version` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_upgrade_mode_events`
--

LOCK TABLES `ec_upgrade_mode_events` WRITE;
/*!40000 ALTER TABLE `ec_upgrade_mode_events` DISABLE KEYS */;
INSERT INTO `ec_upgrade_mode_events` VALUES (1,'install','completed','29.0.0','30.0.0','Priority 8 installer completed as fresh installation.',NULL,'2026-06-12 11:21:09',NULL);
/*!40000 ALTER TABLE `ec_upgrade_mode_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_user_branch_access`
--

DROP TABLE IF EXISTS `ec_user_branch_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_user_branch_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `can_view` tinyint(1) DEFAULT 1,
  `can_transact` tinyint(1) DEFAULT 1,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_user_branch_access`
--

LOCK TABLES `ec_user_branch_access` WRITE;
/*!40000 ALTER TABLE `ec_user_branch_access` DISABLE KEYS */;
INSERT INTO `ec_user_branch_access` VALUES (1,1,1,1,1,1,1,1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_user_branch_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_users`
--

DROP TABLE IF EXISTS `ec_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` varchar(30) DEFAULT 'customer',
  `erp_role_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `can_login_erp` tinyint(1) DEFAULT 0,
  `default_company_id` int(11) DEFAULT NULL,
  `default_branch_id` int(11) DEFAULT NULL,
  `default_warehouse_id` int(11) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_users`
--

LOCK TABLES `ec_users` WRITE;
/*!40000 ALTER TABLE `ec_users` DISABLE KEYS */;
INSERT INTO `ec_users` VALUES (1,'3B-nexosuite@gmail.com','$2y$10$soF6FHD6CdEuPfplbGf/PeTeJ3N1DJ4vAR1KUMw7pUjZhTQ1lMOXm','Admin','User',NULL,NULL,'admin',1,NULL,1,1,1,1,'active','2026-06-12 11:21:09'),(2,'3b@me.com','$2y$10$GLv.TUeCrEjBv5qH.0ArXeFtHq3Ece4nT7u7Ra2WLg1nNR3uehumS','Developer','Module Controller',NULL,NULL,'employee',6,NULL,1,NULL,NULL,NULL,'active','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_vat_periods`
--

DROP TABLE IF EXISTS `ec_vat_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_vat_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `period_name` varchar(120) NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `output_vat` decimal(14,2) DEFAULT 0.00,
  `input_vat` decimal(14,2) DEFAULT 0.00,
  `net_vat` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'draft',
  `filed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_vat_periods`
--

LOCK TABLES `ec_vat_periods` WRITE;
/*!40000 ALTER TABLE `ec_vat_periods` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_vat_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_vendor_document_uploads`
--

DROP TABLE IF EXISTS `ec_vendor_document_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_vendor_document_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `document_type` varchar(100) DEFAULT NULL,
  `document_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `stored_path` varchar(255) DEFAULT NULL,
  `mime_type` varchar(160) DEFAULT NULL,
  `file_size` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_vendor_document_uploads`
--

LOCK TABLES `ec_vendor_document_uploads` WRITE;
/*!40000 ALTER TABLE `ec_vendor_document_uploads` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_vendor_document_uploads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_vendor_quote_responses`
--

DROP TABLE IF EXISTS `ec_vendor_quote_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_vendor_quote_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `purchase_requisition_id` int(11) DEFAULT NULL,
  `purchase_order_id` int(11) DEFAULT NULL,
  `response_number` varchar(120) NOT NULL,
  `total_amount` decimal(14,2) DEFAULT 0.00,
  `delivery_days` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT 'submitted',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `response_number` (`response_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_vendor_quote_responses`
--

LOCK TABLES `ec_vendor_quote_responses` WRITE;
/*!40000 ALTER TABLE `ec_vendor_quote_responses` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_vendor_quote_responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_warehouse_bins`
--

DROP TABLE IF EXISTS `ec_warehouse_bins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_warehouse_bins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `warehouse_id` int(11) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  `bin_code` varchar(120) NOT NULL,
  `bin_name` varchar(255) DEFAULT NULL,
  `bin_type` varchar(80) DEFAULT 'storage',
  `capacity_qty` decimal(14,4) DEFAULT 0.0000,
  `current_qty` decimal(14,4) DEFAULT 0.0000,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bin_scope` (`warehouse_id`,`location_id`,`bin_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_warehouse_bins`
--

LOCK TABLES `ec_warehouse_bins` WRITE;
/*!40000 ALTER TABLE `ec_warehouse_bins` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_warehouse_bins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_warehouse_dispatch_items`
--

DROP TABLE IF EXISTS `ec_warehouse_dispatch_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_warehouse_dispatch_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `warehouse_dispatch_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `dispatch_qty` decimal(14,4) DEFAULT 0.0000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_warehouse_dispatch_items`
--

LOCK TABLES `ec_warehouse_dispatch_items` WRITE;
/*!40000 ALTER TABLE `ec_warehouse_dispatch_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_warehouse_dispatch_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_warehouse_dispatches`
--

DROP TABLE IF EXISTS `ec_warehouse_dispatches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_warehouse_dispatches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatch_number` varchar(120) NOT NULL,
  `packing_slip_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) NOT NULL,
  `carrier` varchar(160) DEFAULT NULL,
  `tracking_number` varchar(160) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'ready',
  `dispatched_by` int(11) DEFAULT NULL,
  `dispatched_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dispatch_number` (`dispatch_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_warehouse_dispatches`
--

LOCK TABLES `ec_warehouse_dispatches` WRITE;
/*!40000 ALTER TABLE `ec_warehouse_dispatches` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_warehouse_dispatches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_warehouse_locations`
--

DROP TABLE IF EXISTS `ec_warehouse_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_warehouse_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `warehouse_id` int(11) NOT NULL,
  `location_code` varchar(80) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `zone_type` varchar(80) DEFAULT 'general',
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_location_per_warehouse` (`warehouse_id`,`location_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_warehouse_locations`
--

LOCK TABLES `ec_warehouse_locations` WRITE;
/*!40000 ALTER TABLE `ec_warehouse_locations` DISABLE KEYS */;
INSERT INTO `ec_warehouse_locations` VALUES (1,1,'LOC-001','General Stock','general','active','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_warehouse_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_warehouse_stock`
--

DROP TABLE IF EXISTS `ec_warehouse_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_warehouse_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 0.00,
  `reserved_quantity` decimal(12,2) DEFAULT 0.00,
  `reorder_level` decimal(12,2) DEFAULT 5.00,
  `average_unit_cost` decimal(12,2) DEFAULT 0.00,
  `stock_value` decimal(14,2) DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_warehouse_stock_scope` (`product_id`,`warehouse_id`,`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_warehouse_stock`
--

LOCK TABLES `ec_warehouse_stock` WRITE;
/*!40000 ALTER TABLE `ec_warehouse_stock` DISABLE KEYS */;
INSERT INTO `ec_warehouse_stock` VALUES (1,1,1,1,1,1,28.00,0.00,5.00,419.40,11743.20,'2026-06-12 11:21:09'),(2,2,1,1,1,1,16.00,0.00,5.00,719.40,11510.40,'2026-06-12 11:21:09'),(3,3,1,1,1,1,12.00,0.00,5.00,1139.40,13672.80,'2026-06-12 11:21:09'),(4,4,1,1,1,1,999.00,0.00,150.00,149.40,149250.60,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_warehouse_stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_warehouses`
--

DROP TABLE IF EXISTS `ec_warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_warehouses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `warehouse_code` varchar(80) NOT NULL,
  `warehouse_name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouse_code` (`warehouse_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_warehouses`
--

LOCK TABLES `ec_warehouses` WRITE;
/*!40000 ALTER TABLE `ec_warehouses` DISABLE KEYS */;
INSERT INTO `ec_warehouses` VALUES (1,1,1,'WH-001','Main Warehouse','','active',1,'2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_warehouses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_warranty_claim_parts`
--

DROP TABLE IF EXISTS `ec_warranty_claim_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_warranty_claim_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `warranty_claim_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` decimal(12,2) DEFAULT 0.00,
  `unit_value` decimal(12,2) DEFAULT 0.00,
  `line_total` decimal(14,2) DEFAULT 0.00,
  `disposition` varchar(80) DEFAULT 'replace',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_warranty_claim_parts`
--

LOCK TABLES `ec_warranty_claim_parts` WRITE;
/*!40000 ALTER TABLE `ec_warranty_claim_parts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_warranty_claim_parts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_warranty_claims`
--

DROP TABLE IF EXISTS `ec_warranty_claims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_warranty_claims` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `cost_center_id` int(11) DEFAULT NULL,
  `claim_number` varchar(120) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `job_card_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `serial_number` varchar(160) DEFAULT NULL,
  `claim_type` varchar(80) DEFAULT 'customer',
  `failure_description` text DEFAULT NULL,
  `resolution` text DEFAULT NULL,
  `claim_value` decimal(14,2) DEFAULT 0.00,
  `approved_amount` decimal(14,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'draft',
  `approval_status` varchar(50) DEFAULT 'not_required',
  `supplier_claim_reference` varchar(160) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `claim_number` (`claim_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_warranty_claims`
--

LOCK TABLES `ec_warranty_claims` WRITE;
/*!40000 ALTER TABLE `ec_warranty_claims` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_warranty_claims` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_webhook_delivery_attempts`
--

DROP TABLE IF EXISTS `ec_webhook_delivery_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_webhook_delivery_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `webhook_event_id` int(11) NOT NULL,
  `webhook_subscription_id` int(11) DEFAULT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'queued',
  `http_status` int(11) DEFAULT 0,
  `response_body` text DEFAULT NULL,
  `attempted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_webhook_delivery_attempts`
--

LOCK TABLES `ec_webhook_delivery_attempts` WRITE;
/*!40000 ALTER TABLE `ec_webhook_delivery_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_webhook_delivery_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_webhook_event_templates`
--

DROP TABLE IF EXISTS `ec_webhook_event_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_webhook_event_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_code` varchar(120) NOT NULL,
  `event_type` varchar(160) DEFAULT NULL,
  `template_name` varchar(255) DEFAULT NULL,
  `payload_schema` longtext DEFAULT NULL,
  `sample_payload` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_code` (`template_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_webhook_event_templates`
--

LOCK TABLES `ec_webhook_event_templates` WRITE;
/*!40000 ALTER TABLE `ec_webhook_event_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_webhook_event_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_webhook_events`
--

DROP TABLE IF EXISTS `ec_webhook_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_webhook_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_number` varchar(120) NOT NULL,
  `event_type` varchar(120) DEFAULT NULL,
  `payload_json` longtext DEFAULT NULL,
  `reference_type` varchar(120) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'queued',
  `attempt_count` int(11) DEFAULT 0,
  `last_attempt_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_number` (`event_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_webhook_events`
--

LOCK TABLES `ec_webhook_events` WRITE;
/*!40000 ALTER TABLE `ec_webhook_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_webhook_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_webhook_retry_queue`
--

DROP TABLE IF EXISTS `ec_webhook_retry_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_webhook_retry_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `retry_number` varchar(120) NOT NULL,
  `webhook_event_id` int(11) NOT NULL,
  `webhook_subscription_id` int(11) DEFAULT NULL,
  `next_retry_at` datetime DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `max_retries` int(11) DEFAULT 3,
  `status` varchar(40) DEFAULT 'queued',
  `last_error` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `retry_number` (`retry_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_webhook_retry_queue`
--

LOCK TABLES `ec_webhook_retry_queue` WRITE;
/*!40000 ALTER TABLE `ec_webhook_retry_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_webhook_retry_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_webhook_subscriptions`
--

DROP TABLE IF EXISTS `ec_webhook_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_webhook_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscription_code` varchar(120) NOT NULL,
  `event_type` varchar(120) NOT NULL,
  `target_url` varchar(255) NOT NULL,
  `secret_token` varchar(255) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `last_delivery_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscription_code` (`subscription_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_webhook_subscriptions`
--

LOCK TABLES `ec_webhook_subscriptions` WRITE;
/*!40000 ALTER TABLE `ec_webhook_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_webhook_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_whatsapp_queue`
--

DROP TABLE IF EXISTS `ec_whatsapp_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_whatsapp_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_number` varchar(120) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `recipient_phone` varchar(80) DEFAULT NULL,
  `recipient_name` varchar(255) DEFAULT NULL,
  `message_body` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'queued',
  `provider_message_id` varchar(180) DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `queue_number` (`queue_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_whatsapp_queue`
--

LOCK TABLES `ec_whatsapp_queue` WRITE;
/*!40000 ALTER TABLE `ec_whatsapp_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_whatsapp_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_whatsapp_templates`
--

DROP TABLE IF EXISTS `ec_whatsapp_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_whatsapp_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_code` varchar(120) NOT NULL,
  `template_name` varchar(255) DEFAULT NULL,
  `language_code` varchar(20) DEFAULT 'en',
  `category` varchar(120) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_code` (`template_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_whatsapp_templates`
--

LOCK TABLES `ec_whatsapp_templates` WRITE;
/*!40000 ALTER TABLE `ec_whatsapp_templates` DISABLE KEYS */;
INSERT INTO `ec_whatsapp_templates` VALUES (1,'order_update','Order Update','en','Order','Hello {{name}}, your order {{order_number}} status is now {{status}}. Thank you.','active','2026-06-12 11:21:09'),(2,'quotation_followup','Quotation Follow-up','en','Sales','Hello {{name}}, this is a follow-up for quotation {{quotation_number}}. Please let us know if you need support.','active','2026-06-12 11:21:09'),(3,'payment_reminder','Payment Reminder','en','Finance','Hello {{name}}, your invoice {{invoice_number}} has an outstanding balance of {{amount}}.','active','2026-06-12 11:21:09'),(4,'service_booking','Service Booking Reminder','en','Service','Hello {{name}}, your service booking is scheduled for {{booking_date}}.','active','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_whatsapp_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_wishlist_items`
--

DROP TABLE IF EXISTS `ec_wishlist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_wishlist_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wishlist_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(12,2) DEFAULT 1.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wishlist_item` (`wishlist_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_wishlist_items`
--

LOCK TABLES `ec_wishlist_items` WRITE;
/*!40000 ALTER TABLE `ec_wishlist_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_wishlist_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_wishlists`
--

DROP TABLE IF EXISTS `ec_wishlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_wishlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wishlist_number` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `session_token` varchar(160) DEFAULT NULL,
  `wishlist_name` varchar(255) DEFAULT 'My Wishlist',
  `visibility` varchar(40) DEFAULT 'private',
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `wishlist_number` (`wishlist_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_wishlists`
--

LOCK TABLES `ec_wishlists` WRITE;
/*!40000 ALTER TABLE `ec_wishlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_wishlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_work_order_materials`
--

DROP TABLE IF EXISTS `ec_work_order_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_work_order_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manufacturing_work_order_id` int(11) NOT NULL,
  `component_product_id` int(11) NOT NULL,
  `required_quantity` decimal(14,4) DEFAULT 0.0000,
  `issued_quantity` decimal(14,4) DEFAULT 0.0000,
  `unit_cost` decimal(14,2) DEFAULT 0.00,
  `total_cost` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'required',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_work_order_materials`
--

LOCK TABLES `ec_work_order_materials` WRITE;
/*!40000 ALTER TABLE `ec_work_order_materials` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_work_order_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_work_order_operations`
--

DROP TABLE IF EXISTS `ec_work_order_operations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_work_order_operations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manufacturing_work_order_id` int(11) NOT NULL,
  `work_center_id` int(11) DEFAULT NULL,
  `operation_name` varchar(255) DEFAULT NULL,
  `planned_minutes` decimal(10,2) DEFAULT 0.00,
  `actual_minutes` decimal(10,2) DEFAULT 0.00,
  `labor_cost` decimal(14,2) DEFAULT 0.00,
  `status` varchar(40) DEFAULT 'pending',
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_work_order_operations`
--

LOCK TABLES `ec_work_order_operations` WRITE;
/*!40000 ALTER TABLE `ec_work_order_operations` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_work_order_operations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_workflow_approval_escalations`
--

DROP TABLE IF EXISTS `ec_workflow_approval_escalations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_workflow_approval_escalations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `escalation_number` varchar(120) NOT NULL,
  `approval_request_id` int(11) DEFAULT NULL,
  `document_type` varchar(120) DEFAULT NULL,
  `document_number` varchar(120) DEFAULT NULL,
  `current_step` int(11) DEFAULT 0,
  `days_pending` int(11) DEFAULT 0,
  `escalated_to_role` varchar(160) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'open',
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `escalation_number` (`escalation_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_workflow_approval_escalations`
--

LOCK TABLES `ec_workflow_approval_escalations` WRITE;
/*!40000 ALTER TABLE `ec_workflow_approval_escalations` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_workflow_approval_escalations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_workflow_automation_rules`
--

DROP TABLE IF EXISTS `ec_workflow_automation_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_workflow_automation_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_code` varchar(120) NOT NULL,
  `rule_name` varchar(255) NOT NULL,
  `module` varchar(120) DEFAULT NULL,
  `trigger_event` varchar(160) DEFAULT NULL,
  `condition_json` longtext DEFAULT NULL,
  `action_json` longtext DEFAULT NULL,
  `frequency` varchar(80) DEFAULT 'manual',
  `last_run_at` datetime DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_code` (`rule_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_workflow_automation_rules`
--

LOCK TABLES `ec_workflow_automation_rules` WRITE;
/*!40000 ALTER TABLE `ec_workflow_automation_rules` DISABLE KEYS */;
INSERT INTO `ec_workflow_automation_rules` VALUES (1,'AUTO_LOW_STOCK_RFQ','Create RFQ from low-stock products','Procurement','low_stock_rfq','{\"stock_lte\":3}','{\"create\":\"rfq\"}','manual',NULL,'active','2026-06-12 11:21:09'),(2,'AUTO_APPROVED_REQ_RFQ','Create RFQ from approved requisitions','Procurement','approved_requisition_rfq','{\"status\":\"approved\"}','{\"create\":\"rfq\"}','manual',NULL,'active','2026-06-12 11:21:09');
/*!40000 ALTER TABLE `ec_workflow_automation_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_workflow_automation_runs`
--

DROP TABLE IF EXISTS `ec_workflow_automation_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_workflow_automation_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_automation_rule_id` int(11) DEFAULT NULL,
  `run_number` varchar(120) NOT NULL,
  `status` varchar(40) DEFAULT 'running',
  `records_checked` int(11) DEFAULT 0,
  `actions_created` int(11) DEFAULT 0,
  `summary` text DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `run_number` (`run_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_workflow_automation_runs`
--

LOCK TABLES `ec_workflow_automation_runs` WRITE;
/*!40000 ALTER TABLE `ec_workflow_automation_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_workflow_automation_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_workflow_builder_action_logs`
--

DROP TABLE IF EXISTS `ec_workflow_builder_action_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_workflow_builder_action_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_number` varchar(120) NOT NULL,
  `workflow_builder_rule_id` int(11) NOT NULL,
  `workflow_builder_action_id` int(11) DEFAULT NULL,
  `reference_type` varchar(120) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `action_type` varchar(120) DEFAULT NULL,
  `status` varchar(40) DEFAULT 'success',
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `log_number` (`log_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_workflow_builder_action_logs`
--

LOCK TABLES `ec_workflow_builder_action_logs` WRITE;
/*!40000 ALTER TABLE `ec_workflow_builder_action_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_workflow_builder_action_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_workflow_builder_actions`
--

DROP TABLE IF EXISTS `ec_workflow_builder_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_workflow_builder_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_builder_rule_id` int(11) NOT NULL,
  `action_type` varchar(120) DEFAULT NULL,
  `action_label` varchar(255) DEFAULT NULL,
  `target_module` varchar(120) DEFAULT NULL,
  `target_status` varchar(120) DEFAULT NULL,
  `notification_title` varchar(255) DEFAULT NULL,
  `notification_message` text DEFAULT NULL,
  `task_due_days` int(11) DEFAULT 1,
  `action_config_json` longtext DEFAULT NULL,
  `status` varchar(40) DEFAULT 'active',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_workflow_builder_actions`
--

LOCK TABLES `ec_workflow_builder_actions` WRITE;
/*!40000 ALTER TABLE `ec_workflow_builder_actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_workflow_builder_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_workflow_builder_conditions`
--

DROP TABLE IF EXISTS `ec_workflow_builder_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_workflow_builder_conditions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_builder_rule_id` int(11) NOT NULL,
  `condition_group` varchar(80) DEFAULT 'AND',
  `field_key` varchar(160) DEFAULT NULL,
  `operator_key` varchar(40) DEFAULT 'equals',
  `compare_value` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_workflow_builder_conditions`
--

LOCK TABLES `ec_workflow_builder_conditions` WRITE;
/*!40000 ALTER TABLE `ec_workflow_builder_conditions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_workflow_builder_conditions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_workflow_builder_rules`
--

DROP TABLE IF EXISTS `ec_workflow_builder_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_workflow_builder_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_number` varchar(120) NOT NULL,
  `rule_name` varchar(255) NOT NULL,
  `module` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `priority` int(11) DEFAULT 50,
  `status` varchar(40) DEFAULT 'active',
  `run_mode` varchar(40) DEFAULT 'manual',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_run_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_number` (`rule_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_workflow_builder_rules`
--

LOCK TABLES `ec_workflow_builder_rules` WRITE;
/*!40000 ALTER TABLE `ec_workflow_builder_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_workflow_builder_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_workflow_builder_run_steps`
--

DROP TABLE IF EXISTS `ec_workflow_builder_run_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_workflow_builder_run_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_automation_run_id` int(11) DEFAULT NULL,
  `workflow_builder_rule_id` int(11) DEFAULT NULL,
  `workflow_builder_action_id` int(11) DEFAULT NULL,
  `step_number` int(11) DEFAULT 0,
  `step_type` varchar(120) DEFAULT NULL,
  `step_status` varchar(40) DEFAULT 'success',
  `reference_type` varchar(120) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_workflow_builder_run_steps`
--

LOCK TABLES `ec_workflow_builder_run_steps` WRITE;
/*!40000 ALTER TABLE `ec_workflow_builder_run_steps` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_workflow_builder_run_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ec_workflow_builder_triggers`
--

DROP TABLE IF EXISTS `ec_workflow_builder_triggers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ec_workflow_builder_triggers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workflow_builder_rule_id` int(11) NOT NULL,
  `trigger_type` varchar(120) DEFAULT NULL,
  `event_key` varchar(160) DEFAULT NULL,
  `schedule_expression` varchar(255) DEFAULT NULL,
  `source_table` varchar(160) DEFAULT NULL,
  `status_filter` varchar(120) DEFAULT NULL,
  `days_offset` int(11) DEFAULT 0,
  `config_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ec_workflow_builder_triggers`
--

LOCK TABLES `ec_workflow_builder_triggers` WRITE;
/*!40000 ALTER TABLE `ec_workflow_builder_triggers` DISABLE KEYS */;
/*!40000 ALTER TABLE `ec_workflow_builder_triggers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `license_audit_logs`
--

DROP TABLE IF EXISTS `license_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `license_audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(120) NOT NULL,
  `severity` varchar(30) NOT NULL DEFAULT 'info',
  `message` text DEFAULT NULL,
  `context` longtext DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `license_audit_logs`
--

LOCK TABLES `license_audit_logs` WRITE;
/*!40000 ALTER TABLE `license_audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `license_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `license_settings`
--

DROP TABLE IF EXISTS `license_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `license_settings` (
  `setting_key` varchar(190) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `license_settings`
--

LOCK TABLES `license_settings` WRITE;
/*!40000 ALTER TABLE `license_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `license_settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-12 14:46:06
