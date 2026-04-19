/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `answers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) unsigned NOT NULL,
  `text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `answers_question_id_foreign` (`question_id`),
  KEY `answers_question_correct_idx` (`question_id`,`is_correct`),
  CONSTRAINT `answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_reset_tokens_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint(20) unsigned NOT NULL,
  `text` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `correct_answers_count` int(11) NOT NULL DEFAULT 1,
  `order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `questions_quiz_id_foreign` (`quiz_id`),
  KEY `questions_quiz_order_idx` (`quiz_id`,`order`),
  CONSTRAINT `questions_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_anonymous_pool_reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_anonymous_pool_reservations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint(20) unsigned NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `slot_code` varchar(4) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quiz_anonymous_pool_reservations_quiz_id_session_id_unique` (`quiz_id`,`session_id`),
  UNIQUE KEY `quiz_anonymous_pool_reservations_quiz_id_slot_code_unique` (`quiz_id`,`slot_code`),
  KEY `quiz_anonymous_pool_reservations_quiz_id_expires_at_index` (`quiz_id`,`expires_at`),
  CONSTRAINT `quiz_anonymous_pool_reservations_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_attempt_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_attempt_answers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attempt_id` bigint(20) unsigned NOT NULL,
  `question_id` bigint(20) unsigned NOT NULL,
  `answer_id` bigint(20) unsigned NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quiz_attempt_answers_attempt_question_answer_unique` (`attempt_id`,`question_id`,`answer_id`),
  KEY `quiz_attempt_answers_attempt_id_foreign` (`attempt_id`),
  KEY `quiz_attempt_answers_question_id_foreign` (`question_id`),
  KEY `quiz_attempt_answers_answer_id_foreign` (`answer_id`),
  CONSTRAINT `quiz_attempt_answers_answer_id_foreign` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_attempt_answers_attempt_id_foreign` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_attempt_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint(20) unsigned NOT NULL,
  `quiz_student_id` bigint(20) unsigned DEFAULT NULL,
  `student_code` varchar(4) NOT NULL,
  `student_name` text NOT NULL,
  `student_name_blind_index` text DEFAULT NULL,
  `max_attempts` int(11) NOT NULL DEFAULT 1,
  `current_question_index` int(10) unsigned NOT NULL DEFAULT 0,
  `question_order` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`question_order`)),
  `answer_order` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answer_order`)),
  `skipped_question_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skipped_question_ids`)),
  `score` int(11) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'in_progress',
  `finish_reason` varchar(30) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `finalized_at` timestamp NULL DEFAULT NULL,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_attempts_quiz_id_foreign` (`quiz_id`),
  KEY `quiz_attempts_quiz_student_idx` (`quiz_id`,`student_code`),
  KEY `quiz_attempts_quiz_submitted_idx` (`quiz_id`,`submitted_at`),
  KEY `quiz_attempts_student_submitted_idx` (`quiz_student_id`,`submitted_at`),
  KEY `quiz_attempts_status_expires_idx` (`status`,`expires_at`),
  KEY `quiz_attempts_quiz_status_idx` (`quiz_id`,`status`),
  CONSTRAINT `quiz_attempts_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_attempts_quiz_student_id_foreign` FOREIGN KEY (`quiz_student_id`) REFERENCES `quiz_students` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_display_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_display_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint(20) unsigned NOT NULL,
  `quiz_student_id` bigint(20) unsigned NOT NULL,
  `quiz_attempt_id` bigint(20) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'waiting',
  `controller_session_hash` varchar(64) DEFAULT NULL,
  `state_version` int(10) unsigned NOT NULL DEFAULT 1,
  `controller_claimed_at` timestamp NULL DEFAULT NULL,
  `controller_last_seen_at` timestamp NULL DEFAULT NULL,
  `screen_last_seen_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_display_sessions_quiz_attempt_id_foreign` (`quiz_attempt_id`),
  KEY `quiz_display_sessions_quiz_status_idx` (`quiz_id`,`status`),
  KEY `quiz_display_sessions_student_status_idx` (`quiz_student_id`,`status`),
  CONSTRAINT `quiz_display_sessions_quiz_attempt_id_foreign` FOREIGN KEY (`quiz_attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_display_sessions_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_display_sessions_quiz_student_id_foreign` FOREIGN KEY (`quiz_student_id`) REFERENCES `quiz_students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_students` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint(20) unsigned NOT NULL,
  `student_code` varchar(4) NOT NULL,
  `access_token` varchar(32) DEFAULT NULL,
  `access_token_hash` varchar(64) DEFAULT NULL,
  `student_name` text NOT NULL,
  `student_name_blind_index` text DEFAULT NULL,
  `max_attempts` smallint(5) unsigned NOT NULL DEFAULT 1,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quiz_students_quiz_id_student_code_unique` (`quiz_id`,`student_code`),
  UNIQUE KEY `quiz_students_access_token_unique` (`access_token`),
  UNIQUE KEY `quiz_students_access_token_hash_unique` (`access_token_hash`),
  KEY `quiz_students_quiz_id_is_anonymous_index` (`quiz_id`,`is_anonymous`),
  CONSTRAINT `quiz_students_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_template_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_template_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_template_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quiz_template_user_quiz_template_id_user_id_unique` (`quiz_template_id`,`user_id`),
  KEY `quiz_template_user_user_id_foreign` (`user_id`),
  CONSTRAINT `quiz_template_user_quiz_template_id_foreign` FOREIGN KEY (`quiz_template_id`) REFERENCES `quiz_templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_template_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_common` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quiz_templates_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quizzes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quizzes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_system_example` tinyint(1) NOT NULL DEFAULT 0,
  `system_key` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `creator_id` bigint(20) unsigned NOT NULL,
  `quiz_code` varchar(8) NOT NULL,
  `max_attempts` int(11) NOT NULL DEFAULT 1,
  `time_limit` int(11) NOT NULL DEFAULT 600,
  `has_timer` tinyint(1) NOT NULL DEFAULT 1,
  `allow_resume` tinyint(1) NOT NULL DEFAULT 1,
  `is_learning_mode` tinyint(1) NOT NULL DEFAULT 0,
  `is_certificate_verification_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `is_second_screen_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `notify_creator_on_pass` tinyint(1) NOT NULL DEFAULT 1,
  `is_random_order` tinyint(1) NOT NULL DEFAULT 0,
  `is_random_answers_order` tinyint(1) NOT NULL DEFAULT 0,
  `show_answer_numbering` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `allow_guest` tinyint(1) NOT NULL DEFAULT 0,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `is_anonymous_bulk_mode` tinyint(1) NOT NULL DEFAULT 0,
  `is_public_anonymous_pool_mode` tinyint(1) NOT NULL DEFAULT 0,
  `anonymous_pool_capacity` int(10) unsigned DEFAULT NULL,
  `student_access_policy` varchar(20) NOT NULL DEFAULT 'pin_and_links',
  `public_token` varchar(32) DEFAULT NULL,
  `public_token_hash` varchar(64) DEFAULT NULL,
  `pass_percentage` int(11) NOT NULL DEFAULT 50,
  `question_view` varchar(255) NOT NULL DEFAULT 'default',
  `language` varchar(10) NOT NULL DEFAULT 'el',
  `questions_limit` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quizzes_quiz_code_unique` (`quiz_code`),
  UNIQUE KEY `quizzes_public_token_unique` (`public_token`),
  UNIQUE KEY `quizzes_public_token_hash_unique` (`public_token_hash`),
  UNIQUE KEY `quizzes_system_key_unique` (`system_key`),
  KEY `quizzes_category_id_foreign` (`category_id`),
  KEY `quizzes_creator_id_foreign` (`creator_id`),
  KEY `quizzes_creator_status_created_idx` (`creator_id`,`status`,`created_at`),
  KEY `quizzes_is_anonymous_bulk_mode_index` (`is_anonymous_bulk_mode`),
  KEY `quizzes_is_public_anonymous_pool_mode_index` (`is_public_anonymous_pool_mode`),
  CONSTRAINT `quizzes_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quizzes_creator_id_foreign` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `updates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher') NOT NULL DEFAULT 'teacher',
  `max_quizzes` smallint(5) unsigned NOT NULL DEFAULT 1,
  `max_questions_per_quiz` smallint(5) unsigned NOT NULL DEFAULT 30,
  `max_answers_per_question` smallint(5) unsigned NOT NULL DEFAULT 4,
  `max_students_per_quiz` smallint(5) unsigned NOT NULL DEFAULT 30,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2025_03_16_084755_create_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_03_16_084755_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_03_16_084756_create_quizzes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_03_16_084757_create_questions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_03_16_084758_create_answers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_03_16_084759_create_quiz_attempts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_03_16_084760_create_quiz_attempt_answers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_03_16_093107_create_sessions_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_03_22_215543_add_allow_guest_to_quizzes_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_03_23_100709_add_max_attempts_to_quiz_attempts_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_03_23_125429_add_pass_percentage_to_quizzes_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_03_24_082828_add_question_view_to_quizzes_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_03_25_093316_add_timer_and_resume_to_quizzes_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_03_26_114432_add_started_at_to_quiz_attempts_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_03_26_163441_remove_started_at_from_quiz_attempts_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_03_26_185100_create_quiz_students_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_03_29_210646_add_questions_limit_to_quizzes_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_04_04_110309_add_tokens_to_quizzes_and_students',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_04_05_160551_create_updates_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_04_13_143944_add_language_to_quizzes_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_04_14_130848_add_image_to_quizzes_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_07_05_102737_create_quiz_templates_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_07_05_102744_create_quiz_template_user_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_07_09_112541_create_password_reset_tokens_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_03_16_120000_add_is_random_answers_order_to_quizzes_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_03_16_163000_add_show_answer_numbering_to_quizzes_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_03_16_190000_add_safe_performance_indexes',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2026_03_16_200000_add_unique_constraint_to_quiz_attempt_answers',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2026_03_16_210000_add_quiz_student_id_to_quiz_attempts',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2026_03_17_120000_add_attempt_lifecycle_columns_to_quiz_attempts',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2026_03_17_170000_add_quota_limits_to_users_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2026_03_18_221555_add_dashboard_index_to_quizzes',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2026_03_23_120000_add_hashed_link_fingerprints',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2026_03_23_140000_encrypt_student_names_at_rest',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2026_03_23_150000_add_student_name_blind_indexes',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2026_03_25_120000_add_anonymous_bulk_mode_to_quizzes_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_03_25_130000_add_public_anonymous_pool_to_quizzes',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2026_03_25_131000_add_is_anonymous_to_quiz_students',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_03_25_132000_create_quiz_anonymous_pool_reservations_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_03_25_140000_add_student_access_policy_to_quizzes_table',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_04_09_120000_add_is_learning_mode_to_quizzes_table',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_04_10_090000_add_is_certificate_verification_enabled_to_quizzes_table',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_04_10_120000_add_system_example_columns_to_quizzes_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_04_11_130000_add_is_second_screen_enabled_to_quizzes_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_04_11_131000_create_quiz_display_sessions_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_04_12_090000_add_notify_creator_on_pass_to_quizzes_table',36);
