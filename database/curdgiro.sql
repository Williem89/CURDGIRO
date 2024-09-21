/*
 Navicat Premium Dump SQL

 Source Server         : MyLocalHost
 Source Server Type    : MariaDB
 Source Server Version : 100432 (10.4.32-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : curdgiro

 Target Server Type    : MariaDB
 Target Server Version : 100432 (10.4.32-MariaDB)
 File Encoding         : 65001

 Date: 21/09/2024 21:50:03
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for data_giro
-- ----------------------------
DROP TABLE IF EXISTS `data_giro`;
CREATE TABLE `data_giro`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nogiro` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `namabank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `ac_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `AC_Name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `statusgiro` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 51 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of data_giro
-- ----------------------------
INSERT INTO `data_giro` VALUES (1, '898888', 'ABC', '09099922222', 'Test AKUN 1', 'Used', 'system', '2024-09-21 16:36:16');
INSERT INTO `data_giro` VALUES (2, '898889', 'ABC', '09099922222', 'Test AKUN 1', 'Unused', 'system', '2024-09-21 16:36:16');
INSERT INTO `data_giro` VALUES (3, '898890', 'ABC', '09099922222', 'Test AKUN 1', 'Unused', 'system', '2024-09-21 16:36:16');
INSERT INTO `data_giro` VALUES (4, '898891', 'ABC', '09099922222', 'Test AKUN 1', 'Unused', 'system', '2024-09-21 16:36:16');
INSERT INTO `data_giro` VALUES (5, '898892', 'ABC', '09099922222', 'Test AKUN 1', 'Unused', 'system', '2024-09-21 16:36:16');
INSERT INTO `data_giro` VALUES (6, '898893', 'ABC', '09099922222', 'Test AKUN 1', 'Unused', 'system', '2024-09-21 16:36:16');
INSERT INTO `data_giro` VALUES (7, '898894', 'ABC', '09099922222', 'Test AKUN 1', 'Unused', 'system', '2024-09-21 16:36:16');
INSERT INTO `data_giro` VALUES (8, '898895', 'ABC', '09099922222', 'Test AKUN 1', 'Unused', 'system', '2024-09-21 16:36:16');
INSERT INTO `data_giro` VALUES (9, '898896', 'ABC', '09099922222', 'Test AKUN 1', 'Unused', 'system', '2024-09-21 16:36:16');
INSERT INTO `data_giro` VALUES (10, '898897', 'ABC', '09099922222', 'Test AKUN 1', 'Unused', 'system', '2024-09-21 16:36:16');
INSERT INTO `data_giro` VALUES (11, '777111111', 'hjb', '9191919191', 'Test AKUN 3', 'Used', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (12, '777111112', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (13, '777111113', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (14, '777111114', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (15, '777111115', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (16, '777111116', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (17, '777111117', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (18, '777111118', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (19, '777111119', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (20, '777111120', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (21, '777111121', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (22, '777111122', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (23, '777111123', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (24, '777111124', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (25, '777111125', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (26, '777111126', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (27, '777111127', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (28, '777111128', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (29, '777111129', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (30, '777111130', 'hjb', '9191919191', 'Test AKUN 3', 'Unused', 'system', '2024-09-21 16:36:37');
INSERT INTO `data_giro` VALUES (31, '900000000', 'ABC', '3123123123123123', 'Test AKUN 2', 'Unused', 'system', '2024-09-21 18:25:06');
INSERT INTO `data_giro` VALUES (32, '900000001', 'ABC', '3123123123123123', 'Test AKUN 2', 'Unused', 'system', '2024-09-21 18:25:06');
INSERT INTO `data_giro` VALUES (33, '900000002', 'ABC', '3123123123123123', 'Test AKUN 2', 'Unused', 'system', '2024-09-21 18:25:06');
INSERT INTO `data_giro` VALUES (34, '900000003', 'ABC', '3123123123123123', 'Test AKUN 2', 'Unused', 'system', '2024-09-21 18:25:06');
INSERT INTO `data_giro` VALUES (35, '900000004', 'ABC', '3123123123123123', 'Test AKUN 2', 'Unused', 'system', '2024-09-21 18:25:06');
INSERT INTO `data_giro` VALUES (36, '888899999', 'KJH', '91919199191', 'Akun Test 4', 'Unused', 'system', '2024-09-21 18:36:59');
INSERT INTO `data_giro` VALUES (37, '888900000', 'KJH', '91919199191', 'Akun Test 4', 'Used', 'system', '2024-09-21 18:36:59');
INSERT INTO `data_giro` VALUES (38, '888900001', 'KJH', '91919199191', 'Akun Test 4', 'Unused', 'system', '2024-09-21 18:36:59');
INSERT INTO `data_giro` VALUES (39, '888900002', 'KJH', '91919199191', 'Akun Test 4', 'Unused', 'system', '2024-09-21 18:36:59');
INSERT INTO `data_giro` VALUES (40, '888900003', 'KJH', '91919199191', 'Akun Test 4', 'Unused', 'system', '2024-09-21 18:36:59');
INSERT INTO `data_giro` VALUES (41, '888900004', 'KJH', '91919199191', 'Akun Test 4', 'Unused', 'system', '2024-09-21 18:36:59');
INSERT INTO `data_giro` VALUES (42, '888900005', 'KJH', '91919199191', 'Akun Test 4', 'Unused', 'system', '2024-09-21 18:36:59');
INSERT INTO `data_giro` VALUES (43, '888900006', 'KJH', '91919199191', 'Akun Test 4', 'Unused', 'system', '2024-09-21 18:36:59');
INSERT INTO `data_giro` VALUES (44, '888900007', 'KJH', '91919199191', 'Akun Test 4', 'Unused', 'system', '2024-09-21 18:36:59');
INSERT INTO `data_giro` VALUES (45, '888900008', 'KJH', '91919199191', 'Akun Test 4', 'Unused', 'system', '2024-09-21 18:36:59');
INSERT INTO `data_giro` VALUES (46, '090', 'BNN', '7272727272272', 'Akun Test 5', 'Unused', 'system', '2024-09-21 18:37:32');
INSERT INTO `data_giro` VALUES (47, '091', 'BNN', '7272727272272', 'Akun Test 5', 'Used', 'system', '2024-09-21 18:37:32');
INSERT INTO `data_giro` VALUES (48, '092', 'BNN', '7272727272272', 'Akun Test 5', 'Unused', 'system', '2024-09-21 18:37:32');
INSERT INTO `data_giro` VALUES (49, '093', 'BNN', '7272727272272', 'Akun Test 5', 'Unused', 'system', '2024-09-21 18:37:32');
INSERT INTO `data_giro` VALUES (50, '094', 'BNN', '7272727272272', 'Akun Test 5', 'Used', 'system', '2024-09-21 18:37:32');

-- ----------------------------
-- Table structure for detail_giro
-- ----------------------------
DROP TABLE IF EXISTS `detail_giro`;
CREATE TABLE `detail_giro`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nogiro` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_giro` date NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `Nominal` int(11) NOT NULL,
  `nama_penerima` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `bank_penerima` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ac_penerima` int(20) NOT NULL,
  `StatGiro` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `tanggal_cair_giro` date NULL DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `nogiro`(`nogiro`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of detail_giro
-- ----------------------------
INSERT INTO `detail_giro` VALUES (1, '898888', '2024-09-19', '2024-09-19', 1000000, 'Test 1', 'Bank test 1', 1234567890, 'Settled', '*Optional Tulis Keterangan di Sini*', NULL, 'system', '2024-09-21 16:47:45');
INSERT INTO `detail_giro` VALUES (2, '777111111', '2024-09-20', '2024-09-20', 2000000, 'Test 2', 'Bank Test 2', 987654321, 'Issued', '*Optional Tulis Keterangan di Sini*', NULL, 'system', '2024-09-21 16:48:43');
INSERT INTO `detail_giro` VALUES (3, '091', '2024-09-23', '2024-09-23', 9000000, 'Akun Test 5', 'Bank Test 5', 2147483647, 'Issued', NULL, NULL, 'system', '2024-09-21 20:32:19');
INSERT INTO `detail_giro` VALUES (4, '094', '2024-09-24', '2024-09-24', 1000000, 'Akun Test kesekian', 'Bank Penerima Kesekian', 3456789, 'Issued', NULL, NULL, 'system', '2024-09-21 20:36:30');
INSERT INTO `detail_giro` VALUES (5, '888900000', '2024-09-24', '2024-09-24', 900000000, 'Tester Lain', 'Bank Penerima', 2147483647, 'Issued', 'Testing Again', NULL, 'system', '2024-09-21 20:37:18');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `UsrLevel` int(1) NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'active',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'itgel', '$2y$10$5icP2qx624M2.xa76rf5/.aRBDP4gLh0QmON4iOKmr9L5AYYmm/Yq', 'IT Avenger', 1, '2024-09-21 15:07:25', '2024-09-21 15:07:25', 'active');

SET FOREIGN_KEY_CHECKS = 1;
