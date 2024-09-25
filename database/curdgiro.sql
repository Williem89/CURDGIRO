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

 Date: 25/09/2024 18:37:28
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for data_giro
-- ----------------------------
DROP TABLE IF EXISTS `data_giro`;
CREATE TABLE `data_giro`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nogiro` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `namabank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `ac_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `ac_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `statusgiro` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'Unused',
  `created_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `jenis_giro` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `id_entitas` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `nogiro`(`nogiro`) USING BTREE,
  INDEX `id_entitas`(`id_entitas`) USING BTREE,
  CONSTRAINT `data_giro_ibfk_1` FOREIGN KEY (`id_entitas`) REFERENCES `list_entitas` (`id_entitas`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 117 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of data_giro
-- ----------------------------
INSERT INTO `data_giro` VALUES (1, '45678', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-24 14:01:03', '0', 1);
INSERT INTO `data_giro` VALUES (2, '45679', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-24 14:01:03', '0', 1);
INSERT INTO `data_giro` VALUES (3, '45680', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Used', 'itgel', '2024-09-24 14:01:03', '0', 1);
INSERT INTO `data_giro` VALUES (4, '45681', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-24 14:01:03', '0', 1);
INSERT INTO `data_giro` VALUES (5, '45682', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-24 14:01:03', '0', 1);
INSERT INTO `data_giro` VALUES (6, '45683', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Used', 'itgel', '2024-09-24 14:01:03', '0', 1);
INSERT INTO `data_giro` VALUES (7, '45684', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-24 14:01:03', '0', 1);
INSERT INTO `data_giro` VALUES (8, '45685', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-24 14:01:03', '0', 1);
INSERT INTO `data_giro` VALUES (9, '45686', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-24 14:01:03', '0', 1);
INSERT INTO `data_giro` VALUES (10, '45687', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-24 14:01:03', '0', 1);
INSERT INTO `data_giro` VALUES (11, '898888', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (12, '898889', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (13, '898890', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Used', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (14, '898891', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Used', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (15, '898892', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (16, '898893', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (17, '898894', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (18, '898895', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Used', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (19, '898896', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (20, '898897', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (21, '898898', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (22, '898899', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (23, '898900', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (24, '898901', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (25, '898902', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (26, '898903', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (27, '898904', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (28, '898905', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (29, '898906', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Used', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (30, '898907', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (31, '898908', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (32, '898909', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (33, '898910', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (34, '898911', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (35, '898912', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (36, '898913', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (37, '898914', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (38, '898915', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (39, '898916', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (40, '898917', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (41, '898918', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (42, '898919', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (43, '898920', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (44, '898921', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Used', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (45, '898922', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (46, '898923', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (47, '898924', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Used', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (48, '898925', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (49, '898926', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (50, '898927', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (51, '898928', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (52, '898929', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Used', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (53, '898930', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (54, '898931', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Used', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (55, '898932', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (56, '898933', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (57, '898934', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Used', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (58, '898935', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (59, '898936', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (60, '898937', 'Bank Negara Indonesia', '58974444', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 14:46:01', '0', 1);
INSERT INTO `data_giro` VALUES (61, '5687', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (62, '5688', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (63, '5689', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (64, '5690', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (65, '5691', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (66, '5692', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (67, '5693', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (68, '5694', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (69, '5695', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (70, '5696', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (71, '5697', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (72, '5698', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (73, '5699', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (74, '5700', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (75, '5701', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (76, '5702', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (77, '5703', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (78, '5704', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (79, '5705', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (80, '5706', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (81, '5707', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (82, '5708', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (83, '5709', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (84, '5710', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (85, '5711', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (86, '5712', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (87, '5713', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (88, '5714', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (89, '5715', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (90, '5716', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (91, '5717', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (92, '5718', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (93, '5719', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (94, '5720', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (95, '5721', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (96, '5722', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (97, '5723', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (98, '5724', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (99, '5725', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (100, '5726', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (101, '5727', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (102, '5728', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (103, '5729', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (104, '5730', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (105, '5731', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (106, '5732', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Used', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (107, '5733', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (108, '5734', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (109, '5735', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (110, '5736', 'Bank Central Asia', '80555555555', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 17:56:59', '0', 1);
INSERT INTO `data_giro` VALUES (111, 'abc001', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 18:30:38', '0', 1);
INSERT INTO `data_giro` VALUES (112, 'abc002', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 18:30:38', '0', 1);
INSERT INTO `data_giro` VALUES (113, 'wqe-001', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 18:32:12', '0', 1);
INSERT INTO `data_giro` VALUES (114, 'wqe-002', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 18:32:12', '0', 1);
INSERT INTO `data_giro` VALUES (115, 'wqe-003', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 18:32:12', '0', 1);
INSERT INTO `data_giro` VALUES (116, 'wqe-004', 'Bank Central Asia', '12345678', 'PT. Global Energy Lestari', 'Unused', 'itgel', '2024-09-25 18:32:12', '0', 1);

-- ----------------------------
-- Table structure for detail_giro
-- ----------------------------
DROP TABLE IF EXISTS `detail_giro`;
CREATE TABLE `detail_giro`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nogiro` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `tanggal_giro` date NULL DEFAULT NULL,
  `tanggal_jatuh_tempo` date NULL DEFAULT NULL,
  `Nominal` int(11) NULL DEFAULT NULL,
  `nama_penerima` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `bank_penerima` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `ac_penerima` int(20) NULL DEFAULT NULL,
  `StatGiro` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `PVRNo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `tanggal_cair_giro` date NULL DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `nogiro`(`nogiro`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 18 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of detail_giro
-- ----------------------------
INSERT INTO `detail_giro` VALUES (6, '45680', '2024-09-24', '2024-09-24', 1000, 'test 1 ', 'Bank Aladin Syariah', 123456, 'Seatle', 'Test1', NULL, '2024-09-25', 'system', '2024-09-24 22:18:13');
INSERT INTO `detail_giro` VALUES (7, '898931', '2024-08-14', '2024-08-14', 5000000, 'Tester 1', 'Bank Amar Indonesia', 987654, 'Issued', 'Testing Overdue', NULL, NULL, 'system', '2024-09-25 14:46:56');
INSERT INTO `detail_giro` VALUES (8, '898929', '2024-09-18', '2024-09-18', 6000000, 'test 2', 'Bank Aceh Syariah', 654321, 'Issued', 'Testing on this month', NULL, NULL, 'system', '2024-09-25 14:48:13');
INSERT INTO `detail_giro` VALUES (9, '898934', '2024-09-21', '2024-09-21', 8000000, 'Tester 3', 'Bank UOB Indonesia', 2147483647, 'Issued', 'Tester 3', NULL, NULL, 'system', '2024-09-25 14:49:00');
INSERT INTO `detail_giro` VALUES (10, '898891', '2024-09-27', '2024-09-27', 1000000, 'Testing 4', 'Bank HSBC Indonesia', 2147483647, 'Issued', 'Tester 4', NULL, NULL, 'system', '2024-09-25 14:49:45');
INSERT INTO `detail_giro` VALUES (11, '898924', '2024-09-25', '2024-09-25', 2147483647, 'test', 'Bank Amar Indonesia', 0, 'Issued', 'testing kesekian', NULL, NULL, 'system', '2024-09-25 14:50:16');
INSERT INTO `detail_giro` VALUES (12, '45683', '2024-09-25', '2024-09-25', 1000, 'asdsa', 'Bank Aladin Syariah', 12312321, 'Issued', '-', NULL, NULL, 'system', '2024-09-25 17:35:56');
INSERT INTO `detail_giro` VALUES (13, '898895', '2024-09-25', '2024-09-25', 2147483647, 'test', 'Bank Allo Indonesia', 2147483647, 'Issued', '-', NULL, NULL, 'system', '2024-09-25 17:47:09');
INSERT INTO `detail_giro` VALUES (14, '898890', '2024-09-25', '2024-09-25', 5000000, 'testing testing', 'Bank Aladin Syariah', 2147483647, 'Issued', '-', NULL, NULL, 'system', '2024-09-25 17:48:36');
INSERT INTO `detail_giro` VALUES (15, '5732', '2024-09-24', '2024-09-24', 2000, 'maya', 'Bank Negara Indonesia', 2147483647, 'Seatle', '-', NULL, '2024-09-25', 'system', '2024-09-25 17:57:39');
INSERT INTO `detail_giro` VALUES (16, '898906', '2024-09-17', '2024-09-17', 1000000000, 'Satria', 'Bank Central Asia', 1234567890, 'Issued', 'capcipcup', NULL, NULL, 'system', '2024-09-25 18:02:03');
INSERT INTO `detail_giro` VALUES (17, '898921', '2024-10-04', '2024-10-31', 8500, 'maya', 'Bank UOB Indonesia', 2147483647, 'Issued', 'hahahaha', NULL, NULL, 'system', '2024-09-25 18:03:56');

-- ----------------------------
-- Table structure for list_bank
-- ----------------------------
DROP TABLE IF EXISTS `list_bank`;
CREATE TABLE `list_bank`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_bank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 99 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of list_bank
-- ----------------------------
INSERT INTO `list_bank` VALUES (1, 'Bank Aceh Syariah');
INSERT INTO `list_bank` VALUES (2, 'Bank Aladin Syariah');
INSERT INTO `list_bank` VALUES (3, 'Bank Allo Indonesia');
INSERT INTO `list_bank` VALUES (4, 'Bank Amar Indonesia');
INSERT INTO `list_bank` VALUES (5, 'Bank ANZ Indonesia');
INSERT INTO `list_bank` VALUES (6, 'Bank Artha Graha Internasional');
INSERT INTO `list_bank` VALUES (7, 'Bank Banten');
INSERT INTO `list_bank` VALUES (8, 'Bank BCA Syariah');
INSERT INTO `list_bank` VALUES (9, 'Bank Bengkulu');
INSERT INTO `list_bank` VALUES (10, 'Bank BJB');
INSERT INTO `list_bank` VALUES (11, 'Bank BJB Syariah');
INSERT INTO `list_bank` VALUES (12, 'Bank BNP Paribas Indonesia');
INSERT INTO `list_bank` VALUES (13, 'Bank BPD Bali');
INSERT INTO `list_bank` VALUES (14, 'Bank BPD DIY');
INSERT INTO `list_bank` VALUES (15, 'Bank BRK Syariah');
INSERT INTO `list_bank` VALUES (16, 'Bank BSG');
INSERT INTO `list_bank` VALUES (17, 'Bank BTPN');
INSERT INTO `list_bank` VALUES (18, 'Bank BTPN Syariah');
INSERT INTO `list_bank` VALUES (19, 'Bank Bumi Arta');
INSERT INTO `list_bank` VALUES (20, 'Bank Capital Indonesia');
INSERT INTO `list_bank` VALUES (21, 'Bank Central Asia');
INSERT INTO `list_bank` VALUES (22, 'Bank China Construction Bank Indonesia');
INSERT INTO `list_bank` VALUES (23, 'Bank CIMB Niaga');
INSERT INTO `list_bank` VALUES (24, 'Bank CTBC Indonesia');
INSERT INTO `list_bank` VALUES (25, 'Bank Danamon Indonesia');
INSERT INTO `list_bank` VALUES (26, 'Bank DBS Indonesia');
INSERT INTO `list_bank` VALUES (27, 'Bank Digital BCA');
INSERT INTO `list_bank` VALUES (28, 'Bank DKI');
INSERT INTO `list_bank` VALUES (29, 'Bank Ganesha');
INSERT INTO `list_bank` VALUES (30, 'Bank Hana Indonesia');
INSERT INTO `list_bank` VALUES (31, 'Bank Hibank Indonesia');
INSERT INTO `list_bank` VALUES (32, 'Bank HSBC Indonesia');
INSERT INTO `list_bank` VALUES (33, 'Bank IBK Indonesia');
INSERT INTO `list_bank` VALUES (34, 'Bank ICBC Indonesia');
INSERT INTO `list_bank` VALUES (35, 'Bank Ina Perdana');
INSERT INTO `list_bank` VALUES (36, 'Bank Index Selindo');
INSERT INTO `list_bank` VALUES (37, 'Bank Jago');
INSERT INTO `list_bank` VALUES (38, 'Bank Jambi');
INSERT INTO `list_bank` VALUES (39, 'Bank Jasa Jakarta');
INSERT INTO `list_bank` VALUES (40, 'Bank Jateng');
INSERT INTO `list_bank` VALUES (41, 'Bank Jatim');
INSERT INTO `list_bank` VALUES (42, 'Bank J Trust Indonesia');
INSERT INTO `list_bank` VALUES (43, 'Bank Kalbar');
INSERT INTO `list_bank` VALUES (44, 'Bank Kalsel');
INSERT INTO `list_bank` VALUES (45, 'Bank Kalteng');
INSERT INTO `list_bank` VALUES (46, 'Bank Kaltimtara');
INSERT INTO `list_bank` VALUES (47, 'Bank KB Indonesia');
INSERT INTO `list_bank` VALUES (48, 'Bank KB Syariah');
INSERT INTO `list_bank` VALUES (49, 'Bank Krom Indonesia');
INSERT INTO `list_bank` VALUES (50, 'Bank Lampung');
INSERT INTO `list_bank` VALUES (51, 'Bank Maluku Malut');
INSERT INTO `list_bank` VALUES (52, 'Bank Mandiri');
INSERT INTO `list_bank` VALUES (53, 'Bank Mandiri Taspen');
INSERT INTO `list_bank` VALUES (54, 'Bank Maspion');
INSERT INTO `list_bank` VALUES (55, 'Bank Mayapada Internasional');
INSERT INTO `list_bank` VALUES (56, 'Bank Maybank Indonesia');
INSERT INTO `list_bank` VALUES (57, 'Bank Mega');
INSERT INTO `list_bank` VALUES (58, 'Bank Mega Syariah');
INSERT INTO `list_bank` VALUES (59, 'Bank Mestika Dharma');
INSERT INTO `list_bank` VALUES (60, 'Bank Mizuho Indonesia');
INSERT INTO `list_bank` VALUES (61, 'Bank MNC Internasional');
INSERT INTO `list_bank` VALUES (62, 'Bank Muamalat Indonesia');
INSERT INTO `list_bank` VALUES (63, 'Bank Multiarta Sentosa');
INSERT INTO `list_bank` VALUES (64, 'Bank Nagari');
INSERT INTO `list_bank` VALUES (65, 'Bank Nano Syariah');
INSERT INTO `list_bank` VALUES (66, 'Bank Nationalnobu');
INSERT INTO `list_bank` VALUES (67, 'Bank Negara Indonesia');
INSERT INTO `list_bank` VALUES (68, 'Bank Neo Commerce');
INSERT INTO `list_bank` VALUES (69, 'Bank NTB Syariah');
INSERT INTO `list_bank` VALUES (70, 'Bank NTT');
INSERT INTO `list_bank` VALUES (71, 'Bank OCBC Indonesia');
INSERT INTO `list_bank` VALUES (72, 'Bank of India Indonesia');
INSERT INTO `list_bank` VALUES (73, 'Bank Oke Indonesia');
INSERT INTO `list_bank` VALUES (74, 'Bank Panin');
INSERT INTO `list_bank` VALUES (75, 'Bank Panin Dubai Syariah');
INSERT INTO `list_bank` VALUES (76, 'Bank Papua');
INSERT INTO `list_bank` VALUES (77, 'Bank Permata');
INSERT INTO `list_bank` VALUES (78, 'Bank QNB Indonesia');
INSERT INTO `list_bank` VALUES (79, 'Bank Rakyat Indonesia');
INSERT INTO `list_bank` VALUES (80, 'Bank Raya Indonesia');
INSERT INTO `list_bank` VALUES (81, 'Bank Resona Perdania');
INSERT INTO `list_bank` VALUES (82, 'Bank Sahabat Sampoerna');
INSERT INTO `list_bank` VALUES (83, 'Bank SBI Indonesia');
INSERT INTO `list_bank` VALUES (84, 'Bank Seabank Indonesia');
INSERT INTO `list_bank` VALUES (85, 'Bank Shinhan Indonesia');
INSERT INTO `list_bank` VALUES (86, 'Bank Sinarmas');
INSERT INTO `list_bank` VALUES (87, 'Bank Sulselbar');
INSERT INTO `list_bank` VALUES (88, 'Bank Sulteng');
INSERT INTO `list_bank` VALUES (89, 'Bank Sultra');
INSERT INTO `list_bank` VALUES (90, 'Bank Sumsel Babel');
INSERT INTO `list_bank` VALUES (91, 'Bank Sumut');
INSERT INTO `list_bank` VALUES (92, 'Bank Superbank Indonesia');
INSERT INTO `list_bank` VALUES (93, 'Bank Syariah Indonesia');
INSERT INTO `list_bank` VALUES (94, 'Bank Tabungan Negara');
INSERT INTO `list_bank` VALUES (95, 'Bank UOB Indonesia');
INSERT INTO `list_bank` VALUES (96, 'Bank Victoria Internasional');
INSERT INTO `list_bank` VALUES (97, 'Bank Victoria Syariah');
INSERT INTO `list_bank` VALUES (98, 'Bank Woori Saudara');

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
INSERT INTO `list_entitas` VALUES (1, 'PT. Global Energy Lestari', '');
INSERT INTO `list_entitas` VALUES (2, 'PT. Indo Agro Permata Permai', '');
INSERT INTO `list_entitas` VALUES (3, 'PT. Kelinci Karya Sampoerna', '');

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
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of list_rekening
-- ----------------------------
INSERT INTO `list_rekening` VALUES (4, '12345678', 'Bank Central Asia', 'PT. Global Energy Lestari', 1);
INSERT INTO `list_rekening` VALUES (5, '4567890123', 'Bank Rakyat Indonesia', 'PT. Global Energy Lestari', 1);
INSERT INTO `list_rekening` VALUES (6, '58974444', 'Bank Negara Indonesia', 'PT. Global Energy Lestari', 1);
INSERT INTO `list_rekening` VALUES (7, '8077556688', 'Bank Central Asia', 'PT. Global Energy Lestari', 1);
INSERT INTO `list_rekening` VALUES (8, '8077556688', 'Bank Central Asia', 'PT. Global Energy Lestari', 1);
INSERT INTO `list_rekening` VALUES (9, '80555555555', 'Bank Central Asia', 'PT. Global Energy Lestari', 1);

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
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'itgel', '$2y$10$5icP2qx624M2.xa76rf5/.aRBDP4gLh0QmON4iOKmr9L5AYYmm/Yq', 'IT Avenger', 1, '2024-09-21 15:07:25', '2024-09-21 15:07:25', 'active');
INSERT INTO `users` VALUES (2, 'test', '$2y$10$oaixi4gnw2D7iLctEkAV1eBnLpyIv4VD5vOyCW8yOLnkwsKNLvzHO', 'test', 1, '2024-09-24 09:53:18', '2024-09-24 09:53:18', 'active');

SET FOREIGN_KEY_CHECKS = 1;
