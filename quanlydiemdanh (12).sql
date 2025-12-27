-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th12 10, 2025 lúc 03:03 PM
-- Phiên bản máy phục vụ: 9.1.0
-- Phiên bản PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `quanlydiemdanh`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `attendance_records`
--

DROP TABLE IF EXISTS `attendance_records`;
CREATE TABLE IF NOT EXISTS `attendance_records` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `exam_schedule_id` bigint UNSIGNED NOT NULL,
  `student_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rekognition_result` enum('match','not_match','unknown') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confidence` decimal(5,2) DEFAULT NULL,
  `attendance_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_exam` (`student_code`,`exam_schedule_id`),
  KEY `fk_attendance_schedule` (`exam_schedule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=354 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `attendance_records`
--

INSERT INTO `attendance_records` (`id`, `exam_schedule_id`, `student_code`, `rekognition_result`, `confidence`, `attendance_time`, `created_at`, `updated_at`) VALUES
(8, 7, 'DH52200529', NULL, NULL, NULL, NULL, NULL),
(10, 7, 'DH52200350', NULL, NULL, NULL, NULL, NULL),
(11, 7, 'DH52200365', NULL, NULL, NULL, NULL, NULL),
(12, 7, 'DH52200384', NULL, NULL, NULL, NULL, NULL),
(13, 7, 'DH52200418', NULL, NULL, NULL, NULL, NULL),
(14, 7, 'DH52200424', NULL, NULL, NULL, NULL, NULL),
(15, 7, 'DH52200439', NULL, NULL, NULL, NULL, NULL),
(16, 7, 'DH52200448', NULL, NULL, NULL, NULL, NULL),
(17, 7, 'DH52200453', NULL, NULL, NULL, NULL, NULL),
(18, 8, 'DH52200490', NULL, NULL, NULL, NULL, NULL),
(19, 8, 'DH52200497', NULL, NULL, NULL, NULL, NULL),
(20, 8, 'DH52200499', NULL, NULL, NULL, NULL, NULL),
(23, 8, 'DH52200539', NULL, NULL, NULL, NULL, NULL),
(24, 8, 'DH52200543', NULL, NULL, NULL, NULL, NULL),
(25, 8, 'DH52200558', NULL, NULL, NULL, NULL, NULL),
(26, 8, 'DH52200589', NULL, NULL, NULL, NULL, NULL),
(27, 8, 'DH52200602', NULL, NULL, NULL, NULL, NULL),
(28, 9, 'DH52200608', NULL, NULL, NULL, NULL, NULL),
(29, 9, 'DH52200613', NULL, NULL, NULL, NULL, NULL),
(30, 9, 'DH52200616', NULL, NULL, NULL, NULL, NULL),
(31, 9, 'DH52200645', NULL, NULL, NULL, NULL, NULL),
(32, 9, 'DH52200662', NULL, NULL, NULL, NULL, NULL),
(33, 9, 'DH52200669', NULL, NULL, NULL, NULL, NULL),
(34, 9, 'DH52200673', NULL, NULL, NULL, NULL, NULL),
(35, 9, 'DH52200681', NULL, NULL, NULL, NULL, NULL),
(36, 9, 'DH52200687', NULL, NULL, NULL, NULL, NULL),
(37, 9, 'DH52200699', NULL, NULL, NULL, NULL, NULL),
(38, 10, 'DH52200701', NULL, NULL, NULL, NULL, NULL),
(39, 10, 'DH52200705', NULL, NULL, NULL, NULL, NULL),
(40, 10, 'DH52200771', NULL, NULL, NULL, NULL, NULL),
(41, 10, 'DH52200806', NULL, NULL, NULL, NULL, NULL),
(42, 10, 'DH52200815', NULL, NULL, NULL, NULL, NULL),
(43, 10, 'DH52200844', NULL, NULL, NULL, NULL, NULL),
(44, 10, 'DH52200860', NULL, NULL, NULL, NULL, NULL),
(45, 10, 'DH52200880', NULL, NULL, NULL, NULL, NULL),
(46, 10, 'DH52200901', NULL, NULL, NULL, NULL, NULL),
(47, 10, 'DH52200910', NULL, NULL, NULL, NULL, NULL),
(48, 11, 'DH52200915', NULL, NULL, NULL, NULL, NULL),
(49, 11, 'DH52200962', NULL, NULL, NULL, NULL, NULL),
(50, 11, 'DH52200986', NULL, NULL, NULL, NULL, NULL),
(51, 11, 'DH52201018', NULL, NULL, NULL, NULL, NULL),
(52, 11, 'DH52201065', NULL, NULL, NULL, NULL, NULL),
(53, 11, 'DH52201066', NULL, NULL, NULL, NULL, NULL),
(54, 11, 'DH52201069', NULL, NULL, NULL, NULL, NULL),
(55, 11, 'DH52201070', NULL, NULL, NULL, NULL, NULL),
(56, 11, 'DH52201088', NULL, NULL, NULL, NULL, NULL),
(57, 11, 'DH52201095', NULL, NULL, NULL, NULL, NULL),
(58, 12, 'DH52201148', NULL, NULL, NULL, NULL, NULL),
(59, 12, 'DH52201201', NULL, NULL, NULL, NULL, NULL),
(60, 12, 'DH52201241', NULL, NULL, NULL, NULL, NULL),
(61, 12, 'DH52201249', NULL, NULL, NULL, NULL, NULL),
(62, 12, 'DH52201253', NULL, NULL, NULL, NULL, NULL),
(63, 12, 'DH52201275', NULL, NULL, NULL, NULL, NULL),
(64, 12, 'DH52201290', NULL, NULL, NULL, NULL, NULL),
(65, 12, 'DH52201307', NULL, NULL, NULL, NULL, NULL),
(66, 12, 'DH52201316', NULL, NULL, NULL, NULL, NULL),
(67, 12, 'DH52201345', NULL, NULL, NULL, NULL, NULL),
(68, 13, 'DH52201358', NULL, NULL, NULL, NULL, NULL),
(69, 13, 'DH52201393', NULL, NULL, NULL, NULL, NULL),
(70, 13, 'DH52201410', NULL, NULL, NULL, NULL, NULL),
(71, 13, 'DH52201412', NULL, NULL, NULL, NULL, NULL),
(72, 13, 'DH52201416', NULL, NULL, NULL, NULL, NULL),
(73, 13, 'DH52201419', NULL, NULL, NULL, NULL, NULL),
(74, 13, 'DH52201441', NULL, NULL, NULL, NULL, NULL),
(75, 13, 'DH52201448', NULL, NULL, NULL, NULL, NULL),
(76, 13, 'DH52201451', NULL, NULL, NULL, NULL, NULL),
(77, 13, 'DH52201469', NULL, NULL, NULL, NULL, NULL),
(78, 14, 'DH52201475', NULL, NULL, NULL, NULL, NULL),
(79, 14, 'DH52201506', NULL, NULL, NULL, NULL, NULL),
(80, 14, 'DH52201526', NULL, NULL, NULL, NULL, NULL),
(81, 14, 'DH52201590', NULL, NULL, NULL, NULL, NULL),
(82, 14, 'DH52201597', NULL, NULL, NULL, NULL, NULL),
(83, 14, 'DH52201646', NULL, NULL, NULL, NULL, NULL),
(84, 14, 'DH52201659', NULL, NULL, NULL, NULL, NULL),
(85, 14, 'DH52201699', NULL, NULL, NULL, NULL, NULL),
(86, 14, 'DH52201708', NULL, NULL, NULL, NULL, NULL),
(87, 14, 'DH52201713', NULL, NULL, NULL, NULL, NULL),
(154, 8, 'DH52200529', NULL, NULL, NULL, NULL, NULL),
(155, 17, 'DH52200529', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(157, 17, 'DH52200350', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(158, 17, 'DH52200365', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(159, 17, 'DH52200384', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(160, 17, 'DH52200418', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(161, 17, 'DH52200424', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(162, 17, 'DH52200439', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(163, 17, 'DH52200448', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(164, 17, 'DH52200453', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(165, 18, 'DH52200490', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(166, 18, 'DH52200497', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(167, 18, 'DH52200499', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(170, 18, 'DH52200539', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(171, 18, 'DH52200543', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(172, 18, 'DH52200558', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(173, 18, 'DH52200589', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(174, 18, 'DH52200602', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(175, 19, 'DH52200608', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(176, 19, 'DH52200613', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(177, 19, 'DH52200616', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(178, 19, 'DH52200645', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(179, 19, 'DH52200662', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(180, 19, 'DH52200669', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(181, 19, 'DH52200673', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(182, 19, 'DH52200681', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(183, 19, 'DH52200687', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(184, 19, 'DH52200699', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(185, 20, 'DH52200701', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(186, 20, 'DH52200705', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(187, 20, 'DH52200771', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(188, 20, 'DH52200806', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(189, 20, 'DH52200815', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(190, 20, 'DH52200844', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(191, 20, 'DH52200860', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(192, 20, 'DH52200880', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(193, 20, 'DH52200901', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(194, 20, 'DH52200910', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(195, 21, 'DH52200915', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(196, 21, 'DH52200962', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(197, 21, 'DH52200986', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(198, 21, 'DH52201018', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(199, 21, 'DH52201065', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(200, 21, 'DH52201066', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(201, 21, 'DH52201069', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(202, 21, 'DH52201070', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(203, 21, 'DH52201088', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(204, 21, 'DH52201095', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(205, 22, 'DH52201148', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(206, 22, 'DH52201201', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(207, 22, 'DH52201241', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(208, 22, 'DH52201249', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(209, 22, 'DH52201253', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(210, 22, 'DH52201275', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(211, 22, 'DH52201290', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(212, 22, 'DH52201307', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(213, 22, 'DH52201316', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(214, 22, 'DH52201345', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(215, 23, 'DH52201358', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(216, 23, 'DH52201393', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(217, 23, 'DH52201410', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(218, 23, 'DH52201412', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(219, 23, 'DH52201416', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(220, 23, 'DH52201419', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(221, 23, 'DH52201441', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(222, 23, 'DH52201448', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(223, 23, 'DH52201451', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(224, 23, 'DH52201469', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(225, 24, 'DH52201475', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(226, 24, 'DH52201506', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(227, 24, 'DH52201526', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(228, 24, 'DH52201590', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(229, 24, 'DH52201597', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(230, 24, 'DH52201646', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(231, 24, 'DH52201659', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(232, 24, 'DH52201699', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(233, 24, 'DH52201708', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(234, 24, 'DH52201713', NULL, NULL, '2025-11-11 04:38:56', NULL, NULL),
(245, 26, 'DH52200384', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(246, 26, 'DH52200418', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(247, 26, 'DH52200424', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(248, 26, 'DH52200439', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(249, 26, 'DH52200448', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(250, 26, 'DH52200453', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(251, 26, 'DH52200490', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(252, 26, 'DH52200497', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(253, 26, 'DH52200499', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(256, 27, 'DH52200539', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(257, 27, 'DH52200543', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(258, 27, 'DH52200558', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(259, 27, 'DH52200589', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(260, 27, 'DH52200602', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(261, 27, 'DH52200608', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(262, 27, 'DH52200613', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(263, 27, 'DH52200616', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL),
(264, 27, 'DH52200645', NULL, NULL, '2025-11-11 04:43:46', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `classes`
--

DROP TABLE IF EXISTS `classes`;
CREATE TABLE IF NOT EXISTS `classes` (
  `class_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `faculty_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`class_code`),
  KEY `faculty_code` (`faculty_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `classes`
--

INSERT INTO `classes` (`class_code`, `class_name`, `faculty_code`, `created_at`, `updated_at`) VALUES
('D20_TH02', 'D20_TH02', 'CNTT', NULL, NULL),
('D21_TH06', 'D21_TH06', 'CNTT', NULL, NULL),
('D21_TH07', 'D21_TH07', 'CNTT', NULL, NULL),
('D22_TH01', 'D22_TH01', 'CNTT', '2025-10-13 05:00:32', '2025-10-13 05:00:32'),
('D22_TH02', 'D22_TH02', 'CNTT', '2025-10-13 05:00:32', '2025-10-13 05:00:32'),
('D22_TH03', 'D22_TH03', 'CNTT', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
('D22_TH04', 'D22_TH04', 'CNTT', '2025-10-13 05:00:32', '2025-10-13 05:00:32'),
('D22_TH05', 'D22_TH05', 'CNTT', '2025-10-13 05:00:32', '2025-10-13 05:00:32'),
('D22_TH06', 'D22_TH06', 'CNTT', '2025-10-13 05:00:32', '2025-10-13 05:00:32'),
('D22_TH07', 'D22_TH07', 'CNTT', '2025-10-13 05:00:32', '2025-10-13 05:00:32'),
('D22_TH08', 'D22_TH08', 'CNTT', '2025-10-13 05:00:32', '2025-10-13 05:00:32'),
('D22_TH09', 'D22_TH09', 'CNTT', '2025-10-13 05:00:32', '2025-10-13 05:00:32'),
('D22_TH10', 'D22_TH10', 'CNTT', '2025-10-13 05:00:32', '2025-10-13 05:00:32'),
('D22_TH11', 'D22_TH11', 'CNTT', '2025-10-13 05:00:32', '2025-10-13 05:00:32'),
('D22_TH12', 'D22_TH12', 'CNTT', '2025-10-13 05:00:32', '2025-10-13 05:00:32'),
('D22_TH13', 'D22_TH13', 'CNTT', '2025-10-13 05:00:32', '2025-10-13 05:00:32'),
('D22_TH14', 'D22_TH14', 'CNTT', '2025-10-13 05:00:32', '2025-10-13 05:00:32'),
('D22_TH15', 'D22_TH15', 'CNTT', '2025-10-13 05:00:32', '2025-10-13 05:00:32');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `exam_schedules`
--

DROP TABLE IF EXISTS `exam_schedules`;
CREATE TABLE IF NOT EXISTS `exam_schedules` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exam_date` date NOT NULL,
  `exam_time` time NOT NULL,
  `duration` int NOT NULL,
  `room` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_exam_schedule_natural_key` (`subject_code`,`exam_date`,`exam_time`,`room`),
  KEY `fk_exam_schedules_subject` (`subject_code`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `exam_schedules`
--

INSERT INTO `exam_schedules` (`id`, `subject_code`, `exam_date`, `exam_time`, `duration`, `room`, `note`, `created_at`, `updated_at`) VALUES
(5, 'CS03001', '2025-11-05', '10:00:00', 60, 'C703', NULL, '2025-11-04 19:35:13', '2025-11-04 19:35:13'),
(6, 'CS03001', '2025-05-11', '10:00:00', 60, 'C703', NULL, '2025-11-04 20:43:06', '2025-11-04 20:43:06'),
(7, 'CS03007', '2025-11-05', '07:30:00', 90, 'C701', 'Ca thi giữa kỳ', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(8, 'CS03008', '2025-11-05', '07:30:00', 90, 'C702', 'Ca thi giữa kỳ', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(9, 'CS03009', '2025-11-05', '09:30:00', 90, 'C703', 'Ca thi giữa kỳ', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(10, 'CS03013', '2025-11-05', '09:30:00', 90, 'C704', 'Ca thi giữa kỳ', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(11, 'CS03015', '2025-11-05', '13:30:00', 90, 'C705', 'Ca thi cuối kỳ', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(12, 'CS03017', '2025-11-06', '07:30:00', 90, 'C706', 'Ca thi cuối kỳ', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(13, 'CS03020', '2025-11-06', '07:30:00', 90, 'C707', 'Ca thi giữa kỳ', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(14, 'CS03036', '2025-11-06', '09:30:00', 90, 'C708', 'Ca thi cuối kỳ', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(15, 'CS09009', '2025-11-06', '13:30:00', 90, 'C709', 'Ca thi giữa kỳ', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(16, 'CS09010', '2025-11-06', '13:30:00', 90, 'C710', 'Ca thi cuối kỳ', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(17, 'CS03005', '2025-11-12', '07:30:00', 90, 'C701', 'Ca thi giữa kỳ', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(18, 'CS03022', '2025-11-12', '07:30:00', 90, 'C702', 'Ca thi giữa kỳ', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(19, 'CS03023', '2025-11-12', '09:30:00', 90, 'C703', 'Ca thi cuối kỳ', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(20, 'CS03024', '2025-11-12', '09:30:00', 90, 'C704', 'Ca thi giữa kỳ', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(21, 'CS03026', '2025-11-12', '13:30:00', 90, 'C705', 'Ca thi cuối kỳ', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(22, 'CS03033', '2025-11-12', '13:30:00', 90, 'C706', 'Ca thi giữa kỳ', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(23, 'CS03037', '2025-11-13', '07:30:00', 90, 'C707', 'Ca thi cuối kỳ', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(24, 'CS03038', '2025-11-13', '07:30:00', 90, 'C708', 'Ca thi giữa kỳ', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(25, 'CS03042', '2025-11-13', '09:30:00', 90, 'C709', 'Ca thi cuối kỳ', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(26, 'CS03043', '2025-11-13', '09:30:00', 90, 'C710', 'Ca thi giữa kỳ', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(27, 'CS03045', '2025-11-13', '13:30:00', 90, 'C701', 'Ca thi cuối kỳ', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(28, 'CS03057', '2025-11-13', '13:30:00', 90, 'C702', 'Ca thi giữa kỳ', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(30, 'CS03001', '2025-11-21', '07:30:00', 90, 'C701', 'Ca thi giữa kỳ', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(31, 'CS03002', '2025-11-21', '07:30:00', 90, 'C702', 'Ca thi thực hành', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(32, 'CS03003', '2025-11-21', '09:30:00', 90, 'C703', 'Ca thi cuối kỳ', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(33, 'CS03004', '2025-11-21', '09:30:00', 90, 'C704', 'Ca thi thực hành', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(34, 'CS03005', '2025-11-21', '13:30:00', 90, 'C705', 'Ca thi giữa kỳ', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(35, 'CS03007', '2025-11-21', '13:30:00', 90, 'C706', 'Ca thi cuối kỳ', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(36, 'CS03008', '2025-11-21', '15:30:00', 90, 'C707', 'Ca thi giữa kỳ', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(37, 'CS03009', '2025-11-21', '15:30:00', 90, 'C708', 'Ca thi cuối kỳ', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(38, 'CS03013', '2025-11-22', '07:30:00', 90, 'C701', 'Ca thi giữa kỳ', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(39, 'CS03015', '2025-11-22', '07:30:00', 90, 'C702', 'Ca thi cuối kỳ', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(40, 'CS03017', '2025-11-22', '09:30:00', 90, 'C703', 'Ca thi giữa kỳ', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(41, 'CS03020', '2025-11-22', '09:30:00', 90, 'C704', 'Ca thi cuối kỳ', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(42, 'CS09009', '2025-11-22', '13:30:00', 90, 'C705', 'Ca thi mạng máy tính', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(43, 'CS09010', '2025-11-22', '13:30:00', 90, 'C706', 'Ca thi phân tích thiết kế', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(44, 'GS33001', '2025-11-22', '15:30:00', 90, 'C707', 'Ca thi Toán A1', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(45, 'GS33002', '2025-11-22', '15:30:00', 90, 'C708', 'Ca thi Toán A2', '2025-11-21 09:20:42', '2025-11-21 09:20:42'),
(47, 'CS03003', '2025-11-23', '17:00:00', 90, 'C702', 'Ca thi bổ sung chiều', '2025-11-22 05:55:51', '2025-11-22 05:55:51'),
(48, 'CS03005', '2025-11-24', '13:00:00', 120, 'C703', 'Ca thi bổ sung chiều', '2025-11-22 05:55:51', '2025-11-22 05:55:51'),
(50, 'CS03057', '2025-11-26', '20:53:53', 90, 'PM4', NULL, '2025-12-09 13:52:55', '2025-12-09 13:52:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `exam_supervisors`
--

DROP TABLE IF EXISTS `exam_supervisors`;
CREATE TABLE IF NOT EXISTS `exam_supervisors` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `exam_schedule_id` bigint UNSIGNED NOT NULL,
  `lecturer_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_exam_supervisors_schedule` (`exam_schedule_id`),
  KEY `fk_exam_supervisors_lecturer` (`lecturer_code`)
) ENGINE=InnoDB AUTO_INCREMENT=177 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `exam_supervisors`
--

INSERT INTO `exam_supervisors` (`id`, `exam_schedule_id`, `lecturer_code`, `created_at`, `updated_at`) VALUES
(12, 10, '10', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(13, 10, '11', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(14, 10, '12', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(15, 11, '13', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(16, 11, '14', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(17, 11, '15', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(18, 12, '16', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(19, 12, '17', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(20, 12, '18', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(21, 13, '19', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(22, 13, '20', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(23, 13, '21', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(24, 14, '22', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(25, 14, '23', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(26, 14, '24', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(27, 15, '25', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(28, 15, '26', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(29, 15, '27', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(30, 16, '28', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(31, 16, '1', '2025-11-05 04:44:15', '2025-11-05 04:44:15'),
(34, 17, '2', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(35, 17, '3', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(36, 17, '4', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(37, 18, '5', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(38, 18, '6', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(39, 18, '7', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(40, 19, '8', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(41, 19, '9', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(42, 19, '10', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(43, 20, '11', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(44, 20, '12', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(45, 20, '13', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(46, 21, '14', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(47, 21, '15', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(48, 21, '16', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(49, 22, '17', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(50, 22, '18', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(51, 22, '19', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(52, 23, '20', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(53, 23, '21', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(54, 23, '22', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(55, 24, '23', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(56, 24, '24', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(57, 24, '25', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(58, 25, '26', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(59, 25, '27', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(60, 25, '28', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(61, 26, '1', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(62, 26, '2', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(63, 26, '3', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(64, 27, '4', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(65, 27, '5', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(66, 27, '6', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(67, 28, '7', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(68, 28, '8', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(69, 28, '9', '2025-11-11 04:38:56', '2025-11-11 04:38:56'),
(118, 30, '1', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(119, 30, '2', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(120, 30, '3', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(121, 31, '4', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(122, 31, '5', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(123, 31, '6', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(124, 32, '7', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(125, 32, '8', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(126, 32, '9', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(127, 33, '10', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(128, 33, '11', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(129, 33, '12', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(130, 34, '13', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(131, 34, '14', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(132, 34, '15', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(133, 35, '16', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(134, 35, '17', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(135, 35, '18', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(136, 36, '19', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(137, 36, '20', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(138, 36, '21', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(139, 37, '22', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(140, 37, '23', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(141, 37, '24', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(142, 38, '25', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(143, 38, '26', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(144, 38, '27', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(145, 39, '28', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(146, 39, '1', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(147, 39, '2', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(148, 40, '3', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(149, 40, '4', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(150, 40, '5', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(151, 41, '6', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(152, 41, '7', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(153, 41, '8', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(154, 42, '9', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(155, 42, '10', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(156, 42, '11', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(157, 43, '12', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(158, 43, '13', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(159, 43, '14', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(160, 44, '15', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(161, 44, '16', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(162, 44, '17', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(163, 45, '18', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(164, 45, '19', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(165, 45, '20', '2025-11-21 09:36:39', '2025-11-21 09:36:39'),
(169, 47, '2', '2025-11-22 05:55:51', '2025-11-22 05:55:51'),
(170, 47, '4', '2025-11-22 05:55:51', '2025-11-22 05:55:51'),
(171, 47, '6', '2025-11-22 05:55:51', '2025-11-22 05:55:51'),
(172, 48, '7', '2025-11-22 05:55:51', '2025-11-22 05:55:51'),
(173, 48, '8', '2025-11-22 05:55:51', '2025-11-22 05:55:51'),
(174, 48, '10', '2025-11-22 05:55:51', '2025-11-22 05:55:51');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `faculties`
--

DROP TABLE IF EXISTS `faculties`;
CREATE TABLE IF NOT EXISTS `faculties` (
  `faculty_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`faculty_code`),
  UNIQUE KEY `faculties_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `faculties`
--

INSERT INTO `faculties` (`faculty_code`, `name`, `created_at`, `updated_at`) VALUES
('CNTT', 'Công nghệ Thông tin', '2025-10-07 14:34:58', '2025-10-07 14:34:58'),
('QTKD', 'Quản trị Kinh doanh', '2025-10-07 14:34:58', '2025-10-07 14:34:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lecturers`
--

DROP TABLE IF EXISTS `lecturers`;
CREATE TABLE IF NOT EXISTS `lecturers` (
  `lecturer_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `faculty_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`lecturer_code`),
  UNIQUE KEY `lecturers_user_id_unique` (`user_id`),
  UNIQUE KEY `lecturers_email_unique` (`email`),
  KEY `fk_lecturers_faculty` (`faculty_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `lecturers`
--

INSERT INTO `lecturers` (`lecturer_code`, `user_id`, `full_name`, `email`, `phone`, `faculty_code`, `created_at`, `updated_at`) VALUES
('1', 19, 'Ngô Xuân Bách', 'ngoxuanbach@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('10', 28, 'Trần Văn Hùng', 'tranvanhung@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('11', 29, 'Nguyễn Ngọc Lâm', 'nguyenngoclam@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('12', 30, 'Hồ Đình Khả', 'hodinhkha@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('13', 31, 'Trần Thị Mỹ Huỳnh', 'tranthimyhuynh@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('14', 32, 'Nguyễn Trọng Nghĩa', 'nguyentrongnghia@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('15', 33, 'Nguyễn Kiều Oanh', 'nguyenkieuoanh@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('16', 34, 'Hoàng Xuân Phương', 'hoangxuanphuong@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('17', 35, 'Đặng Trường Sơn', 'dangtruongson@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('18', 36, 'Trần Trung Tâm', 'trantrungtam@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('19', 37, 'Nguyễn Trần Phúc Thịnh', 'nguyentranphucthinh@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('2', 20, 'Bùi Nhật Bằng', 'buinhatbang@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('20', 38, 'Nguyễn Lạc An Thư', 'nguyenlacanthu@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('21', 39, 'Dương Thái Thương', 'duongthaIthuong@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('22', 40, 'Ngô Thị Bảo Trân', 'ngothibaotran@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('23', 41, 'Hà Vũ Tuân', 'havutuan@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('24', 42, 'Nguyễn Thanh Tùng', 'nguyenthanhtung@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('25', 43, 'Lương An Vinh', 'luonganvinh@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('26', 44, 'Hà Anh Vũ', 'haanhvu@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('27', 45, 'Nguyễn Thị Thanh Xuân', 'nguyenthithanhxuan@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('28', 46, 'Trần Thị Như Ý', 'tranthinhuy@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('3', 21, 'Đoàn Trình Dục', 'doantrInhduc@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('4', 22, 'Lê Kim Dung', 'lekimdung@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('5', 23, 'Lê Thị Mỹ Dung', 'lethimydung@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('6', 24, 'Trịnh Thanh Duy', 'trinhthanhduy@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('7', 25, 'Lê Triệu Ngọc Đức', 'letrieunagocduc@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('8', 26, 'Hồ Hải', 'hohai@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13'),
('9', 27, 'Nguyễn Thái Hoà', 'nguyenthaihoa@stu.edu.vn', NULL, 'CNTT', '2025-11-04 20:02:13', '2025-11-04 20:02:13');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `students`
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE IF NOT EXISTS `students` (
  `student_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`student_code`),
  UNIQUE KEY `students_email_unique` (`email`),
  UNIQUE KEY `students_phone_unique` (`phone`),
  KEY `fk_students_class` (`class_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `students`
--

INSERT INTO `students` (`student_code`, `class_code`, `full_name`, `email`, `phone`, `created_at`, `updated_at`) VALUES
('DH52002062', 'D20_TH02', 'Phan Thanh Thúy', 'DH52002062@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52103676', 'D21_TH07', 'Dương Yến Vy', 'DH52103676@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52110819', 'D21_TH06', 'Lý Tuấn Đức', 'DH52110819@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52200319', 'D22_TH03', 'Bùi Mai Trâm Anh', 'DH52200319@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52200350', 'D22_TH03', 'Châu Hoàng Gia Bảo', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200365', 'D22_TH03', 'Nguyễn Đặng Quốc Bảo', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200384', 'D22_TH03', 'Bùi Tuấn Chương', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200414', 'D22_TH13', 'Nguyễn Thế Chương', 'DH52200414@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52200418', 'D22_TH03', 'Phan Văn Thế Clarent', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200424', 'D22_TH03', 'Trần Hữu Đăng', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200439', 'D22_TH03', 'Nguyễn Hải Đăng', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200448', 'D22_TH03', 'Nguyễn Vũ Thành Đạt', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200453', 'D22_TH03', 'Phan Tất Thành Đạt', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200490', 'D22_TH03', 'Nguyễn Tiến Đạt', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200497', 'D22_TH03', 'Phạm Minh Đạt', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200499', 'D22_TH03', 'Trần Tuấn Đạt', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200529', 'D22_TH03', 'Bùi Hoàng Đức Dũng', 'dh5200529@student.stu.edu.vn', '0975108384', '2025-10-10 01:15:18', '2025-11-22 12:36:16'),
('DH52200539', 'D22_TH03', 'Phạm Quang Dũng', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200543', 'D22_TH03', 'Phù Hữu Dũng', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200558', 'D22_TH03', 'Quản Trương Duy', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200589', 'D22_TH03', 'Nguyễn Hoàng Giang', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200602', 'D22_TH03', 'Phạm Trường Giang', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200608', 'D22_TH03', 'Trịnh Minh Giàu', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200613', 'D22_TH03', 'Phan Thị Mỹ Hà', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200616', 'D22_TH03', 'Hứa khắc Hải', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200640', 'D22_TH07', 'Tô Nhật Hào', 'DH52200640@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52200645', 'D22_TH03', 'Nguyễn Tấn Hậu', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200662', 'D22_TH03', 'Nguyễn Minh Hiền', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200669', 'D22_TH03', 'Nguyễn Minh Hiếu', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200673', 'D22_TH03', 'Bùi Khải Hiệp', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200681', 'D22_TH03', 'Ngô Trần Trung Hiếu', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200687', 'D22_TH03', 'Nguyễn Thanh Hiếu', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200699', 'D22_TH03', 'Trương Ân Hoa', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200701', 'D22_TH03', 'Trần Tấn Hòan', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200705', 'D22_TH03', 'Lê Ngọc Hoàng', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200706', 'D22_TH07', 'Lê Nguyễn Huy Hoàng', 'DH52200706@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52200771', 'D22_TH03', 'Nguyễn Thái Hòang', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200806', 'D22_TH03', 'Trần Nguyên Quốc Huy', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200815', 'D22_TH03', 'Nguyễn Văn Hùng', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200844', 'D22_TH03', 'Lê Lê Minh Hưng', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200860', 'D22_TH03', 'Phạm Lý Thị Hương', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200880', 'D22_TH03', 'Trương Gia Khang', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200901', 'D22_TH03', 'Dương Nguyễn Đông Khoa', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200910', 'D22_TH03', 'Hoàng Đăng Khoa', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200915', 'D22_TH03', 'Phan Anh Khoa', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200962', 'D22_TH03', 'Đặng Thái Lâm', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52200986', 'D22_TH03', 'Nguyễn Thế Linh', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201...', 'D22_TH03', 'Nguyễn Tâm Chi Uyên', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201018', 'D22_TH03', 'Doãn Sán Văn Long', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201065', 'D22_TH03', 'Trần Trúc Ly', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201066', 'D22_TH03', 'Trịnh Nhật Minh', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201069', 'D22_TH03', 'Lê Hoàng Nhật Minh', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201070', 'D22_TH03', 'Nguyễn Thị Trúc My', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201088', 'D22_TH03', 'Huỳnh Thanh Nam', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201095', 'D22_TH03', 'Nguyễn Sùng Ngân', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201104', 'D22_TH07', 'Trần Tuấn Nghĩa', 'DH52201104@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201123', 'D22_TH07', 'Phạm Văn Nhật Nguyên', 'DH52201123@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201148', 'D22_TH03', 'Lê Thành Nhân', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201201', 'D22_TH03', 'Trần Gia Phát', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201241', 'D22_TH03', 'Nguyễn Hoàng Phúc', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201249', 'D22_TH03', 'Nguyễn Tường Phúc', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201253', 'D22_TH03', 'Phan Hữu Phúc', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201259', 'D22_TH07', 'Trần Trọng Phúc', 'DH52201259@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201265', 'D22_TH07', 'Lê Đặng Hải Phục', 'DH52201265@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201267', 'D22_TH07', 'Đỗ Hoàng Phước', 'DH52201267@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201268', 'D22_TH07', 'Nguyễn Đình Phước', 'DH52201268@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201271', 'D22_TH07', 'Trần Hữu Phước', 'DH52201271@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201275', 'D22_TH03', 'Đặng Ngọc Thanh Phụng', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201290', 'D22_TH03', 'Nguyễn Hồng Quân', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201305', 'D22_TH13', 'Nguyễn Đức Quang', 'DH52201305@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201307', 'D22_TH03', 'Nguyễn Phúc Tòan Quân', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201316', 'D22_TH03', 'Trần Nhựt Quang', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201326', 'D22_TH07', 'Trần Ái Quốc', 'DH52201326@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201345', 'D22_TH03', 'Nguyễn Ngọc Quý', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201358', 'D22_TH03', 'Bùi Gia Sang', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201373', 'D22_TH04', 'Phạm Ngọc Sơn', 'DH52201373@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201380', 'D22_TH07', 'Bùi Minh Tài', 'DH52201380@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201386', 'D22_TH09', 'Nguyễn Đức Tài', 'DH52201386@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201393', 'D22_TH03', 'Nguyễn Xuân Tài', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201408', 'D22_TH13', 'Hà Võ Thanh Tân', 'DH52201408@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201410', 'D22_TH03', 'Đặng Minh Tân', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201412', 'D22_TH03', 'Trần Thanh Tân', 'DH52201412@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201416', 'D22_TH03', 'Nguyễn Hữu Tấn', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201419', 'D22_TH03', 'Nguyễn Quốc Thái', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201441', 'D22_TH03', 'Chung Nguyễn Quốc Thắng', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201443', 'D22_TH07', 'Trương Minh Thắng', 'DH52201443@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201448', 'D22_TH03', 'Lữ Chí Thành', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201451', 'D22_TH03', 'Tản Khuê Thành', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201469', 'D22_TH03', 'Lê Viết Thành', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201475', 'D22_TH03', 'Nguyễn Hoàng Phương Thảo', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201484', 'D22_TH07', 'Huỳnh Quang Thiện', 'DH52201484@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201487', 'D22_TH07', 'Trần Chí Thiện', 'DH52201487@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201506', 'D22_TH03', 'Tạ Lê Minh Thư', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201526', 'D22_TH03', 'Trần Phạm Minh Thư', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201529', 'D22_TH07', 'Châu Thanh Thuận', 'DH52201529@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201590', 'D22_TH03', 'Nguyễn Hữu Tín', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201597', 'D22_TH03', 'Trần Hữu Trí Tín', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201625', 'D22_TH07', 'Nguyễn Đình Trí', 'DH52201625@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201645', 'D22_TH07', 'Hồ Minh Triệu', 'DH52201645@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201646', 'D22_TH03', 'Nguyễn Minh Triều', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201656', 'D22_TH07', 'Đặng Võ Quốc Trọng', 'DH52201656@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201659', 'D22_TH03', 'Đào Trung Trực', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201699', 'D22_TH03', 'Nguyễn Thị Cẩm Tú', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201707', 'D22_TH07', 'Lê Dương Anh Tuấn', 'DH52201707@student.stu.edu.vn', NULL, '2025-11-26 01:34:41', '2025-11-26 01:34:41'),
('DH52201708', 'D22_TH03', 'Lê Minh Tuấn', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201713', 'D22_TH03', 'Nguyễn Hoàng Anh Tuấn', NULL, NULL, '2025-11-05 00:27:45', '2025-11-05 00:27:45'),
('DH52201724', 'D22_TH03', 'Võ Hoàng Tuấn', 'dh52005@student.stu.edu.vn', '0975108387', '2025-11-25 03:05:38', '2025-11-25 03:05:44');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `student_photos`
--

DROP TABLE IF EXISTS `student_photos`;
CREATE TABLE IF NOT EXISTS `student_photos` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_student_photos_student` (`student_code`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `student_photos`
--

INSERT INTO `student_photos` (`id`, `student_code`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'DH52200529', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/DH52200529.jpg', NULL, NULL),
(2, 'DH52201724', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/DH52201724.jpg', NULL, NULL),
(3, 'DH52200640', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/images_to_register/DH52200640.jpg', '2025-12-09 15:57:37', '2025-12-09 15:57:37'),
(4, 'DH52201104', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/images_to_register/DH52201104.jpg', '2025-12-09 15:57:37', '2025-12-09 15:57:37'),
(5, 'DH52201123', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/images_to_register/DH52201123.jpg', '2025-12-09 15:57:37', '2025-12-09 15:57:37'),
(6, 'DH52201267', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/images_to_register/DH52201267.jpg', '2025-12-09 15:57:37', '2025-12-09 15:57:37'),
(7, 'DH52201326', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/images_to_register/DH52201326.jpg', '2025-12-09 15:57:37', '2025-12-09 15:57:37'),
(8, 'DH52201412', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/images_to_register/DH52201412.jpg', '2025-12-09 15:57:37', '2025-12-09 15:57:37'),
(9, 'DH52201443', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/images_to_register/DH52201443.jpg', '2025-12-09 15:57:37', '2025-12-09 15:57:37'),
(10, 'DH52201487', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/images_to_register/DH52201487.jpg', '2025-12-09 15:57:37', '2025-12-09 15:57:37'),
(11, 'DH52201529', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/images_to_register/DH52201529.jpg', '2025-12-09 15:57:37', '2025-12-09 15:57:37'),
(12, 'DH52201625', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/images_to_register/DH52201625.jpg', '2025-12-09 15:57:37', '2025-12-09 15:57:37'),
(13, 'DH52201645', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/images_to_register/DH52201645.jpg', '2025-12-09 15:57:37', '2025-12-09 15:57:37'),
(14, 'DH52201656', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/images_to_register/DH52201656.jpg', '2025-12-09 15:57:37', '2025-12-09 15:57:37'),
(15, 'DH52201707', 'https://ducdung-student-images.s3.ap-southeast-1.amazonaws.com/images_to_register/DH52201707.jpg', '2025-12-09 15:57:37', '2025-12-09 15:57:37');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `subjects`
--

DROP TABLE IF EXISTS `subjects`;
CREATE TABLE IF NOT EXISTS `subjects` (
  `subject_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `credit` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`subject_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `subjects`
--

INSERT INTO `subjects` (`subject_code`, `name`, `credit`, `created_at`, `updated_at`) VALUES
('CS03001', 'Kỹ thuật số', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03002', 'Thí nghiệm Kỹ thuật số', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03003', 'Kỹ thuật lập trình', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03004', 'Thực hành Kỹ thuật lập trình', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03005', 'Toán tin học', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03007', 'Cấu trúc dữ liệu và thuật giải', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03008', 'Cơ sở dữ liệu', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03009', 'Hệ điều hành', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03010', 'Thực hành Cấu trúc dữ liệu và thuật giải', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03011', 'Thực hành Cơ sở dữ liệu', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03012', 'Thực hành Hệ điều hành', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03013', 'Công nghệ phần mềm', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03014', 'Đồ án tin học', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03015', 'Lập trình hướng đối tượng', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03016', 'Thực hành Lập trình hướng đối tượng', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03017', 'Lập trình ứng dụng cơ sở dữ liệu', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03020', 'Quản trị cơ sở dữ liệu', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03022', 'Quản lý dự án', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03023', 'Thương mại điện tử', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03024', 'An ninh máy tính', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03025', 'Thực tập An ninh máy tính', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03026', 'Mã hóa ứng dụng', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03027', 'Thực hành Hệ quản trị cơ sơ dữ liệu', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03028', 'Thực hành Lập trình ứng dụng cơ sở dữ liệu', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03030', 'Đồ án Phân tích thiết kế hệ thống thông tin', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03033', 'Phát triển phần mềm nguồn mở', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03034', 'Thực hành Phát triển phần mềm nguồn mở', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03036', 'Lập trình Web', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03037', 'Lập trình Windows', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03038', 'Lập trình cho thiết bị di động', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03039', 'Thực hành Lập trình Web', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03040', 'Thực hành Lập trình Windows', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03041', 'Thực hành Lập trình cho thiết bị di động', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03042', 'Triển khai hệ thống thông tin', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03043', 'Xây dựng phần mềm Web', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03044', 'Xây dựng phần mềm Windows', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03045', 'Kiểm thử phần mềm', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03047', 'Nhập môn công tác kỹ sư', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03056', 'Thực tập nghề nghiệp', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03057', 'AI cơ bản và ứng dụng', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03058', 'Xây dựng phần mềm thiết bị di động', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS03153', 'Đồ án/Khóa luận tốt nghiệp', 5, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS09001', 'Nhập môn lập trình', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS09002', 'Thực hành Nhập môn lập trình', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS09003', 'Nhập môn Web và ứng dụng', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS09004', 'Thực hành Nhập môn Web và ứng dụng', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS09005', 'Nhập môn cấu trúc dữ liệu', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS09006', 'Tổ chức cấu trúc máy tính', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS09007', 'Thực hành Nhập môn cấu trúc dữ liệu', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS09008', 'Thực hành Tổ chức cấu trúc máy tính', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS09009', 'Mạng máy tính', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS09010', 'Phân tích thiết kế hệ thống thông tin', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('CS09151', 'Thực tập tốt nghiệp', 4, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS09011', 'KHXHNV_Đại cương văn hóa Việt Nam', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS09012', 'KHXHNV_Kỹ năng giao tiếp', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS09013', 'KHXHNV_Phương pháp luận sáng tạo', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS19001', 'Tiếng Anh 1', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS19002', 'Tiếng Anh 2', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS19003', 'Tiếng Anh 3', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS19004', 'Tiếng Anh 4', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS29001', 'Pháp luật Việt Nam đại cương', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS33001', 'Toán A1 (Hàm 1 biến, chuỗi)', 4, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS33002', 'Toán A2 (Hàm nhiều biến, giải tích vec tơ)', 4, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS33003', 'Toán A3 (Đại số tuyến tính)', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS43001', 'Vật lý 1', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS43002', 'Vật lý 2', 4, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS49004', 'Thí nghiệm Vật lý_Phần 1', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS49005', 'Thí nghiệm Vật lý_Phần 2', 1, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS59001', 'Tin học đại cương', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS59002', 'Thực hành Tin học đại cương', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS79005', 'Triết học Mác - Lênin', 3, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS79006', 'Kinh tế chính trị Mác - Lênin', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS79007', 'Chủ nghĩa xã hội khoa học', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS79008', 'Lịch sử Đảng cộng sản Việt Nam', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('GS79009', 'Tư tưởng Hồ Chí Minh', 2, '2025-10-19 08:39:20', '2025-10-19 08:39:20'),
('MI03002', 'Giáo dục quốc phòng (ĐH)', 0, '2025-10-19 08:39:20', '2025-10-19 08:39:20');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','lecturer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lecturer',
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `role`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'buihoangducdung04@gmail.com', '2025-10-07 14:34:58', 'admin', '$2y$12$Y44vscpBvEhKLyPdp5S2ee7A7FGuUGgrZfGW.vZANby6/9bN9TCFG', 'mrWyneb21qnFAKhbeQDehIaYEUiSZOvhXSD6lspWkQ3jZy4IoVmoJundlSTA', '2025-10-07 14:34:58', '2025-11-10 06:45:34'),
(7, 'Bùi Hoàng Đức Dũng', 'buihoangdung@gmail.com', NULL, 'lecturer', '$2y$12$4Sj2GTjYNSZkl6lLsy1ED.rmz4BARVKo5UYYqdEWhS.9ZemX97QGS', NULL, '2025-10-09 00:05:58', '2025-10-10 05:11:47'),
(12, 'Dung', 'user07@gmail.com', NULL, 'lecturer', '$2y$12$mJaj4rCpQy0dAxZCMWv.xu3VVJXiDUiEPDq/bdI.2.3D9CCl13QLa', NULL, '2025-10-12 05:48:51', '2025-10-12 05:48:51'),
(13, '123rr', 'buihoangdug@gmail.com', NULL, 'lecturer', '$2y$12$yvhYSZhll0YxhFS7t8KHdeExTQk.UsKbAaF7SBNq23xoYEhiCjeNu', NULL, '2025-10-12 05:52:30', '2025-10-12 05:52:30'),
(14, 'Dungg', 'admin@example.com', NULL, 'lecturer', '$2y$12$Ki1iipAooL/rNhW4MtbIFu5BFdG9pEBeDo1MwzAFdojbI7elBER8C', NULL, '2025-10-12 05:56:33', '2025-10-12 06:07:06'),
(16, 'ducdung', 'dh52005@student.stu.edu.vn', NULL, 'lecturer', '$2y$12$iFJeTYI8.Z1DmkCHTBYyq.6VQM5TSJ7DdvaVNSTl0yMl/DrNXXg.W', NULL, '2025-10-12 09:03:48', '2025-10-12 09:03:48'),
(17, 'Dung', 'dh55@student.stu.edu.vn', NULL, 'lecturer', '$2y$12$8hSNNrs.cJO3aU1w5O/LH.JE6xxsAWIVbSJ4IdVyXTdTQupP.JfIK', NULL, '2025-10-12 23:18:51', '2025-10-12 23:18:51'),
(18, 'ducdung', 'buihoangducdung004@gmail.com', NULL, 'admin', '$2y$12$CV7rh6Ti8HMqhZO4W2Z1m.t123zc7r0p9..cCXtg6BLbp.kFHSJHi', NULL, '2025-10-18 19:11:46', '2025-10-18 19:11:46'),
(19, 'Ngô Xuân Bách', 'ngoxuanbach@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(20, 'Bùi Nhật Bằng', 'buinhatbang@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(21, 'Đoàn Trình Dục', 'doantrinhduc@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(22, 'Lê Kim Dung', 'lekimdung@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(23, 'Lê Thị Mỹ Dung', 'lethimydung@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(24, 'Trịnh Thanh Duy', 'trinhthanhduy@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(25, 'Lê Triệu Ngọc Đức', 'letrieungocduc@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-21 02:53:29'),
(26, 'Hồ Hải', 'hohai@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(27, 'Nguyễn Thái Hoà', 'nguyenthaihoa@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(28, 'Trần Văn Hùng', 'tranvanhung@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(29, 'Nguyễn Ngọc Lâm', 'nguyenngoclam@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(30, 'Hồ Đình Khả', 'hodinhkha@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(31, 'Trần Thị Mỹ Huỳnh', 'tranthimyhuynh@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(32, 'Nguyễn Trọng Nghĩa', 'nguyentrongnghia@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(33, 'Nguyễn Kiều Oanh', 'nguyenkieuoanh@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(34, 'Hoàng Xuân Phương', 'hoangxuanphuong@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(35, 'Đặng Trường Sơn', 'dangtruongson@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(36, 'Trần Trung Tâm', 'trantrungtam@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(37, 'Nguyễn Trần Phúc Thịnh', 'nguyentranphucthinh@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(38, 'Nguyễn Lạc An Thư', 'nguyenlacanthu@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(39, 'Dương Thái Thương', 'duongthaIthuong@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(40, 'Ngô Thị Bảo Trân', 'ngothibaotran@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(41, 'Hà Vũ Tuân', 'havutuan@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(42, 'Nguyễn Thanh Tùng', 'nguyenthanhtung@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(43, 'Lương An Vinh', 'luonganvinh@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(44, 'Hà Anh Vũ', 'haanhvu@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(45, 'Nguyễn Thị Thanh Xuân', 'nguyenthithanhxuan@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(46, 'Trần Thị Như Ý', 'tranthinhuy@stu.edu.vn', NULL, 'lecturer', '$2y$12$H92d9ntm07WsVVEt1LfC5.iIDEu5cRLx6i/eiEMC12vzP3F1uuhpK', NULL, '2025-11-05 04:52:06', '2025-11-05 04:52:06'),
(47, 'ducdung', 'buihoangducdung111004@gmail.com', NULL, 'lecturer', '$2y$12$Ir348ytCFX3leOUB3HPFNOTi8IuGj2GIJm//.20fW2fjrlGXz3oKm', NULL, '2025-11-10 23:42:14', '2025-11-10 23:42:14');

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `fk_attendance_schedule` FOREIGN KEY (`exam_schedule_id`) REFERENCES `exam_schedules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attendance_student` FOREIGN KEY (`student_code`) REFERENCES `students` (`student_code`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_faculty_code_foreign` FOREIGN KEY (`faculty_code`) REFERENCES `faculties` (`faculty_code`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `exam_schedules`
--
ALTER TABLE `exam_schedules`
  ADD CONSTRAINT `fk_exam_schedules_subject` FOREIGN KEY (`subject_code`) REFERENCES `subjects` (`subject_code`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `exam_supervisors`
--
ALTER TABLE `exam_supervisors`
  ADD CONSTRAINT `fk_exam_supervisors_lecturer` FOREIGN KEY (`lecturer_code`) REFERENCES `lecturers` (`lecturer_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exam_supervisors_schedule` FOREIGN KEY (`exam_schedule_id`) REFERENCES `exam_schedules` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `lecturers`
--
ALTER TABLE `lecturers`
  ADD CONSTRAINT `fk_lecturers_faculty` FOREIGN KEY (`faculty_code`) REFERENCES `faculties` (`faculty_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lecturers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_class` FOREIGN KEY (`class_code`) REFERENCES `classes` (`class_code`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `student_photos`
--
ALTER TABLE `student_photos`
  ADD CONSTRAINT `fk_student_photos_student` FOREIGN KEY (`student_code`) REFERENCES `students` (`student_code`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
