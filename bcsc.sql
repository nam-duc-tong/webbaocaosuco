-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2026 at 12:12 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bcsc`
--

-- --------------------------------------------------------

--
-- Table structure for table `baocao`
--

CREATE TABLE `baocao` (
  `id` int(11) NOT NULL,
  `nguoibaocao` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `hinhthuc` enum('tự nguyện','bắt buộc') DEFAULT 'tự nguyện',
  `diem_xay_ra_su_co` text DEFAULT NULL,
  `diem_xay_ra_su_co_khac` varchar(255) DEFAULT NULL,
  `nhom_su_co` enum('Sự cố y khoa','Sự cố ngoài y khoa') NOT NULL,
  `thoigian` date NOT NULL,
  `doi_tuong` text DEFAULT NULL,
  `doi_tuong_khac` varchar(100) DEFAULT NULL,
  `thong_tin_nguoi_benh` text DEFAULT NULL,
  `mo_ta_su_co` text NOT NULL,
  `tinh_chat_su_co` enum('Suýt xảy ra','Đã xảy ra') NOT NULL,
  `muc_do_su_co` enum('NC0','NC1','NC2','NC3') NOT NULL,
  `phan_loai_su_co` text DEFAULT NULL,
  `phan_loai_su_co_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`phan_loai_su_co_json`)),
  `thong_bao_cap_tren` enum('yes','no') DEFAULT 'no',
  `xu_ly_ban_dau` text NOT NULL,
  `giai_phap` text NOT NULL,
  `ghi_nhan_ho_so` enum('yes','no') DEFAULT 'no',
  `status` enum('pending','processing','resolved','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `baocao`
--

INSERT INTO `baocao` (`id`, `nguoibaocao`, `email`, `hinhthuc`, `diem_xay_ra_su_co`, `diem_xay_ra_su_co_khac`, `nhom_su_co`, `thoigian`, `doi_tuong`, `doi_tuong_khac`, `thong_tin_nguoi_benh`, `mo_ta_su_co`, `tinh_chat_su_co`, `muc_do_su_co`, `phan_loai_su_co`, `phan_loai_su_co_json`, `thong_bao_cap_tren`, `xu_ly_ban_dau`, `giai_phap`, `ghi_nhan_ho_so`, `status`, `admin_notes`, `processed_by`, `processed_at`, `created_at`, `updated_at`) VALUES
(4, 'Vũ Thị Phương Tuyết', 'benhvienmathoalu@gmail.com', 'bắt buộc', 'Phòng KHTH_QLCL', NULL, 'Sự cố ngoài y khoa', '2026-06-09', 'Nhân viên y tế', NULL, 'thong tin nguoi benh', 'Mô tả sự cố', 'Đã xảy ra', 'NC3', 'Hồ sơ bệnh án, tài liệu hành chính, Xét nghiệm, chẩn đoán hình ảnh', '[\"Hồ sơ bệnh án, tài liệu hành chính\",\"Xét nghiệm, chẩn đoán hình ảnh\"]', 'yes', 'Điều trị/ xử lý ban đầu', 'Đề xuất giải pháp', 'yes', 'pending', NULL, NULL, NULL, '2026-06-10 07:17:02', '2026-06-10 14:24:18'),
(5, 'Tống Đức Nam', 'nam.tong@visicare.com.vn', 'tự nguyện', 'Phòng Tài chính kế toán', NULL, 'Sự cố ngoài y khoa', '2026-06-10', 'Nhân viên y tế', NULL, 'thong tin nguoi benh', 'Mô tả sự cố', 'Đã xảy ra', 'NC0', 'Thuốc và dịch truyền, Hồ sơ bệnh án, tài liệu hành chính, Xét nghiệm, chẩn đoán hình ảnh', '[\"Thuốc và dịch truyền\",\"Hồ sơ bệnh án, tài liệu hành chính\",\"Xét nghiệm, chẩn đoán hình ảnh\"]', 'yes', 'Điều trị/ xử lý ban đầu', 'Đề xuất giải pháp', 'yes', 'processing', 'không thích duyệt', NULL, '2026-06-11 13:19:09', '2026-06-10 07:25:40', '2026-06-11 06:19:09'),
(7, 'Trần Hữu Vị', 'tranhuuvi99@gmail.com', 'bắt buộc', 'Địa điểm mục khác', 'Địa điểm mục khác', 'Sự cố ngoài y khoa', '2026-06-10', 'đối tượng mục khác', 'đối tượng mục khác', 'thong tin nguoi benh', 'Mô tả sự cố', 'Đã xảy ra', 'NC2', 'Thuốc và dịch truyền, Hồ sơ bệnh án, tài liệu hành chính, Xét nghiệm, chẩn đoán hình ảnh', '[\"Thuốc và dịch truyền\",\"Hồ sơ bệnh án, tài liệu hành chính\",\"Xét nghiệm, chẩn đoán hình ảnh\"]', 'yes', 'Điều trị/ xử lý ban đầu', 'Đề xuất giải pháp', 'yes', 'resolved', 'đã giải quyết xong !!!!', NULL, '2026-06-11 17:09:55', '2026-06-10 07:40:39', '2026-06-11 10:09:55'),
(8, 'Vũ Thị Thực', 'thuc.vu@visicare.com.vn', 'bắt buộc', 'Khoa Mắt Tổng Hợp', NULL, 'Sự cố ngoài y khoa', '2026-06-10', 'Người bệnh', NULL, 'Thông tin người bệnh: Vũ Thị Thực', 'Mô tả sự cố test', 'Đã xảy ra', 'NC2', 'Quy trình, kỹ thuật, thủ thuật chuyên môn, Thuốc và dịch truyền, Thiết bị y tế', '[\"Quy trình, kỹ thuật, thủ thuật chuyên môn\",\"Thuốc và dịch truyền\",\"Thiết bị y tế\"]', 'yes', 'đang tìm cách', 'trả lại thuốc', 'yes', 'pending', NULL, NULL, NULL, '2026-06-10 15:29:45', '2026-06-10 15:30:16'),
(9, 'Trần Thị Hoài', 'nam2382000@gmail.com', 'bắt buộc', 'Khoa Khám bệnh', NULL, 'Sự cố ngoài y khoa', '2026-06-11', 'Người nhà', NULL, 'thong tin nguoi benh', 'Mô tả sự cố', 'Đã xảy ra', 'NC3', 'Xét nghiệm, chẩn đoán hình ảnh, Nhiễm khuẩn bệnh viện', '[\"Xét nghiệm, chẩn đoán hình ảnh\",\"Nhiễm khuẩn bệnh viện\"]', '', 'Điều trị/ xử lý ban đầu', 'Đề xuất giải pháp', '', 'rejected', '', NULL, '2026-06-11 08:41:16', '2026-06-11 01:37:44', '2026-06-11 01:41:16'),
(10, 'Trần Thị Hoài 2', 'nam2382000@gmail.com', '', 'Khoa Khám bệnh', NULL, 'Sự cố y khoa', '2026-06-11', 'Nhân viên y tế', NULL, 'thong tin nguoi benh', 'Mô tả sự cố', 'Đã xảy ra', 'NC3', 'Thiết bị y tế, Tai nạn, chấn thương, té ngã, Nhiễm khuẩn bệnh viện', '[\"Thiết bị y tế\",\"Tai nạn, chấn thương, té ngã\",\"Nhiễm khuẩn bệnh viện\"]', '', 'Điều trị/ xử lý ban đầu', 'Đề xuất giải pháp', '', 'pending', NULL, NULL, NULL, '2026-06-11 01:39:53', '2026-06-11 01:39:53'),
(11, 'jinjin238', 'jinjin2382k2@gmail.com', 'bắt buộc', 'Khoa Mắt Tổng Hợp', NULL, 'Sự cố y khoa', '2026-06-11', 'Nhân viên y tế', NULL, 'thong tin nguoi benh', 'Mô tả sự cố', 'Đã xảy ra', 'NC3', 'Thuốc và dịch truyền, Nhiễm khuẩn bệnh viện', '[\"Thuốc và dịch truyền\",\"Nhiễm khuẩn bệnh viện\"]', '', 'Điều trị/ xử lý ban đầu', 'Đề xuất giải pháp', '', 'pending', NULL, NULL, NULL, '2026-06-11 03:06:37', '2026-06-11 03:06:37'),
(12, 'Đinh Thị Thu Thương', 'nam2382000@gmail.com', 'bắt buộc', 'Khoa Mắt Tổng Hợp', NULL, 'Sự cố ngoài y khoa', '2026-06-11', 'Người nhà', NULL, 'thong tin nguoi benh', 'Mô tả sự cố', 'Suýt xảy ra', 'NC3', 'Quy trình, kỹ thuật, thủ thuật chuyên môn, Thuốc và dịch truyền', '[\"Quy trình, kỹ thuật, thủ thuật chuyên môn\",\"Thuốc và dịch truyền\"]', '', 'Điều trị/ xử lý ban đầu', 'Đề xuất giải pháp', '', 'pending', NULL, NULL, NULL, '2026-06-11 04:18:55', '2026-06-11 04:18:55'),
(13, 'Phạm Trần Quốc Giang', 'giang.tran@visicare.com.vn', 'bắt buộc', 'Phòng Kĩ thuật Đà Lạt', NULL, 'Sự cố ngoài y khoa', '2026-06-11', 'muc khac', NULL, 'thong tin nguoi benh', 'Mô tả sự cố', 'Suýt xảy ra', 'NC0', 'Quy trình, kỹ thuật, thủ thuật chuyên môn, Thuốc và dịch truyền', '[\"Quy trình, kỹ thuật, thủ thuật chuyên môn\",\"Thuốc và dịch truyền\"]', '', 'Điều trị/ xử lý ban đầu', 'Đề xuất giải pháp', '', 'pending', NULL, NULL, NULL, '2026-06-11 06:59:46', '2026-06-11 06:59:46'),
(14, 'Giang Hồ', 'giang.pham@visicare.com.vn', 'tự nguyện', 'I tờ', NULL, 'Sự cố ngoài y khoa', '2026-06-11', 'Nhân viên y tế', NULL, 'Giang hồ', 'Sập Server', 'Suýt xảy ra', 'NC3', 'Thiết bị y tế', '[\"Thiết bị y tế\"]', '', 'trốn', 'trốn qua campuchia', '', 'pending', NULL, NULL, NULL, '2026-06-11 07:02:37', '2026-06-11 07:02:37'),
(15, 'giang hồ đà lạt', 'nam2382000@gmail.com', 'tự nguyện', 'Khoa Dược', NULL, 'Sự cố y khoa', '2026-06-11', 'Người bệnh', NULL, 'Thông tin người bệnh', 'Mô tả chi tiết sự cố', 'Suýt xảy ra', 'NC0', 'Thiết bị y tế, Tai nạn, chấn thương, té ngã, Nhiễm khuẩn bệnh viện', '[\"Thiết bị y tế\",\"Tai nạn, chấn thương, té ngã\",\"Nhiễm khuẩn bệnh viện\"]', 'yes', 'nét', 'trốn', 'yes', 'resolved', '', NULL, '2026-06-11 15:43:26', '2026-06-11 08:42:25', '2026-06-11 08:43:26');

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remember_tokens`
--

INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `created_at`) VALUES
(1, 1, '211fe064006d5b9a33d04d25be9ffacf9265a9e1b04faa4a714ae8fe96e7ad67', '2026-06-10 03:31:46'),
(2, 1, 'ff9e7e290aa83c4816c8756224066c3aad6e834d3be824d0dc9774bc31723778', '2026-06-10 03:51:30'),
(3, 1, '662fd6f7b86d740c64935d638e09479fe6d22fa3992af53d2219fa726ccaee46', '2026-06-10 03:52:55'),
(4, 1, '1540b597a27b3b1d31fbb97a22097a3fd4f2530240e2458b1ac08b49991718b1', '2026-06-10 14:12:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role`, `status`, `last_login`, `created_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'admin@example.com', 'Quản trị viên', 'admin', 'active', '2026-06-10 21:12:32', '2026-06-10 02:09:21'),
(2, 'user1', '6ad14ba9986e3615423dfca256d04e3f', 'user1@example.com', 'Tong Duc Nam', 'user', 'active', NULL, '2026-06-10 02:09:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `baocao`
--
ALTER TABLE `baocao`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `baocao`
--
ALTER TABLE `baocao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
