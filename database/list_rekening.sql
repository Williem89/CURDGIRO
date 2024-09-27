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

 Date: 27/09/2024 14:39:18
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for list_rekening
-- ----------------------------
DROP TABLE IF EXISTS `list_rekening`;
CREATE TABLE `list_rekening`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_akun` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_bank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_akun` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_entitas` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id_entitas`(`id_entitas`) USING BTREE,
  CONSTRAINT `list_rekening_ibfk_1` FOREIGN KEY (`id_entitas`) REFERENCES `list_entitas` (`id_entitas`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of list_rekening
-- ----------------------------
INSERT INTO `list_rekening` VALUES (4, '12345678', 'Bank Central Asia', 'PT. Global Energi Lestari', 1);
INSERT INTO `list_rekening` VALUES (5, '4567890123', 'Bank Rakyat Indonesia', 'PT. Global Energi Lestari', 1);
INSERT INTO `list_rekening` VALUES (6, '58974444', 'Bank Negara Indonesia', 'PT. Global Energi Lestari', 1);
INSERT INTO `list_rekening` VALUES (7, '8077556688', 'Bank Central Asia', 'PT. Global Energi Lestari', 1);
INSERT INTO `list_rekening` VALUES (8, '8077556688', 'Bank Central Asia', 'PT. Global Energi Lestari', 1);
INSERT INTO `list_rekening` VALUES (9, '80555555555', 'Bank Central Asia', 'PT. Global Energi Lestari', 1);
INSERT INTO `list_rekening` VALUES (10, '234567890', 'Bank Negara Indonesia', 'PT. Global Energi Lestari', 1);
INSERT INTO `list_rekening` VALUES (11, '4546645465456465465', 'Bank Central Asia', 'PT. Global Energi Lestari', 1);

SET FOREIGN_KEY_CHECKS = 1;
