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

 Date: 27/09/2024 14:41:17
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for list_entitas
-- ----------------------------
DROP TABLE IF EXISTS `list_entitas`;
CREATE TABLE `list_entitas`  (
  `id_entitas` int(11) NOT NULL AUTO_INCREMENT,
  `nama_entitas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id_entitas`) USING BTREE,
  UNIQUE INDEX `nama_entitas`(`nama_entitas`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of list_entitas
-- ----------------------------
INSERT INTO `list_entitas` VALUES (1, 'PT. Global Energi Lestari', '');
INSERT INTO `list_entitas` VALUES (2, 'PT. Indo Agro Permata Permai', '');
INSERT INTO `list_entitas` VALUES (3, 'PT. Kelinci Karya Sampoerna', '');

SET FOREIGN_KEY_CHECKS = 1;
