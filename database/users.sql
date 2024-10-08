/*
 Navicat Premium Data Transfer

 Source Server         : Server_IT
 Source Server Type    : MariaDB
 Source Server Version : 101108 (10.11.8-MariaDB-0ubuntu0.24.04.1)
 Source Host           : 10.10.10.20:3306
 Source Schema         : curdgiro

 Target Server Type    : MariaDB
 Target Server Version : 101108 (10.11.8-MariaDB-0ubuntu0.24.04.1)
 File Encoding         : 65001

 Date: 07/10/2024 08:28:54
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'itgel', '$2y$10$5icP2qx624M2.xa76rf5/.aRBDP4gLh0QmON4iOKmr9L5AYYmm/Yq', 'IT Avenger', 1, '2024-09-21 15:07:25', '2024-09-21 15:07:25', 'active');
INSERT INTO `users` VALUES (2, 'test', '$2y$10$oaixi4gnw2D7iLctEkAV1eBnLpyIv4VD5vOyCW8yOLnkwsKNLvzHO', 'test', 1, '2024-09-24 09:53:18', '2024-09-24 09:53:18', 'active');
INSERT INTO `users` VALUES (3, 'Febri ', '$2y$10$YAqMy7hvT6cGsD8PD3.vTuYJZF1MOH9t/1cGkGSNyPsdwU6aZVHuG', 'Febri Winday', 1, '2024-10-01 14:48:07', '2024-10-01 14:48:07', 'active');
INSERT INTO `users` VALUES (4, 'MAYA', '$2y$10$ZOrtmFpuoxkLA0sOgszC3OUImSny73x2O3a8phYGrVRGlQ7/NFsfW', 'LEASING_001', 1, '2024-10-03 17:11:00', '2024-10-03 17:11:00', 'active');

SET FOREIGN_KEY_CHECKS = 1;
