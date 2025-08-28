-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 28, 2025 at 11:05 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `error_order_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` bigint UNSIGNED NOT NULL,
  `task_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reactions` json DEFAULT NULL,
  `is_edited` tinyint(1) NOT NULL DEFAULT '0',
  `edited_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `task_id`, `user_id`, `parent_id`, `content`, `reactions`, `is_edited`, `edited_at`, `created_at`, `updated_at`) VALUES
(6, 15, 2, NULL, 'ádasd', NULL, 0, NULL, '2025-08-28 10:32:06', '2025-08-28 10:32:06');

-- --------------------------------------------------------

--
-- Table structure for table `comment_attachments`
--

CREATE TABLE `comment_attachments` (
  `id` bigint UNSIGNED NOT NULL,
  `comment_id` bigint UNSIGNED NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint NOT NULL,
  `file_extension` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contract_images`
--

CREATE TABLE `contract_images` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_contract_id` bigint UNSIGNED NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_number` int NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'IT', '2025-08-20 01:03:49', '2025-08-20 01:03:49'),
(2, 'Marketing', '2025-08-20 01:03:57', '2025-08-20 01:03:57'),
(3, 'Factory', '2025-08-20 01:04:04', '2025-08-20 01:04:04'),
(4, 'Design', '2025-08-20 01:04:15', '2025-08-20 01:04:15');

-- --------------------------------------------------------

--
-- Table structure for table `department_tasks`
--

CREATE TABLE `department_tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `task_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department_tasks`
--

INSERT INTO `department_tasks` (`id`, `task_id`, `department_id`, `created_at`, `updated_at`) VALUES
(50, 14, 1, '2025-08-28 10:08:08', '2025-08-28 10:08:08'),
(51, 15, 4, '2025-08-28 10:18:28', '2025-08-28 10:18:28'),
(52, 15, 1, '2025-08-28 10:18:28', '2025-08-28 10:18:28'),
(53, 13, 4, '2025-08-28 10:28:18', '2025-08-28 10:28:18'),
(54, 13, 1, '2025-08-28 10:28:18', '2025-08-28 10:28:18');

-- --------------------------------------------------------

--
-- Table structure for table `employee_contracts`
--

CREATE TABLE `employee_contracts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `probation_salary` decimal(10,2) NOT NULL,
  `probation_period` int NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` enum('active','completed','terminated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_contracts`
--

INSERT INTO `employee_contracts` (`id`, `user_id`, `probation_salary`, `probation_period`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 5000000.00, 2, '2025-08-20 08:05:08', NULL, 'completed', '2025-08-20 01:05:08', '2025-08-20 01:47:15'),
(2, 2, 7000000.00, 12, '2025-08-20 00:00:00', '2026-08-20 00:00:00', 'completed', '2025-08-20 01:47:15', '2025-08-20 01:47:15'),
(3, 6, 8000000.00, 1, '2025-08-20 09:00:17', NULL, 'active', '2025-08-20 02:00:17', '2025-08-20 02:00:17'),
(5, 11, 5000000.00, 2, '2025-08-26 17:23:12', NULL, 'active', '2025-08-26 10:23:12', '2025-08-26 10:23:12'),
(6, 10, 5000000.00, 2, '2025-08-26 00:00:00', NULL, 'active', '2025-08-26 10:29:49', '2025-08-26 10:29:49'),
(7, 12, 5000000.00, 2, '2025-08-26 17:54:31', NULL, 'active', '2025-08-26 10:54:31', '2025-08-26 10:54:31'),
(10, 15, 5000000.00, 2, '2025-08-26 18:06:40', '2025-10-26 18:06:40', 'active', '2025-08-26 11:06:40', '2025-08-26 11:06:40'),
(11, 16, 5000000.00, 2, '2025-08-26 18:06:40', '2025-10-26 18:06:40', 'active', '2025-08-26 11:06:40', '2025-08-26 11:06:40'),
(12, 17, 5000000.00, 2, '2025-08-26 18:06:40', '2025-10-26 18:06:40', 'active', '2025-08-26 11:06:40', '2025-08-26 11:06:40'),
(13, 18, 5000000.00, 2, '2025-08-26 18:06:41', '2025-10-26 18:06:41', 'active', '2025-08-26 11:06:41', '2025-08-26 11:06:41'),
(14, 19, 5000000.00, 2, '2025-08-26 18:06:41', '2025-10-26 18:06:41', 'active', '2025-08-26 11:06:41', '2025-08-26 11:06:41'),
(15, 20, 5000000.00, 2, '2025-08-26 18:06:41', '2025-10-26 18:06:41', 'active', '2025-08-26 11:06:41', '2025-08-26 11:06:41'),
(16, 21, 5000000.00, 2, '2025-08-26 18:06:41', '2025-10-26 18:06:41', 'active', '2025-08-26 11:06:41', '2025-08-26 11:06:41'),
(20, 25, 5000000.00, 2, '2025-08-26 18:06:42', '2025-10-26 18:06:42', 'completed', '2025-08-26 11:06:42', '2025-08-26 11:21:44'),
(21, 26, 5000000.00, 2, '2025-08-26 18:06:42', '2025-10-26 18:06:42', 'completed', '2025-08-26 11:06:42', '2025-08-26 11:23:08'),
(22, 25, 6000000.00, 12, '2025-08-26 00:00:00', '2026-08-26 00:00:00', 'active', '2025-08-26 11:21:44', '2025-08-26 11:21:44'),
(23, 26, 7000000.00, 12, '2025-08-27 00:00:00', '2026-08-27 00:00:00', 'active', '2025-08-26 11:23:08', '2025-08-26 11:23:08'),
(24, 27, 7000000.00, 2, '2025-08-26 18:25:05', NULL, 'completed', '2025-08-26 11:25:05', '2025-08-26 11:25:29'),
(25, 27, 8000000.00, 12, '2025-08-26 00:00:00', '2026-08-26 00:00:00', 'active', '2025-08-26 11:25:29', '2025-08-26 11:25:29');

-- --------------------------------------------------------

--
-- Table structure for table `employee_salaries`
--

CREATE TABLE `employee_salaries` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `gross_salary` decimal(10,2) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `allowance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bonus` decimal(10,2) NOT NULL DEFAULT '0.00',
  `deduction` decimal(10,2) NOT NULL DEFAULT '0.00',
  `insurance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(10,2) NOT NULL DEFAULT '0.00',
  `net_salary` decimal(10,2) NOT NULL,
  `effective_date` datetime NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_salaries`
--

INSERT INTO `employee_salaries` (`id`, `user_id`, `gross_salary`, `basic_salary`, `allowance`, `bonus`, `deduction`, `insurance`, `tax`, `net_salary`, `effective_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 7000000.00, 5600000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7000000.00, '2025-08-20 00:00:00', 'active', '2025-08-20 01:47:15', '2025-08-20 01:47:15'),
(2, 25, 6000000.00, 6000000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 6000000.00, '2025-08-26 00:00:00', 'active', '2025-08-26 11:21:44', '2025-08-26 11:21:44'),
(4, 26, 7000000.00, 7000000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 7000000.00, '2025-08-27 00:00:00', 'active', '2025-08-26 11:23:08', '2025-08-26 11:23:08'),
(5, 27, 8000000.00, 8000000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 8000000.00, '2025-08-26 00:00:00', 'active', '2025-08-26 11:25:29', '2025-08-26 11:25:29');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(2, '2025_08_15_021700_create_departments_table', 1),
(3, '2025_08_15_021710_create_users_table', 1),
(4, '2025_08_15_021744_create_tasks_table', 1),
(5, '2025_08_15_021745_create_task_activities_table', 1),
(6, '2025_08_18_000000_add_phone_to_users_table', 1),
(7, '2025_08_18_065614_add_priority_to_tasks_table', 1),
(8, '2025_08_18_071502_add_attachments_to_tasks_table', 1),
(9, '2025_08_18_074629_drop_spatie_permission_tables', 1),
(10, '2025_08_18_075000_update_task_status_enum', 1),
(11, '2025_08_18_075100_add_rejection_reason_to_tasks', 1),
(12, '2025_08_18_075200_add_finished_status_to_tasks', 1),
(13, '2025_08_19_014423_add_finish_note_to_tasks_table', 1),
(14, '2025_08_19_101648_add_qr_code_to_tasks_table', 1),
(15, '2025_08_20_075527_add_employee_fields_to_users_table', 1),
(16, '2025_08_20_075842_create_employee_contracts_table', 1),
(19, '2025_08_20_075907_create_contract_images_table', 2),
(20, '2025_08_20_080023_create_employee_salaries_table', 2),
(21, '2025_08_20_081627_add_additional_fields_to_users_table', 3),
(22, '2025_08_21_062422_add_recurring_fields_to_tasks_table', 4),
(23, '2025_08_21_063213_add_completed_at_to_tasks_table', 5),
(24, '2025_08_25_072400_create_task_assignees_table', 6),
(25, '2025_08_25_072410_create_department_tasks_table', 6),
(26, '2025_08_25_072507_add_is_multi_department_to_tasks_table', 6),
(27, '2025_08_25_091207_create_comments_table', 7),
(28, '2025_08_25_091231_create_comment_attachments_table', 7),
(29, '2025_08_25_091300_create_task_files_table', 7),
(30, '2025_08_25_091323_update_task_activities_table', 7),
(32, '2025_01_20_000001_create_work_reports_table', 8),
(33, '2025_08_26_032056_update_work_reports_department_id_nullable', 8),
(34, '2025_08_26_082918_add_week_of_month_to_work_reports_table', 9),
(35, '2025_08_28_134501_create_task_followers_table', 10),
(36, '2025_08_28_150408_create_task_approvals_table', 11),
(37, '2025_08_28_165650_create_notifications_table', 12);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `is_read`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 15, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Luu Pham Anh Kiet giao công việc: teesst thông báo', '{\"task_id\": 14, \"assigner_id\": 1, \"assigner_name\": \"Luu Pham Anh Kiet\"}', 0, NULL, '2025-08-28 10:08:08', '2025-08-28 10:08:08'),
(2, 1, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 1, '2025-08-28 10:41:09', '2025-08-28 10:16:17', '2025-08-28 10:41:09'),
(3, 1, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 1, '2025-08-28 10:41:00', '2025-08-28 10:16:17', '2025-08-28 10:41:00'),
(4, 1, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, '2025-08-28 10:40:58', '2025-08-28 10:16:17', '2025-08-28 10:40:58'),
(5, 2, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 1, '2025-08-28 10:41:21', '2025-08-28 10:16:17', '2025-08-28 10:41:21'),
(6, 2, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 1, '2025-08-28 10:41:21', '2025-08-28 10:16:17', '2025-08-28 10:41:21'),
(7, 2, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(8, 3, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(9, 3, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(10, 3, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(11, 4, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(12, 4, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(13, 4, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(14, 5, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(15, 5, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(16, 5, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(17, 6, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(18, 6, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(19, 6, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(20, 8, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(21, 8, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(22, 8, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(23, 10, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(24, 10, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(25, 10, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(26, 11, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(27, 11, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(28, 11, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(29, 12, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(30, 12, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(31, 12, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(32, 15, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(33, 15, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(34, 15, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(35, 16, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(36, 16, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(37, 16, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(38, 17, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(39, 17, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(40, 17, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(41, 18, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(42, 18, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(43, 18, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(44, 19, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(45, 19, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(46, 19, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(47, 20, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(48, 20, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(49, 20, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(50, 21, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(51, 21, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(52, 21, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(53, 25, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(54, 25, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(55, 25, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(56, 26, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(57, 26, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(58, 26, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(59, 27, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task', '{\"task_id\": 13, \"assigner_id\": 1, \"assigner_name\": \"Admin\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(60, 27, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update', '{\"task_id\": 13, \"updater_id\": 4, \"updater_name\": \"Manager\"}', 0, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(61, 27, 'work_report_submitted', 'Báo cáo công việc mới', 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc', '{\"report_id\": 1, \"submitter_id\": 2, \"submitter_name\": \"Employee\"}', 1, NULL, '2025-08-28 10:16:17', '2025-08-28 10:16:17'),
(62, 3, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Luu Pham Anh Kiet giao công việc: testfollower', '{\"task_id\": 15, \"assigner_id\": 1, \"assigner_name\": \"Luu Pham Anh Kiet\"}', 0, NULL, '2025-08-28 10:18:28', '2025-08-28 10:18:28'),
(63, 4, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Luu Pham Anh Kiet giao công việc: testfollower', '{\"task_id\": 15, \"assigner_id\": 1, \"assigner_name\": \"Luu Pham Anh Kiet\"}', 0, NULL, '2025-08-28 10:18:28', '2025-08-28 10:18:28'),
(64, 5, 'task_assigned', 'Công việc mới được giao', 'Bạn nhận được thông báo mời từ Luu Pham Anh Kiet giao công việc: testfollower', '{\"task_id\": 15, \"assigner_id\": 1, \"assigner_name\": \"Luu Pham Anh Kiet\"}', 0, NULL, '2025-08-28 10:18:28', '2025-08-28 10:18:28'),
(65, 2, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Luu Pham Anh Kiet cập nhật công việc: test', '{\"task_id\": 13, \"updater_id\": 1, \"updater_name\": \"Luu Pham Anh Kiet\"}', 1, '2025-08-28 10:41:21', '2025-08-28 10:28:18', '2025-08-28 10:41:21'),
(66, 3, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Luu Pham Anh Kiet cập nhật công việc: test', '{\"task_id\": 13, \"updater_id\": 1, \"updater_name\": \"Luu Pham Anh Kiet\"}', 0, NULL, '2025-08-28 10:28:18', '2025-08-28 10:28:18'),
(67, 5, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Luu Pham Anh Kiet cập nhật công việc: test', '{\"task_id\": 13, \"updater_id\": 1, \"updater_name\": \"Luu Pham Anh Kiet\"}', 0, NULL, '2025-08-28 10:28:18', '2025-08-28 10:28:18'),
(68, 18, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Luu Pham Anh Kiet cập nhật công việc: test', '{\"task_id\": 13, \"updater_id\": 1, \"updater_name\": \"Luu Pham Anh Kiet\"}', 0, NULL, '2025-08-28 10:28:18', '2025-08-28 10:28:18'),
(69, 15, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Luu Pham Anh Kiet cập nhật công việc: test', '{\"task_id\": 13, \"updater_id\": 1, \"updater_name\": \"Luu Pham Anh Kiet\"}', 0, NULL, '2025-08-28 10:28:18', '2025-08-28 10:28:18'),
(70, 19, 'task_updated', 'Công việc được cập nhật', 'Bạn nhận được thông báo mời từ Luu Pham Anh Kiet cập nhật công việc: test', '{\"task_id\": 13, \"updater_id\": 1, \"updater_name\": \"Luu Pham Anh Kiet\"}', 0, NULL, '2025-08-28 10:28:18', '2025-08-28 10:28:18');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachments` json DEFAULT NULL,
  `qr_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `is_multi_department` tinyint(1) NOT NULL DEFAULT '0',
  `assignee_id` bigint UNSIGNED DEFAULT NULL,
  `creator_id` bigint UNSIGNED NOT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `deadline` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `finish_note` text COLLATE utf8mb4_unicode_ci,
  `status` enum('in_progress','completed','rejected','overdue','finished') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in_progress',
  `is_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `recurring_start_date` date DEFAULT NULL,
  `recurring_days` int DEFAULT NULL,
  `last_reset_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `priority`, `attachments`, `qr_code`, `tracking_code`, `department_id`, `is_multi_department`, `assignee_id`, `creator_id`, `assigned_at`, `deadline`, `created_at`, `updated_at`, `rejection_reason`, `finish_note`, `status`, `is_recurring`, `recurring_start_date`, `recurring_days`, `last_reset_date`, `completed_at`) VALUES
(13, 'test', NULL, 'medium', '[]', NULL, NULL, NULL, 1, NULL, 1, NULL, '2025-08-31 08:15:00', '2025-08-28 08:14:48', '2025-08-28 10:28:18', NULL, NULL, 'in_progress', 0, NULL, NULL, NULL, '2025-08-28 10:28:05'),
(14, 'teesst thông báo', NULL, 'high', '[]', NULL, NULL, NULL, 1, NULL, 1, NULL, '2025-08-31 10:08:00', '2025-08-28 10:08:08', '2025-08-28 10:08:08', NULL, NULL, 'in_progress', 0, NULL, NULL, NULL, NULL),
(15, 'testfollower', NULL, 'high', '[]', NULL, NULL, NULL, 1, NULL, 1, NULL, '2025-08-31 10:18:00', '2025-08-28 10:18:28', '2025-08-28 10:18:28', NULL, NULL, 'in_progress', 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `task_activities`
--

CREATE TABLE `task_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `task_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_activities`
--

INSERT INTO `task_activities` (`id`, `task_id`, `user_id`, `action`, `meta`, `created_at`, `updated_at`) VALUES
(60, 13, 1, 'updated_task', 'Cập nhật thông tin công việc', '2025-08-28 08:19:57', '2025-08-28 08:19:57'),
(61, 13, 1, 'updated_status', 'Đã hoàn thành và gửi duyệt', '2025-08-28 09:10:32', '2025-08-28 09:10:32'),
(62, 13, 1, 'updated_task', 'Cập nhật thông tin công việc', '2025-08-28 09:11:59', '2025-08-28 09:11:59'),
(63, 13, 1, 'updated_status', 'Đã hoàn thành và gửi duyệt', '2025-08-28 10:28:05', '2025-08-28 10:28:05'),
(64, 13, 1, 'updated_task', 'Cập nhật thông tin công việc', '2025-08-28 10:28:18', '2025-08-28 10:28:18'),
(65, 15, 2, 'comment', '{\"comment_id\":6,\"content\":\"\\u00e1dasd\"}', '2025-08-28 10:32:06', '2025-08-28 10:32:06');

-- --------------------------------------------------------

--
-- Table structure for table `task_approvals`
--

CREATE TABLE `task_approvals` (
  `id` bigint UNSIGNED NOT NULL,
  `task_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `manager_id` bigint UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `comment` text COLLATE utf8mb4_unicode_ci,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_assignees`
--

CREATE TABLE `task_assignees` (
  `id` bigint UNSIGNED NOT NULL,
  `task_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_assignees`
--

INSERT INTO `task_assignees` (`id`, `task_id`, `user_id`, `created_at`, `updated_at`) VALUES
(94, 14, 15, '2025-08-28 10:08:08', '2025-08-28 10:08:08'),
(95, 15, 3, '2025-08-28 10:18:28', '2025-08-28 10:18:28'),
(96, 15, 4, '2025-08-28 10:18:28', '2025-08-28 10:18:28'),
(97, 15, 5, '2025-08-28 10:18:28', '2025-08-28 10:18:28'),
(98, 13, 2, '2025-08-28 10:28:18', '2025-08-28 10:28:18'),
(99, 13, 3, '2025-08-28 10:28:18', '2025-08-28 10:28:18'),
(100, 13, 5, '2025-08-28 10:28:18', '2025-08-28 10:28:18'),
(101, 13, 18, '2025-08-28 10:28:18', '2025-08-28 10:28:18');

-- --------------------------------------------------------

--
-- Table structure for table `task_files`
--

CREATE TABLE `task_files` (
  `id` bigint UNSIGNED NOT NULL,
  `task_id` bigint UNSIGNED NOT NULL,
  `uploaded_by` bigint UNSIGNED NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint NOT NULL,
  `file_extension` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` json DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_followers`
--

CREATE TABLE `task_followers` (
  `id` bigint UNSIGNED NOT NULL,
  `task_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_followers`
--

INSERT INTO `task_followers` (`id`, `task_id`, `user_id`, `created_at`, `updated_at`) VALUES
(15, 14, 2, NULL, NULL),
(16, 15, 2, NULL, NULL),
(18, 13, 15, NULL, NULL),
(19, 13, 19, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','manager','employee') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'employee',
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `employee_type` enum('new','official') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'official',
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_insurance_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `health_insurance_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `personal_identification_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `role`, `department_id`, `employee_type`, `position`, `social_insurance_number`, `health_insurance_number`, `personal_identification_number`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Luu Pham Anh Kiet', 'luukiet488@gmail.com', '+84949010942', NULL, '$2y$12$08vwy9Wy9O9F6UAeLDEqVOxuSoKbnb3/9KqJ/IjiBI11lcEdsVA12', 'admin', NULL, 'official', NULL, NULL, NULL, NULL, NULL, '2025-08-20 01:02:40', '2025-08-20 01:02:40'),
(2, 'Lê Thành Nhân', 'nhanlt554@gmail.com', '09999999999', NULL, '$2y$12$Ha7meKqOxR2fESMLHN7bVuGHQyRo3fVnRqBLqNcxTlni1bRBTYZzS', 'employee', 1, 'official', NULL, NULL, NULL, NULL, NULL, '2025-08-20 01:05:08', '2025-08-28 10:20:08'),
(3, 'Nhân Viên IT 1', 'nhanvienit1@gmail.com', NULL, NULL, '$2y$12$AlrI4MvPPQ/5tOufJqK7pek2dHASt7X4J8D4j5x6hafm7ELapljdy', 'employee', 1, 'official', NULL, NULL, NULL, NULL, NULL, '2025-08-20 01:41:50', '2025-08-20 01:41:50'),
(4, 'Quản lý IT 1', 'quanlyit1@gmail.com', NULL, NULL, '$2y$12$U3fRf016V0WIVQzFHi7BXe6lXckUwJDci50uE7zyneI5S/OLmwGPq', 'manager', 1, 'official', NULL, NULL, NULL, NULL, NULL, '2025-08-20 01:42:26', '2025-08-25 01:39:56'),
(5, 'Nhân Viên Design 1', 'nhanviendesign1@gmail.com', NULL, NULL, '$2y$12$PIxH6lj.HADRLK26zTWMner3wO6LqZ3Nt9kqRN3RKvILG672Luh4W', 'employee', 4, 'official', NULL, NULL, NULL, NULL, NULL, '2025-08-20 01:43:54', '2025-08-20 01:43:54'),
(6, 'Thử việc IT', 'thuviecIT@gmail.com', NULL, NULL, '$2y$12$CMOKoANG.r1.8fmtTInVz.F/6MjeuKKVZigYFdJKVc6.7hmomKYr6', 'employee', 1, 'official', NULL, NULL, NULL, NULL, NULL, '2025-08-20 02:00:17', '2025-08-20 02:00:48'),
(8, 'Quản Lý Design', 'quanlydesign1@gmail.com', '11111111111', NULL, '$2y$12$EQAE2L92VRXGdDWsvrzZuO5JwQZhgODh7G6CPUY3aFwBCd5qfvgB2', 'manager', 4, 'official', NULL, NULL, NULL, NULL, NULL, '2025-08-26 09:26:26', '2025-08-26 09:26:26'),
(10, 'Nhân Viên Factory 1', 'nhanvienfactory1@gmail.com', '0123654789', NULL, '$2y$12$oy7PLbJ.uoT9rKbhwvFPbu7ZMksklXx6mXzWky4/eORvljvSnWU1y', 'employee', 3, 'official', NULL, NULL, NULL, NULL, NULL, '2025-08-26 10:11:31', '2025-08-26 10:52:14'),
(11, 'Nhân Viên Factory 2', 'nhanvienfactory2@gmail.com', '1236547890', NULL, '$2y$12$2WtiIFj0scg2pTrvYaiKjuG1mdU4MDHQsPaBKQHcFLAvbVQ9CcOQC', 'employee', 3, 'official', NULL, NULL, NULL, NULL, NULL, '2025-08-26 10:23:12', '2025-08-26 10:58:06'),
(12, 'Factory3', 'Factory3@gmail.com', '1242352352341', NULL, '$2y$12$D789fWYFiZXc0WOQkGTZxunOG43QWODvpcO5ecvJRhdUxTNKlvPa2', 'employee', 3, 'official', NULL, NULL, NULL, NULL, NULL, '2025-08-26 10:54:31', '2025-08-26 10:54:51'),
(15, 'Thử việc 1', 'thuviec1@gmail.com', '1233211231', NULL, '$2y$12$AKEV0VGQ8ORqboJR2LAY5.YAqqNhfHoA5UR7A3D5eSplXomqKC3a2', 'employee', 1, 'new', 'Nhân viên thử việc', NULL, NULL, NULL, NULL, '2025-08-26 11:06:40', '2025-08-26 11:06:40'),
(16, 'Thử việc 2', 'thuviec2@gmail.com', '1233211232', NULL, '$2y$12$MKzTwMXGlv2KtcU6FoLee.zEs66f2Q2FfqJB9.gY8iRYkB769VH5a', 'employee', 2, 'new', 'Nhân viên thử việc', NULL, NULL, NULL, NULL, '2025-08-26 11:06:40', '2025-08-26 11:06:40'),
(17, 'Thử việc 3', 'thuviec3@gmail.com', '1233211233', NULL, '$2y$12$3S8XuoZ67TxHp9mpcrGg8.fcFtmIl74FDGmNC023p5vlKouEPvRn.', 'employee', 3, 'new', 'Nhân viên thử việc', NULL, NULL, NULL, NULL, '2025-08-26 11:06:40', '2025-08-26 11:06:40'),
(18, 'Thử việc 4', 'thuviec4@gmail.com', '1233211234', NULL, '$2y$12$38alHY7OO8J7aqeYBnJNfecRJgjtuhfs4ae51e9QN2PLnXfVuAKm.', 'employee', 4, 'new', 'Nhân viên thử việc', NULL, NULL, NULL, NULL, '2025-08-26 11:06:41', '2025-08-26 11:06:41'),
(19, 'Thử việc 5', 'thuviec5@gmail.com', '1233211235', NULL, '$2y$12$6/czsZsfqX3l0fuJlsGMz.eIlBd41AfSdC1gdkZzkWNjUoD.a9Tme', 'employee', 1, 'new', 'Nhân viên thử việc', NULL, NULL, NULL, NULL, '2025-08-26 11:06:41', '2025-08-26 11:06:41'),
(20, 'Thử việc 6', 'thuviec6@gmail.com', '1233211236', NULL, '$2y$12$u.5PPA.QzM1l/hm7RPoThOZyFRDsBgyhZRKB.voPZaG3Nti5SWzf.', 'employee', 2, 'new', 'Nhân viên thử việc', NULL, NULL, NULL, NULL, '2025-08-26 11:06:41', '2025-08-26 11:06:41'),
(21, 'Thử việc 7', 'thuviec7@gmail.com', '1233211237', NULL, '$2y$12$Kg4rsXD0WMYr429bhFjyY.lnY6aGosPoey2t.ksA8FIAXhu/A5/2W', 'employee', 3, 'new', 'Nhân viên thử việc', NULL, NULL, NULL, NULL, '2025-08-26 11:06:41', '2025-08-26 11:06:41'),
(25, 'Thử việc 11', 'thuviec11@gmail.com', '12332112311', NULL, '$2y$12$WXC01W8hyIHrbRNHJ8qDu.eHfcEaigWGP5do2rhFcRWt0xNVkCK9m', 'manager', 3, 'official', 'Nhân viên thử việc', NULL, NULL, NULL, NULL, '2025-08-26 11:06:42', '2025-08-26 11:21:44'),
(26, 'Thử việc 12', 'thuviec12@gmail.com', '12332112312', NULL, '$2y$12$yikiTPeOdURBDrKz/E3CbOWgJqhKjSpwBTzVpHfOCLyBgkUgMGHnu', 'manager', 4, 'official', 'Nhân viên thử việc', NULL, NULL, NULL, NULL, '2025-08-26 11:06:42', '2025-08-26 11:23:08'),
(27, 'testmarketing1', 'testmarketing1@gmail.com', '456456456', NULL, '$2y$12$jysgbEBN7EkeRgqvROmjo.2/X6WOBCUO6ZSRdZq77AwhWtdVd5MOO', 'employee', 2, 'official', NULL, NULL, NULL, NULL, NULL, '2025-08-26 11:25:05', '2025-08-26 11:26:24');

-- --------------------------------------------------------

--
-- Table structure for table `work_reports`
--

CREATE TABLE `work_reports` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `year` int NOT NULL,
  `month` int NOT NULL,
  `week` int NOT NULL,
  `week_of_month` int DEFAULT NULL,
  `report_date` date NOT NULL,
  `daily_work` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `difficulties` text COLLATE utf8mb4_unicode_ci,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `custom_fields` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_reports`
--

INSERT INTO `work_reports` (`id`, `user_id`, `department_id`, `year`, `month`, `week`, `week_of_month`, `report_date`, `daily_work`, `difficulties`, `comments`, `custom_fields`, `created_at`, `updated_at`) VALUES
(114, 1, NULL, 2025, 8, 35, 5, '2025-08-26', 'Test Fix bugs và tối ưu hiệu suất', NULL, NULL, NULL, '2025-08-25 17:00:00', '2025-08-25 17:00:00'),
(115, 1, NULL, 2025, 8, 35, 5, '2025-08-30', 'Hoàn thành Tham gia cuộc họp sprint planning', NULL, NULL, NULL, '2025-08-29 17:00:00', '2025-08-29 17:00:00'),
(116, 2, 1, 2025, 8, 35, 5, '2025-08-29', 'Hoàn thành Viết tài liệu kỹ thuật', NULL, NULL, NULL, '2025-08-28 17:00:00', '2025-08-28 17:00:00'),
(117, 2, 1, 2025, 8, 35, 5, '2025-08-26', 'Review Viết tài liệu kỹ thuật', NULL, NULL, NULL, '2025-08-25 17:00:00', '2025-08-25 17:00:00'),
(118, 3, 1, 2025, 8, 35, 5, '2025-08-29', 'Review Phát triển tính năng mới cho hệ thống quản lý', NULL, NULL, NULL, '2025-08-28 17:00:00', '2025-08-28 17:00:00'),
(119, 3, 1, 2025, 8, 35, 5, '2025-08-27', 'Review Code review cho các pull request', NULL, NULL, NULL, '2025-08-26 17:00:00', '2025-08-26 17:00:00'),
(121, 4, 1, 2025, 8, 35, 5, '2025-08-28', 'Tiếp tục Viết tài liệu kỹ thuật', NULL, NULL, NULL, '2025-08-27 17:00:00', '2025-08-27 17:00:00'),
(122, 4, 1, 2025, 8, 35, 5, '2025-08-30', 'Hoàn thành Viết tài liệu kỹ thuật', NULL, NULL, NULL, '2025-08-29 17:00:00', '2025-08-29 17:00:00'),
(123, 5, 4, 2025, 8, 35, 5, '2025-08-27', 'Hoàn thành Phát triển tính năng mới cho hệ thống quản lý', NULL, NULL, NULL, '2025-08-26 17:00:00', '2025-08-26 17:00:00'),
(125, 5, 4, 2025, 8, 35, 5, '2025-08-28', 'Test Fix bugs và tối ưu hiệu suất', NULL, NULL, NULL, '2025-08-27 17:00:00', '2025-08-27 17:00:00'),
(126, 6, 1, 2025, 8, 35, 5, '2025-08-29', 'Tiếp tục Code review cho các pull request', NULL, NULL, NULL, '2025-08-28 17:00:00', '2025-08-28 17:00:00'),
(127, 6, 1, 2025, 8, 35, 5, '2025-08-28', 'Bắt đầu Fix bugs và tối ưu hiệu suất', NULL, NULL, NULL, '2025-08-27 17:00:00', '2025-08-27 17:00:00'),
(131, 1, NULL, 2025, 9, 36, 1, '2025-09-02', 'Review Kiểm tra và test hệ thống', NULL, NULL, NULL, '2025-09-01 17:00:00', '2025-09-01 17:00:00'),
(132, 1, NULL, 2025, 9, 36, 1, '2025-09-03', 'Review Triển khai tính năng mới lên production', NULL, NULL, NULL, '2025-09-02 17:00:00', '2025-09-02 17:00:00'),
(133, 1, NULL, 2025, 9, 36, 1, '2025-09-05', 'Hoàn thành Họp với khách hàng về yêu cầu mới', NULL, NULL, NULL, '2025-09-04 17:00:00', '2025-09-04 17:00:00'),
(134, 2, 1, 2025, 9, 36, 1, '2025-09-03', 'Review Họp với khách hàng về yêu cầu mới', NULL, NULL, NULL, '2025-09-02 17:00:00', '2025-09-02 17:00:00'),
(135, 2, 1, 2025, 9, 36, 1, '2025-09-01', 'Test Đào tạo nhân viên mới', NULL, NULL, NULL, '2025-08-31 17:00:00', '2025-08-31 17:00:00'),
(136, 2, 1, 2025, 9, 36, 1, '2025-09-04', 'Tiếp tục Đào tạo nhân viên mới', NULL, NULL, NULL, '2025-09-03 17:00:00', '2025-09-03 17:00:00'),
(137, 3, 1, 2025, 9, 36, 1, '2025-09-05', 'Hoàn thành Họp với khách hàng về yêu cầu mới', NULL, NULL, NULL, '2025-09-04 17:00:00', '2025-09-04 17:00:00'),
(138, 3, 1, 2025, 9, 36, 1, '2025-09-02', 'Test Đào tạo nhân viên mới', NULL, NULL, NULL, '2025-09-01 17:00:00', '2025-09-01 17:00:00'),
(139, 3, 1, 2025, 9, 36, 1, '2025-09-01', 'Tiếp tục Họp với khách hàng về yêu cầu mới', NULL, NULL, NULL, '2025-08-31 17:00:00', '2025-08-31 17:00:00'),
(140, 4, 1, 2025, 9, 36, 1, '2025-09-01', 'Test Kiểm tra và test hệ thống', NULL, NULL, NULL, '2025-08-31 17:00:00', '2025-08-31 17:00:00'),
(141, 4, 1, 2025, 9, 36, 1, '2025-09-03', 'Bắt đầu Đào tạo nhân viên mới', NULL, NULL, NULL, '2025-09-02 17:00:00', '2025-09-02 17:00:00'),
(142, 5, 4, 2025, 9, 36, 1, '2025-09-02', 'Review Cập nhật database schema', NULL, NULL, NULL, '2025-09-01 17:00:00', '2025-09-01 17:00:00'),
(143, 5, 4, 2025, 9, 36, 1, '2025-09-05', 'Bắt đầu Đào tạo nhân viên mới', NULL, NULL, NULL, '2025-09-04 17:00:00', '2025-09-04 17:00:00'),
(144, 5, 4, 2025, 9, 36, 1, '2025-09-04', 'Test Kiểm tra và test hệ thống', NULL, NULL, NULL, '2025-09-03 17:00:00', '2025-09-03 17:00:00'),
(145, 6, 1, 2025, 9, 36, 1, '2025-09-03', 'Tiếp tục Họp với khách hàng về yêu cầu mới', NULL, NULL, NULL, '2025-09-02 17:00:00', '2025-09-02 17:00:00'),
(146, 6, 1, 2025, 9, 36, 1, '2025-09-05', 'Test Kiểm tra và test hệ thống', NULL, NULL, NULL, '2025-09-04 17:00:00', '2025-09-04 17:00:00'),
(147, 6, 1, 2025, 9, 36, 1, '2025-09-02', 'Tiếp tục Họp với khách hàng về yêu cầu mới', NULL, NULL, NULL, '2025-09-01 17:00:00', '2025-09-01 17:00:00'),
(151, 1, NULL, 2025, 8, 34, 4, '2025-08-22', 'Tiếp tục Tham gia brainstorming session', NULL, NULL, NULL, '2025-08-21 17:00:00', '2025-08-21 17:00:00'),
(152, 1, NULL, 2025, 8, 34, 4, '2025-08-21', 'Test Cập nhật design system', NULL, NULL, NULL, '2025-08-20 17:00:00', '2025-08-20 17:00:00'),
(153, 2, 1, 2025, 8, 34, 4, '2025-08-18', 'Test Tham gia brainstorming session', NULL, NULL, NULL, '2025-08-17 17:00:00', '2025-08-17 17:00:00'),
(154, 2, 1, 2025, 8, 34, 4, '2025-08-19', 'Hoàn thành Review feedback từ khách hàng', NULL, NULL, NULL, '2025-08-18 17:00:00', '2025-08-18 17:00:00'),
(155, 3, 1, 2025, 8, 34, 4, '2025-08-22', 'Review Thiết kế giao diện người dùng', NULL, NULL, NULL, '2025-08-21 17:00:00', '2025-08-21 17:00:00'),
(156, 3, 1, 2025, 8, 34, 4, '2025-08-20', 'Hoàn thành Tham gia brainstorming session', NULL, NULL, NULL, '2025-08-19 17:00:00', '2025-08-19 17:00:00'),
(157, 4, 1, 2025, 8, 34, 4, '2025-08-19', 'Hoàn thành Tham gia brainstorming session', NULL, NULL, NULL, '2025-08-18 17:00:00', '2025-08-18 17:00:00'),
(158, 4, 1, 2025, 8, 34, 4, '2025-08-18', 'Hoàn thành Review feedback từ khách hàng', NULL, NULL, NULL, '2025-08-17 17:00:00', '2025-08-17 17:00:00'),
(159, 5, 4, 2025, 8, 34, 4, '2025-08-22', 'Bắt đầu Review feedback từ khách hàng', NULL, NULL, NULL, '2025-08-21 17:00:00', '2025-08-21 17:00:00'),
(160, 5, 4, 2025, 8, 34, 4, '2025-08-21', 'Review Thiết kế giao diện người dùng', NULL, NULL, NULL, '2025-08-20 17:00:00', '2025-08-20 17:00:00'),
(161, 6, 1, 2025, 8, 34, 4, '2025-08-21', 'Tiếp tục Tạo wireframes và mockups', NULL, NULL, NULL, '2025-08-20 17:00:00', '2025-08-20 17:00:00'),
(162, 6, 1, 2025, 8, 34, 4, '2025-08-20', 'Bắt đầu Tham gia brainstorming session', NULL, NULL, NULL, '2025-08-19 17:00:00', '2025-08-19 17:00:00'),
(163, 6, 1, 2025, 8, 34, 4, '2025-08-19', 'Test Review feedback từ khách hàng', NULL, NULL, NULL, '2025-08-18 17:00:00', '2025-08-18 17:00:00'),
(166, 1, NULL, 2025, 8, 33, 3, '2025-08-14', 'Tiếp tục Viết API documentation', NULL, NULL, NULL, '2025-08-13 17:00:00', '2025-08-13 17:00:00'),
(167, 1, NULL, 2025, 8, 33, 3, '2025-08-13', 'Tiếp tục Tạo database design', NULL, NULL, NULL, '2025-08-12 17:00:00', '2025-08-12 17:00:00'),
(168, 1, NULL, 2025, 8, 33, 3, '2025-08-15', 'Test Lập kế hoạch phát triển', NULL, NULL, NULL, '2025-08-14 17:00:00', '2025-08-14 17:00:00'),
(169, 2, 1, 2025, 8, 33, 3, '2025-08-12', 'Hoàn thành Lập kế hoạch phát triển', NULL, NULL, NULL, '2025-08-11 17:00:00', '2025-08-11 17:00:00'),
(170, 2, 1, 2025, 8, 33, 3, '2025-08-13', 'Bắt đầu Thiết lập môi trường development', NULL, NULL, NULL, '2025-08-12 17:00:00', '2025-08-12 17:00:00'),
(171, 3, 1, 2025, 8, 33, 3, '2025-08-11', 'Test Phân tích yêu cầu dự án', NULL, NULL, NULL, '2025-08-10 17:00:00', '2025-08-10 17:00:00'),
(172, 3, 1, 2025, 8, 33, 3, '2025-08-12', 'Bắt đầu Phân tích yêu cầu dự án', NULL, NULL, NULL, '2025-08-11 17:00:00', '2025-08-11 17:00:00'),
(173, 3, 1, 2025, 8, 33, 3, '2025-08-15', 'Review Viết API documentation', NULL, NULL, NULL, '2025-08-14 17:00:00', '2025-08-14 17:00:00'),
(174, 4, 1, 2025, 8, 33, 3, '2025-08-14', 'Review Lập kế hoạch phát triển', NULL, NULL, NULL, '2025-08-13 17:00:00', '2025-08-13 17:00:00'),
(175, 4, 1, 2025, 8, 33, 3, '2025-08-11', 'Review Phân tích yêu cầu dự án', NULL, NULL, NULL, '2025-08-10 17:00:00', '2025-08-10 17:00:00'),
(176, 5, 4, 2025, 8, 33, 3, '2025-08-11', 'Bắt đầu Lập kế hoạch phát triển', NULL, NULL, NULL, '2025-08-10 17:00:00', '2025-08-10 17:00:00'),
(177, 5, 4, 2025, 8, 33, 3, '2025-08-14', 'Test Thiết lập môi trường development', NULL, NULL, NULL, '2025-08-13 17:00:00', '2025-08-13 17:00:00'),
(178, 6, 1, 2025, 8, 33, 3, '2025-08-14', 'Review Thiết lập môi trường development', NULL, NULL, NULL, '2025-08-13 17:00:00', '2025-08-13 17:00:00'),
(179, 6, 1, 2025, 8, 33, 3, '2025-08-12', 'Bắt đầu Lập kế hoạch phát triển', NULL, NULL, NULL, '2025-08-11 17:00:00', '2025-08-11 17:00:00'),
(183, 1, NULL, 2025, 8, 32, 2, '2025-08-07', 'Tiếp tục Chuẩn bị presentation', NULL, NULL, NULL, '2025-08-06 17:00:00', '2025-08-06 17:00:00'),
(184, 1, NULL, 2025, 8, 32, 2, '2025-08-05', 'Bắt đầu Tham gia workshop về AI/ML', NULL, NULL, NULL, '2025-08-04 17:00:00', '2025-08-04 17:00:00'),
(185, 1, NULL, 2025, 8, 32, 2, '2025-08-06', 'Test Chuẩn bị presentation', NULL, NULL, NULL, '2025-08-05 17:00:00', '2025-08-05 17:00:00'),
(186, 2, 1, 2025, 8, 32, 2, '2025-08-04', 'Tiếp tục Nghiên cứu best practices', NULL, NULL, NULL, '2025-08-03 17:00:00', '2025-08-03 17:00:00'),
(187, 2, 1, 2025, 8, 32, 2, '2025-08-07', 'Test Tham gia workshop về AI/ML', NULL, NULL, NULL, '2025-08-06 17:00:00', '2025-08-06 17:00:00'),
(188, 3, 1, 2025, 8, 32, 2, '2025-08-07', 'Hoàn thành Khảo sát công nghệ mới', NULL, NULL, NULL, '2025-08-06 17:00:00', '2025-08-06 17:00:00'),
(189, 3, 1, 2025, 8, 32, 2, '2025-08-04', 'Bắt đầu Họp team retrospective', NULL, NULL, NULL, '2025-08-03 17:00:00', '2025-08-03 17:00:00'),
(190, 3, 1, 2025, 8, 32, 2, '2025-08-08', 'Tiếp tục Nghiên cứu best practices', NULL, NULL, NULL, '2025-08-07 17:00:00', '2025-08-07 17:00:00'),
(191, 4, 1, 2025, 8, 32, 2, '2025-08-05', 'Test Nghiên cứu best practices', NULL, NULL, NULL, '2025-08-04 17:00:00', '2025-08-04 17:00:00'),
(192, 4, 1, 2025, 8, 32, 2, '2025-08-04', 'Bắt đầu Nghiên cứu best practices', NULL, NULL, NULL, '2025-08-03 17:00:00', '2025-08-03 17:00:00'),
(193, 5, 4, 2025, 8, 32, 2, '2025-08-07', 'Hoàn thành Chuẩn bị presentation', NULL, NULL, NULL, '2025-08-06 17:00:00', '2025-08-06 17:00:00'),
(194, 5, 4, 2025, 8, 32, 2, '2025-08-05', 'Bắt đầu Tham gia workshop về AI/ML', NULL, NULL, NULL, '2025-08-04 17:00:00', '2025-08-04 17:00:00'),
(195, 5, 4, 2025, 8, 32, 2, '2025-08-04', 'Tiếp tục Chuẩn bị presentation', NULL, NULL, NULL, '2025-08-03 17:00:00', '2025-08-03 17:00:00'),
(196, 6, 1, 2025, 8, 32, 2, '2025-08-07', 'Tiếp tục Chuẩn bị presentation', NULL, NULL, NULL, '2025-08-06 17:00:00', '2025-08-06 17:00:00'),
(197, 6, 1, 2025, 8, 32, 2, '2025-08-05', 'Tiếp tục Nghiên cứu best practices', NULL, NULL, NULL, '2025-08-04 17:00:00', '2025-08-04 17:00:00'),
(198, 6, 1, 2025, 8, 32, 2, '2025-08-06', 'Bắt đầu Chuẩn bị presentation', NULL, NULL, NULL, '2025-08-05 17:00:00', '2025-08-05 17:00:00'),
(201, 1, NULL, 2025, 7, 31, 5, '2025-07-31', 'Hoàn thành Cập nhật security patches', NULL, NULL, NULL, '2025-07-30 17:00:00', '2025-07-30 17:00:00'),
(202, 1, NULL, 2025, 7, 31, 5, '2025-07-28', 'Tiếp tục Kiểm tra performance', NULL, NULL, NULL, '2025-07-27 17:00:00', '2025-07-27 17:00:00'),
(203, 2, 1, 2025, 7, 31, 5, '2025-08-01', 'Hoàn thành Kiểm tra performance', NULL, NULL, NULL, '2025-07-31 17:00:00', '2025-07-31 17:00:00'),
(204, 2, 1, 2025, 7, 31, 5, '2025-07-30', 'Test Kiểm tra performance', NULL, NULL, NULL, '2025-07-29 17:00:00', '2025-07-29 17:00:00'),
(205, 3, 1, 2025, 7, 31, 5, '2025-07-30', 'Test Backup dữ liệu', NULL, NULL, NULL, '2025-07-29 17:00:00', '2025-07-29 17:00:00'),
(206, 3, 1, 2025, 7, 31, 5, '2025-07-31', 'Test Backup dữ liệu', NULL, NULL, NULL, '2025-07-30 17:00:00', '2025-07-30 17:00:00'),
(207, 4, 1, 2025, 7, 31, 5, '2025-07-29', 'Tiếp tục Maintenance hệ thống', NULL, NULL, NULL, '2025-07-28 17:00:00', '2025-07-28 17:00:00'),
(208, 4, 1, 2025, 7, 31, 5, '2025-07-31', 'Test Kiểm tra performance', NULL, NULL, NULL, '2025-07-30 17:00:00', '2025-07-30 17:00:00'),
(209, 5, 4, 2025, 7, 31, 5, '2025-07-31', 'Test Chuẩn bị báo cáo tháng', NULL, NULL, NULL, '2025-07-30 17:00:00', '2025-07-30 17:00:00'),
(210, 5, 4, 2025, 7, 31, 5, '2025-07-30', 'Review Backup dữ liệu', NULL, NULL, NULL, '2025-07-29 17:00:00', '2025-07-29 17:00:00'),
(211, 6, 1, 2025, 7, 31, 5, '2025-07-30', 'Tiếp tục Backup dữ liệu', NULL, NULL, NULL, '2025-07-29 17:00:00', '2025-07-29 17:00:00'),
(212, 6, 1, 2025, 7, 31, 5, '2025-08-01', 'Hoàn thành Maintenance hệ thống', NULL, NULL, NULL, '2025-07-31 17:00:00', '2025-07-31 17:00:00'),
(213, 6, 1, 2025, 7, 31, 5, '2025-07-28', 'Test Chuẩn bị báo cáo tháng', NULL, NULL, NULL, '2025-07-27 17:00:00', '2025-07-27 17:00:00'),
(217, 1, NULL, 2024, 12, 52, 4, '2024-12-23', 'Bắt đầu Tổng kết dự án', NULL, NULL, NULL, '2024-12-22 17:00:00', '2024-12-22 17:00:00'),
(218, 1, NULL, 2024, 12, 52, 4, '2024-12-27', 'Test Dọn dẹp workspace', NULL, NULL, NULL, '2024-12-26 17:00:00', '2024-12-26 17:00:00'),
(219, 1, NULL, 2024, 12, 52, 4, '2024-12-25', 'Review Chuẩn bị báo cáo cuối năm', NULL, NULL, NULL, '2024-12-24 17:00:00', '2024-12-24 17:00:00'),
(220, 2, 1, 2024, 12, 52, 4, '2024-12-26', 'Test Chuẩn bị báo cáo cuối năm', NULL, NULL, NULL, '2024-12-25 17:00:00', '2024-12-25 17:00:00'),
(221, 2, 1, 2024, 12, 52, 4, '2024-12-24', 'Tiếp tục Dọn dẹp workspace', NULL, NULL, NULL, '2024-12-23 17:00:00', '2024-12-23 17:00:00'),
(222, 3, 1, 2024, 12, 52, 4, '2024-12-23', 'Review Tổng kết dự án', NULL, NULL, NULL, '2024-12-22 17:00:00', '2024-12-22 17:00:00'),
(223, 3, 1, 2024, 12, 52, 4, '2024-12-25', 'Tiếp tục Backup toàn bộ dữ liệu', NULL, NULL, NULL, '2024-12-24 17:00:00', '2024-12-24 17:00:00'),
(224, 4, 1, 2024, 12, 52, 4, '2024-12-23', 'Bắt đầu Backup toàn bộ dữ liệu', NULL, NULL, NULL, '2024-12-22 17:00:00', '2024-12-22 17:00:00'),
(225, 4, 1, 2024, 12, 52, 4, '2024-12-24', 'Test Lập kế hoạch năm mới', NULL, NULL, NULL, '2024-12-23 17:00:00', '2024-12-23 17:00:00'),
(226, 4, 1, 2024, 12, 52, 4, '2024-12-26', 'Hoàn thành Tổng kết dự án', NULL, NULL, NULL, '2024-12-25 17:00:00', '2024-12-25 17:00:00'),
(227, 5, 4, 2024, 12, 52, 4, '2024-12-27', 'Test Lập kế hoạch năm mới', NULL, NULL, NULL, '2024-12-26 17:00:00', '2024-12-26 17:00:00'),
(228, 5, 4, 2024, 12, 52, 4, '2024-12-26', 'Hoàn thành Backup toàn bộ dữ liệu', NULL, NULL, NULL, '2024-12-25 17:00:00', '2024-12-25 17:00:00'),
(229, 5, 4, 2024, 12, 52, 4, '2024-12-25', 'Test Backup toàn bộ dữ liệu', NULL, NULL, NULL, '2024-12-24 17:00:00', '2024-12-24 17:00:00'),
(230, 6, 1, 2024, 12, 52, 4, '2024-12-24', 'Bắt đầu Backup toàn bộ dữ liệu', NULL, NULL, NULL, '2024-12-23 17:00:00', '2024-12-23 17:00:00'),
(231, 6, 1, 2024, 12, 52, 4, '2024-12-25', 'Hoàn thành Lập kế hoạch năm mới', NULL, NULL, NULL, '2024-12-24 17:00:00', '2024-12-24 17:00:00'),
(232, 6, 1, 2024, 12, 52, 4, '2024-12-26', 'Hoàn thành Chuẩn bị báo cáo cuối năm', NULL, NULL, NULL, '2024-12-25 17:00:00', '2024-12-25 17:00:00'),
(235, 1, NULL, 2024, 12, 51, 3, '2024-12-19', 'Hoàn thành Họp team cuối năm', NULL, NULL, NULL, '2024-12-18 17:00:00', '2024-12-18 17:00:00'),
(236, 1, NULL, 2024, 12, 51, 3, '2024-12-20', 'Bắt đầu Chuẩn bị demo cho khách hàng', NULL, NULL, NULL, '2024-12-19 17:00:00', '2024-12-19 17:00:00'),
(237, 1, NULL, 2024, 12, 51, 3, '2024-12-16', 'Bắt đầu Hoàn thiện tính năng cuối năm', NULL, NULL, NULL, '2024-12-15 17:00:00', '2024-12-15 17:00:00'),
(238, 2, 1, 2024, 12, 51, 3, '2024-12-20', 'Review Chuẩn bị demo cho khách hàng', NULL, NULL, NULL, '2024-12-19 17:00:00', '2024-12-19 17:00:00'),
(239, 2, 1, 2024, 12, 51, 3, '2024-12-16', 'Review Cập nhật documentation', NULL, NULL, NULL, '2024-12-15 17:00:00', '2024-12-15 17:00:00'),
(240, 2, 1, 2024, 12, 51, 3, '2024-12-18', 'Hoàn thành Cập nhật documentation', NULL, NULL, NULL, '2024-12-17 17:00:00', '2024-12-17 17:00:00'),
(241, 3, 1, 2024, 12, 51, 3, '2024-12-18', 'Bắt đầu Họp team cuối năm', NULL, NULL, NULL, '2024-12-17 17:00:00', '2024-12-17 17:00:00'),
(242, 3, 1, 2024, 12, 51, 3, '2024-12-16', 'Tiếp tục Test toàn bộ hệ thống', NULL, NULL, NULL, '2024-12-15 17:00:00', '2024-12-15 17:00:00'),
(243, 3, 1, 2024, 12, 51, 3, '2024-12-19', 'Hoàn thành Hoàn thiện tính năng cuối năm', NULL, NULL, NULL, '2024-12-18 17:00:00', '2024-12-18 17:00:00'),
(244, 4, 1, 2024, 12, 51, 3, '2024-12-20', 'Test Họp team cuối năm', NULL, NULL, NULL, '2024-12-19 17:00:00', '2024-12-19 17:00:00'),
(245, 4, 1, 2024, 12, 51, 3, '2024-12-17', 'Bắt đầu Chuẩn bị demo cho khách hàng', NULL, NULL, NULL, '2024-12-16 17:00:00', '2024-12-16 17:00:00'),
(246, 4, 1, 2024, 12, 51, 3, '2024-12-16', 'Test Chuẩn bị demo cho khách hàng', NULL, NULL, NULL, '2024-12-15 17:00:00', '2024-12-15 17:00:00'),
(247, 5, 4, 2024, 12, 51, 3, '2024-12-17', 'Review Cập nhật documentation', NULL, NULL, NULL, '2024-12-16 17:00:00', '2024-12-16 17:00:00'),
(248, 5, 4, 2024, 12, 51, 3, '2024-12-19', 'Test Test toàn bộ hệ thống', NULL, NULL, NULL, '2024-12-18 17:00:00', '2024-12-18 17:00:00'),
(249, 5, 4, 2024, 12, 51, 3, '2024-12-18', 'Bắt đầu Hoàn thiện tính năng cuối năm', NULL, NULL, NULL, '2024-12-17 17:00:00', '2024-12-17 17:00:00'),
(250, 6, 1, 2024, 12, 51, 3, '2024-12-16', 'Tiếp tục Cập nhật documentation', NULL, NULL, NULL, '2024-12-15 17:00:00', '2024-12-15 17:00:00'),
(251, 6, 1, 2024, 12, 51, 3, '2024-12-17', 'Review Hoàn thiện tính năng cuối năm', NULL, NULL, NULL, '2024-12-16 17:00:00', '2024-12-16 17:00:00'),
(252, 6, 1, 2024, 12, 51, 3, '2024-12-18', 'Hoàn thành Cập nhật documentation', NULL, NULL, NULL, '2024-12-17 17:00:00', '2024-12-17 17:00:00'),
(256, 1, NULL, 2024, 11, 48, 5, '2024-11-28', 'Tiếp tục Phát triển module mới', NULL, NULL, NULL, '2024-11-27 17:00:00', '2024-11-27 17:00:00'),
(257, 1, NULL, 2024, 11, 48, 5, '2024-11-29', 'Test Tối ưu database queries', NULL, NULL, NULL, '2024-11-28 17:00:00', '2024-11-28 17:00:00'),
(258, 1, NULL, 2024, 11, 48, 5, '2024-11-27', 'Review Phát triển module mới', NULL, NULL, NULL, '2024-11-26 17:00:00', '2024-11-26 17:00:00'),
(259, 2, 1, 2024, 11, 48, 5, '2024-11-26', 'Hoàn thành Code review cho team', NULL, NULL, NULL, '2024-11-25 17:00:00', '2024-11-25 17:00:00'),
(260, 2, 1, 2024, 11, 48, 5, '2024-11-28', 'Bắt đầu Tối ưu database queries', NULL, NULL, NULL, '2024-11-27 17:00:00', '2024-11-27 17:00:00'),
(261, 3, 1, 2024, 11, 48, 5, '2024-11-25', 'Test Chuẩn bị presentation', NULL, NULL, NULL, '2024-11-24 17:00:00', '2024-11-24 17:00:00'),
(262, 3, 1, 2024, 11, 48, 5, '2024-11-29', 'Test Code review cho team', NULL, NULL, NULL, '2024-11-28 17:00:00', '2024-11-28 17:00:00'),
(263, 3, 1, 2024, 11, 48, 5, '2024-11-27', 'Tiếp tục Code review cho team', NULL, NULL, NULL, '2024-11-26 17:00:00', '2024-11-26 17:00:00'),
(264, 4, 1, 2024, 11, 48, 5, '2024-11-26', 'Hoàn thành Tối ưu database queries', NULL, NULL, NULL, '2024-11-25 17:00:00', '2024-11-25 17:00:00'),
(265, 4, 1, 2024, 11, 48, 5, '2024-11-28', 'Review Code review cho team', NULL, NULL, NULL, '2024-11-27 17:00:00', '2024-11-27 17:00:00'),
(266, 5, 4, 2024, 11, 48, 5, '2024-11-29', 'Test Tối ưu database queries', NULL, NULL, NULL, '2024-11-28 17:00:00', '2024-11-28 17:00:00'),
(267, 5, 4, 2024, 11, 48, 5, '2024-11-25', 'Tiếp tục Tham gia training session', NULL, NULL, NULL, '2024-11-24 17:00:00', '2024-11-24 17:00:00'),
(268, 5, 4, 2024, 11, 48, 5, '2024-11-26', 'Tiếp tục Chuẩn bị presentation', NULL, NULL, NULL, '2024-11-25 17:00:00', '2024-11-25 17:00:00'),
(269, 6, 1, 2024, 11, 48, 5, '2024-11-29', 'Test Phát triển module mới', NULL, NULL, NULL, '2024-11-28 17:00:00', '2024-11-28 17:00:00'),
(270, 6, 1, 2024, 11, 48, 5, '2024-11-27', 'Review Phát triển module mới', NULL, NULL, NULL, '2024-11-26 17:00:00', '2024-11-26 17:00:00'),
(271, 6, 1, 2024, 11, 48, 5, '2024-11-25', 'Test Phát triển module mới', NULL, NULL, NULL, '2024-11-24 17:00:00', '2024-11-24 17:00:00'),
(275, 1, NULL, 2024, 11, 47, 4, '2024-11-18', 'Hoàn thành User testing', NULL, NULL, NULL, '2024-11-17 17:00:00', '2024-11-17 17:00:00'),
(276, 1, NULL, 2024, 11, 47, 4, '2024-11-20', 'Hoàn thành Cập nhật design guidelines', NULL, NULL, NULL, '2024-11-19 17:00:00', '2024-11-19 17:00:00'),
(277, 2, 1, 2024, 11, 47, 4, '2024-11-22', 'Review Thiết kế UI/UX mới', NULL, NULL, NULL, '2024-11-21 17:00:00', '2024-11-21 17:00:00'),
(278, 2, 1, 2024, 11, 47, 4, '2024-11-20', 'Bắt đầu Họp với stakeholders', NULL, NULL, NULL, '2024-11-19 17:00:00', '2024-11-19 17:00:00'),
(279, 2, 1, 2024, 11, 47, 4, '2024-11-18', 'Tiếp tục Thiết kế UI/UX mới', NULL, NULL, NULL, '2024-11-17 17:00:00', '2024-11-17 17:00:00'),
(280, 3, 1, 2024, 11, 47, 4, '2024-11-20', 'Review Thiết kế UI/UX mới', NULL, NULL, NULL, '2024-11-19 17:00:00', '2024-11-19 17:00:00'),
(281, 3, 1, 2024, 11, 47, 4, '2024-11-22', 'Review Họp với stakeholders', NULL, NULL, NULL, '2024-11-21 17:00:00', '2024-11-21 17:00:00'),
(282, 4, 1, 2024, 11, 47, 4, '2024-11-21', 'Test Họp với stakeholders', NULL, NULL, NULL, '2024-11-20 17:00:00', '2024-11-20 17:00:00'),
(283, 4, 1, 2024, 11, 47, 4, '2024-11-22', 'Bắt đầu Tạo prototype', NULL, NULL, NULL, '2024-11-21 17:00:00', '2024-11-21 17:00:00'),
(284, 4, 1, 2024, 11, 47, 4, '2024-11-20', 'Review Thiết kế UI/UX mới', NULL, NULL, NULL, '2024-11-19 17:00:00', '2024-11-19 17:00:00'),
(285, 5, 4, 2024, 11, 47, 4, '2024-11-21', 'Bắt đầu Thiết kế UI/UX mới', NULL, NULL, NULL, '2024-11-20 17:00:00', '2024-11-20 17:00:00'),
(286, 5, 4, 2024, 11, 47, 4, '2024-11-20', 'Review Thiết kế UI/UX mới', NULL, NULL, NULL, '2024-11-19 17:00:00', '2024-11-19 17:00:00'),
(287, 6, 1, 2024, 11, 47, 4, '2024-11-18', 'Hoàn thành Họp với stakeholders', NULL, NULL, NULL, '2024-11-17 17:00:00', '2024-11-17 17:00:00'),
(288, 6, 1, 2024, 11, 47, 4, '2024-11-19', 'Bắt đầu Cập nhật design guidelines', NULL, NULL, NULL, '2024-11-18 17:00:00', '2024-11-18 17:00:00'),
(289, 6, 1, 2024, 11, 47, 4, '2024-11-21', 'Hoàn thành Tạo prototype', NULL, NULL, NULL, '2024-11-20 17:00:00', '2024-11-20 17:00:00'),
(292, 1, NULL, 2024, 10, 44, 5, '2024-10-31', 'Hoàn thành Monitoring hệ thống', NULL, NULL, NULL, '2024-10-30 17:00:00', '2024-10-30 17:00:00'),
(293, 1, NULL, 2024, 10, 44, 5, '2024-10-29', 'Test Triển khai tính năng mới', NULL, NULL, NULL, '2024-10-28 17:00:00', '2024-10-28 17:00:00'),
(294, 1, NULL, 2024, 10, 44, 5, '2024-10-28', 'Bắt đầu Monitoring hệ thống', NULL, NULL, NULL, '2024-10-27 17:00:00', '2024-10-27 17:00:00'),
(295, 2, 1, 2024, 10, 44, 5, '2024-10-30', 'Hoàn thành Cập nhật security', NULL, NULL, NULL, '2024-10-29 17:00:00', '2024-10-29 17:00:00'),
(296, 2, 1, 2024, 10, 44, 5, '2024-10-29', 'Review Monitoring hệ thống', NULL, NULL, NULL, '2024-10-28 17:00:00', '2024-10-28 17:00:00'),
(297, 2, 1, 2024, 10, 44, 5, '2024-10-28', 'Bắt đầu Triển khai tính năng mới', NULL, NULL, NULL, '2024-10-27 17:00:00', '2024-10-27 17:00:00'),
(298, 3, 1, 2024, 10, 44, 5, '2024-11-01', 'Hoàn thành Fix critical bugs', NULL, NULL, NULL, '2024-10-31 17:00:00', '2024-10-31 17:00:00'),
(299, 3, 1, 2024, 10, 44, 5, '2024-10-30', 'Review Cập nhật security', NULL, NULL, NULL, '2024-10-29 17:00:00', '2024-10-29 17:00:00'),
(300, 3, 1, 2024, 10, 44, 5, '2024-10-31', 'Tiếp tục Chuẩn bị release', NULL, NULL, NULL, '2024-10-30 17:00:00', '2024-10-30 17:00:00'),
(301, 4, 1, 2024, 10, 44, 5, '2024-10-30', 'Test Triển khai tính năng mới', NULL, NULL, NULL, '2024-10-29 17:00:00', '2024-10-29 17:00:00'),
(302, 4, 1, 2024, 10, 44, 5, '2024-11-01', 'Bắt đầu Monitoring hệ thống', NULL, NULL, NULL, '2024-10-31 17:00:00', '2024-10-31 17:00:00'),
(303, 5, 4, 2024, 10, 44, 5, '2024-10-31', 'Review Cập nhật security', NULL, NULL, NULL, '2024-10-30 17:00:00', '2024-10-30 17:00:00'),
(304, 5, 4, 2024, 10, 44, 5, '2024-10-28', 'Bắt đầu Chuẩn bị release', NULL, NULL, NULL, '2024-10-27 17:00:00', '2024-10-27 17:00:00'),
(305, 6, 1, 2024, 10, 44, 5, '2024-10-30', 'Review Monitoring hệ thống', NULL, NULL, NULL, '2024-10-29 17:00:00', '2024-10-29 17:00:00'),
(306, 6, 1, 2024, 10, 44, 5, '2024-11-01', 'Tiếp tục Triển khai tính năng mới', NULL, NULL, NULL, '2024-10-31 17:00:00', '2024-10-31 17:00:00'),
(307, 6, 1, 2024, 10, 44, 5, '2024-10-28', 'Test Triển khai tính năng mới', NULL, NULL, NULL, '2024-10-27 17:00:00', '2024-10-27 17:00:00'),
(310, 1, NULL, 2024, 10, 43, 4, '2024-10-22', 'Hoàn thành Lập kế hoạch sprint', NULL, NULL, NULL, '2024-10-21 17:00:00', '2024-10-21 17:00:00'),
(311, 1, NULL, 2024, 10, 43, 4, '2024-10-23', 'Tiếp tục Thiết lập môi trường test', NULL, NULL, NULL, '2024-10-22 17:00:00', '2024-10-22 17:00:00'),
(312, 2, 1, 2024, 10, 43, 4, '2024-10-25', 'Tiếp tục Họp planning', NULL, NULL, NULL, '2024-10-24 17:00:00', '2024-10-24 17:00:00'),
(313, 2, 1, 2024, 10, 43, 4, '2024-10-22', 'Hoàn thành Thiết lập môi trường test', NULL, NULL, NULL, '2024-10-21 17:00:00', '2024-10-21 17:00:00'),
(314, 3, 1, 2024, 10, 43, 4, '2024-10-23', 'Bắt đầu Thiết lập môi trường test', NULL, NULL, NULL, '2024-10-22 17:00:00', '2024-10-22 17:00:00'),
(315, 3, 1, 2024, 10, 43, 4, '2024-10-25', 'Test Thiết lập môi trường test', NULL, NULL, NULL, '2024-10-24 17:00:00', '2024-10-24 17:00:00'),
(316, 4, 1, 2024, 10, 43, 4, '2024-10-22', 'Bắt đầu Lập kế hoạch sprint', NULL, NULL, NULL, '2024-10-21 17:00:00', '2024-10-21 17:00:00'),
(317, 4, 1, 2024, 10, 43, 4, '2024-10-21', 'Bắt đầu Viết test cases', NULL, NULL, NULL, '2024-10-20 17:00:00', '2024-10-20 17:00:00'),
(318, 5, 4, 2024, 10, 43, 4, '2024-10-23', 'Review Họp planning', NULL, NULL, NULL, '2024-10-22 17:00:00', '2024-10-22 17:00:00'),
(319, 5, 4, 2024, 10, 43, 4, '2024-10-24', 'Test Lập kế hoạch sprint', NULL, NULL, NULL, '2024-10-23 17:00:00', '2024-10-23 17:00:00'),
(320, 5, 4, 2024, 10, 43, 4, '2024-10-21', 'Bắt đầu Thiết lập môi trường test', NULL, NULL, NULL, '2024-10-20 17:00:00', '2024-10-20 17:00:00'),
(321, 6, 1, 2024, 10, 43, 4, '2024-10-23', 'Review Thiết lập môi trường test', NULL, NULL, NULL, '2024-10-22 17:00:00', '2024-10-22 17:00:00'),
(322, 6, 1, 2024, 10, 43, 4, '2024-10-25', 'Test Viết test cases', NULL, NULL, NULL, '2024-10-24 17:00:00', '2024-10-24 17:00:00'),
(323, 6, 1, 2024, 10, 43, 4, '2024-10-22', 'Test Viết test cases', NULL, NULL, NULL, '2024-10-21 17:00:00', '2024-10-21 17:00:00'),
(327, 1, NULL, 2023, 12, 52, 5, '2023-12-26', 'Review Chuẩn bị báo cáo tổng kết', NULL, NULL, NULL, '2023-12-25 17:00:00', '2023-12-25 17:00:00'),
(328, 1, NULL, 2023, 12, 52, 5, '2023-12-25', 'Bắt đầu Lập kế hoạch 2024', NULL, NULL, NULL, '2023-12-24 17:00:00', '2023-12-24 17:00:00'),
(329, 1, NULL, 2023, 12, 52, 5, '2023-12-27', 'Hoàn thành Bảo trì hệ thống cuối năm', NULL, NULL, NULL, '2023-12-26 17:00:00', '2023-12-26 17:00:00'),
(330, 2, 1, 2023, 12, 52, 5, '2023-12-28', 'Test Dọn dẹp codebase', NULL, NULL, NULL, '2023-12-27 17:00:00', '2023-12-27 17:00:00'),
(331, 2, 1, 2023, 12, 52, 5, '2023-12-25', 'Bắt đầu Dọn dẹp codebase', NULL, NULL, NULL, '2023-12-24 17:00:00', '2023-12-24 17:00:00'),
(332, 3, 1, 2023, 12, 52, 5, '2023-12-25', 'Hoàn thành Lập kế hoạch 2024', NULL, NULL, NULL, '2023-12-24 17:00:00', '2023-12-24 17:00:00'),
(333, 3, 1, 2023, 12, 52, 5, '2023-12-29', 'Hoàn thành Bảo trì hệ thống cuối năm', NULL, NULL, NULL, '2023-12-28 17:00:00', '2023-12-28 17:00:00'),
(334, 3, 1, 2023, 12, 52, 5, '2023-12-26', 'Bắt đầu Lập kế hoạch 2024', NULL, NULL, NULL, '2023-12-25 17:00:00', '2023-12-25 17:00:00'),
(335, 4, 1, 2023, 12, 52, 5, '2023-12-28', 'Bắt đầu Bảo trì hệ thống cuối năm', NULL, NULL, NULL, '2023-12-27 17:00:00', '2023-12-27 17:00:00'),
(336, 4, 1, 2023, 12, 52, 5, '2023-12-27', 'Bắt đầu Dọn dẹp codebase', NULL, NULL, NULL, '2023-12-26 17:00:00', '2023-12-26 17:00:00'),
(337, 5, 4, 2023, 12, 52, 5, '2023-12-28', 'Review Bảo trì hệ thống cuối năm', NULL, NULL, NULL, '2023-12-27 17:00:00', '2023-12-27 17:00:00'),
(338, 5, 4, 2023, 12, 52, 5, '2023-12-26', 'Hoàn thành Archive dự án cũ', NULL, NULL, NULL, '2023-12-25 17:00:00', '2023-12-25 17:00:00'),
(339, 5, 4, 2023, 12, 52, 5, '2023-12-29', 'Tiếp tục Lập kế hoạch 2024', NULL, NULL, NULL, '2023-12-28 17:00:00', '2023-12-28 17:00:00'),
(340, 6, 1, 2023, 12, 52, 5, '2023-12-28', 'Test Dọn dẹp codebase', NULL, NULL, NULL, '2023-12-27 17:00:00', '2023-12-27 17:00:00'),
(341, 6, 1, 2023, 12, 52, 5, '2023-12-29', 'Bắt đầu Bảo trì hệ thống cuối năm', NULL, NULL, NULL, '2023-12-28 17:00:00', '2023-12-28 17:00:00'),
(344, 1, NULL, 2023, 12, 51, 4, '2023-12-20', 'Hoàn thành Hoàn thiện dự án cuối năm', NULL, NULL, NULL, '2023-12-19 17:00:00', '2023-12-19 17:00:00'),
(345, 1, NULL, 2023, 12, 51, 4, '2023-12-21', 'Hoàn thành Final testing', NULL, NULL, NULL, '2023-12-20 17:00:00', '2023-12-20 17:00:00'),
(346, 1, NULL, 2023, 12, 51, 4, '2023-12-19', 'Hoàn thành Hoàn thiện dự án cuối năm', NULL, NULL, NULL, '2023-12-18 17:00:00', '2023-12-18 17:00:00'),
(347, 2, 1, 2023, 12, 51, 4, '2023-12-21', 'Hoàn thành Chuẩn bị handover', NULL, NULL, NULL, '2023-12-20 17:00:00', '2023-12-20 17:00:00'),
(348, 2, 1, 2023, 12, 51, 4, '2023-12-19', 'Bắt đầu Hoàn thiện dự án cuối năm', NULL, NULL, NULL, '2023-12-18 17:00:00', '2023-12-18 17:00:00'),
(349, 2, 1, 2023, 12, 51, 4, '2023-12-18', 'Hoàn thành Chuẩn bị handover', NULL, NULL, NULL, '2023-12-17 17:00:00', '2023-12-17 17:00:00'),
(350, 3, 1, 2023, 12, 51, 4, '2023-12-19', 'Test Chuẩn bị handover', NULL, NULL, NULL, '2023-12-18 17:00:00', '2023-12-18 17:00:00'),
(351, 3, 1, 2023, 12, 51, 4, '2023-12-18', 'Review Hoàn thiện dự án cuối năm', NULL, NULL, NULL, '2023-12-17 17:00:00', '2023-12-17 17:00:00'),
(352, 4, 1, 2023, 12, 51, 4, '2023-12-22', 'Test Hoàn thiện dự án cuối năm', NULL, NULL, NULL, '2023-12-21 17:00:00', '2023-12-21 17:00:00'),
(353, 4, 1, 2023, 12, 51, 4, '2023-12-21', 'Review Họp tổng kết team', NULL, NULL, NULL, '2023-12-20 17:00:00', '2023-12-20 17:00:00'),
(354, 5, 4, 2023, 12, 51, 4, '2023-12-20', 'Bắt đầu Chuẩn bị handover', NULL, NULL, NULL, '2023-12-19 17:00:00', '2023-12-19 17:00:00'),
(355, 5, 4, 2023, 12, 51, 4, '2023-12-18', 'Review Hoàn thiện dự án cuối năm', NULL, NULL, NULL, '2023-12-17 17:00:00', '2023-12-17 17:00:00'),
(356, 6, 1, 2023, 12, 51, 4, '2023-12-18', 'Bắt đầu Chuẩn bị handover', NULL, NULL, NULL, '2023-12-17 17:00:00', '2023-12-17 17:00:00'),
(357, 6, 1, 2023, 12, 51, 4, '2023-12-19', 'Test Chuẩn bị handover', NULL, NULL, NULL, '2023-12-18 17:00:00', '2023-12-18 17:00:00'),
(358, 6, 1, 2023, 12, 51, 4, '2023-12-22', 'Review Hoàn thiện dự án cuối năm', NULL, NULL, NULL, '2023-12-21 17:00:00', '2023-12-21 17:00:00'),
(361, 1, NULL, 2023, 6, 26, 5, '2023-06-28', 'Tiếp tục Chuẩn bị release v2.0', NULL, NULL, NULL, '2023-06-27 17:00:00', '2023-06-27 17:00:00'),
(362, 1, NULL, 2023, 6, 26, 5, '2023-06-26', 'Tiếp tục Phát triển tính năng core', NULL, NULL, NULL, '2023-06-25 17:00:00', '2023-06-25 17:00:00'),
(363, 2, 1, 2023, 6, 26, 5, '2023-06-27', 'Review Database optimization', NULL, NULL, NULL, '2023-06-26 17:00:00', '2023-06-26 17:00:00'),
(364, 2, 1, 2023, 6, 26, 5, '2023-06-26', 'Test Phát triển tính năng core', NULL, NULL, NULL, '2023-06-25 17:00:00', '2023-06-25 17:00:00'),
(365, 3, 1, 2023, 6, 26, 5, '2023-06-28', 'Tiếp tục Tối ưu performance', NULL, NULL, NULL, '2023-06-27 17:00:00', '2023-06-27 17:00:00'),
(366, 3, 1, 2023, 6, 26, 5, '2023-06-27', 'Bắt đầu Chuẩn bị release v2.0', NULL, NULL, NULL, '2023-06-26 17:00:00', '2023-06-26 17:00:00'),
(367, 3, 1, 2023, 6, 26, 5, '2023-06-30', 'Review Phát triển tính năng core', NULL, NULL, NULL, '2023-06-29 17:00:00', '2023-06-29 17:00:00'),
(368, 4, 1, 2023, 6, 26, 5, '2023-06-28', 'Review Chuẩn bị release v2.0', NULL, NULL, NULL, '2023-06-27 17:00:00', '2023-06-27 17:00:00'),
(369, 4, 1, 2023, 6, 26, 5, '2023-06-27', 'Bắt đầu Chuẩn bị release v2.0', NULL, NULL, NULL, '2023-06-26 17:00:00', '2023-06-26 17:00:00'),
(370, 5, 4, 2023, 6, 26, 5, '2023-06-29', 'Test Chuẩn bị release v2.0', NULL, NULL, NULL, '2023-06-28 17:00:00', '2023-06-28 17:00:00'),
(371, 5, 4, 2023, 6, 26, 5, '2023-06-28', 'Bắt đầu Tối ưu performance', NULL, NULL, NULL, '2023-06-27 17:00:00', '2023-06-27 17:00:00'),
(372, 6, 1, 2023, 6, 26, 5, '2023-06-27', 'Test Chuẩn bị release v2.0', NULL, NULL, NULL, '2023-06-26 17:00:00', '2023-06-26 17:00:00'),
(373, 6, 1, 2023, 6, 26, 5, '2023-06-29', 'Review Security audit', NULL, NULL, NULL, '2023-06-28 17:00:00', '2023-06-28 17:00:00'),
(376, 1, NULL, 2023, 6, 25, 4, '2023-06-19', 'Bắt đầu Code review session', NULL, NULL, NULL, '2023-06-18 17:00:00', '2023-06-18 17:00:00'),
(377, 1, NULL, 2023, 6, 25, 4, '2023-06-23', 'Bắt đầu Integration testing', NULL, NULL, NULL, '2023-06-22 17:00:00', '2023-06-22 17:00:00'),
(378, 1, NULL, 2023, 6, 25, 4, '2023-06-21', 'Review Thiết kế architecture mới', NULL, NULL, NULL, '2023-06-20 17:00:00', '2023-06-20 17:00:00'),
(379, 2, 1, 2023, 6, 25, 4, '2023-06-23', 'Tiếp tục Integration testing', NULL, NULL, NULL, '2023-06-22 17:00:00', '2023-06-22 17:00:00'),
(380, 2, 1, 2023, 6, 25, 4, '2023-06-20', 'Test Thiết kế architecture mới', NULL, NULL, NULL, '2023-06-19 17:00:00', '2023-06-19 17:00:00'),
(381, 3, 1, 2023, 6, 25, 4, '2023-06-20', 'Tiếp tục Thiết kế architecture mới', NULL, NULL, NULL, '2023-06-19 17:00:00', '2023-06-19 17:00:00'),
(382, 3, 1, 2023, 6, 25, 4, '2023-06-19', 'Test Thiết kế architecture mới', NULL, NULL, NULL, '2023-06-18 17:00:00', '2023-06-18 17:00:00'),
(383, 3, 1, 2023, 6, 25, 4, '2023-06-23', 'Tiếp tục Unit testing', NULL, NULL, NULL, '2023-06-22 17:00:00', '2023-06-22 17:00:00'),
(384, 4, 1, 2023, 6, 25, 4, '2023-06-20', 'Bắt đầu Unit testing', NULL, NULL, NULL, '2023-06-19 17:00:00', '2023-06-19 17:00:00'),
(385, 4, 1, 2023, 6, 25, 4, '2023-06-23', 'Bắt đầu Unit testing', NULL, NULL, NULL, '2023-06-22 17:00:00', '2023-06-22 17:00:00'),
(386, 5, 4, 2023, 6, 25, 4, '2023-06-21', 'Bắt đầu Integration testing', NULL, NULL, NULL, '2023-06-20 17:00:00', '2023-06-20 17:00:00'),
(387, 5, 4, 2023, 6, 25, 4, '2023-06-19', 'Test Integration testing', NULL, NULL, NULL, '2023-06-18 17:00:00', '2023-06-18 17:00:00'),
(388, 5, 4, 2023, 6, 25, 4, '2023-06-20', 'Review Code review session', NULL, NULL, NULL, '2023-06-19 17:00:00', '2023-06-19 17:00:00'),
(389, 6, 1, 2023, 6, 25, 4, '2023-06-20', 'Hoàn thành Code review session', NULL, NULL, NULL, '2023-06-19 17:00:00', '2023-06-19 17:00:00'),
(390, 6, 1, 2023, 6, 25, 4, '2023-06-21', 'Hoàn thành Code refactoring', NULL, NULL, NULL, '2023-06-20 17:00:00', '2023-06-20 17:00:00'),
(391, 6, 1, 2023, 6, 25, 4, '2023-06-19', 'Test Integration testing', NULL, NULL, NULL, '2023-06-18 17:00:00', '2023-06-18 17:00:00'),
(395, 1, NULL, 2023, 3, 13, 5, '2023-03-29', 'Test Setup development environment', NULL, NULL, NULL, '2023-03-28 17:00:00', '2023-03-28 17:00:00'),
(396, 1, NULL, 2023, 3, 13, 5, '2023-03-27', 'Review Tạo project structure', NULL, NULL, NULL, '2023-03-26 17:00:00', '2023-03-26 17:00:00'),
(397, 2, 1, 2023, 3, 13, 5, '2023-03-30', 'Tiếp tục Setup development environment', NULL, NULL, NULL, '2023-03-29 17:00:00', '2023-03-29 17:00:00'),
(398, 2, 1, 2023, 3, 13, 5, '2023-03-27', 'Hoàn thành Khởi tạo dự án mới', NULL, NULL, NULL, '2023-03-26 17:00:00', '2023-03-26 17:00:00'),
(399, 2, 1, 2023, 3, 13, 5, '2023-03-28', 'Review Cài đặt dependencies', NULL, NULL, NULL, '2023-03-27 17:00:00', '2023-03-27 17:00:00'),
(400, 3, 1, 2023, 3, 13, 5, '2023-03-27', 'Test Cài đặt dependencies', NULL, NULL, NULL, '2023-03-26 17:00:00', '2023-03-26 17:00:00'),
(401, 3, 1, 2023, 3, 13, 5, '2023-03-28', 'Hoàn thành Setup development environment', NULL, NULL, NULL, '2023-03-27 17:00:00', '2023-03-27 17:00:00'),
(402, 3, 1, 2023, 3, 13, 5, '2023-03-29', 'Review Khởi tạo dự án mới', NULL, NULL, NULL, '2023-03-28 17:00:00', '2023-03-28 17:00:00'),
(403, 4, 1, 2023, 3, 13, 5, '2023-03-31', 'Hoàn thành Setup development environment', NULL, NULL, NULL, '2023-03-30 17:00:00', '2023-03-30 17:00:00'),
(404, 4, 1, 2023, 3, 13, 5, '2023-03-30', 'Bắt đầu Lập kế hoạch sprint đầu tiên', NULL, NULL, NULL, '2023-03-29 17:00:00', '2023-03-29 17:00:00'),
(405, 4, 1, 2023, 3, 13, 5, '2023-03-27', 'Test Cài đặt dependencies', NULL, NULL, NULL, '2023-03-26 17:00:00', '2023-03-26 17:00:00'),
(406, 5, 4, 2023, 3, 13, 5, '2023-03-28', 'Hoàn thành Cài đặt dependencies', NULL, NULL, NULL, '2023-03-27 17:00:00', '2023-03-27 17:00:00'),
(407, 5, 4, 2023, 3, 13, 5, '2023-03-29', 'Bắt đầu Lập kế hoạch sprint đầu tiên', NULL, NULL, NULL, '2023-03-28 17:00:00', '2023-03-28 17:00:00'),
(408, 5, 4, 2023, 3, 13, 5, '2023-03-31', 'Review Tạo project structure', NULL, NULL, NULL, '2023-03-30 17:00:00', '2023-03-30 17:00:00'),
(409, 6, 1, 2023, 3, 13, 5, '2023-03-31', 'Bắt đầu Cài đặt dependencies', NULL, NULL, NULL, '2023-03-30 17:00:00', '2023-03-30 17:00:00'),
(410, 6, 1, 2023, 3, 13, 5, '2023-03-27', 'Review Khởi tạo dự án mới', NULL, NULL, NULL, '2023-03-26 17:00:00', '2023-03-26 17:00:00'),
(413, 1, NULL, 2023, 3, 12, 4, '2023-03-24', 'Tiếp tục Họp kickoff với khách hàng', NULL, NULL, NULL, '2023-03-23 17:00:00', '2023-03-23 17:00:00'),
(414, 1, NULL, 2023, 3, 12, 4, '2023-03-23', 'Bắt đầu Lập timeline dự án', NULL, NULL, NULL, '2023-03-22 17:00:00', '2023-03-22 17:00:00'),
(415, 1, NULL, 2023, 3, 12, 4, '2023-03-21', 'Bắt đầu Lập timeline dự án', NULL, NULL, NULL, '2023-03-20 17:00:00', '2023-03-20 17:00:00'),
(416, 2, 1, 2023, 3, 12, 4, '2023-03-24', 'Test Tạo API specifications', NULL, NULL, NULL, '2023-03-23 17:00:00', '2023-03-23 17:00:00'),
(417, 2, 1, 2023, 3, 12, 4, '2023-03-20', 'Review Họp kickoff với khách hàng', NULL, NULL, NULL, '2023-03-19 17:00:00', '2023-03-19 17:00:00'),
(418, 2, 1, 2023, 3, 12, 4, '2023-03-21', 'Bắt đầu Thiết kế database schema', NULL, NULL, NULL, '2023-03-20 17:00:00', '2023-03-20 17:00:00'),
(419, 3, 1, 2023, 3, 12, 4, '2023-03-23', 'Bắt đầu Họp kickoff với khách hàng', NULL, NULL, NULL, '2023-03-22 17:00:00', '2023-03-22 17:00:00'),
(420, 3, 1, 2023, 3, 12, 4, '2023-03-22', 'Tiếp tục Tạo API specifications', NULL, NULL, NULL, '2023-03-21 17:00:00', '2023-03-21 17:00:00'),
(421, 3, 1, 2023, 3, 12, 4, '2023-03-24', 'Bắt đầu Phân tích yêu cầu dự án', NULL, NULL, NULL, '2023-03-23 17:00:00', '2023-03-23 17:00:00'),
(422, 4, 1, 2023, 3, 12, 4, '2023-03-20', 'Hoàn thành Lập timeline dự án', NULL, NULL, NULL, '2023-03-19 17:00:00', '2023-03-19 17:00:00'),
(423, 4, 1, 2023, 3, 12, 4, '2023-03-24', 'Hoàn thành Tạo API specifications', NULL, NULL, NULL, '2023-03-23 17:00:00', '2023-03-23 17:00:00'),
(424, 5, 4, 2023, 3, 12, 4, '2023-03-23', 'Tiếp tục Phân tích yêu cầu dự án', NULL, NULL, NULL, '2023-03-22 17:00:00', '2023-03-22 17:00:00'),
(425, 5, 4, 2023, 3, 12, 4, '2023-03-21', 'Bắt đầu Phân tích yêu cầu dự án', NULL, NULL, NULL, '2023-03-20 17:00:00', '2023-03-20 17:00:00'),
(426, 6, 1, 2023, 3, 12, 4, '2023-03-23', 'Review Phân tích yêu cầu dự án', NULL, NULL, NULL, '2023-03-22 17:00:00', '2023-03-22 17:00:00'),
(427, 6, 1, 2023, 3, 12, 4, '2023-03-21', 'Tiếp tục Lập timeline dự án', NULL, NULL, NULL, '2023-03-20 17:00:00', '2023-03-20 17:00:00'),
(430, 1, NULL, 2022, 12, 52, 5, '2022-12-29', 'Test Chuẩn bị báo cáo năm', NULL, NULL, NULL, '2022-12-28 17:00:00', '2022-12-28 17:00:00'),
(431, 1, NULL, 2022, 12, 52, 5, '2022-12-30', 'Review Bảo trì hệ thống legacy', NULL, NULL, NULL, '2022-12-29 17:00:00', '2022-12-29 17:00:00'),
(432, 2, 1, 2022, 12, 52, 5, '2022-12-26', 'Bắt đầu Bảo trì hệ thống legacy', NULL, NULL, NULL, '2022-12-25 17:00:00', '2022-12-25 17:00:00'),
(433, 2, 1, 2022, 12, 52, 5, '2022-12-28', 'Review Bảo trì hệ thống legacy', NULL, NULL, NULL, '2022-12-27 17:00:00', '2022-12-27 17:00:00'),
(434, 3, 1, 2022, 12, 52, 5, '2022-12-26', 'Test Chuẩn bị báo cáo năm', NULL, NULL, NULL, '2022-12-25 17:00:00', '2022-12-25 17:00:00'),
(435, 3, 1, 2022, 12, 52, 5, '2022-12-28', 'Tiếp tục Chuẩn bị báo cáo năm', NULL, NULL, NULL, '2022-12-27 17:00:00', '2022-12-27 17:00:00'),
(436, 3, 1, 2022, 12, 52, 5, '2022-12-29', 'Tiếp tục Cập nhật documentation', NULL, NULL, NULL, '2022-12-28 17:00:00', '2022-12-28 17:00:00'),
(437, 4, 1, 2022, 12, 52, 5, '2022-12-28', 'Test Migration dữ liệu cũ', NULL, NULL, NULL, '2022-12-27 17:00:00', '2022-12-27 17:00:00'),
(438, 4, 1, 2022, 12, 52, 5, '2022-12-30', 'Review Migration dữ liệu cũ', NULL, NULL, NULL, '2022-12-29 17:00:00', '2022-12-29 17:00:00'),
(439, 4, 1, 2022, 12, 52, 5, '2022-12-26', 'Review Chuẩn bị báo cáo năm', NULL, NULL, NULL, '2022-12-25 17:00:00', '2022-12-25 17:00:00'),
(440, 5, 4, 2022, 12, 52, 5, '2022-12-30', 'Test Migration dữ liệu cũ', NULL, NULL, NULL, '2022-12-29 17:00:00', '2022-12-29 17:00:00'),
(441, 5, 4, 2022, 12, 52, 5, '2022-12-28', 'Hoàn thành Cập nhật documentation', NULL, NULL, NULL, '2022-12-27 17:00:00', '2022-12-27 17:00:00'),
(442, 5, 4, 2022, 12, 52, 5, '2022-12-27', 'Hoàn thành Lập kế hoạch 2023', NULL, NULL, NULL, '2022-12-26 17:00:00', '2022-12-26 17:00:00'),
(443, 6, 1, 2022, 12, 52, 5, '2022-12-27', 'Test Chuẩn bị báo cáo năm', NULL, NULL, NULL, '2022-12-26 17:00:00', '2022-12-26 17:00:00'),
(444, 6, 1, 2022, 12, 52, 5, '2022-12-29', 'Review Lập kế hoạch 2023', NULL, NULL, NULL, '2022-12-28 17:00:00', '2022-12-28 17:00:00'),
(448, 1, NULL, 2022, 12, 51, 4, '2022-12-20', 'Test Hoàn thiện dự án cuối năm', NULL, NULL, NULL, '2022-12-19 17:00:00', '2022-12-19 17:00:00'),
(449, 1, NULL, 2022, 12, 51, 4, '2022-12-23', 'Hoàn thành Chuẩn bị handover', NULL, NULL, NULL, '2022-12-22 17:00:00', '2022-12-22 17:00:00'),
(450, 2, 1, 2022, 12, 51, 4, '2022-12-20', 'Review Hoàn thiện dự án cuối năm', NULL, NULL, NULL, '2022-12-19 17:00:00', '2022-12-19 17:00:00'),
(451, 2, 1, 2022, 12, 51, 4, '2022-12-21', 'Review Hoàn thiện dự án cuối năm', NULL, NULL, NULL, '2022-12-20 17:00:00', '2022-12-20 17:00:00'),
(452, 3, 1, 2022, 12, 51, 4, '2022-12-20', 'Tiếp tục Final testing và deployment', NULL, NULL, NULL, '2022-12-19 17:00:00', '2022-12-19 17:00:00'),
(453, 3, 1, 2022, 12, 51, 4, '2022-12-19', 'Review Hoàn thiện dự án cuối năm', NULL, NULL, NULL, '2022-12-18 17:00:00', '2022-12-18 17:00:00'),
(454, 3, 1, 2022, 12, 51, 4, '2022-12-22', 'Hoàn thành Họp tổng kết', NULL, NULL, NULL, '2022-12-21 17:00:00', '2022-12-21 17:00:00'),
(455, 4, 1, 2022, 12, 51, 4, '2022-12-21', 'Tiếp tục Chuẩn bị handover', NULL, NULL, NULL, '2022-12-20 17:00:00', '2022-12-20 17:00:00'),
(456, 4, 1, 2022, 12, 51, 4, '2022-12-22', 'Hoàn thành Hoàn thiện dự án cuối năm', NULL, NULL, NULL, '2022-12-21 17:00:00', '2022-12-21 17:00:00'),
(457, 4, 1, 2022, 12, 51, 4, '2022-12-20', 'Bắt đầu Họp tổng kết', NULL, NULL, NULL, '2022-12-19 17:00:00', '2022-12-19 17:00:00'),
(458, 5, 4, 2022, 12, 51, 4, '2022-12-19', 'Tiếp tục Training end users', NULL, NULL, NULL, '2022-12-18 17:00:00', '2022-12-18 17:00:00'),
(459, 5, 4, 2022, 12, 51, 4, '2022-12-20', 'Tiếp tục Final testing và deployment', NULL, NULL, NULL, '2022-12-19 17:00:00', '2022-12-19 17:00:00'),
(460, 5, 4, 2022, 12, 51, 4, '2022-12-23', 'Test Chuẩn bị handover', NULL, NULL, NULL, '2022-12-22 17:00:00', '2022-12-22 17:00:00'),
(461, 6, 1, 2022, 12, 51, 4, '2022-12-19', 'Tiếp tục Training end users', NULL, NULL, NULL, '2022-12-18 17:00:00', '2022-12-18 17:00:00'),
(462, 6, 1, 2022, 12, 51, 4, '2022-12-20', 'Bắt đầu Họp tổng kết', NULL, NULL, NULL, '2022-12-19 17:00:00', '2022-12-19 17:00:00'),
(465, 5, 4, 2025, 8, 35, NULL, '2025-08-29', '- Dự án đang tiến hành\r\n- Dự án đang tiến hành\r\n- Dự án đang tiến hành\r\n- Dự án đang tiến hành\r\n- Dự án đang tiến hành\r\n- Dự án đang tiến hành', NULL, NULL, NULL, '2025-08-26 01:56:11', '2025-08-26 01:56:11'),
(466, 5, 4, 2025, 8, 35, NULL, '2025-08-26', 'áhdfhfgshfgh', NULL, NULL, NULL, '2025-08-26 09:20:12', '2025-08-26 09:20:12'),
(467, 3, 1, 2025, 8, 35, NULL, '2025-08-28', 'Tôi muốn hỗ trợ ABC', NULL, NULL, '{\"bugs_fixed\": null, \"code_reviews\": null, \"meetings_attended\": null, \"projects_worked_on\": null}', '2025-08-28 09:33:44', '2025-08-28 09:33:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comments_parent_id_foreign` (`parent_id`),
  ADD KEY `comments_task_id_created_at_index` (`task_id`,`created_at`),
  ADD KEY `comments_user_id_created_at_index` (`user_id`,`created_at`);

--
-- Indexes for table `comment_attachments`
--
ALTER TABLE `comment_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comment_attachments_comment_id_created_at_index` (`comment_id`,`created_at`),
  ADD KEY `comment_attachments_mime_type_index` (`mime_type`);

--
-- Indexes for table `contract_images`
--
ALTER TABLE `contract_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_images_employee_contract_id_foreign` (`employee_contract_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_name_unique` (`name`);

--
-- Indexes for table `department_tasks`
--
ALTER TABLE `department_tasks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_tasks_task_id_department_id_unique` (`task_id`,`department_id`),
  ADD KEY `department_tasks_department_id_foreign` (`department_id`);

--
-- Indexes for table `employee_contracts`
--
ALTER TABLE `employee_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_contracts_user_id_foreign` (`user_id`);

--
-- Indexes for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_salaries_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_is_read_index` (`user_id`,`is_read`),
  ADD KEY `notifications_user_id_created_at_index` (`user_id`,`created_at`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_department_id_foreign` (`department_id`),
  ADD KEY `tasks_assignee_id_foreign` (`assignee_id`),
  ADD KEY `tasks_creator_id_foreign` (`creator_id`);

--
-- Indexes for table `task_activities`
--
ALTER TABLE `task_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_activities_task_id_action_created_at_index` (`task_id`,`action`,`created_at`),
  ADD KEY `task_activities_user_id_is_read_index` (`user_id`);

--
-- Indexes for table `task_approvals`
--
ALTER TABLE `task_approvals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_approvals_task_id_department_id_manager_id_unique` (`task_id`,`department_id`,`manager_id`),
  ADD KEY `task_approvals_department_id_foreign` (`department_id`),
  ADD KEY `task_approvals_manager_id_foreign` (`manager_id`);

--
-- Indexes for table `task_assignees`
--
ALTER TABLE `task_assignees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_assignees_task_id_user_id_unique` (`task_id`,`user_id`),
  ADD KEY `task_assignees_user_id_foreign` (`user_id`);

--
-- Indexes for table `task_files`
--
ALTER TABLE `task_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_files_task_id_created_at_index` (`task_id`,`created_at`),
  ADD KEY `task_files_uploaded_by_created_at_index` (`uploaded_by`,`created_at`),
  ADD KEY `task_files_mime_type_index` (`mime_type`);

--
-- Indexes for table `task_followers`
--
ALTER TABLE `task_followers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_followers_task_id_user_id_unique` (`task_id`,`user_id`),
  ADD KEY `task_followers_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD KEY `users_department_id_foreign` (`department_id`);

--
-- Indexes for table `work_reports`
--
ALTER TABLE `work_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `work_reports_user_id_report_date_unique` (`user_id`,`report_date`),
  ADD KEY `work_reports_year_month_week_index` (`year`,`month`,`week`),
  ADD KEY `work_reports_department_id_year_month_index` (`department_id`,`year`,`month`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `comment_attachments`
--
ALTER TABLE `comment_attachments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contract_images`
--
ALTER TABLE `contract_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `department_tasks`
--
ALTER TABLE `department_tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `employee_contracts`
--
ALTER TABLE `employee_contracts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `task_activities`
--
ALTER TABLE `task_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `task_approvals`
--
ALTER TABLE `task_approvals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_assignees`
--
ALTER TABLE `task_assignees`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `task_files`
--
ALTER TABLE `task_files`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_followers`
--
ALTER TABLE `task_followers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `work_reports`
--
ALTER TABLE `work_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=468;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comment_attachments`
--
ALTER TABLE `comment_attachments`
  ADD CONSTRAINT `comment_attachments_comment_id_foreign` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contract_images`
--
ALTER TABLE `contract_images`
  ADD CONSTRAINT `contract_images_employee_contract_id_foreign` FOREIGN KEY (`employee_contract_id`) REFERENCES `employee_contracts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `department_tasks`
--
ALTER TABLE `department_tasks`
  ADD CONSTRAINT `department_tasks_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `department_tasks_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_contracts`
--
ALTER TABLE `employee_contracts`
  ADD CONSTRAINT `employee_contracts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  ADD CONSTRAINT `employee_salaries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_assignee_id_foreign` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_creator_id_foreign` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `task_activities`
--
ALTER TABLE `task_activities`
  ADD CONSTRAINT `task_activities_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_approvals`
--
ALTER TABLE `task_approvals`
  ADD CONSTRAINT `task_approvals_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_approvals_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_approvals_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_assignees`
--
ALTER TABLE `task_assignees`
  ADD CONSTRAINT `task_assignees_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_assignees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_files`
--
ALTER TABLE `task_files`
  ADD CONSTRAINT `task_files_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_files_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_followers`
--
ALTER TABLE `task_followers`
  ADD CONSTRAINT `task_followers_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_followers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `work_reports`
--
ALTER TABLE `work_reports`
  ADD CONSTRAINT `work_reports_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `work_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
