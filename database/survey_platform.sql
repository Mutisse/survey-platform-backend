-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 16, 2026 at 10:10 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: survey_platform
--

-- --------------------------------------------------------

--
-- Table structure for table academic_configurations
--

DROP TABLE IF EXISTS academic_configurations;
CREATE TABLE IF NOT EXISTS academic_configurations (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  type varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  value varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  label varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  is_active tinyint(1) NOT NULL DEFAULT '1',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY academic_configurations_type_value_unique (type,value),
  KEY academic_configurations_type_index (type),
  KEY academic_configurations_order_index (`order`)
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table academic_configurations
--

INSERT INTO academic_configurations (id, type, value, label, `order`, is_active, created_at, updated_at) VALUES
(1, 'institution_types', 'Universidade Pública', 'Universidade Pública', 1, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(2, 'institution_types', 'Universidade Privada', 'Universidade Privada', 2, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(3, 'institution_types', 'Instituto Superior', 'Instituto Superior', 3, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(4, 'institution_types', 'Escola Superior', 'Escola Superior', 4, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(5, 'institution_types', 'Outra', 'Outra', 5, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(6, 'courses', 'Engenharia Informática', 'Engenharia Informática', 1, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(7, 'courses', 'Medicina', 'Medicina', 2, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(8, 'courses', 'Direito', 'Direito', 3, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(9, 'courses', 'Economia', 'Economia', 4, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(10, 'courses', 'Administração e Gestão', 'Administração e Gestão', 5, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(11, 'courses', 'Contabilidade', 'Contabilidade', 6, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(12, 'courses', 'Enfermagem', 'Enfermagem', 7, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(13, 'courses', 'Arquitetura', 'Arquitetura', 8, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(14, 'courses', 'Engenharia Civil', 'Engenharia Civil', 9, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(15, 'courses', 'Psicologia', 'Psicologia', 10, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(16, 'courses', 'Sociologia', 'Sociologia', 11, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(17, 'courses', 'Ciências da Educação', 'Ciências da Educação', 12, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(18, 'courses', 'Biologia', 'Biologia', 13, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(19, 'courses', 'Química', 'Química', 14, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(20, 'courses', 'Matemática', 'Matemática', 15, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(21, 'courses', 'Física', 'Física', 16, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(22, 'courses', 'História', 'História', 17, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(23, 'courses', 'Geografia', 'Geografia', 18, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(24, 'courses', 'Línguas e Literaturas', 'Línguas e Literaturas', 19, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(25, 'courses', 'Artes', 'Artes', 20, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(26, 'courses', 'Turismo', 'Turismo', 21, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(27, 'courses', 'Hotelaria', 'Hotelaria', 22, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(28, 'courses', 'Agronomia', 'Agronomia', 23, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(29, 'courses', 'Veterinária', 'Veterinária', 24, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(30, 'courses', 'Outro', 'Outro', 99, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(31, 'academic_levels', 'Licenciatura - 1º ano', 'Licenciatura - 1º ano', 1, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(32, 'academic_levels', 'Licenciatura - 2º ano', 'Licenciatura - 2º ano', 2, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(33, 'academic_levels', 'Licenciatura - 3º ano', 'Licenciatura - 3º ano', 3, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(34, 'academic_levels', 'Licenciatura - 4º ano', 'Licenciatura - 4º ano', 4, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(35, 'academic_levels', 'Licenciatura - 5º ano', 'Licenciatura - 5º ano', 5, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(36, 'academic_levels', 'Pós-graduação', 'Pós-graduação', 6, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(37, 'academic_levels', 'Mestrado', 'Mestrado', 7, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(38, 'academic_levels', 'Doutoramento', 'Doutoramento', 8, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(39, 'research_areas', 'ciencias_sociais', 'Ciências Sociais', 1, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(40, 'research_areas', 'saude', 'Saúde', 2, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(41, 'research_areas', 'tecnologia', 'Tecnologia', 3, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(42, 'research_areas', 'educacao', 'Educação', 4, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(43, 'research_areas', 'economia', 'Economia', 5, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(44, 'research_areas', 'ambiente', 'Meio Ambiente', 6, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(45, 'research_areas', 'cultura', 'Cultura', 7, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05'),
(46, 'research_areas', 'politica', 'Política', 8, 1, '2026-02-03 15:49:05', '2026-02-03 15:49:05');

-- --------------------------------------------------------

--
-- Table structure for table activity_logs
--

DROP TABLE IF EXISTS activity_logs;
CREATE TABLE IF NOT EXISTS activity_logs (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id bigint UNSIGNED DEFAULT NULL,
  action varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  description text COLLATE utf8mb4_unicode_ci NOT NULL,
  metadata json DEFAULT NULL,
  ip_address varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  user_agent text COLLATE utf8mb4_unicode_ci,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY activity_logs_user_id_created_at_index (user_id,created_at),
  KEY activity_logs_action_index (action)
) ENGINE=MyISAM AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table activity_logs
--

INSERT INTO activity_logs (id, user_id, action, description, metadata, ip_address, user_agent, created_at, updated_at) VALUES
(1, 1, 'payment_processed', 'Pagamento #3 processado para usuário Sofia Maria Chaúque', '\"{\\\"transaction_id\\\":3,\\\"amount\\\":\\\"50.00\\\",\\\"user_id\\\":10,\\\"method\\\":\\\"mpesa\\\"}\"', NULL, NULL, '2026-02-07 09:38:27', '2026-02-07 09:38:27'),
(2, 1, 'payment_processed', 'Pagamento #4 processado para usuário Sofia Maria Chaúque', '\"{\\\"transaction_id\\\":4,\\\"amount\\\":\\\"70.00\\\",\\\"user_id\\\":10,\\\"method\\\":\\\"mpesa\\\"}\"', NULL, NULL, '2026-02-07 09:38:28', '2026-02-07 09:38:28'),
(3, 1, 'payment_processed', 'Pagamento #6 processado para usuário Paulo José Matola', '\"{\\\"transaction_id\\\":6,\\\"amount\\\":\\\"50.00\\\",\\\"user_id\\\":9,\\\"method\\\":\\\"mpesa\\\"}\"', NULL, NULL, '2026-02-07 09:38:28', '2026-02-07 09:38:28'),
(4, 1, 'user_created', 'Usuário Teste PowerShell criado pelo administrador', '\"{\\\"user_id\\\":43,\\\"role\\\":\\\"participant\\\"}\"', NULL, NULL, '2026-02-07 10:34:49', '2026-02-07 10:34:49'),
(5, 1, 'user_updated', 'Usuário Teste PowerShell Atualizado atualizado pelo administrador', '\"{\\\"user_id\\\":43,\\\"changes\\\":{\\\"name\\\":\\\"Teste PowerShell Atualizado\\\",\\\"phone\\\":\\\"(84) 98888-8888\\\",\\\"status\\\":\\\"active\\\"}}\"', NULL, NULL, '2026-02-07 10:34:59', '2026-02-07 10:34:59'),
(6, 1, 'user_updated', 'Usuário Teste PowerShell Atualizado atualizado pelo administrador', '\"{\\\"user_id\\\":43,\\\"changes\\\":[]}\"', NULL, NULL, '2026-02-07 10:55:34', '2026-02-07 10:55:34'),
(7, 1, 'user_updated', 'Usuário Teste PowerShell Atualizado [MODIFICADO] atualizado pelo administrador', '\"{\\\"user_id\\\":43,\\\"changes\\\":{\\\"name\\\":\\\"Teste PowerShell Atualizado [MODIFICADO]\\\",\\\"phone\\\":\\\"844466353\\\"}}\"', NULL, NULL, '2026-02-07 13:15:24', '2026-02-07 13:15:24'),
(8, 1, 'user_updated', 'Usuário Teste PowerShell Atualizado [MODIFICADO] atualizado pelo administrador', '\"{\\\"user_id\\\":43,\\\"changes\\\":{\\\"name\\\":\\\"Teste PowerShell Atualizado [MODIFICADO]\\\",\\\"email\\\":\\\"teste.powershell@exemplo.com\\\",\\\"role\\\":\\\"participant\\\",\\\"phone\\\":\\\"844466353\\\",\\\"verification_status\\\":\\\"approved\\\",\\\"balance\\\":\\\"0.00\\\"}}\"', NULL, NULL, '2026-02-07 13:26:11', '2026-02-07 13:26:11'),
(9, 1, 'user_updated', 'Usuário Teste PowerShell Atualizado [MODIFICADO] atualizado pelo administrador', '\"{\\\"user_id\\\":43,\\\"changes\\\":{\\\"verification_status\\\":\\\"rejected\\\"}}\"', NULL, NULL, '2026-02-07 13:26:56', '2026-02-07 13:26:56'),
(10, 1, 'user_updated', 'Usuário Teste PowerShell Atualizado [MODIFICADO] atualizado pelo administrador', '\"{\\\"user_id\\\":43,\\\"changes\\\":[]}\"', NULL, NULL, '2026-02-07 13:31:16', '2026-02-07 13:31:16'),
(11, 1, 'user_deleted', 'Usuário Teste PowerShell Atualizado [MODIFICADO] excluído pelo administrador', '\"{\\\"user_id\\\":\\\"43\\\"}\"', NULL, NULL, '2026-02-07 13:31:16', '2026-02-07 13:31:16'),
(12, 1, 'bulk_user_action', 'Ação em massa \'activate\' executada em 2 usuários', '\"{\\\"action\\\":\\\"activate\\\",\\\"success_count\\\":2,\\\"failed_count\\\":0,\\\"reason\\\":\\\"Teste\\\"}\"', NULL, NULL, '2026-02-07 13:31:17', '2026-02-07 13:31:17'),
(13, 1, 'user_created', 'Usuário Teste Completo 174452 criado pelo administrador', '\"{\\\"user_id\\\":44,\\\"role\\\":\\\"participant\\\"}\"', NULL, NULL, '2026-02-07 13:44:55', '2026-02-07 13:44:55'),
(14, 1, 'user_created', 'Usuário Teste Completo 174501 criado pelo administrador', '\"{\\\"user_id\\\":45,\\\"role\\\":\\\"participant\\\"}\"', NULL, NULL, '2026-02-07 13:45:02', '2026-02-07 13:45:02'),
(15, 1, 'user_updated', 'Usuário Teste Completo 174501 atualizado pelo administrador', '\"{\\\"user_id\\\":45,\\\"changes\\\":{\\\"verification_status\\\":\\\"rejected\\\"}}\"', NULL, NULL, '2026-02-07 13:46:45', '2026-02-07 13:46:45'),
(16, 1, 'user_updated', 'Usuário Teste Completo 174452 atualizado pelo administrador', '\"{\\\"user_id\\\":44,\\\"changes\\\":{\\\"verification_status\\\":\\\"rejected\\\"}}\"', NULL, NULL, '2026-02-07 13:46:53', '2026-02-07 13:46:53'),
(17, 1, 'user_updated', 'Usuário Teste Completo 174501 atualizado pelo administrador', '\"{\\\"user_id\\\":45,\\\"changes\\\":[]}\"', NULL, NULL, '2026-02-07 13:47:59', '2026-02-07 13:47:59'),
(18, 1, 'user_created', 'Usuário edilson Mutisse mutisse criado pelo administrador', '\"{\\\"user_id\\\":46,\\\"role\\\":\\\"admin\\\"}\"', NULL, NULL, '2026-02-07 14:50:53', '2026-02-07 14:50:53'),
(19, 1, 'user_updated', 'Usuário edilson Mutisse mutisse atualizado pelo administrador', '\"{\\\"user_id\\\":46,\\\"changes\\\":{\\\"name\\\":\\\"edilson Mutisse mutisse\\\",\\\"email\\\":\\\"edilson.mutisse@uem.ac.mz\\\",\\\"role\\\":\\\"admin\\\",\\\"phone\\\":\\\"841234567\\\",\\\"verification_status\\\":\\\"approved\\\"}}\"', NULL, NULL, '2026-02-12 08:09:44', '2026-02-12 08:09:44'),
(20, 1, 'user_updated', 'Usuário Teste Completo 174452 atualizado pelo administrador', '\"{\\\"user_id\\\":44,\\\"changes\\\":{\\\"verification_status\\\":\\\"approved\\\"}}\"', NULL, NULL, '2026-02-12 09:27:07', '2026-02-12 09:27:07'),
(21, 1, 'user_updated', 'Usuário Participante 12 atualizado pelo administrador', '\"{\\\"user_id\\\":34,\\\"changes\\\":{\\\"verification_status\\\":\\\"rejected\\\"}}\"', NULL, NULL, '2026-02-12 10:02:26', '2026-02-12 10:02:26'),
(22, 1, 'user_updated', 'Usuário Participante 11 atualizado pelo administrador', '\"{\\\"user_id\\\":33,\\\"changes\\\":{\\\"verification_status\\\":\\\"rejected\\\"}}\"', NULL, NULL, '2026-02-12 10:02:31', '2026-02-12 10:02:31'),
(23, 1, 'user_updated', 'Usuário Respondente 4 atualizado pelo administrador', '\"{\\\"user_id\\\":31,\\\"changes\\\":{\\\"verification_status\\\":\\\"rejected\\\"}}\"', NULL, NULL, '2026-02-12 10:02:35', '2026-02-12 10:02:35'),
(24, 1, 'user_updated', 'Usuário Respondente 5 atualizado pelo administrador', '\"{\\\"user_id\\\":32,\\\"changes\\\":{\\\"verification_status\\\":\\\"rejected\\\"}}\"', NULL, NULL, '2026-02-12 10:02:38', '2026-02-12 10:02:38'),
(25, 1, 'user_updated', 'Usuário Respondente 1 atualizado pelo administrador', '\"{\\\"user_id\\\":28,\\\"changes\\\":{\\\"verification_status\\\":\\\"rejected\\\"}}\"', NULL, NULL, '2026-02-12 10:02:42', '2026-02-12 10:02:42'),
(26, 1, 'user_updated', 'Usuário Participante 7 atualizado pelo administrador', '\"{\\\"user_id\\\":24,\\\"changes\\\":{\\\"verification_status\\\":\\\"rejected\\\"}}\"', NULL, NULL, '2026-02-12 10:02:48', '2026-02-12 10:02:48'),
(27, 1, 'user_updated', 'Usuário Participante 8 atualizado pelo administrador', '\"{\\\"user_id\\\":25,\\\"changes\\\":{\\\"verification_status\\\":\\\"rejected\\\"}}\"', NULL, NULL, '2026-02-12 10:02:56', '2026-02-12 10:02:56'),
(28, 1, 'user_deleted', 'Usuário Teste Completo 174501 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"45\\\"}\"', NULL, NULL, '2026-02-12 10:32:35', '2026-02-12 10:32:35'),
(29, 1, 'user_deleted', 'Usuário Teste Completo 174452 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"44\\\"}\"', NULL, NULL, '2026-02-12 10:32:43', '2026-02-12 10:32:43'),
(30, 1, 'user_deleted', 'Usuário Participante 19 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"41\\\"}\"', NULL, NULL, '2026-02-12 10:32:52', '2026-02-12 10:32:52'),
(31, 1, 'user_deleted', 'Usuário Participante 20 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"42\\\"}\"', NULL, NULL, '2026-02-12 10:32:57', '2026-02-12 10:32:57'),
(32, 1, 'user_deleted', 'Usuário Respondente 1 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"28\\\"}\"', NULL, NULL, '2026-02-12 10:33:06', '2026-02-12 10:33:06'),
(33, 1, 'user_deleted', 'Usuário Participante 8 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"25\\\"}\"', NULL, NULL, '2026-02-12 10:33:12', '2026-02-12 10:33:12'),
(34, 1, 'user_deleted', 'Usuário Participante 18 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"40\\\"}\"', NULL, NULL, '2026-02-12 10:33:48', '2026-02-12 10:33:48'),
(35, 1, 'user_deleted', 'Usuário Participante 11 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"33\\\"}\"', NULL, NULL, '2026-02-12 10:33:55', '2026-02-12 10:33:55'),
(36, 1, 'user_deleted', 'Usuário Participante 14 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"36\\\"}\"', NULL, NULL, '2026-02-12 10:34:03', '2026-02-12 10:34:03'),
(37, 1, 'user_deleted', 'Usuário Participante 17 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"39\\\"}\"', NULL, NULL, '2026-02-12 10:34:22', '2026-02-12 10:34:22'),
(38, 1, 'user_deleted', 'Usuário Respondente 5 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"32\\\"}\"', NULL, NULL, '2026-02-12 10:34:29', '2026-02-12 10:34:29'),
(39, 1, 'user_deleted', 'Usuário Participante 13 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"35\\\"}\"', NULL, NULL, '2026-02-12 10:34:35', '2026-02-12 10:34:35'),
(40, 1, 'user_deleted', 'Usuário Pesquisador Acadêmico excluído pelo administrador', '\"{\\\"user_id\\\":\\\"17\\\"}\"', NULL, NULL, '2026-02-12 10:34:50', '2026-02-12 10:34:50'),
(41, 1, 'user_deleted', 'Usuário Participante 10 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"27\\\"}\"', NULL, NULL, '2026-02-12 10:34:58', '2026-02-12 10:34:58'),
(42, 1, 'user_deleted', 'Usuário Participante 2 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"19\\\"}\"', NULL, NULL, '2026-02-12 10:35:07', '2026-02-12 10:35:07'),
(43, 1, 'user_deleted', 'Usuário Participante 1 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"18\\\"}\"', NULL, NULL, '2026-02-12 10:35:12', '2026-02-12 10:35:12'),
(44, 1, 'user_deleted', 'Usuário Participante 6 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"23\\\"}\"', NULL, NULL, '2026-02-12 10:35:19', '2026-02-12 10:35:19'),
(45, 1, 'user_deleted', 'Usuário Participante 15 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"37\\\"}\"', NULL, NULL, '2026-02-12 10:35:26', '2026-02-12 10:35:26'),
(46, 1, 'user_deleted', 'Usuário Participante 12 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"34\\\"}\"', NULL, NULL, '2026-02-12 10:35:32', '2026-02-12 10:35:32'),
(47, 1, 'user_deleted', 'Usuário Respondente 3 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"30\\\"}\"', NULL, NULL, '2026-02-12 10:35:37', '2026-02-12 10:35:37'),
(48, 1, 'user_deleted', 'Usuário Participante 16 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"38\\\"}\"', NULL, NULL, '2026-02-12 10:35:40', '2026-02-12 10:35:40'),
(49, 1, 'user_deleted', 'Usuário Respondente 2 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"29\\\"}\"', NULL, NULL, '2026-02-12 10:35:46', '2026-02-12 10:35:46'),
(50, 1, 'user_deleted', 'Usuário Participante 9 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"26\\\"}\"', NULL, NULL, '2026-02-12 10:35:50', '2026-02-12 10:35:50'),
(51, 1, 'user_deleted', 'Usuário Respondente 4 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"31\\\"}\"', NULL, NULL, '2026-02-12 10:35:54', '2026-02-12 10:35:54'),
(52, 1, 'user_deleted', 'Usuário Participante 3 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"20\\\"}\"', NULL, NULL, '2026-02-12 10:36:00', '2026-02-12 10:36:00'),
(53, 1, 'user_deleted', 'Usuário Participante 7 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"24\\\"}\"', NULL, NULL, '2026-02-12 10:36:04', '2026-02-12 10:36:04'),
(54, 1, 'user_deleted', 'Usuário Participante 4 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"21\\\"}\"', NULL, NULL, '2026-02-12 10:36:08', '2026-02-12 10:36:08'),
(55, 1, 'user_deleted', 'Usuário Participante 5 excluído pelo administrador', '\"{\\\"user_id\\\":\\\"22\\\"}\"', NULL, NULL, '2026-02-12 10:36:13', '2026-02-12 10:36:13'),
(56, 1, 'user_updated', 'Usuário Marta José Amisse atualizado pelo administrador', '\"{\\\"user_id\\\":16,\\\"changes\\\":{\\\"verification_status\\\":\\\"approved\\\"}}\"', NULL, NULL, '2026-02-12 10:37:02', '2026-02-12 10:37:02'),
(57, 1, 'user_deleted', 'Usuário edilson Mutisse mutisse excluído pelo administrador', '\"{\\\"user_id\\\":\\\"46\\\"}\"', NULL, NULL, '2026-02-12 10:51:53', '2026-02-12 10:51:53'),
(58, 1, 'user_created', 'Usuário edilson Mutisse criado pelo administrador', '\"{\\\"user_id\\\":47,\\\"role\\\":\\\"admin\\\"}\"', NULL, NULL, '2026-02-12 10:53:04', '2026-02-12 10:53:04'),
(59, 1, 'user_updated', 'Usuário edilson Mutisse atualizado pelo administrador', '\"{\\\"user_id\\\":47,\\\"changes\\\":{\\\"name\\\":\\\"edilson Mutisse\\\",\\\"email\\\":\\\"edilson.mutisse@mozpesquisa.ac.mz\\\",\\\"role\\\":\\\"admin\\\",\\\"phone\\\":\\\"841234567\\\",\\\"verification_status\\\":\\\"approved\\\"}}\"', NULL, NULL, '2026-02-12 19:39:12', '2026-02-12 19:39:12'),
(60, 1, 'user_updated', 'Usuário edilson Mutisse atualizado pelo administrador', '\"{\\\"user_id\\\":47,\\\"changes\\\":{\\\"name\\\":\\\"edilson Mutisse\\\",\\\"email\\\":\\\"edilson.mutisse@mozpesquisa.ac.mz\\\",\\\"role\\\":\\\"admin\\\",\\\"phone\\\":\\\"841234567\\\",\\\"verification_status\\\":\\\"approved\\\"}}\"', NULL, NULL, '2026-02-12 19:39:23', '2026-02-12 19:39:23'),
(61, 1, 'user_deleted', 'Usuário edilson Mutisse excluído pelo administrador', '\"{\\\"user_id\\\":\\\"47\\\"}\"', NULL, NULL, '2026-02-15 02:31:29', '2026-02-15 02:31:29');

-- --------------------------------------------------------

--
-- Table structure for table cache
--

DROP TABLE IF EXISTS cache;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  value mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  expiration int NOT NULL,
  PRIMARY KEY (`key`),
  KEY cache_expiration_index (expiration)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table cache_locks
--

DROP TABLE IF EXISTS cache_locks;
CREATE TABLE IF NOT EXISTS cache_locks (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  owner varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  expiration int NOT NULL,
  PRIMARY KEY (`key`),
  KEY cache_locks_expiration_index (expiration)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table failed_jobs
--

DROP TABLE IF EXISTS failed_jobs;
CREATE TABLE IF NOT EXISTS failed_jobs (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  connection text COLLATE utf8mb4_unicode_ci NOT NULL,
  queue text COLLATE utf8mb4_unicode_ci NOT NULL,
  payload longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  exception longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  failed_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY failed_jobs_uuid_unique (uuid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table jobs
--

DROP TABLE IF EXISTS jobs;
CREATE TABLE IF NOT EXISTS jobs (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  queue varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  payload longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  attempts tinyint UNSIGNED NOT NULL,
  reserved_at int UNSIGNED DEFAULT NULL,
  available_at int UNSIGNED NOT NULL,
  created_at int UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY jobs_queue_index (queue)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table job_batches
--

DROP TABLE IF EXISTS job_batches;
CREATE TABLE IF NOT EXISTS job_batches (
  id varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  name varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  total_jobs int NOT NULL,
  pending_jobs int NOT NULL,
  failed_jobs int NOT NULL,
  failed_job_ids longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  options mediumtext COLLATE utf8mb4_unicode_ci,
  cancelled_at int DEFAULT NULL,
  created_at int NOT NULL,
  finished_at int DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table migrations
--

DROP TABLE IF EXISTS migrations;
CREATE TABLE IF NOT EXISTS migrations (
  id int UNSIGNED NOT NULL AUTO_INCREMENT,
  migration varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  batch int NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table migrations
--

INSERT INTO migrations (id, migration, batch) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_29_160841_create_personal_access_tokens_table', 1),
(5, '2026_01_29_161014_create_student_documents_table', 1),
(6, '2026_01_29_161115_create_student_stats_table', 1),
(7, '2026_01_29_161653_create_universities_table', 1),
(8, '2026_01_29_161713_create_academic_configurations_table', 1),
(9, '2026_01_29_220308_create_participant_stats_table', 1),
(10, '2026_01_30_084903_create_sessions_table', 1),
(11, '2026_01_30_121736_create_complete_survey_system', 1),
(12, '2026_01_30_122639_update_users_table_for_surveys', 1),
(13, '2026_01_30_215206_create_notifications_table', 1),
(14, '2026_01_30_215206_create_withdrawal_requests_table', 1),
(15, '2026_01_31_000000_create_activity_logs_table', 1),
(16, '2026_01_31_091527_create_transactions_table', 1),
(17, '2026_02_03_161641_add_builder_fields_to_surveys_table', 1),
(18, '2026_02_03_171900_add_missing_columns_to_universities_table', 2),
(19, '2026_02_05_042723_create_report_histories_table', 3),
(20, '2026_02_05_042724_create_report_templates_table', 4),
(21, '2026_02_05_072555_add_missing_columns_to_survey_responses_table', 5),
(22, '2026_02_05_073019_add_missing_columns_to_survey_responses_table', 5),
(23, '2026_02_05_153546_update_notifications_table_structure', 6),
(24, '2026_02_07_104557_make_answers_nullable_in_survey_responses_table', 7),
(26, '2024_01_01_000000_create_payments_table', 8),
(27, '2026_02_15_003638_add_soft_deletes_to_transactions_table', 9);

-- --------------------------------------------------------

--
-- Table structure for table notifications
--

DROP TABLE IF EXISTS notifications;
CREATE TABLE IF NOT EXISTS notifications (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id bigint UNSIGNED NOT NULL,
  type enum('survey_response','survey_approved','survey_rejected','survey_expiring','survey_completed','survey_published','payment_received','withdrawal_processed','withdrawal_rejected','low_balance','research_reminder','deadline_alert','survey_available','survey_invitation','response_completed','payment_credited','profile_update','qualification_approved','bonus_received','rank_improved','weekly_summary','referral_bonus','new_user_registered','survey_pending_review','withdrawal_requested','user_verification_pending','system_alert','batch_payment_processed','low_system_funds','abuse_reported','high_activity','system_maintenance','new_feature','policy_update','security_alert','holiday_schedule','app_update','general_announcement','important_reminder') COLLATE utf8mb4_unicode_ci DEFAULT 'general_announcement',
  title varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  message text COLLATE utf8mb4_unicode_ci NOT NULL,
  icon varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  action_url varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  action_label varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  is_read tinyint(1) NOT NULL DEFAULT '0',
  read_at timestamp NULL DEFAULT NULL,
  expires_at timestamp NULL DEFAULT NULL,
  priority int NOT NULL DEFAULT '1',
  data json DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY notifications_user_id_is_read_index (user_id,is_read),
  KEY notifications_user_id_type_index (user_id,type),
  KEY notifications_user_id_created_at_index (user_id,created_at),
  KEY notifications_priority_index (priority),
  KEY notifications_expires_at_index (expires_at)
) ENGINE=MyISAM AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table notifications
--

INSERT INTO notifications (id, user_id, type, title, message, icon, action_url, action_label, is_read, read_at, expires_at, priority, data, created_at, updated_at) VALUES
(128, 10, 'withdrawal_requested', 'Solicitação de Saque', 'Sua solicitação de saque de 50.00 MZN foi recebida e está em análise.', NULL, NULL, NULL, 1, '2026-02-07 05:45:41', NULL, 1, '\"{\\\"transaction_id\\\":3,\\\"amount\\\":50,\\\"payment_method\\\":\\\"mpesa\\\"}\"', '2026-02-07 03:46:13', '2026-02-07 05:45:41'),
(2, 2, 'survey_response', 'Nova resposta recebida', 'Sua pesquisa recebeu uma nova resposta. Total: 45/50 respostas.', 'assignment_turned_in', '/dashboard', 'Ver detalhes', 1, '2026-02-06 04:04:45', '2026-03-07 13:46:49', 1, '{\"survey_id\": 5, \"response_count\": 45}', '2026-02-05 07:46:49', '2026-02-06 04:04:45'),
(3, 2, 'payment_received', 'Pagamento recebido', 'Você recebeu MZN 250.00 pelas respostas da sua pesquisa.', 'payments', '/dashboard', 'Ver detalhes', 1, '2026-02-06 04:04:45', '2026-03-07 13:46:49', 2, '{\"amount\": 250, \"currency\": \"MZN\"}', '2026-02-04 13:46:49', '2026-02-06 04:04:45'),
(4, 2, 'survey_expiring', 'Pesquisa expirando', 'Sua pesquisa \'Consumo de Energia Residencial\' expira em 3 dias.', 'schedule', '/dashboard', 'Ver detalhes', 1, '2026-02-06 04:04:45', '2026-03-07 13:46:49', 3, '{\"days_left\": 3, \"survey_id\": 9}', '2026-02-03 13:46:49', '2026-02-06 04:04:45'),
(5, 2, 'survey_completed', 'Meta atingida!', 'Parabéns! Sua pesquisa atingiu 100% das respostas necessárias.', 'task_alt', '/dashboard', 'Ver detalhes', 1, '2026-02-02 13:46:49', '2026-03-07 13:46:49', 1, '{\"survey_id\": 12}', '2026-02-01 13:46:49', '2026-02-05 13:46:49'),
(6, 2, 'withdrawal_processed', 'Saque realizado', 'Seu saque de MZN 500.00 foi processado com sucesso.', 'account_balance_wallet', '/dashboard', 'Ver detalhes', 1, '2026-01-31 13:46:49', '2026-03-07 13:46:49', 2, '{\"amount\": 500}', '2026-01-31 13:46:49', '2026-02-05 13:46:49'),
(7, 2, 'new_feature', 'Nova funcionalidade', 'Agora você pode exportar relatórios em PDF. Experimente!', 'new_releases', '/dashboard', 'Ver detalhes', 1, '2026-02-06 04:04:45', '2026-03-07 13:46:49', 1, '{\"feature\": \"export_pdf\"}', '2026-02-04 13:46:49', '2026-02-06 04:04:45'),
(8, 2, 'system_maintenance', 'Manutenção programada', 'O sistema estará indisponível das 02:00 às 04:00 para manutenção.', 'build', '/dashboard', 'Ver detalhes', 1, '2026-02-06 04:04:45', '2026-03-07 13:46:49', 2, '{\"end_time\": \"04:00\", \"start_time\": \"02:00\"}', '2026-02-03 13:46:49', '2026-02-06 04:04:45'),
(9, 3, 'survey_approved', 'Pesquisa aprovada!', 'Sua pesquisa \'Hábitos de Leitura em Universidades Moçambicanas\' foi aprovada e está publicada.', 'check_circle', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"action\": \"view_survey\", \"survey_id\": 5}', '2026-02-05 11:46:49', '2026-02-05 13:46:49'),
(10, 3, 'survey_response', 'Nova resposta recebida', 'Sua pesquisa recebeu uma nova resposta. Total: 45/50 respostas.', 'assignment_turned_in', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 1, '{\"survey_id\": 3, \"response_count\": 45}', '2026-02-05 07:46:49', '2026-02-05 13:46:49'),
(11, 3, 'payment_received', 'Pagamento recebido', 'Você recebeu MZN 250.00 pelas respostas da sua pesquisa.', 'payments', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"amount\": 250, \"currency\": \"MZN\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(12, 3, 'survey_expiring', 'Pesquisa expirando', 'Sua pesquisa \'Consumo de Energia Residencial\' expira em 3 dias.', 'schedule', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 3, '{\"days_left\": 3, \"survey_id\": 9}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(13, 3, 'survey_completed', 'Meta atingida!', 'Parabéns! Sua pesquisa atingiu 100% das respostas necessárias.', 'task_alt', '/dashboard', 'Ver detalhes', 1, '2026-02-02 13:46:49', '2026-03-07 13:46:49', 1, '{\"survey_id\": 11}', '2026-02-01 13:46:49', '2026-02-05 13:46:49'),
(14, 3, 'withdrawal_processed', 'Saque realizado', 'Seu saque de MZN 500.00 foi processado com sucesso.', 'account_balance_wallet', '/dashboard', 'Ver detalhes', 1, '2026-01-31 13:46:49', '2026-03-07 13:46:49', 2, '{\"amount\": 500}', '2026-01-31 13:46:49', '2026-02-05 13:46:49'),
(15, 3, 'new_feature', 'Nova funcionalidade', 'Agora você pode exportar relatórios em PDF. Experimente!', 'new_releases', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 1, '{\"feature\": \"export_pdf\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(16, 3, 'system_maintenance', 'Manutenção programada', 'O sistema estará indisponível das 02:00 às 04:00 para manutenção.', 'build', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"end_time\": \"04:00\", \"start_time\": \"02:00\"}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(17, 4, 'survey_approved', 'Pesquisa aprovada!', 'Sua pesquisa \'Hábitos de Leitura em Universidades Moçambicanas\' foi aprovada e está publicada.', 'check_circle', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"action\": \"view_survey\", \"survey_id\": 3}', '2026-02-05 11:46:49', '2026-02-05 13:46:49'),
(18, 4, 'survey_response', 'Nova resposta recebida', 'Sua pesquisa recebeu uma nova resposta. Total: 45/50 respostas.', 'assignment_turned_in', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 1, '{\"survey_id\": 2, \"response_count\": 45}', '2026-02-05 07:46:49', '2026-02-05 13:46:49'),
(19, 4, 'payment_received', 'Pagamento recebido', 'Você recebeu MZN 250.00 pelas respostas da sua pesquisa.', 'payments', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"amount\": 250, \"currency\": \"MZN\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(20, 4, 'survey_expiring', 'Pesquisa expirando', 'Sua pesquisa \'Consumo de Energia Residencial\' expira em 3 dias.', 'schedule', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 3, '{\"days_left\": 3, \"survey_id\": 9}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(21, 4, 'survey_completed', 'Meta atingida!', 'Parabéns! Sua pesquisa atingiu 100% das respostas necessárias.', 'task_alt', '/dashboard', 'Ver detalhes', 1, '2026-02-02 13:46:49', '2026-03-07 13:46:49', 1, '{\"survey_id\": 12}', '2026-02-01 13:46:49', '2026-02-05 13:46:49'),
(22, 4, 'withdrawal_processed', 'Saque realizado', 'Seu saque de MZN 500.00 foi processado com sucesso.', 'account_balance_wallet', '/dashboard', 'Ver detalhes', 1, '2026-01-31 13:46:49', '2026-03-07 13:46:49', 2, '{\"amount\": 500}', '2026-01-31 13:46:49', '2026-02-05 13:46:49'),
(23, 4, 'new_feature', 'Nova funcionalidade', 'Agora você pode exportar relatórios em PDF. Experimente!', 'new_releases', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 1, '{\"feature\": \"export_pdf\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(24, 4, 'system_maintenance', 'Manutenção programada', 'O sistema estará indisponível das 02:00 às 04:00 para manutenção.', 'build', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"end_time\": \"04:00\", \"start_time\": \"02:00\"}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(25, 5, 'survey_approved', 'Pesquisa aprovada!', 'Sua pesquisa \'Hábitos de Leitura em Universidades Moçambicanas\' foi aprovada e está publicada.', 'check_circle', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"action\": \"view_survey\", \"survey_id\": 2}', '2026-02-05 11:46:49', '2026-02-05 13:46:49'),
(26, 5, 'survey_response', 'Nova resposta recebida', 'Sua pesquisa recebeu uma nova resposta. Total: 45/50 respostas.', 'assignment_turned_in', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 1, '{\"survey_id\": 2, \"response_count\": 45}', '2026-02-05 07:46:49', '2026-02-05 13:46:49'),
(27, 5, 'payment_received', 'Pagamento recebido', 'Você recebeu MZN 250.00 pelas respostas da sua pesquisa.', 'payments', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"amount\": 250, \"currency\": \"MZN\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(28, 5, 'survey_expiring', 'Pesquisa expirando', 'Sua pesquisa \'Consumo de Energia Residencial\' expira em 3 dias.', 'schedule', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 3, '{\"days_left\": 3, \"survey_id\": 6}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(29, 5, 'survey_completed', 'Meta atingida!', 'Parabéns! Sua pesquisa atingiu 100% das respostas necessárias.', 'task_alt', '/dashboard', 'Ver detalhes', 1, '2026-02-02 13:46:49', '2026-03-07 13:46:49', 1, '{\"survey_id\": 15}', '2026-02-01 13:46:49', '2026-02-05 13:46:49'),
(30, 5, 'withdrawal_processed', 'Saque realizado', 'Seu saque de MZN 500.00 foi processado com sucesso.', 'account_balance_wallet', '/dashboard', 'Ver detalhes', 1, '2026-01-31 13:46:49', '2026-03-07 13:46:49', 2, '{\"amount\": 500}', '2026-01-31 13:46:49', '2026-02-05 13:46:49'),
(31, 5, 'new_feature', 'Nova funcionalidade', 'Agora você pode exportar relatórios em PDF. Experimente!', 'new_releases', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 1, '{\"feature\": \"export_pdf\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(32, 5, 'system_maintenance', 'Manutenção programada', 'O sistema estará indisponível das 02:00 às 04:00 para manutenção.', 'build', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"end_time\": \"04:00\", \"start_time\": \"02:00\"}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(33, 6, 'survey_approved', 'Pesquisa aprovada!', 'Sua pesquisa \'Hábitos de Leitura em Universidades Moçambicanas\' foi aprovada e está publicada.', 'check_circle', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"action\": \"view_survey\", \"survey_id\": 2}', '2026-02-05 11:46:49', '2026-02-05 13:46:49'),
(34, 6, 'survey_response', 'Nova resposta recebida', 'Sua pesquisa recebeu uma nova resposta. Total: 45/50 respostas.', 'assignment_turned_in', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 1, '{\"survey_id\": 4, \"response_count\": 45}', '2026-02-05 07:46:49', '2026-02-05 13:46:49'),
(35, 6, 'payment_received', 'Pagamento recebido', 'Você recebeu MZN 250.00 pelas respostas da sua pesquisa.', 'payments', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"amount\": 250, \"currency\": \"MZN\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(36, 6, 'survey_expiring', 'Pesquisa expirando', 'Sua pesquisa \'Consumo de Energia Residencial\' expira em 3 dias.', 'schedule', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 3, '{\"days_left\": 3, \"survey_id\": 6}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(37, 6, 'survey_completed', 'Meta atingida!', 'Parabéns! Sua pesquisa atingiu 100% das respostas necessárias.', 'task_alt', '/dashboard', 'Ver detalhes', 1, '2026-02-02 13:46:49', '2026-03-07 13:46:49', 1, '{\"survey_id\": 13}', '2026-02-01 13:46:49', '2026-02-05 13:46:49'),
(38, 6, 'withdrawal_processed', 'Saque realizado', 'Seu saque de MZN 500.00 foi processado com sucesso.', 'account_balance_wallet', '/dashboard', 'Ver detalhes', 1, '2026-01-31 13:46:49', '2026-03-07 13:46:49', 2, '{\"amount\": 500}', '2026-01-31 13:46:49', '2026-02-05 13:46:49'),
(39, 6, 'new_feature', 'Nova funcionalidade', 'Agora você pode exportar relatórios em PDF. Experimente!', 'new_releases', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 1, '{\"feature\": \"export_pdf\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(40, 6, 'system_maintenance', 'Manutenção programada', 'O sistema estará indisponível das 02:00 às 04:00 para manutenção.', 'build', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"end_time\": \"04:00\", \"start_time\": \"02:00\"}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(41, 17, 'survey_approved', 'Pesquisa aprovada!', 'Sua pesquisa \'Hábitos de Leitura em Universidades Moçambicanas\' foi aprovada e está publicada.', 'check_circle', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"action\": \"view_survey\", \"survey_id\": 5}', '2026-02-05 11:46:49', '2026-02-05 13:46:49'),
(42, 17, 'survey_response', 'Nova resposta recebida', 'Sua pesquisa recebeu uma nova resposta. Total: 45/50 respostas.', 'assignment_turned_in', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 1, '{\"survey_id\": 2, \"response_count\": 45}', '2026-02-05 07:46:49', '2026-02-05 13:46:49'),
(43, 17, 'payment_received', 'Pagamento recebido', 'Você recebeu MZN 250.00 pelas respostas da sua pesquisa.', 'payments', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"amount\": 250, \"currency\": \"MZN\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(44, 17, 'survey_expiring', 'Pesquisa expirando', 'Sua pesquisa \'Consumo de Energia Residencial\' expira em 3 dias.', 'schedule', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 3, '{\"days_left\": 3, \"survey_id\": 10}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(45, 17, 'survey_completed', 'Meta atingida!', 'Parabéns! Sua pesquisa atingiu 100% das respostas necessárias.', 'task_alt', '/dashboard', 'Ver detalhes', 1, '2026-02-02 13:46:49', '2026-03-07 13:46:49', 1, '{\"survey_id\": 14}', '2026-02-01 13:46:49', '2026-02-05 13:46:49'),
(46, 17, 'withdrawal_processed', 'Saque realizado', 'Seu saque de MZN 500.00 foi processado com sucesso.', 'account_balance_wallet', '/dashboard', 'Ver detalhes', 1, '2026-01-31 13:46:49', '2026-03-07 13:46:49', 2, '{\"amount\": 500}', '2026-01-31 13:46:49', '2026-02-05 13:46:49'),
(47, 17, 'new_feature', 'Nova funcionalidade', 'Agora você pode exportar relatórios em PDF. Experimente!', 'new_releases', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 1, '{\"feature\": \"export_pdf\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(48, 17, 'system_maintenance', 'Manutenção programada', 'O sistema estará indisponível das 02:00 às 04:00 para manutenção.', 'build', '/dashboard', 'Ver detalhes', 0, NULL, '2026-03-07 13:46:49', 2, '{\"end_time\": \"04:00\", \"start_time\": \"02:00\"}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(49, 7, 'survey_available', 'Nova pesquisa disponível', 'Pesquisa sobre hábitos de consumo - Ganhe MZN 30 por responder.', 'assignment', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"reward\": 30, \"survey_id\": 20}', '2026-02-05 12:46:49', '2026-02-05 13:46:49'),
(50, 7, 'survey_available', 'Pesquisa compatível com seu perfil', 'Pesquisa sobre educação online para moradores de Maputo Cidade', 'assignment', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"reward\": 25, \"survey_id\": 23}', '2026-02-05 09:46:49', '2026-02-05 13:46:49'),
(51, 7, 'bonus_received', 'Bônus recebido!', 'Você ganhou um bônus de MZN 10 por responder 5 pesquisas este mês.', 'card_giftcard', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 2, '{\"amount\": 10, \"reason\": \"fidelity\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(52, 7, 'response_completed', 'Resposta completada', 'Você completou a pesquisa \"Tecnologia na Educação\" - MZN 25 creditado.', 'done_all', '/surveys', 'Ver pesquisas', 1, '2026-02-03 13:46:49', '2026-02-20 13:46:49', 1, '{\"amount\": 25, \"survey_id\": 27}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(53, 7, 'payment_credited', 'Pagamento creditado', 'Recebeu MZN 75.00 por pesquisas respondidas.', 'payments', '/surveys', 'Ver pesquisas', 1, '2026-02-02 13:46:49', '2026-02-20 13:46:49', 2, '{\"amount\": 75}', '2026-02-02 13:46:49', '2026-02-05 13:46:49'),
(54, 7, 'qualification_approved', 'Qualificação aprovada', 'Seu perfil agora se qualifica para pesquisas de nível superior.', 'verified', '/surveys', 'Ver pesquisas', 1, '2026-01-31 13:46:49', '2026-02-20 13:46:49', 2, '{\"new_level\": \"advanced\"}', '2026-01-31 13:46:49', '2026-02-05 13:46:49'),
(55, 7, 'profile_update', 'Atualize seu perfil', 'Complete seu perfil para receber mais pesquisas compatíveis.', 'person', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"completion_percentage\": \"65%\"}', '2026-01-29 13:46:49', '2026-02-05 13:46:49'),
(56, 7, 'weekly_summary', 'Resumo semanal', 'Esta semana você ganhou MZN 125.00 respondendo pesquisas. Continue assim!', 'assessment', '/surveys', 'Ver pesquisas', 1, '2026-02-04 13:46:49', '2026-02-20 13:46:49', 1, '{\"earnings\": 125, \"surveys_completed\": 5}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(57, 8, 'survey_available', 'Nova pesquisa disponível', 'Pesquisa sobre hábitos de consumo - Ganhe MZN 30 por responder.', 'assignment', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"reward\": 30, \"survey_id\": 16}', '2026-02-05 12:46:49', '2026-02-05 13:46:49'),
(58, 8, 'survey_available', 'Pesquisa compatível com seu perfil', 'Pesquisa sobre educação online para moradores de Maputo Província', 'assignment', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"reward\": 25, \"survey_id\": 21}', '2026-02-05 09:46:49', '2026-02-05 13:46:49'),
(59, 8, 'bonus_received', 'Bônus recebido!', 'Você ganhou um bônus de MZN 10 por responder 5 pesquisas este mês.', 'card_giftcard', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 2, '{\"amount\": 10, \"reason\": \"fidelity\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(60, 8, 'response_completed', 'Resposta completada', 'Você completou a pesquisa \"Tecnologia na Educação\" - MZN 25 creditado.', 'done_all', '/surveys', 'Ver pesquisas', 1, '2026-02-03 13:46:49', '2026-02-20 13:46:49', 1, '{\"amount\": 25, \"survey_id\": 30}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(61, 8, 'payment_credited', 'Pagamento creditado', 'Recebeu MZN 75.00 por pesquisas respondidas.', 'payments', '/surveys', 'Ver pesquisas', 1, '2026-02-02 13:46:49', '2026-02-20 13:46:49', 2, '{\"amount\": 75}', '2026-02-02 13:46:49', '2026-02-05 13:46:49'),
(62, 8, 'qualification_approved', 'Qualificação aprovada', 'Seu perfil agora se qualifica para pesquisas de nível superior.', 'verified', '/surveys', 'Ver pesquisas', 1, '2026-01-31 13:46:49', '2026-02-20 13:46:49', 2, '{\"new_level\": \"advanced\"}', '2026-01-31 13:46:49', '2026-02-05 13:46:49'),
(63, 8, 'profile_update', 'Atualize seu perfil', 'Complete seu perfil para receber mais pesquisas compatíveis.', 'person', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"completion_percentage\": \"65%\"}', '2026-01-29 13:46:49', '2026-02-05 13:46:49'),
(64, 8, 'weekly_summary', 'Resumo semanal', 'Esta semana você ganhou MZN 125.00 respondendo pesquisas. Continue assim!', 'assessment', '/surveys', 'Ver pesquisas', 1, '2026-02-04 13:46:49', '2026-02-20 13:46:49', 1, '{\"earnings\": 125, \"surveys_completed\": 5}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(65, 9, 'survey_available', 'Nova pesquisa disponível', 'Pesquisa sobre hábitos de consumo - Ganhe MZN 30 por responder.', 'assignment', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"reward\": 30, \"survey_id\": 18}', '2026-02-05 12:46:49', '2026-02-05 13:46:49'),
(66, 9, 'survey_available', 'Pesquisa compatível com seu perfil', 'Pesquisa sobre educação online para moradores de Gaza', 'assignment', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"reward\": 25, \"survey_id\": 25}', '2026-02-05 09:46:49', '2026-02-05 13:46:49'),
(67, 9, 'bonus_received', 'Bônus recebido!', 'Você ganhou um bônus de MZN 10 por responder 5 pesquisas este mês.', 'card_giftcard', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 2, '{\"amount\": 10, \"reason\": \"fidelity\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(68, 9, 'response_completed', 'Resposta completada', 'Você completou a pesquisa \"Tecnologia na Educação\" - MZN 25 creditado.', 'done_all', '/surveys', 'Ver pesquisas', 1, '2026-02-03 13:46:49', '2026-02-20 13:46:49', 1, '{\"amount\": 25, \"survey_id\": 26}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(69, 9, 'payment_credited', 'Pagamento creditado', 'Recebeu MZN 75.00 por pesquisas respondidas.', 'payments', '/surveys', 'Ver pesquisas', 1, '2026-02-02 13:46:49', '2026-02-20 13:46:49', 2, '{\"amount\": 75}', '2026-02-02 13:46:49', '2026-02-05 13:46:49'),
(70, 9, 'qualification_approved', 'Qualificação aprovada', 'Seu perfil agora se qualifica para pesquisas de nível superior.', 'verified', '/surveys', 'Ver pesquisas', 1, '2026-01-31 13:46:49', '2026-02-20 13:46:49', 2, '{\"new_level\": \"advanced\"}', '2026-01-31 13:46:49', '2026-02-05 13:46:49'),
(71, 9, 'profile_update', 'Atualize seu perfil', 'Complete seu perfil para receber mais pesquisas compatíveis.', 'person', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"completion_percentage\": \"65%\"}', '2026-01-29 13:46:49', '2026-02-05 13:46:49'),
(72, 9, 'weekly_summary', 'Resumo semanal', 'Esta semana você ganhou MZN 125.00 respondendo pesquisas. Continue assim!', 'assessment', '/surveys', 'Ver pesquisas', 1, '2026-02-04 13:46:49', '2026-02-20 13:46:49', 1, '{\"earnings\": 125, \"surveys_completed\": 5}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(73, 10, 'survey_available', 'Nova pesquisa disponível', 'Pesquisa sobre hábitos de consumo - Ganhe MZN 30 por responder.', 'assignment', '/surveys', 'Ver pesquisas', 1, '2026-02-07 05:45:41', '2026-02-20 13:46:49', 1, '{\"reward\": 30, \"survey_id\": 20}', '2026-02-05 12:46:49', '2026-02-07 05:45:41'),
(74, 10, 'survey_available', 'Pesquisa compatível com seu perfil', 'Pesquisa sobre educação online para moradores de Inhambane', 'assignment', '/surveys', 'Ver pesquisas', 1, '2026-02-07 05:45:41', '2026-02-20 13:46:49', 1, '{\"reward\": 25, \"survey_id\": 24}', '2026-02-05 09:46:49', '2026-02-07 05:45:41'),
(75, 10, 'bonus_received', 'Bônus recebido!', 'Você ganhou um bônus de MZN 10 por responder 5 pesquisas este mês.', 'card_giftcard', '/surveys', 'Ver pesquisas', 1, '2026-02-07 05:45:41', '2026-02-20 13:46:49', 2, '{\"amount\": 10, \"reason\": \"fidelity\"}', '2026-02-04 13:46:49', '2026-02-07 05:45:41'),
(76, 10, 'response_completed', 'Resposta completada', 'Você completou a pesquisa \"Tecnologia na Educação\" - MZN 25 creditado.', 'done_all', '/surveys', 'Ver pesquisas', 1, '2026-02-03 13:46:49', '2026-02-20 13:46:49', 1, '{\"amount\": 25, \"survey_id\": 27}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(77, 10, 'payment_credited', 'Pagamento creditado', 'Recebeu MZN 75.00 por pesquisas respondidas.', 'payments', '/surveys', 'Ver pesquisas', 1, '2026-02-02 13:46:49', '2026-02-20 13:46:49', 2, '{\"amount\": 75}', '2026-02-02 13:46:49', '2026-02-05 13:46:49'),
(78, 10, 'qualification_approved', 'Qualificação aprovada', 'Seu perfil agora se qualifica para pesquisas de nível superior.', 'verified', '/surveys', 'Ver pesquisas', 1, '2026-01-31 13:46:49', '2026-02-20 13:46:49', 2, '{\"new_level\": \"advanced\"}', '2026-01-31 13:46:49', '2026-02-05 13:46:49'),
(79, 10, 'profile_update', 'Atualize seu perfil', 'Complete seu perfil para receber mais pesquisas compatíveis.', 'person', '/surveys', 'Ver pesquisas', 1, '2026-02-07 05:45:41', '2026-02-20 13:46:49', 1, '{\"completion_percentage\": \"65%\"}', '2026-01-29 13:46:49', '2026-02-07 05:45:41'),
(80, 10, 'weekly_summary', 'Resumo semanal', 'Esta semana você ganhou MZN 125.00 respondendo pesquisas. Continue assim!', 'assessment', '/surveys', 'Ver pesquisas', 1, '2026-02-04 13:46:49', '2026-02-20 13:46:49', 1, '{\"earnings\": 125, \"surveys_completed\": 5}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(81, 11, 'survey_available', 'Nova pesquisa disponível', 'Pesquisa sobre hábitos de consumo - Ganhe MZN 30 por responder.', 'assignment', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"reward\": 30, \"survey_id\": 17}', '2026-02-05 12:46:49', '2026-02-05 13:46:49'),
(82, 11, 'survey_available', 'Pesquisa compatível com seu perfil', 'Pesquisa sobre educação online para moradores de Sofala', 'assignment', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"reward\": 25, \"survey_id\": 21}', '2026-02-05 09:46:49', '2026-02-05 13:46:49'),
(83, 11, 'bonus_received', 'Bônus recebido!', 'Você ganhou um bônus de MZN 10 por responder 5 pesquisas este mês.', 'card_giftcard', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 2, '{\"amount\": 10, \"reason\": \"fidelity\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(84, 11, 'response_completed', 'Resposta completada', 'Você completou a pesquisa \"Tecnologia na Educação\" - MZN 25 creditado.', 'done_all', '/surveys', 'Ver pesquisas', 1, '2026-02-03 13:46:49', '2026-02-20 13:46:49', 1, '{\"amount\": 25, \"survey_id\": 29}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(85, 11, 'payment_credited', 'Pagamento creditado', 'Recebeu MZN 75.00 por pesquisas respondidas.', 'payments', '/surveys', 'Ver pesquisas', 1, '2026-02-02 13:46:49', '2026-02-20 13:46:49', 2, '{\"amount\": 75}', '2026-02-02 13:46:49', '2026-02-05 13:46:49'),
(86, 11, 'qualification_approved', 'Qualificação aprovada', 'Seu perfil agora se qualifica para pesquisas de nível superior.', 'verified', '/surveys', 'Ver pesquisas', 1, '2026-01-31 13:46:49', '2026-02-20 13:46:49', 2, '{\"new_level\": \"advanced\"}', '2026-01-31 13:46:49', '2026-02-05 13:46:49'),
(87, 11, 'profile_update', 'Atualize seu perfil', 'Complete seu perfil para receber mais pesquisas compatíveis.', 'person', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"completion_percentage\": \"65%\"}', '2026-01-29 13:46:49', '2026-02-05 13:46:49'),
(88, 11, 'weekly_summary', 'Resumo semanal', 'Esta semana você ganhou MZN 125.00 respondendo pesquisas. Continue assim!', 'assessment', '/surveys', 'Ver pesquisas', 1, '2026-02-04 13:46:49', '2026-02-20 13:46:49', 1, '{\"earnings\": 125, \"surveys_completed\": 5}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(89, 12, 'survey_available', 'Nova pesquisa disponível', 'Pesquisa sobre hábitos de consumo - Ganhe MZN 30 por responder.', 'assignment', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"reward\": 30, \"survey_id\": 16}', '2026-02-05 12:46:49', '2026-02-05 13:46:49'),
(90, 12, 'survey_available', 'Pesquisa compatível com seu perfil', 'Pesquisa sobre educação online para moradores de Manica', 'assignment', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"reward\": 25, \"survey_id\": 22}', '2026-02-05 09:46:49', '2026-02-05 13:46:49'),
(91, 12, 'bonus_received', 'Bônus recebido!', 'Você ganhou um bônus de MZN 10 por responder 5 pesquisas este mês.', 'card_giftcard', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 2, '{\"amount\": 10, \"reason\": \"fidelity\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(92, 12, 'response_completed', 'Resposta completada', 'Você completou a pesquisa \"Tecnologia na Educação\" - MZN 25 creditado.', 'done_all', '/surveys', 'Ver pesquisas', 1, '2026-02-03 13:46:49', '2026-02-20 13:46:49', 1, '{\"amount\": 25, \"survey_id\": 26}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(93, 12, 'payment_credited', 'Pagamento creditado', 'Recebeu MZN 75.00 por pesquisas respondidas.', 'payments', '/surveys', 'Ver pesquisas', 1, '2026-02-02 13:46:49', '2026-02-20 13:46:49', 2, '{\"amount\": 75}', '2026-02-02 13:46:49', '2026-02-05 13:46:49'),
(94, 12, 'qualification_approved', 'Qualificação aprovada', 'Seu perfil agora se qualifica para pesquisas de nível superior.', 'verified', '/surveys', 'Ver pesquisas', 1, '2026-01-31 13:46:49', '2026-02-20 13:46:49', 2, '{\"new_level\": \"advanced\"}', '2026-01-31 13:46:49', '2026-02-05 13:46:49'),
(95, 12, 'profile_update', 'Atualize seu perfil', 'Complete seu perfil para receber mais pesquisas compatíveis.', 'person', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"completion_percentage\": \"65%\"}', '2026-01-29 13:46:49', '2026-02-05 13:46:49'),
(96, 12, 'weekly_summary', 'Resumo semanal', 'Esta semana você ganhou MZN 125.00 respondendo pesquisas. Continue assim!', 'assessment', '/surveys', 'Ver pesquisas', 1, '2026-02-04 13:46:49', '2026-02-20 13:46:49', 1, '{\"earnings\": 125, \"surveys_completed\": 5}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(97, 14, 'survey_available', 'Nova pesquisa disponível', 'Pesquisa sobre hábitos de consumo - Ganhe MZN 30 por responder.', 'assignment', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"reward\": 30, \"survey_id\": 16}', '2026-02-05 12:46:49', '2026-02-05 13:46:49'),
(98, 14, 'survey_available', 'Pesquisa compatível com seu perfil', 'Pesquisa sobre educação online para moradores de Zambézia', 'assignment', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"reward\": 25, \"survey_id\": 25}', '2026-02-05 09:46:49', '2026-02-05 13:46:49'),
(99, 14, 'bonus_received', 'Bônus recebido!', 'Você ganhou um bônus de MZN 10 por responder 5 pesquisas este mês.', 'card_giftcard', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 2, '{\"amount\": 10, \"reason\": \"fidelity\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(100, 14, 'response_completed', 'Resposta completada', 'Você completou a pesquisa \"Tecnologia na Educação\" - MZN 25 creditado.', 'done_all', '/surveys', 'Ver pesquisas', 1, '2026-02-03 13:46:49', '2026-02-20 13:46:49', 1, '{\"amount\": 25, \"survey_id\": 28}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(101, 14, 'payment_credited', 'Pagamento creditado', 'Recebeu MZN 75.00 por pesquisas respondidas.', 'payments', '/surveys', 'Ver pesquisas', 1, '2026-02-02 13:46:49', '2026-02-20 13:46:49', 2, '{\"amount\": 75}', '2026-02-02 13:46:49', '2026-02-05 13:46:49'),
(102, 14, 'qualification_approved', 'Qualificação aprovada', 'Seu perfil agora se qualifica para pesquisas de nível superior.', 'verified', '/surveys', 'Ver pesquisas', 1, '2026-01-31 13:46:49', '2026-02-20 13:46:49', 2, '{\"new_level\": \"advanced\"}', '2026-01-31 13:46:49', '2026-02-05 13:46:49'),
(103, 14, 'profile_update', 'Atualize seu perfil', 'Complete seu perfil para receber mais pesquisas compatíveis.', 'person', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"completion_percentage\": \"65%\"}', '2026-01-29 13:46:49', '2026-02-05 13:46:49'),
(104, 14, 'weekly_summary', 'Resumo semanal', 'Esta semana você ganhou MZN 125.00 respondendo pesquisas. Continue assim!', 'assessment', '/surveys', 'Ver pesquisas', 1, '2026-02-04 13:46:49', '2026-02-20 13:46:49', 1, '{\"earnings\": 125, \"surveys_completed\": 5}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(105, 15, 'survey_available', 'Nova pesquisa disponível', 'Pesquisa sobre hábitos de consumo - Ganhe MZN 30 por responder.', 'assignment', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"reward\": 30, \"survey_id\": 17}', '2026-02-05 12:46:49', '2026-02-05 13:46:49'),
(106, 15, 'survey_available', 'Pesquisa compatível com seu perfil', 'Pesquisa sobre educação online para moradores de Nampula', 'assignment', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"reward\": 25, \"survey_id\": 22}', '2026-02-05 09:46:49', '2026-02-05 13:46:49'),
(107, 15, 'bonus_received', 'Bônus recebido!', 'Você ganhou um bônus de MZN 10 por responder 5 pesquisas este mês.', 'card_giftcard', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 2, '{\"amount\": 10, \"reason\": \"fidelity\"}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(108, 15, 'response_completed', 'Resposta completada', 'Você completou a pesquisa \"Tecnologia na Educação\" - MZN 25 creditado.', 'done_all', '/surveys', 'Ver pesquisas', 1, '2026-02-03 13:46:49', '2026-02-20 13:46:49', 1, '{\"amount\": 25, \"survey_id\": 28}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(109, 15, 'payment_credited', 'Pagamento creditado', 'Recebeu MZN 75.00 por pesquisas respondidas.', 'payments', '/surveys', 'Ver pesquisas', 1, '2026-02-02 13:46:49', '2026-02-20 13:46:49', 2, '{\"amount\": 75}', '2026-02-02 13:46:49', '2026-02-05 13:46:49'),
(110, 15, 'qualification_approved', 'Qualificação aprovada', 'Seu perfil agora se qualifica para pesquisas de nível superior.', 'verified', '/surveys', 'Ver pesquisas', 1, '2026-01-31 13:46:49', '2026-02-20 13:46:49', 2, '{\"new_level\": \"advanced\"}', '2026-01-31 13:46:49', '2026-02-05 13:46:49'),
(111, 15, 'profile_update', 'Atualize seu perfil', 'Complete seu perfil para receber mais pesquisas compatíveis.', 'person', '/surveys', 'Ver pesquisas', 0, NULL, '2026-02-20 13:46:49', 1, '{\"completion_percentage\": \"65%\"}', '2026-01-29 13:46:49', '2026-02-05 13:46:49'),
(112, 15, 'weekly_summary', 'Resumo semanal', 'Esta semana você ganhou MZN 125.00 respondendo pesquisas. Continue assim!', 'assessment', '/surveys', 'Ver pesquisas', 1, '2026-02-04 13:46:49', '2026-02-20 13:46:49', 1, '{\"earnings\": 125, \"surveys_completed\": 5}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(113, 1, 'system_alert', 'Alerta do sistema', 'Alta atividade de respostas detectada nas últimas 24h. Verifique logs.', 'warning', '/admin/dashboard', 'Administrar', 0, NULL, '2026-04-06 13:46:49', 3, '{\"activity_count\": 245}', '2026-02-05 12:46:49', '2026-02-05 13:46:49'),
(114, 1, 'new_user_registered', 'Novo estudante registrado', 'Ana Paula Macuácua registrou-se como estudante do ISCTEM.', 'person_add', '/admin/dashboard', 'Administrar', 0, NULL, '2026-04-06 13:46:49', 2, '{\"user_id\": 6, \"user_type\": \"student\"}', '2026-02-05 10:46:49', '2026-02-05 13:46:49'),
(115, 1, 'survey_pending_review', 'Pesquisas pendentes', '3 pesquisas aguardando aprovação.', 'pending_actions', '/admin/dashboard', 'Administrar', 0, NULL, '2026-04-06 13:46:49', 2, '{\"pending_count\": 3}', '2026-02-05 07:46:49', '2026-02-05 13:46:49'),
(116, 1, 'withdrawal_requested', 'Novo pedido de saque', 'Carlos Mondlane solicitou saque de MZN 300.00.', 'request_quote', '/admin/dashboard', 'Administrar', 1, '2026-02-07 06:31:53', '2026-04-06 13:46:49', 2, '{\"amount\": 300, \"user_id\": 2}', '2026-02-05 01:46:49', '2026-02-07 06:31:53'),
(117, 1, 'user_verification_pending', 'Verificações pendentes', '2 usuários aguardando verificação de documentos.', 'verified_user', '/admin/dashboard', 'Administrar', 1, '2026-02-04 13:46:49', '2026-04-06 13:46:49', 1, '{\"pending_verifications\": 2}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(118, 1, 'batch_payment_processed', 'Pagamentos processados', 'Pagamentos em lote processados: 15 transações concluídas.', 'payments', '/admin/dashboard', 'Administrar', 1, '2026-02-03 13:46:49', '2026-04-06 13:46:49', 1, '{\"total_amount\": 1250, \"transactions\": 15}', '2026-02-03 13:46:49', '2026-02-05 13:46:49'),
(119, 1, 'low_system_funds', 'Fundos do sistema baixos', 'Fundos do sistema: MZN 1.500 restantes. Considere recarregar.', 'account_balance', '/admin/dashboard', 'Administrar', 1, '2026-02-02 13:46:49', '2026-04-06 13:46:49', 2, '{\"balance\": 1500, \"threshold\": 2000}', '2026-02-02 13:46:49', '2026-02-05 13:46:49'),
(120, 1, 'general_announcement', 'Relatório semanal disponível', 'Relatório semanal do sistema está disponível para consulta.', 'assessment', '/admin/dashboard', 'Administrar', 0, NULL, '2026-04-06 13:46:49', 1, '{\"period\": \"semanal\", \"report_id\": 1}', '2026-02-04 13:46:49', '2026-02-05 13:46:49'),
(121, 1, 'high_activity', 'Alta atividade detectada', 'Pico de acessos detectado: 1.234 usuários ativos nas últimas 2h.', 'trending_up', '/admin/dashboard', 'Administrar', 0, NULL, '2026-04-06 13:46:49', 2, '{\"time_period\": \"2h\", \"active_users\": 1234}', '2026-02-05 11:46:49', '2026-02-05 13:46:49'),
(122, 17, 'survey_response', 'Progresso da pesquisa', 'Sua pesquisa sobre metodologias de ensino já tem 120 respostas.', 'assignment_turned_in', '/research/dashboard', 'Ver pesquisa', 0, NULL, '2026-03-22 13:46:50', 1, '{\"responses\": 120, \"survey_id\": 31}', '2026-02-05 09:46:50', '2026-02-05 13:46:50'),
(123, 17, 'deadline_alert', 'Prazo importante', 'Último dia para coletar dados da sua pesquisa de mestrado.', 'event', '/research/dashboard', 'Ver pesquisa', 0, NULL, '2026-03-22 13:46:50', 3, '{\"deadline_date\": \"2026-02-06\"}', '2026-02-04 13:46:50', '2026-02-05 13:46:50'),
(124, 17, 'research_reminder', 'Lembrete de análise', 'Lembre-se de analisar os dados coletados esta semana.', 'analytics', '/research/dashboard', 'Ver pesquisa', 0, NULL, '2026-03-22 13:46:50', 2, '{\"reminder_type\": \"data_analysis\"}', '2026-02-03 13:46:50', '2026-02-05 13:46:50'),
(125, 17, 'survey_approved', 'Pesquisa aprovada pelo comitê', 'Sua pesquisa foi aprovada pelo comitê de ética.', 'verified', '/research/dashboard', 'Ver pesquisa', 1, '2026-02-02 13:46:50', '2026-03-22 13:46:50', 2, '{\"committee\": \"Comitê de Ética em Pesquisa\"}', '2026-02-02 13:46:50', '2026-02-05 13:46:50'),
(126, 17, 'general_announcement', 'Comentários dos participantes', 'Os participantes deixaram comentários sobre sua pesquisa.', 'forum', '/research/dashboard', 'Ver pesquisa', 1, '2026-02-01 13:46:50', '2026-03-22 13:46:50', 1, '{\"feedback_count\": 8}', '2026-02-01 13:46:50', '2026-02-05 13:46:50'),
(127, 17, 'general_announcement', 'Novo recurso disponível', 'Curso: \"Análise de Dados Qualitativos\" disponível na plataforma.', 'school', '/research/dashboard', 'Ver pesquisa', 0, NULL, '2026-03-22 13:46:50', 1, '{\"course_id\": 1, \"course_title\": \"Análise de Dados Qualitativos\"}', '2026-01-31 13:46:50', '2026-02-05 13:46:50');

-- --------------------------------------------------------

--
-- Table structure for table participant_stats
--

DROP TABLE IF EXISTS participant_stats;
CREATE TABLE IF NOT EXISTS participant_stats (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id bigint UNSIGNED NOT NULL,
  birth_date date DEFAULT NULL,
  gender enum('masculino','feminino','outro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  province varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  bi_number varchar(13) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  mpesa_number varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  occupation varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  education_level varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  research_interests json DEFAULT NULL,
  participation_frequency varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  consent_data_collection tinyint(1) NOT NULL DEFAULT '0',
  sms_notifications tinyint(1) NOT NULL DEFAULT '1',
  total_surveys_completed int NOT NULL DEFAULT '0',
  total_earnings decimal(10,2) NOT NULL DEFAULT '0.00',
  last_survey_date timestamp NULL DEFAULT NULL,
  metadata json DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY participant_stats_bi_number_unique (bi_number),
  KEY participant_stats_user_id_index (user_id),
  KEY participant_stats_province_index (province),
  KEY participant_stats_occupation_index (occupation),
  KEY participant_stats_total_surveys_completed_index (total_surveys_completed),
  KEY participant_stats_total_earnings_index (total_earnings),
  KEY participant_stats_last_survey_date_index (last_survey_date)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table participant_stats
--

INSERT INTO participant_stats (id, user_id, birth_date, gender, province, bi_number, mpesa_number, occupation, education_level, research_interests, participation_frequency, consent_data_collection, sms_notifications, total_surveys_completed, total_earnings, last_survey_date, metadata, created_at, updated_at) VALUES
(1, 7, '1985-04-12', 'masculino', 'Maputo Cidade', '678901234567F', '+258846667788', 'Engenheiro Civil', 'Licenciatura', '\"[\\\"tecnologia\\\",\\\"infraestrutura\\\",\\\"desenvolvimento_urbano\\\"]\"', 'Regularmente (várias vezes por semana)', 1, 0, 3, 325.75, '2026-01-29 19:35:01', NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(2, 8, '1990-08-25', 'feminino', 'Maputo Província', '789012345678G', '+258847778899', 'Professora', 'Pós-graduação/Mestrado', '\"[\\\"educacao\\\",\\\"formacao_continua\\\",\\\"tecnologia_educativa\\\"]\"', 'Frequentemente (1-2 vezes por semana)', 1, 0, 19, 480.50, '2025-12-05 19:35:01', NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(3, 9, '1988-12-18', 'masculino', 'Gaza', '890123456789H', '+258848889900', 'Empresário', 'Ensino Superior Incompleto', '\"[\\\"economia\\\",\\\"empreendedorismo\\\",\\\"gestao\\\"]\"', 'Ocasionalmente (1-2 vezes por mês)', 1, 0, 2, 195.00, '2026-01-30 19:35:01', NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(4, 10, '1992-03-30', 'feminino', 'Inhambane', '901234567890I', '+258849990011', 'Médica', 'Licenciatura', '\"[\\\"saude\\\",\\\"medicina_preventiva\\\",\\\"nutricao\\\"]\"', 'Regularmente (várias vezes por semana)', 1, 0, 14, 620.25, '2025-12-16 19:35:01', NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(5, 11, '1975-06-22', 'masculino', 'Sofala', '012345678901J', '+258840011122', 'Funcionário Público', 'Curso Técnico', '\"[\\\"administracao_publica\\\",\\\"politica\\\",\\\"gestao_publica\\\"]\"', 'Regularmente (várias vezes por semana)', 1, 0, 15, 275.50, '2025-12-20 19:35:01', NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(6, 12, '1995-09-14', 'feminino', 'Manica', '112345678901K', '+258841122233', 'Estudante Universitário', 'Ensino Superior Incompleto', '\"[\\\"sociologia\\\",\\\"desenvolvimento_comunitario\\\",\\\"genero\\\"]\"', 'Frequentemente (1-2 vezes por semana)', 1, 0, 3, 150.00, '2025-12-12 19:35:01', NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(7, 13, '1998-01-27', 'masculino', 'Tete', '212345678901L', '+258842233344', 'Agricultor', 'Ensino Secundário (até 10ª classe)', '\"[\\\"agricultura\\\",\\\"meio_ambiente\\\",\\\"desenvolvimento_rural\\\"]\"', 'Frequentemente (1-2 vezes por semana)', 1, 0, 18, 0.00, '2026-01-12 19:35:01', NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(8, 14, '1987-11-19', 'feminino', 'Zambézia', '312345678901M', '+258843344455', 'Enfermeira', 'Curso Técnico', '\"[\\\"saude\\\",\\\"enfermagem\\\",\\\"cuidados_paliativos\\\"]\"', 'Regularmente (várias vezes por semana)', 1, 0, 18, 385.75, '2026-01-11 19:35:01', NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(9, 15, '1982-07-05', 'masculino', 'Nampula', '412345678901N', '+258844455566', 'Comerciante', 'Ensino Médio (12ª classe)', '\"[\\\"economia\\\",\\\"comercio\\\",\\\"gestao_financeira\\\"]\"', 'Ocasionalmente (1-2 vezes por mês)', 1, 0, 12, 225.00, '2025-11-30 19:35:01', NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(10, 16, '1993-10-15', 'feminino', 'Cabo Delgado', '512345678901O', '+258845566677', 'Desempregado(a)', 'Ensino Superior Incompleto', '\"[\\\"desenvolvimento_social\\\",\\\"emprego\\\",\\\"qualificacao_profissional\\\"]\"', 'Ocasionalmente (1-2 vezes por mês)', 1, 0, 15, 0.00, '2026-01-28 19:35:02', NULL, '2026-02-03 19:35:02', '2026-02-03 19:35:02');

-- --------------------------------------------------------

--
-- Table structure for table payments
--

DROP TABLE IF EXISTS payments;
CREATE TABLE IF NOT EXISTS payments (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  payment_intent_id varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  client_secret varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  amount decimal(10,2) NOT NULL,
  currency varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'MZN',
  customer_phone varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  payment_method varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mpesa',
  provider varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  status varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  mpesa_reference varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  mpesa_response_code varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  mpesa_response_message text COLLATE utf8mb4_unicode_ci,
  metadata json DEFAULT NULL,
  idempotency_key varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  deleted_at timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY payments_idempotency_key_unique (idempotency_key),
  KEY payments_status_index (status),
  KEY payments_customer_phone_index (customer_phone),
  KEY payments_mpesa_reference_index (mpesa_reference),
  KEY payments_created_at_index (created_at)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table personal_access_tokens
--

DROP TABLE IF EXISTS personal_access_tokens;
CREATE TABLE IF NOT EXISTS personal_access_tokens (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  tokenable_type varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  tokenable_id bigint UNSIGNED NOT NULL,
  name varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  token varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  abilities text COLLATE utf8mb4_unicode_ci,
  last_used_at timestamp NULL DEFAULT NULL,
  expires_at timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY personal_access_tokens_token_unique (token),
  KEY personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type,tokenable_id)
) ENGINE=MyISAM AUTO_INCREMENT=186 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table personal_access_tokens
--

INSERT INTO personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) VALUES
(1, 'App\\Models\\User', 2, 'auth_token', '94c617ea8305a8b51745170bd51411c8bd4232982e0cb5c0033b1ea5bb07e3f6', '[\"*\"]', '2026-02-04 06:36:32', NULL, '2026-02-03 19:37:06', '2026-02-04 06:36:32'),
(9, 'App\\Models\\User', 1, 'auth_token', '97710c87e113337e5b064f74e98c759dd2e3cb57fbd1b2a455308779d7f23baf', '[\"*\"]', NULL, NULL, '2026-02-05 08:01:46', '2026-02-05 08:01:46'),
(32, 'App\\Models\\User', 1, 'auth_token', '57a49b50ed2747d5167f58db7ba6af2956ee7435bf0078fc238a3dad649620dc', '[\"*\"]', '2026-02-06 08:27:42', NULL, '2026-02-06 08:02:37', '2026-02-06 08:27:42'),
(44, 'App\\Models\\User', 1, 'auth_token', '6c49536643f0e3997375e701fcad700ae71b4485466c48757a2d3c73476f7df7', '[\"*\"]', NULL, NULL, '2026-02-07 06:30:54', '2026-02-07 06:30:54'),
(52, 'App\\Models\\User', 1, 'auth_token', '31cd21ca6bc196b1d2d9da909ddb41125624e0dba9438884536a25d85877e86d', '[\"*\"]', NULL, NULL, '2026-02-07 10:20:10', '2026-02-07 10:20:10'),
(62, 'App\\Models\\User', 9, 'auth_token', 'd2dec4e284670a866bf3e38dc320071b306bb790e85bccb78204a9c0c7c50830', '[\"*\"]', '2026-02-07 13:06:51', NULL, '2026-02-07 13:03:12', '2026-02-07 13:06:51'),
(64, 'App\\Models\\User', 10, 'auth_token', '1044f1cb86011872188104d07ced7c52f527aeadd0cc74c60f79e1329279c73e', '[\"*\"]', '2026-02-07 14:26:50', NULL, '2026-02-07 14:26:18', '2026-02-07 14:26:50'),
(84, 'App\\Models\\User', 1, 'auth_token', 'dcc3daa26edc3bf71450752555f9a0ada32a5b2f0a49cfed5e040cd3068dbbc4', '[\"*\"]', NULL, NULL, '2026-02-10 00:36:48', '2026-02-10 00:36:48'),
(102, 'App\\Models\\User', 1, 'auth_token', '2102249601dea8aaf63af79e0368cc168c78ae6e1ffb9e4d450963e4d0625ebb', '[\"*\"]', '2026-02-11 04:38:36', NULL, '2026-02-11 04:18:13', '2026-02-11 04:38:36'),
(117, 'App\\Models\\User', 1, 'auth_token', 'e5f6d033efdaebd1052476027b27fba7da829d465f298c12836705eb3947f3a7', '[\"*\"]', NULL, NULL, '2026-02-12 09:26:30', '2026-02-12 09:26:30'),
(185, 'App\\Models\\User', 2, 'auth_token', '76e1bf6b3999d42df2a539fefd1c8305cf17e2c5b350d7d2a38c586264de1f78', '[\"*\"]', '2026-02-16 02:25:19', NULL, '2026-02-15 23:58:41', '2026-02-16 02:25:19'),
(167, 'App\\Models\\User', 1, 'auth_token', 'cbcefed003af84100bc503815754d8249cfe1b688c2ba7c17d0ac93950c8dbbc', '[\"*\"]', '2026-02-14 15:18:41', NULL, '2026-02-14 14:12:06', '2026-02-14 15:18:41'),
(165, 'App\\Models\\User', 1, 'auth_token', 'a75b4d3c47a1fb8750938e60c8f3c7e42350b5f2d5a1898e758bb51734e7ceb0', '[\"*\"]', NULL, NULL, '2026-02-14 02:47:21', '2026-02-14 02:47:21');

-- --------------------------------------------------------

--
-- Table structure for table report_histories
--

DROP TABLE IF EXISTS report_histories;
CREATE TABLE IF NOT EXISTS report_histories (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id bigint UNSIGNED NOT NULL,
  report_type varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  title varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  format varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  parameters json DEFAULT NULL,
  file_path varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  file_size int DEFAULT NULL,
  generated_at timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY report_histories_user_id_foreign (user_id)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table report_histories
--

INSERT INTO report_histories (id, user_id, report_type, title, format, parameters, file_path, file_size, generated_at, created_at, updated_at) VALUES
(1, 2, 'student_summary', 'Resumo Estudante', 'csv', '\"{\\\"report_type\\\":\\\"student_summary\\\",\\\"title\\\":\\\"Resumo Estudante\\\",\\\"data\\\":{\\\"test\\\":\\\"data\\\"}}\"', 'reports/relatorio-student_summary-2026-02-05-07-58-22.csv', NULL, '2026-02-05 05:58:22', '2026-02-05 05:58:22', '2026-02-05 05:58:22'),
(2, 2, 'student_summary', 'Resumo Estudante', 'json', '\"{\\\"report_type\\\":\\\"student_summary\\\",\\\"title\\\":\\\"Resumo Estudante\\\",\\\"data\\\":{\\\"test\\\":\\\"data\\\"}}\"', 'reports/relatorio-student_summary-2026-02-05-07-58-22.json', NULL, '2026-02-05 05:58:22', '2026-02-05 05:58:22', '2026-02-05 05:58:22');

-- --------------------------------------------------------

--
-- Table structure for table report_templates
--

DROP TABLE IF EXISTS report_templates;
CREATE TABLE IF NOT EXISTS report_templates (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id bigint UNSIGNED NOT NULL,
  name varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  report_type varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  filters json DEFAULT NULL,
  columns json DEFAULT NULL,
  format varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pdf',
  schedule json DEFAULT NULL,
  is_active tinyint(1) NOT NULL DEFAULT '1',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY report_templates_user_id_foreign (user_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table sessions
--

DROP TABLE IF EXISTS sessions;
CREATE TABLE IF NOT EXISTS sessions (
  id varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  user_id bigint UNSIGNED DEFAULT NULL,
  ip_address varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  user_agent text COLLATE utf8mb4_unicode_ci,
  payload longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  last_activity int NOT NULL,
  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY sessions_user_id_index (user_id),
  KEY sessions_last_activity_index (last_activity)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table student_documents
--

DROP TABLE IF EXISTS student_documents;
CREATE TABLE IF NOT EXISTS student_documents (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id bigint UNSIGNED NOT NULL,
  document_type enum('student_card','enrollment_proof','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  file_path varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  file_name varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  mime_type varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  file_size int NOT NULL,
  status enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  rejection_reason text COLLATE utf8mb4_unicode_ci,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY student_documents_user_id_document_type_index (user_id,document_type),
  KEY student_documents_status_index (status)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table student_stats
--

DROP TABLE IF EXISTS student_stats;
CREATE TABLE IF NOT EXISTS student_stats (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id bigint UNSIGNED NOT NULL,
  bi_number varchar(13) COLLATE utf8mb4_unicode_ci NOT NULL,
  birth_date date DEFAULT NULL,
  gender enum('masculino','feminino') COLLATE utf8mb4_unicode_ci NOT NULL,
  institution_type varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  university varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  course varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  admission_year int DEFAULT NULL,
  expected_graduation int DEFAULT NULL,
  academic_level varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  student_card_number varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  research_interests json DEFAULT NULL,
  documents_submitted tinyint(1) NOT NULL DEFAULT '0',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY student_stats_bi_number_unique (bi_number),
  KEY student_stats_user_id_foreign (user_id),
  KEY student_stats_bi_number_index (bi_number),
  KEY student_stats_university_index (university),
  KEY student_stats_course_index (course),
  KEY student_stats_documents_submitted_index (documents_submitted)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table student_stats
--

INSERT INTO student_stats (id, user_id, bi_number, birth_date, gender, institution_type, university, course, admission_year, expected_graduation, academic_level, student_card_number, research_interests, documents_submitted, created_at, updated_at) VALUES
(1, 2, '123456789012A', '2000-05-15', 'masculino', NULL, 'Universidade Eduardo Mondlane (UEM)', 'Engenharia Informática', 2020, 2024, 'Licenciatura - 4º ano', 'UEM202012345', '\"[\\\"tecnologia\\\",\\\"inteligencia_artificial\\\",\\\"desenvolvimento_web\\\"]\"', 1, '2026-02-03 19:34:59', '2026-02-03 19:34:59'),
(2, 3, '234567890123B', '2001-03-22', 'feminino', NULL, 'Universidade Pedagógica (UP)', 'Ciências da Educação', 2021, 2025, 'Licenciatura - 3º ano', 'UP202123456', '\"[\\\"educacao_inclusiva\\\",\\\"psicologia_educacional\\\",\\\"tecnologia_educacional\\\"]\"', 1, '2026-02-03 19:34:59', '2026-02-03 19:34:59'),
(3, 4, '345678901234C', '1999-11-08', 'masculino', NULL, 'Universidade Lúrio (UniLúrio)', 'Medicina', 2019, 2025, 'Licenciatura - 5º ano', 'UL201934567', '\"[\\\"saude_publica\\\",\\\"medicina_tropical\\\",\\\"epidemiologia\\\"]\"', 1, '2026-02-03 19:34:59', '2026-02-03 19:34:59'),
(4, 5, '456789012345D', '2002-07-30', 'feminino', NULL, 'Instituto Superior de Ciências e Tecnologia de Moçambique (ISCTEM)', 'Arquitetura', 2022, 2026, 'Licenciatura - 2º ano', 'ISCTEM202278901', '\"[\\\"arquitetura_sustentavel\\\",\\\"urbanismo\\\",\\\"design_urbano\\\"]\"', 0, '2026-02-03 19:34:59', '2026-02-03 19:34:59'),
(5, 6, '567890123456E', '2001-01-18', 'masculino', NULL, 'Universidade Católica de Moçambique (UCM)', 'Direito', 2021, 2025, 'Licenciatura - 3º ano', 'UCM202156789', '\"[\\\"direito_humano\\\",\\\"direito_constitucional\\\",\\\"direito_internacional\\\"]\"', 1, '2026-02-03 19:34:59', '2026-02-03 19:34:59'),
(6, 17, '999999999999Z', '1990-01-01', 'masculino', NULL, 'Universidade Eduardo Mondlane (UEM)', 'Mestrado em Pesquisa Social', 2024, 2026, 'Mestrado', 'UEM202499999', '\"[\\\"pesquisa_social\\\",\\\"metodologia\\\",\\\"analise_dados\\\"]\"', 1, '2026-02-03 19:35:02', '2026-02-03 19:35:02');

-- --------------------------------------------------------

--
-- Table structure for table surveys
--

DROP TABLE IF EXISTS surveys;
CREATE TABLE IF NOT EXISTS surveys (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id bigint UNSIGNED NOT NULL,
  researcher_id bigint UNSIGNED DEFAULT NULL,
  title varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  description text COLLATE utf8mb4_unicode_ci,
  category varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  institution varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  duration int NOT NULL,
  reward decimal(10,2) NOT NULL,
  requirements json DEFAULT NULL,
  target_responses int NOT NULL,
  current_responses int NOT NULL DEFAULT '0',
  responses_count int NOT NULL DEFAULT '0',
  status enum('draft','active','paused','completed','archived','rejected','approved','pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  settings json DEFAULT NULL,
  config json DEFAULT NULL,
  published_at timestamp NULL DEFAULT NULL,
  total_earned decimal(12,2) NOT NULL DEFAULT '0.00',
  total_paid decimal(12,2) NOT NULL DEFAULT '0.00',
  allow_anonymous tinyint(1) NOT NULL DEFAULT '0',
  require_login tinyint(1) NOT NULL DEFAULT '0',
  multiple_responses tinyint(1) NOT NULL DEFAULT '0',
  shuffle_questions tinyint(1) NOT NULL DEFAULT '0',
  show_progress tinyint(1) NOT NULL DEFAULT '1',
  confirmation_message text COLLATE utf8mb4_unicode_ci,
  time_limit int DEFAULT NULL,
  start_date timestamp NULL DEFAULT NULL,
  end_date timestamp NULL DEFAULT NULL,
  max_responses int DEFAULT NULL,
  notify_on_response tinyint(1) NOT NULL DEFAULT '0',
  notify_email varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  theme json DEFAULT NULL,
  completion_rate int DEFAULT NULL,
  average_completion_time decimal(8,2) DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  deleted_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY surveys_user_id_index (user_id),
  KEY surveys_researcher_id_index (researcher_id),
  KEY surveys_category_index (category),
  KEY surveys_institution_index (institution),
  KEY surveys_status_index (status),
  KEY surveys_published_at_index (published_at),
  KEY surveys_created_at_index (created_at),
  KEY surveys_start_date_index (start_date),
  KEY surveys_end_date_index (end_date)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table surveys
--

INSERT INTO surveys (id, user_id, researcher_id, title, description, category, institution, duration, reward, requirements, target_responses, current_responses, responses_count, status, settings, config, published_at, total_earned, total_paid, allow_anonymous, require_login, multiple_responses, shuffle_questions, show_progress, confirmation_message, time_limit, start_date, end_date, max_responses, notify_on_response, notify_email, theme, completion_rate, average_completion_time, created_at, updated_at, deleted_at) VALUES
(1, 1, 1, 'Hábitos de Consumo em Maputo - 2025', 'Pesquisa sobre padrões de consumo de produtos alimentícios e não-alimentícios na cidade de Maputo. O objetivo é entender as preferências dos consumidores e fatores que influenciam as decisões de compra.', 'Economia', 'Universidade Eduardo Mondlane', 12, 35.00, '\"{\\\"idade_minima\\\":18,\\\"localizacao\\\":\\\"Maputo\\\",\\\"frequencia_compras\\\":\\\"pelo menos 1 vez por m\\\\u00eas\\\",\\\"renda_minima\\\":\\\"5000 MZN mensais\\\"}\"', 250, 130, 142, 'active', '\"{\\\"language\\\":\\\"pt\\\",\\\"currency\\\":\\\"MZN\\\",\\\"region\\\":\\\"Maputo\\\",\\\"data_retention_days\\\":365}\"', '\"{\\\"theme\\\":\\\"default\\\",\\\"welcome_message\\\":\\\"Bem-vindo \\\\u00e0 nossa pesquisa sobre h\\\\u00e1bitos de consumo!\\\",\\\"completion_message\\\":\\\"Obrigado por participar! Suas respostas s\\\\u00e3o muito valiosas.\\\",\\\"progress_bar\\\":true,\\\"randomize_questions\\\":false,\\\"allow_pause\\\":true,\\\"time_per_question\\\":60}\"', '2026-01-24 14:38:19', 4445.00, 4000.00, 0, 1, 0, 0, 1, 'Obrigado por completar nossa pesquisa! Suas respostas foram registradas com sucesso.', NULL, '2026-01-24 14:38:19', '2026-03-05 14:38:19', 300, 1, 'pesquisador@exemplo.com', '{\"font_family\": \"Roboto, Arial, sans-serif\", \"button_style\": \"rounded\", \"primary_color\": \"#1976D2\", \"secondary_color\": \"#4CAF50\", \"background_color\": \"#FFFFFF\"}', 89, 8.50, '2026-02-03 14:38:19', '2026-02-13 07:54:16', NULL),
(2, 1, 1, 'Satisfação com o Ensino Superior em Moçambique', 'Avaliação abrangente da qualidade do ensino, infraestrutura e serviços nas instituições de ensino superior moçambicanas. Esta pesquisa visa identificar áreas de melhoria e boas práticas.', 'Educação', 'Universidade Pedagógica', 18, 45.00, '\"{\\\"tipo_participante\\\":\\\"estudante_atual_ou_egresso\\\",\\\"nivel_ensino\\\":\\\"superior\\\",\\\"minimo_tempo\\\":\\\"pelo menos 1 semestre conclu\\\\u00eddo\\\"}\"', 180, 92, 105, 'active', NULL, '\"{\\\"theme\\\":\\\"academic\\\",\\\"welcome_message\\\":\\\"Bem-vindo \\\\u00e0 pesquisa sobre ensino superior!\\\",\\\"completion_message\\\":\\\"Obrigado por contribuir para a melhoria da educa\\\\u00e7\\\\u00e3o em Mo\\\\u00e7ambique.\\\",\\\"progress_bar\\\":true,\\\"randomize_sections\\\":false,\\\"allow_back_navigation\\\":true}\"', '2026-01-29 14:38:19', 4140.00, 3600.00, 1, 0, 0, 1, 1, NULL, 30, '2026-01-29 14:38:19', '2026-03-20 14:38:19', 200, 0, NULL, '{\"font_family\": \"Arial, sans-serif\", \"button_style\": \"square\", \"primary_color\": \"#FF9800\", \"secondary_color\": \"#2196F3\"}', 87, 12.30, '2026-02-03 14:38:19', '2026-02-03 14:38:19', NULL),
(3, 1, 1, 'Impacto da Tecnologia no Trabalho Remoto', 'Pesquisa sobre como as tecnologias digitais afetam a produtividade e bem-estar no trabalho remoto. Foco em ferramentas, desafios e oportunidades.', 'Tecnologia', 'ISUTC', 15, 40.00, '\"{\\\"experiencia_trabalho_remoto\\\":\\\"pelo menos 3 meses\\\",\\\"uso_tecnologia\\\":\\\"regular\\\",\\\"setor\\\":\\\"qualquer\\\"}\"', 120, 58, 65, 'draft', NULL, '\"{\\\"theme\\\":\\\"tech\\\",\\\"welcome_message\\\":\\\"Pesquisa sobre trabalho remoto e tecnologia\\\",\\\"estimated_time\\\":\\\"15 minutos\\\"}\"', NULL, 0.00, 0.00, 0, 1, 0, 0, 1, NULL, NULL, '2026-02-06 14:38:19', '2026-04-04 14:38:19', NULL, 0, NULL, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19', NULL),
(4, 2, NULL, 'hbdhb', 'hsbdjh', 'Economia', 'Instituto Superior de Transportes e Comunicações', 5, 200.00, '[]', 100, 17, 17, 'pending', NULL, NULL, NULL, 0.00, 0.00, 0, 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-02-05 03:11:59', '2026-02-05 06:10:53', NULL),
(5, 2, NULL, 'tfytft', 'jhjygug', 'Saúde', 'Universidade Pedagógica', 5, 200.00, '[]', 100, 15, 15, 'pending', NULL, NULL, NULL, 0.00, 0.00, 0, 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-02-05 04:42:00', '2026-02-05 06:10:53', NULL),
(6, 2, NULL, 'kkkk', 'ghhtftfty', 'Psicologia', 'Universidade Eduardo Mondlane', 5, 1000.00, '[]', 100, 15, 15, 'pending', NULL, NULL, NULL, 0.00, 0.00, 0, 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-02-05 05:37:35', '2026-02-15 03:14:02', '2026-02-15 03:14:02'),
(7, 2, NULL, 'tste', 'jhbhjbh', 'Tecnologia', 'Universidade Lúrio', 5, 300.00, '[]', 100, 19, 19, 'pending', NULL, NULL, NULL, 0.00, 0.00, 0, 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-02-05 05:57:20', '2026-02-05 06:10:53', NULL),
(8, 2, NULL, 'nova pesquisa', 'jhbjhb', 'Economia', 'Universidade Pedagógica', 5, 140.00, '[]', 100, 0, 0, 'pending', NULL, NULL, NULL, 0.00, 0.00, 0, 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-02-05 06:19:46', '2026-02-05 06:19:46', NULL),
(9, 2, NULL, 'tste (Cópia)', 'jhbhjbh', 'Tecnologia', 'Universidade Lúrio', 5, 300.00, '[]', 100, 19, 0, 'pending', NULL, NULL, NULL, 0.00, 0.00, 0, 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-02-06 07:45:54', '2026-02-06 07:45:54', NULL),
(10, 2, NULL, 'uma pesquia sem titulo', 'para fazer o teste da pesquisa como ele funcina', 'Educação', 'Universidade Católica de Moçambique', 72, 500.00, '[]', 100, 0, 0, 'pending', NULL, NULL, NULL, 0.00, 0.00, 0, 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, '2026-02-06 08:01:23', '2026-02-06 08:01:43', NULL),
(15, 1, 2, 'Inovação Tecnológica e Transformação Digital em Moçambique', 'Esta pesquisa visa compreender o nível de adoção de tecnologias digitais, desafios e oportunidades para a transformação digital no setor empresarial e educacional moçambicano.', 'Tecnologia', 'Universidade Eduardo Mondlane', 25, 75.00, '{\"tipo_participante\": \"estudante_area_tecnologia\", \"nivel_conhecimento\": \"basico_intermediario\"}', 200, 0, 0, 'active', NULL, '{\"theme\": \"tech\", \"primary_color\": \"#2196F3\", \"secondary_color\": \"#4CAF50\", \"welcome_message\": \"Obrigado por participar da pesquisa sobre transformação digital!\", \"completion_message\": \"Sua contribuição é fundamental para entender o cenário tecnológico em Moçambique.\"}', '2026-02-13 05:49:42', 0.00, 0.00, 1, 0, 0, 1, 1, 'Pesquisa concluída com sucesso! Obrigado por contribuir para o desenvolvimento tecnológico do país.', 45, '2026-02-13 05:49:42', '2026-03-30 05:49:42', 250, 0, NULL, '{\"font_family\": \"Inter, sans-serif\", \"button_style\": \"rounded\", \"primary_color\": \"#2196F3\", \"secondary_color\": \"#4CAF50\"}', NULL, NULL, '2026-02-13 05:49:42', '2026-02-13 05:49:42', NULL),
(16, 1, 3, 'Metodologias Ativas e Qualidade do Ensino Superior', 'Pesquisa sobre a implementação de metodologias ativas de aprendizagem e seu impacto na qualidade do ensino superior em Moçambique.', 'Educação', 'Universidade Pedagógica', 20, 60.00, '{\"area\": \"educacao\", \"tipo_participante\": \"estudante_ou_docente\"}', 150, 0, 0, 'active', NULL, '{\"theme\": \"education\", \"primary_color\": \"#FF9800\", \"secondary_color\": \"#795548\", \"welcome_message\": \"Bem-vindo à pesquisa sobre metodologias ativas!\", \"completion_message\": \"Obrigado por contribuir para a melhoria da qualidade do ensino!\"}', '2026-02-13 05:49:42', 0.00, 0.00, 1, 0, 0, 1, 1, 'Pesquisa concluída! Suas respostas ajudarão a melhorar as práticas pedagógicas.', 30, '2026-02-13 05:49:42', '2026-03-15 05:49:42', 180, 0, NULL, '{\"font_family\": \"Open Sans, sans-serif\", \"button_style\": \"rounded\", \"primary_color\": \"#FF9800\", \"secondary_color\": \"#795548\"}', NULL, NULL, '2026-02-13 05:49:42', '2026-02-13 05:49:42', NULL),
(17, 1, 4, 'Qualidade e Acesso aos Serviços de Saúde em Moçambique', 'Esta pesquisa tem como objetivo avaliar a percepção da população sobre a qualidade e acesso aos serviços de saúde nas diferentes regiões do país.', 'Saúde', 'Universidade Lúrio', 22, 80.00, '{\"tipo_participante\": \"profissional_saude_ou_usuario\"}', 300, 0, 0, 'active', NULL, '{\"theme\": \"health\", \"primary_color\": \"#4CAF50\", \"secondary_color\": \"#2196F3\", \"welcome_message\": \"Obrigado por participar desta importante pesquisa sobre saúde!\", \"completion_message\": \"Sua opinião é essencial para melhorar os serviços de saúde em Moçambique.\"}', '2026-02-13 05:49:43', 0.00, 0.00, 1, 0, 0, 1, 1, 'Pesquisa concluída! Suas respostas ajudarão a identificar áreas prioritárias para melhoria.', 40, '2026-02-13 05:49:43', '2026-04-04 05:49:43', 350, 0, NULL, '{\"font_family\": \"Roboto, sans-serif\", \"button_style\": \"rounded\", \"primary_color\": \"#4CAF50\", \"secondary_color\": \"#2196F3\"}', NULL, NULL, '2026-02-13 05:49:43', '2026-02-13 05:49:43', NULL),
(18, 1, 6, 'Acesso à Justiça e Direitos Humanos em Moçambique', 'Pesquisa sobre a percepção da população quanto ao acesso à justiça, conhecimento dos direitos humanos e a eficácia do sistema judicial moçambicano.', 'Direito', 'Universidade Católica de Moçambique', 28, 85.00, '{\"escolaridade\": \"alfabetizado\", \"tipo_participante\": \"cidadão_maior_18\"}', 250, 0, 0, 'active', NULL, '{\"theme\": \"law\", \"primary_color\": \"#9C27B0\", \"secondary_color\": \"#673AB7\", \"welcome_message\": \"Obrigado por participar desta pesquisa sobre acesso à justiça!\", \"completion_message\": \"Sua opinião é fundamental para entender os desafios do sistema judicial.\"}', '2026-02-13 05:49:43', 0.00, 0.00, 1, 0, 0, 1, 1, 'Pesquisa concluída! Suas respostas contribuirão para o fortalecimento do acesso à justiça.', 35, '2026-02-13 05:49:43', '2026-03-25 05:49:43', 280, 0, NULL, '{\"font_family\": \"Merriweather, serif\", \"button_style\": \"rounded\", \"primary_color\": \"#9C27B0\", \"secondary_color\": \"#673AB7\"}', NULL, NULL, '2026-02-13 05:49:43', '2026-02-13 05:49:43', NULL),
(19, 1, 7, 'Desenvolvimento Econômico e Empreendedorismo Juvenil', 'Estudo sobre os desafios e oportunidades para o empreendedorismo juvenil e o desenvolvimento econômico em Moçambique, com foco em inovação e criação de negócios.', 'Economia', 'Universidade Eduardo Mondlane', 24, 70.00, '{\"faixa_etaria\": \"18_35\", \"tipo_participante\": \"jovem_empreendedor_ou_interessado\"}', 200, 0, 0, 'active', NULL, '{\"theme\": \"business\", \"primary_color\": \"#FF5722\", \"secondary_color\": \"#9C27B0\", \"welcome_message\": \"Bem-vindo à pesquisa sobre empreendedorismo juvenil!\", \"completion_message\": \"Obrigado por contribuir para o desenvolvimento econômico do país!\"}', '2026-02-13 05:49:43', 0.00, 0.00, 1, 0, 0, 1, 1, 'Pesquisa concluída! Suas respostas ajudarão a criar políticas de apoio ao empreendedorismo.', 30, '2026-02-13 05:49:43', '2026-03-20 05:49:43', 220, 0, NULL, '{\"font_family\": \"Poppins, sans-serif\", \"button_style\": \"rounded\", \"primary_color\": \"#FF5722\", \"secondary_color\": \"#9C27B0\"}', NULL, NULL, '2026-02-13 05:49:43', '2026-02-13 05:49:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table survey_categories
--

DROP TABLE IF EXISTS survey_categories;
CREATE TABLE IF NOT EXISTS survey_categories (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  name varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  slug varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  description text COLLATE utf8mb4_unicode_ci,
  icon varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  color varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '#1976D2',
  survey_count int NOT NULL DEFAULT '0',
  is_active tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY survey_categories_slug_unique (slug),
  KEY survey_categories_slug_index (slug),
  KEY survey_categories_is_active_index (is_active),
  KEY survey_categories_order_index (`order`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table survey_categories
--

INSERT INTO survey_categories (id, name, slug, description, icon, color, survey_count, is_active, `order`, created_at, updated_at) VALUES
(1, 'Economia', 'economia', 'Pesquisas sobre economia, finanças e consumo', 'attach_money', '#4CAF50', 2, 1, 1, '2026-02-03 14:38:19', '2026-02-05 06:19:46'),
(2, 'Sociologia', 'sociologia', 'Pesquisas sobre sociedade e relações humanas', 'people', '#2196F3', 0, 1, 2, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(3, 'Psicologia', 'psicologia', 'Pesquisas sobre comportamento e mente humana', 'psychology', '#9C27B0', 1, 1, 3, '2026-02-03 14:38:19', '2026-02-13 09:18:56'),
(4, 'Educação', 'educacao', 'Pesquisas sobre ensino e aprendizagem', 'school', '#FF9800', 2, 1, 4, '2026-02-03 14:38:19', '2026-02-06 08:01:23'),
(5, 'Tecnologia', 'tecnologia', 'Pesquisas sobre inovação e tecnologia', 'computer', '#2196F3', 1, 1, 5, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(6, 'Saúde', 'saude', 'Pesquisas sobre saúde e bem-estar', 'medical_services', '#F44336', 0, 1, 6, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(7, 'Meio Ambiente', 'meio-ambiente', 'Pesquisas sobre ecologia e sustentabilidade', 'eco', '#4CAF50', 0, 1, 7, '2026-02-03 14:38:19', '2026-02-03 14:38:19');

-- --------------------------------------------------------

--
-- Table structure for table survey_exports
--

DROP TABLE IF EXISTS survey_exports;
CREATE TABLE IF NOT EXISTS survey_exports (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  survey_id bigint UNSIGNED NOT NULL,
  user_id bigint UNSIGNED NOT NULL,
  format varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  filename varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  file_path varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  file_size int DEFAULT NULL,
  status varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'processing',
  options json DEFAULT NULL,
  error_message text COLLATE utf8mb4_unicode_ci,
  total_records int NOT NULL DEFAULT '0',
  expires_at timestamp NULL DEFAULT NULL,
  completed_at timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY survey_exports_survey_id_index (survey_id),
  KEY survey_exports_user_id_index (user_id),
  KEY survey_exports_status_index (status),
  KEY survey_exports_expires_at_index (expires_at)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table survey_images
--

DROP TABLE IF EXISTS survey_images;
CREATE TABLE IF NOT EXISTS survey_images (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  survey_id bigint UNSIGNED DEFAULT NULL,
  question_id bigint UNSIGNED DEFAULT NULL,
  user_id bigint UNSIGNED NOT NULL,
  filename varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  original_name varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  path varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  url varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  mime_type varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  size int NOT NULL,
  metadata json DEFAULT NULL,
  is_temp tinyint(1) NOT NULL DEFAULT '1',
  temp_until timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY survey_images_survey_id_index (survey_id),
  KEY survey_images_question_id_index (question_id),
  KEY survey_images_user_id_index (user_id),
  KEY survey_images_is_temp_index (is_temp),
  KEY survey_images_temp_until_index (temp_until)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table survey_institutions
--

DROP TABLE IF EXISTS survey_institutions;
CREATE TABLE IF NOT EXISTS survey_institutions (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  name varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  abbreviation varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  type enum('university','college','research_center','company','ngo','government','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  logo_url varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  website varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  contact_email varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  phone varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  address text COLLATE utf8mb4_unicode_ci,
  description text COLLATE utf8mb4_unicode_ci,
  is_verified tinyint(1) NOT NULL DEFAULT '0',
  survey_count int NOT NULL DEFAULT '0',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY survey_institutions_abbreviation_index (abbreviation),
  KEY survey_institutions_type_index (type),
  KEY survey_institutions_is_verified_index (is_verified),
  KEY survey_institutions_survey_count_index (survey_count)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table survey_institutions
--

INSERT INTO survey_institutions (id, name, abbreviation, type, logo_url, website, contact_email, phone, address, description, is_verified, survey_count, created_at, updated_at) VALUES
(1, 'Universidade Eduardo Mondlane', 'UEM', 'university', NULL, 'https://www.uem.mz', 'pesquisa@uem.mz', NULL, NULL, 'Principal universidade de Moçambique', 1, 1, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(2, 'Universidade Pedagógica', 'UP', 'university', NULL, 'https://www.up.ac.mz', 'investigacao@up.ac.mz', NULL, NULL, 'Universidade focada em ciências da educação', 1, 3, '2026-02-03 14:38:19', '2026-02-13 09:18:56'),
(3, 'Instituto Superior de Transportes e Comunicações', 'ISUTC', 'college', NULL, 'https://www.isutc.ac.mz', 'pesquisa@isutc.ac.mz', NULL, NULL, 'Instituição especializada em transportes e comunicações', 1, 0, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(4, 'Universidade Católica de Moçambique', 'UCM', 'university', NULL, 'https://www.ucm.ac.mz', 'investigacao@ucm.ac.mz', NULL, NULL, 'Universidade católica privada', 1, 1, '2026-02-03 14:38:19', '2026-02-06 08:01:23'),
(5, 'Universidade Lúrio', 'UniLúrio', 'university', NULL, 'https://www.unilurio.ac.mz', 'ciencia@unilurio.ac.mz', NULL, NULL, 'Universidade pública do norte de Moçambique', 1, 0, '2026-02-03 14:38:19', '2026-02-03 14:38:19');

-- --------------------------------------------------------

--
-- Table structure for table survey_questions
--

DROP TABLE IF EXISTS survey_questions;
CREATE TABLE IF NOT EXISTS survey_questions (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  survey_id bigint UNSIGNED NOT NULL,
  question text COLLATE utf8mb4_unicode_ci,
  title varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  description text COLLATE utf8mb4_unicode_ci,
  type enum('text','paragraph','multiple_choice','checkbox','checkboxes','dropdown','linear_scale','scale','rating','yes_no','date','time','ranking','file_upload') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  options json DEFAULT NULL,
  placeholder varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  default_value varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  min_length int DEFAULT NULL,
  max_length int DEFAULT NULL,
  min_value int DEFAULT NULL,
  max_value int DEFAULT NULL,
  scale_min int DEFAULT NULL,
  scale_max int DEFAULT NULL,
  scale_step int DEFAULT NULL,
  scale_low_label varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  scale_high_label varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  scale_value int DEFAULT NULL,
  low_label varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  high_label varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  min_date date DEFAULT NULL,
  max_date date DEFAULT NULL,
  min_time time DEFAULT NULL,
  max_time time DEFAULT NULL,
  required tinyint(1) NOT NULL DEFAULT '0',
  `order` int NOT NULL DEFAULT '0',
  image_url varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  validation_rules json DEFAULT NULL,
  metadata json DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY survey_questions_survey_id_index (survey_id),
  KEY survey_questions_type_index (type),
  KEY survey_questions_order_index (`order`),
  KEY survey_questions_required_index (required)
) ENGINE=MyISAM AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table survey_questions
--

INSERT INTO survey_questions (id, survey_id, question, title, description, type, options, placeholder, default_value, min_length, max_length, min_value, max_value, scale_min, scale_max, scale_step, scale_low_label, scale_high_label, scale_value, low_label, high_label, min_date, max_date, min_time, max_time, required, `order`, image_url, validation_rules, metadata, created_at, updated_at) VALUES
(1, 1, NULL, 'Com que frequência você faz compras no supermercado?', 'Considere tanto supermercados grandes como pequenos mercados de bairro.', 'multiple_choice', '\"[{\\\"value\\\":\\\"daily\\\",\\\"label\\\":\\\"Diariamente\\\"},{\\\"value\\\":\\\"weekly_2_3\\\",\\\"label\\\":\\\"2-3 vezes por semana\\\"},{\\\"value\\\":\\\"weekly_1\\\",\\\"label\\\":\\\"1 vez por semana\\\"},{\\\"value\\\":\\\"monthly_1_2\\\",\\\"label\\\":\\\"1-2 vezes por m\\\\u00eas\\\"},{\\\"value\\\":\\\"rarely\\\",\\\"label\\\":\\\"Raramente\\\"}]\"', 'Selecione uma opção', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '{\"help_text\": \"Considere todos os tipos de supermercado\", \"skip_logic\": [], \"validation_rules\": [\"required\"]}', '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(2, 1, NULL, 'Qual o valor médio que você gasta por mês em supermercado?', NULL, 'multiple_choice', '\"[{\\\"value\\\":\\\"0_500\\\",\\\"label\\\":\\\"At\\\\u00e9 500 MZN\\\"},{\\\"value\\\":\\\"501_1000\\\",\\\"label\\\":\\\"501-1000 MZN\\\"},{\\\"value\\\":\\\"1001_2000\\\",\\\"label\\\":\\\"1001-2000 MZN\\\"},{\\\"value\\\":\\\"2001_5000\\\",\\\"label\\\":\\\"2001-5000 MZN\\\"},{\\\"value\\\":\\\"5000_plus\\\",\\\"label\\\":\\\"Acima de 5000 MZN\\\"}]\"', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, '{\"currency\": \"MZN\", \"include_not_sure\": false}', '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(3, 1, NULL, 'Que fatores influenciam mais suas escolhas de produtos?', 'Selecione todos que se aplicam (máximo 3)', 'checkboxes', '\"[{\\\"value\\\":\\\"price\\\",\\\"label\\\":\\\"Pre\\\\u00e7o\\\"},{\\\"value\\\":\\\"quality\\\",\\\"label\\\":\\\"Qualidade\\\"},{\\\"value\\\":\\\"brand\\\",\\\"label\\\":\\\"Marca\\\"},{\\\"value\\\":\\\"promotions\\\",\\\"label\\\":\\\"Promo\\\\u00e7\\\\u00f5es\\\"},{\\\"value\\\":\\\"recommendations\\\",\\\"label\\\":\\\"Recomenda\\\\u00e7\\\\u00f5es\\\"},{\\\"value\\\":\\\"availability\\\",\\\"label\\\":\\\"Disponibilidade\\\"},{\\\"value\\\":\\\"packaging\\\",\\\"label\\\":\\\"Embalagem\\\"}]\"', NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, '{\"max_selections\": 3, \"randomize_options\": false}', '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(4, 1, NULL, 'Em uma escala de 1 a 10, como você avalia a variedade de produtos nos supermercados?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 10, 1, 'Muito pouca variedade', 'Variedade excelente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 4, NULL, NULL, '{\"show_labels\": true, \"show_numbers\": true}', '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(5, 1, NULL, 'Que tipo de produtos você gostaria de ver mais nos supermercados?', NULL, 'paragraph', NULL, 'Descreva aqui os produtos que sente falta...', NULL, 10, 500, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, '{\"character_count\": true, \"allow_formatting\": false}', '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(6, 2, NULL, 'Em que instituição de ensino superior você estuda/estudou?', NULL, 'dropdown', '\"[{\\\"value\\\":\\\"uem\\\",\\\"label\\\":\\\"Universidade Eduardo Mondlane (UEM)\\\"},{\\\"value\\\":\\\"up\\\",\\\"label\\\":\\\"Universidade Pedag\\\\u00f3gica (UP)\\\"},{\\\"value\\\":\\\"isutc\\\",\\\"label\\\":\\\"ISUTC\\\"},{\\\"value\\\":\\\"ucm\\\",\\\"label\\\":\\\"Universidade Cat\\\\u00f3lica (UCM)\\\"},{\\\"value\\\":\\\"unilurio\\\",\\\"label\\\":\\\"Universidade L\\\\u00fario\\\"},{\\\"value\\\":\\\"outra\\\",\\\"label\\\":\\\"Outra institui\\\\u00e7\\\\u00e3o\\\"}]\"', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(7, 2, NULL, 'Em uma escala de 1 a 5, como você avalia a qualidade geral do ensino?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Muito insatisfeito', 'Muito satisfeito', 3, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(8, 2, NULL, 'Que áreas precisam de mais investimento? (Selecione até 3)', NULL, 'checkboxes', '\"[{\\\"value\\\":\\\"infraestrutura\\\",\\\"label\\\":\\\"Infraestrutura f\\\\u00edsica\\\"},{\\\"value\\\":\\\"laboratorios\\\",\\\"label\\\":\\\"Laborat\\\\u00f3rios e equipamentos\\\"},{\\\"value\\\":\\\"biblioteca\\\",\\\"label\\\":\\\"Biblioteca e recursos digitais\\\"},{\\\"value\\\":\\\"professores\\\",\\\"label\\\":\\\"Qualifica\\\\u00e7\\\\u00e3o dos professores\\\"},{\\\"value\\\":\\\"material\\\",\\\"label\\\":\\\"Material did\\\\u00e1tico\\\"},{\\\"value\\\":\\\"tecnologia\\\",\\\"label\\\":\\\"Tecnologia e internet\\\"},{\\\"value\\\":\\\"transporte\\\",\\\"label\\\":\\\"Transporte e acesso\\\"},{\\\"value\\\":\\\"bolsas\\\",\\\"label\\\":\\\"Bolsas de estudo\\\"}]\"', NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(9, 2, NULL, 'Quando você começou seus estudos?', NULL, 'date', NULL, 'Selecione a data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2010-01-01', '2026-02-03', NULL, NULL, 1, 4, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(10, 2, NULL, 'Em média, quantas horas por semana você dedica aos estudos fora da sala de aula?', NULL, 'text', NULL, 'Ex: 10-15 horas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, '{\"suffix\": \"horas\", \"max_value\": 80, \"min_value\": 0, \"input_type\": \"number\"}', '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(11, 2, NULL, 'Comentários ou sugestões para melhorar o ensino superior:', NULL, 'paragraph', NULL, 'Compartilhe suas ideias e experiências...', NULL, 20, 1000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 6, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(12, 3, NULL, 'Que ferramentas tecnológicas você usa regularmente para trabalho remoto?', NULL, 'checkboxes', '\"[{\\\"value\\\":\\\"videoconf\\\",\\\"label\\\":\\\"Videoconfer\\\\u00eancia (Zoom, Teams, Meet)\\\"},{\\\"value\\\":\\\"colaboracao\\\",\\\"label\\\":\\\"Ferramentas de colabora\\\\u00e7\\\\u00e3o (Slack, Trello)\\\"},{\\\"value\\\":\\\"cloud\\\",\\\"label\\\":\\\"Armazenamento em nuvem (Google Drive, Dropbox)\\\"},{\\\"value\\\":\\\"vpn\\\",\\\"label\\\":\\\"VPN e seguran\\\\u00e7a\\\"},{\\\"value\\\":\\\"projetos\\\",\\\"label\\\":\\\"Gest\\\\u00e3o de projetos (Asana, Jira)\\\"},{\\\"value\\\":\\\"comunicacao\\\",\\\"label\\\":\\\"Comunica\\\\u00e7\\\\u00e3o instant\\\\u00e2nea (WhatsApp, Telegram)\\\"}]\"', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(13, 3, NULL, 'Qual seu nível de satisfação com a conectividade de internet?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 10, NULL, 'Muito insatisfeito', 'Muito satisfeito', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(14, 8, 'Pergunta de parágrafo', 'Pergunta de parágrafo', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, '2026-02-05 06:19:46', '2026-02-05 06:19:46'),
(15, 10, 'Pergunta de texto curto', 'Pergunta de texto curto', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-02-06 08:01:23', '2026-02-06 08:01:23'),
(16, 10, 'Selecione uma data', 'Selecione uma data', NULL, 'date', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, NULL, '2026-02-06 08:01:23', '2026-02-06 08:01:23'),
(17, 10, 'Pergunta de caixas de seleção', 'Pergunta de caixas de seleção', NULL, 'checkboxes', '[\"Opção 1\", \"Opção 2\", \"Opção 3\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2, NULL, NULL, NULL, '2026-02-06 08:01:23', '2026-02-06 08:01:23'),
(18, 15, NULL, 'Qual é o seu nível de proficiência no uso de ferramentas digitais?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Iniciante', 'Avançado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL),
(19, 15, NULL, 'Quais tecnologias você utiliza regularmente?', NULL, 'checkboxes', '[{\"label\": \"Computação em Nuvem\", \"value\": \"cloud\"}, {\"label\": \"Inteligência Artificial\", \"value\": \"ia\"}, {\"label\": \"Internet das Coisas\", \"value\": \"iot\"}, {\"label\": \"Blockchain\", \"value\": \"blockchain\"}, {\"label\": \"Big Data\", \"value\": \"bigdata\"}, {\"label\": \"Aplicações Móveis\", \"value\": \"mobile\"}]', 'Selecione as opções aplicáveis', NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, '{\"max_selections\": 3, \"min_selections\": 1}', NULL, NULL, NULL),
(20, 15, NULL, 'Na sua instituição, qual o principal desafio para a transformação digital?', NULL, 'dropdown', '[{\"label\": \"Infraestrutura inadequada\", \"value\": \"infraestrutura\"}, {\"label\": \"Falta de formação\", \"value\": \"formacao\"}, {\"label\": \"Custo elevado\", \"value\": \"custo\"}, {\"label\": \"Resistência à mudança\", \"value\": \"resistencia\"}, {\"label\": \"Preocupações com segurança\", \"value\": \"seguranca\"}]', 'Selecione uma opção', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL),
(21, 15, NULL, 'Como você avalia o potencial da transformação digital para o desenvolvimento de Moçambique?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Muito Baixo', 'Muito Alto', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 4, NULL, NULL, NULL, NULL, NULL),
(22, 15, NULL, 'Quais sugestões você daria para acelerar a transformação digital no país?', NULL, 'paragraph', NULL, 'Compartilhe suas ideias e recomendações...', NULL, 20, 1000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, NULL, NULL, NULL),
(23, 15, NULL, 'Você participaria de programas de capacitação em tecnologias emergentes?', NULL, 'multiple_choice', '[{\"label\": \"Sim\", \"value\": \"sim\"}, {\"label\": \"Não\", \"value\": \"nao\"}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 6, NULL, NULL, NULL, NULL, NULL),
(24, 16, NULL, 'Você conhece ou já experimentou metodologias ativas de aprendizagem?', NULL, 'dropdown', '[{\"label\": \"Sim, pratico regularmente\", \"value\": \"sim_pratico\"}, {\"label\": \"Sim, conheço mas não pratico\", \"value\": \"sim_conheco\"}, {\"label\": \"Não conheço\", \"value\": \"nao_conheco\"}, {\"label\": \"Tenho interesse em aprender\", \"value\": \"interessado\"}]', 'Selecione uma opção', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL),
(25, 16, NULL, 'Quais metodologias ativas você considera mais eficazes?', NULL, 'checkboxes', '[{\"label\": \"Aprendizagem Baseada em Problemas\", \"value\": \"pbl\"}, {\"label\": \"Sala de Aula Invertida\", \"value\": \"sala_invertida\"}, {\"label\": \"Gamificação\", \"value\": \"gamificacao\"}, {\"label\": \"Estudo de Caso\", \"value\": \"estudo_caso\"}, {\"label\": \"Aprendizagem por Projetos\", \"value\": \"aprendizagem_projetos\"}, {\"label\": \"Instrução por Pares\", \"value\": \"peer_instruction\"}]', 'Selecione até 3 opções', NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, '{\"max_selections\": 3, \"min_selections\": 1}', NULL, NULL, NULL),
(26, 16, NULL, 'Em uma escala de 1 a 5, como você avalia a contribuição das metodologias ativas para o aprendizado?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Não contribui', 'Contribui muito', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL),
(27, 16, NULL, 'Quais os principais desafios para implementar metodologias ativas?', NULL, 'checkboxes', '[{\"label\": \"Falta de formação docente\", \"value\": \"formacao\"}, {\"label\": \"Infraestrutura inadequada\", \"value\": \"infraestrutura\"}, {\"label\": \"Tempo de preparação\", \"value\": \"tempo\"}, {\"label\": \"Resistência de alunos\", \"value\": \"resistencia\"}, {\"label\": \"Currículo engessado\", \"value\": \"curriculo\"}]', 'Selecione os principais desafios', NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 4, NULL, '{\"max_selections\": 3, \"min_selections\": 1}', NULL, NULL, NULL),
(28, 16, NULL, 'Que recomendações você daria para melhorar a qualidade do ensino superior?', NULL, 'paragraph', NULL, 'Compartilhe suas recomendações...', NULL, 20, 1000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, NULL, NULL, NULL),
(29, 16, NULL, 'Você recomendaria a adoção de metodologias ativas em mais disciplinas?', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 6, NULL, NULL, NULL, NULL, NULL),
(30, 17, NULL, 'Com que frequência você utiliza os serviços de saúde públicos?', NULL, 'dropdown', '[{\"label\": \"Regularmente\", \"value\": \"regularmente\"}, {\"label\": \"Ocasionalmente\", \"value\": \"ocasionalmente\"}, {\"label\": \"Raramente\", \"value\": \"raramente\"}, {\"label\": \"Nunca\", \"value\": \"nunca\"}]', 'Selecione uma opção', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL),
(31, 17, NULL, 'Como você avalia a qualidade geral dos serviços de saúde?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Muito Ruim', 'Excelente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL),
(32, 17, NULL, 'Quais aspectos dos serviços de saúde precisam de mais atenção?', NULL, 'checkboxes', '[{\"label\": \"Infraestrutura\", \"value\": \"infraestrutura\"}, {\"label\": \"Equipamentos\", \"value\": \"equipamentos\"}, {\"label\": \"Disponibilidade de medicamentos\", \"value\": \"medicamentos\"}, {\"label\": \"Quantidade de profissionais\", \"value\": \"profissionais\"}, {\"label\": \"Tempo de espera\", \"value\": \"tempo_espera\"}, {\"label\": \"Qualidade do atendimento\", \"value\": \"atendimento\"}]', 'Selecione até 3 opções', NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, '{\"max_selections\": 3, \"min_selections\": 1}', NULL, NULL, NULL),
(33, 17, NULL, 'Quando foi sua última consulta médica?', NULL, 'date', NULL, 'Selecione a data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-01-01', '2026-02-13', NULL, NULL, 1, 4, NULL, NULL, NULL, NULL, NULL),
(34, 17, NULL, 'Qual o principal desafio que você enfrenta ao acessar serviços de saúde?', NULL, 'paragraph', NULL, 'Descreva o principal desafio...', NULL, 10, 500, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, NULL, NULL, NULL),
(35, 17, NULL, 'Você utiliza serviços de saúde privados como complemento?', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 6, NULL, NULL, NULL, NULL, NULL),
(36, 18, NULL, 'Como você avalia seu conhecimento sobre os direitos fundamentais dos cidadãos?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Nenhum conhecimento', 'Conhecimento aprofundado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL),
(37, 18, NULL, 'Você já precisou de assistência jurídica nos últimos 5 anos?', NULL, 'dropdown', '[{\"label\": \"Sim, e obtive assistência\", \"value\": \"sim_obteve\"}, {\"label\": \"Sim, mas não obtive\", \"value\": \"sim_nao_obteve\"}, {\"label\": \"Não precisei\", \"value\": \"nao_precisei\"}, {\"label\": \"Não confio no sistema\", \"value\": \"nao_confio\"}]', 'Selecione uma opção', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL),
(38, 18, NULL, 'Quais barreiras você identifica para o acesso à justiça em Moçambique?', NULL, 'checkboxes', '[{\"label\": \"Custos elevados\", \"value\": \"custo\"}, {\"label\": \"Distância dos tribunais\", \"value\": \"distancia\"}, {\"label\": \"Morosidade processual\", \"value\": \"morosidade\"}, {\"label\": \"Falta de informação\", \"value\": \"informacao\"}, {\"label\": \"Dificuldade de acesso a advogados\", \"value\": \"advogado\"}, {\"label\": \"Corrupção\", \"value\": \"corrupcao\"}]', 'Selecione até 3 opções', NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, '{\"max_selections\": 3, \"min_selections\": 1}', NULL, NULL, NULL),
(39, 18, NULL, 'Em sua opinião, o sistema judicial moçambicano é eficaz na resolução de conflitos?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Nada eficaz', 'Muito eficaz', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 4, NULL, NULL, NULL, NULL, NULL),
(40, 18, NULL, 'Que medidas poderiam melhorar o acesso à justiça no país?', NULL, 'paragraph', NULL, 'Compartilhe suas sugestões...', NULL, 20, 500, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, NULL, NULL, NULL),
(41, 18, NULL, 'Você confia nas instituições judiciais moçambicanas?', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 6, NULL, NULL, NULL, NULL, NULL),
(42, 19, NULL, 'Qual é seu nível de interesse em empreendedorismo?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Nenhum interesse', 'Muito interessado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL),
(43, 19, NULL, 'Quais os principais desafios para iniciar um negócio em Moçambique?', NULL, 'checkboxes', '[{\"label\": \"Acesso a crédito\", \"value\": \"acesso_credito\"}, {\"label\": \"Burocracia\", \"value\": \"burocracia\"}, {\"label\": \"Acesso ao mercado\", \"value\": \"mercado\"}, {\"label\": \"Falta de formação\", \"value\": \"formacao\"}, {\"label\": \"Infraestrutura\", \"value\": \"infraestrutura\"}, {\"label\": \"Concorrência desleal\", \"value\": \"concorrencia\"}]', 'Selecione até 3 opções', NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, '{\"max_selections\": 3, \"min_selections\": 1}', NULL, NULL, NULL),
(44, 19, NULL, 'Você já participou de programas de capacitação empreendedora?', NULL, 'dropdown', '[{\"label\": \"Sim, já participei\", \"value\": \"sim\"}, {\"label\": \"Não, mas tenho interesse\", \"value\": \"nao_interesse\"}, {\"label\": \"Não, não tenho interesse\", \"value\": \"nao_desinteresse\"}, {\"label\": \"Estou planejando participar\", \"value\": \"planejando\"}]', 'Selecione uma opção', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL),
(45, 19, NULL, 'Qual área de negócio você considera mais promissora?', NULL, 'dropdown', '[{\"label\": \"Tecnologia e Inovação\", \"value\": \"tecnologia\"}, {\"label\": \"Agronegócio\", \"value\": \"agronegocio\"}, {\"label\": \"Comércio\", \"value\": \"comercio\"}, {\"label\": \"Serviços\", \"value\": \"servicos\"}, {\"label\": \"Turismo\", \"value\": \"turismo\"}, {\"label\": \"Indústria\", \"value\": \"industria\"}]', 'Selecione uma opção', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 4, NULL, NULL, NULL, NULL, NULL),
(46, 19, NULL, 'Como você avalia o apoio governamental ao empreendedorismo juvenil?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Muito insuficiente', 'Muito suficiente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, NULL, NULL, NULL, NULL, NULL),
(47, 19, NULL, 'Que sugestões você daria para fomentar o empreendedorismo juvenil?', NULL, 'paragraph', NULL, 'Compartilhe suas ideias...', NULL, 20, 500, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 6, NULL, NULL, NULL, NULL, NULL),
(48, 19, NULL, 'Você tem ou já teve um negócio próprio?', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 7, NULL, NULL, NULL, NULL, NULL),
(49, 20, 'Pergunta de texto curto', 'Pergunta de texto curto', NULL, 'text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, '2026-02-13 09:18:56', '2026-02-13 09:18:56'),
(50, 20, 'Pergunta de parágrafo', 'Pergunta de parágrafo', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, NULL, '2026-02-13 09:18:56', '2026-02-13 09:18:56'),
(51, 20, 'Pergunta de múltipla escolha', 'Pergunta de múltipla escolha', NULL, 'multiple_choice', '[\"Opção 1\", \"Opção 2\", \"Opção 3\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2, NULL, NULL, NULL, '2026-02-13 09:18:56', '2026-02-13 09:18:56'),
(52, 20, 'Pergunta de menu suspenso', 'Pergunta de menu suspenso', NULL, 'dropdown', '[\"Opção 1\", \"Opção 2\", \"Opção 3\", \"Nova opção\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 3, NULL, NULL, NULL, '2026-02-13 09:18:56', '2026-02-13 09:18:56'),
(53, 20, 'Avalie em uma escala', 'Avalie em uma escala', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 4, NULL, NULL, NULL, '2026-02-13 09:18:56', '2026-02-13 09:18:56'),
(54, 20, 'Selecione uma data', 'Selecione uma data', NULL, 'date', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, NULL, '2026-02-13 09:18:56', '2026-02-13 09:18:56'),
(55, 20, 'Selecione um horário', 'Selecione um horário', NULL, 'time', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 6, NULL, NULL, NULL, '2026-02-13 09:18:56', '2026-02-13 09:18:56'),
(56, 7, 'Qual sua opinião sobre tecnologia?', 'Pergunta 1', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-02-15 05:06:13', '2026-02-15 05:06:13'),
(57, 7, 'Com que frequência usa tecnologia?', 'Pergunta 2', NULL, 'multiple_choice', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, '2026-02-15 05:06:13', '2026-02-15 05:06:13'),
(58, 7, 'Sugestões para melhoria', 'Pergunta 3', NULL, 'text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 3, NULL, NULL, NULL, '2026-02-15 05:06:13', '2026-02-15 05:06:13'),
(65, 7, 'Quais são as principais dificuldades que encontra ao usar tecnologia?', 'Dificuldades', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 4, NULL, NULL, NULL, '2026-02-15 05:07:41', '2026-02-15 05:07:41'),
(66, 7, 'Que sugestões você daria para melhorar o acesso à tecnologia em Moçambique?', 'Sugestões', NULL, 'text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, NULL, '2026-02-15 05:07:41', '2026-02-15 05:07:41'),
(73, 17, 'Como classifica a qualidade do atendimento dos profissionais de saúde?', 'Atendimento', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 7, NULL, NULL, NULL, '2026-02-15 05:07:51', '2026-02-15 05:07:51'),
(74, 17, 'Que melhorias específicas gostaria de ver nos serviços de saúde?', 'Melhorias', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 8, NULL, NULL, NULL, '2026-02-15 05:07:51', '2026-02-15 05:07:51'),
(75, 17, 'Compartilhe uma experiência pessoal (positiva ou negativa) com os serviços de saúde em Moçambique', 'Experiência Pessoal', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 9, NULL, NULL, NULL, '2026-02-15 05:07:51', '2026-02-15 05:07:51'),
(76, 17, 'Que sugestões você daria para melhorar o acesso e qualidade dos serviços de saúde no país?', 'Sugestões Finais', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 10, NULL, NULL, NULL, '2026-02-15 05:07:51', '2026-02-15 05:07:51'),
(77, 8, 'Como avalia sua satisfação geral?', 'Satisfação Geral', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, '2026-02-15 05:08:03', '2026-02-15 05:08:03'),
(78, 8, 'Recomendaria nossos serviços?', 'Recomendação', NULL, 'multiple_choice', '[\"Sim\", \"Não\", \"Talvez\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, '2026-02-15 05:08:03', '2026-02-15 05:08:03'),
(79, 8, 'O que poderia ser melhorado?', 'Melhorias', NULL, 'checkboxes', '[\"Atendimento\", \"Qualidade\", \"Preço\", \"Rapidez\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 4, NULL, NULL, NULL, '2026-02-15 05:08:03', '2026-02-15 05:08:03'),
(80, 8, 'Comentários adicionais', 'Comentários', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, NULL, '2026-02-15 05:08:03', '2026-02-15 05:08:03');

-- --------------------------------------------------------

--
-- Table structure for table survey_questions_backup_20260216
--

DROP TABLE IF EXISTS survey_questions_backup_20260216;
CREATE TABLE IF NOT EXISTS survey_questions_backup_20260216 (
  id bigint UNSIGNED NOT NULL DEFAULT '0',
  survey_id bigint UNSIGNED NOT NULL,
  question text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  title varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  type enum('text','paragraph','multiple_choice','checkbox','checkboxes','dropdown','linear_scale','scale','rating','yes_no','date','time','ranking','file_upload') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  options json DEFAULT NULL,
  placeholder varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  default_value varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  min_length int DEFAULT NULL,
  max_length int DEFAULT NULL,
  min_value int DEFAULT NULL,
  max_value int DEFAULT NULL,
  scale_min int DEFAULT NULL,
  scale_max int DEFAULT NULL,
  scale_step int DEFAULT NULL,
  scale_low_label varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  scale_high_label varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  scale_value int DEFAULT NULL,
  low_label varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  high_label varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  min_date date DEFAULT NULL,
  max_date date DEFAULT NULL,
  min_time time DEFAULT NULL,
  max_time time DEFAULT NULL,
  required tinyint(1) NOT NULL DEFAULT '0',
  `order` int NOT NULL DEFAULT '0',
  image_url varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  validation_rules json DEFAULT NULL,
  metadata json DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table survey_questions_backup_20260216
--

INSERT INTO survey_questions_backup_20260216 (id, survey_id, question, title, description, type, options, placeholder, default_value, min_length, max_length, min_value, max_value, scale_min, scale_max, scale_step, scale_low_label, scale_high_label, scale_value, low_label, high_label, min_date, max_date, min_time, max_time, required, `order`, image_url, validation_rules, metadata, created_at, updated_at) VALUES
(1, 1, NULL, 'Com que frequência você faz compras no supermercado?', 'Considere tanto supermercados grandes como pequenos mercados de bairro.', 'multiple_choice', '\"[{\\\"value\\\":\\\"daily\\\",\\\"label\\\":\\\"Diariamente\\\"},{\\\"value\\\":\\\"weekly_2_3\\\",\\\"label\\\":\\\"2-3 vezes por semana\\\"},{\\\"value\\\":\\\"weekly_1\\\",\\\"label\\\":\\\"1 vez por semana\\\"},{\\\"value\\\":\\\"monthly_1_2\\\",\\\"label\\\":\\\"1-2 vezes por m\\\\u00eas\\\"},{\\\"value\\\":\\\"rarely\\\",\\\"label\\\":\\\"Raramente\\\"}]\"', 'Selecione uma opção', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '{\"help_text\": \"Considere todos os tipos de supermercado\", \"skip_logic\": [], \"validation_rules\": [\"required\"]}', '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(2, 1, NULL, 'Qual o valor médio que você gasta por mês em supermercado?', NULL, 'multiple_choice', '\"[{\\\"value\\\":\\\"0_500\\\",\\\"label\\\":\\\"At\\\\u00e9 500 MZN\\\"},{\\\"value\\\":\\\"501_1000\\\",\\\"label\\\":\\\"501-1000 MZN\\\"},{\\\"value\\\":\\\"1001_2000\\\",\\\"label\\\":\\\"1001-2000 MZN\\\"},{\\\"value\\\":\\\"2001_5000\\\",\\\"label\\\":\\\"2001-5000 MZN\\\"},{\\\"value\\\":\\\"5000_plus\\\",\\\"label\\\":\\\"Acima de 5000 MZN\\\"}]\"', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, '{\"currency\": \"MZN\", \"include_not_sure\": false}', '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(3, 1, NULL, 'Que fatores influenciam mais suas escolhas de produtos?', 'Selecione todos que se aplicam (máximo 3)', 'checkboxes', '\"[{\\\"value\\\":\\\"price\\\",\\\"label\\\":\\\"Pre\\\\u00e7o\\\"},{\\\"value\\\":\\\"quality\\\",\\\"label\\\":\\\"Qualidade\\\"},{\\\"value\\\":\\\"brand\\\",\\\"label\\\":\\\"Marca\\\"},{\\\"value\\\":\\\"promotions\\\",\\\"label\\\":\\\"Promo\\\\u00e7\\\\u00f5es\\\"},{\\\"value\\\":\\\"recommendations\\\",\\\"label\\\":\\\"Recomenda\\\\u00e7\\\\u00f5es\\\"},{\\\"value\\\":\\\"availability\\\",\\\"label\\\":\\\"Disponibilidade\\\"},{\\\"value\\\":\\\"packaging\\\",\\\"label\\\":\\\"Embalagem\\\"}]\"', NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, '{\"max_selections\": 3, \"randomize_options\": false}', '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(4, 1, NULL, 'Em uma escala de 1 a 10, como você avalia a variedade de produtos nos supermercados?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 10, 1, 'Muito pouca variedade', 'Variedade excelente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 4, NULL, NULL, '{\"show_labels\": true, \"show_numbers\": true}', '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(5, 1, NULL, 'Que tipo de produtos você gostaria de ver mais nos supermercados?', NULL, 'paragraph', NULL, 'Descreva aqui os produtos que sente falta...', NULL, 10, 500, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, '{\"character_count\": true, \"allow_formatting\": false}', '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(6, 2, NULL, 'Em que instituição de ensino superior você estuda/estudou?', NULL, 'dropdown', '\"[{\\\"value\\\":\\\"uem\\\",\\\"label\\\":\\\"Universidade Eduardo Mondlane (UEM)\\\"},{\\\"value\\\":\\\"up\\\",\\\"label\\\":\\\"Universidade Pedag\\\\u00f3gica (UP)\\\"},{\\\"value\\\":\\\"isutc\\\",\\\"label\\\":\\\"ISUTC\\\"},{\\\"value\\\":\\\"ucm\\\",\\\"label\\\":\\\"Universidade Cat\\\\u00f3lica (UCM)\\\"},{\\\"value\\\":\\\"unilurio\\\",\\\"label\\\":\\\"Universidade L\\\\u00fario\\\"},{\\\"value\\\":\\\"outra\\\",\\\"label\\\":\\\"Outra institui\\\\u00e7\\\\u00e3o\\\"}]\"', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(7, 2, NULL, 'Em uma escala de 1 a 5, como você avalia a qualidade geral do ensino?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Muito insatisfeito', 'Muito satisfeito', 3, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(8, 2, NULL, 'Que áreas precisam de mais investimento? (Selecione até 3)', NULL, 'checkboxes', '\"[{\\\"value\\\":\\\"infraestrutura\\\",\\\"label\\\":\\\"Infraestrutura f\\\\u00edsica\\\"},{\\\"value\\\":\\\"laboratorios\\\",\\\"label\\\":\\\"Laborat\\\\u00f3rios e equipamentos\\\"},{\\\"value\\\":\\\"biblioteca\\\",\\\"label\\\":\\\"Biblioteca e recursos digitais\\\"},{\\\"value\\\":\\\"professores\\\",\\\"label\\\":\\\"Qualifica\\\\u00e7\\\\u00e3o dos professores\\\"},{\\\"value\\\":\\\"material\\\",\\\"label\\\":\\\"Material did\\\\u00e1tico\\\"},{\\\"value\\\":\\\"tecnologia\\\",\\\"label\\\":\\\"Tecnologia e internet\\\"},{\\\"value\\\":\\\"transporte\\\",\\\"label\\\":\\\"Transporte e acesso\\\"},{\\\"value\\\":\\\"bolsas\\\",\\\"label\\\":\\\"Bolsas de estudo\\\"}]\"', NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(9, 2, NULL, 'Quando você começou seus estudos?', NULL, 'date', NULL, 'Selecione a data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2010-01-01', '2026-02-03', NULL, NULL, 1, 4, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(10, 2, NULL, 'Em média, quantas horas por semana você dedica aos estudos fora da sala de aula?', NULL, 'text', NULL, 'Ex: 10-15 horas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, '{\"suffix\": \"horas\", \"max_value\": 80, \"min_value\": 0, \"input_type\": \"number\"}', '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(11, 2, NULL, 'Comentários ou sugestões para melhorar o ensino superior:', NULL, 'paragraph', NULL, 'Compartilhe suas ideias e experiências...', NULL, 20, 1000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 6, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(12, 3, NULL, 'Que ferramentas tecnológicas você usa regularmente para trabalho remoto?', NULL, 'checkboxes', '\"[{\\\"value\\\":\\\"videoconf\\\",\\\"label\\\":\\\"Videoconfer\\\\u00eancia (Zoom, Teams, Meet)\\\"},{\\\"value\\\":\\\"colaboracao\\\",\\\"label\\\":\\\"Ferramentas de colabora\\\\u00e7\\\\u00e3o (Slack, Trello)\\\"},{\\\"value\\\":\\\"cloud\\\",\\\"label\\\":\\\"Armazenamento em nuvem (Google Drive, Dropbox)\\\"},{\\\"value\\\":\\\"vpn\\\",\\\"label\\\":\\\"VPN e seguran\\\\u00e7a\\\"},{\\\"value\\\":\\\"projetos\\\",\\\"label\\\":\\\"Gest\\\\u00e3o de projetos (Asana, Jira)\\\"},{\\\"value\\\":\\\"comunicacao\\\",\\\"label\\\":\\\"Comunica\\\\u00e7\\\\u00e3o instant\\\\u00e2nea (WhatsApp, Telegram)\\\"}]\"', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(13, 3, NULL, 'Qual seu nível de satisfação com a conectividade de internet?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 10, NULL, 'Muito insatisfeito', 'Muito satisfeito', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, '2026-02-03 14:38:19', '2026-02-03 14:38:19'),
(14, 8, 'Pergunta de parágrafo', 'Pergunta de parágrafo', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, '2026-02-05 06:19:46', '2026-02-05 06:19:46'),
(15, 10, 'Pergunta de texto curto', 'Pergunta de texto curto', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-02-06 08:01:23', '2026-02-06 08:01:23'),
(16, 10, 'Selecione uma data', 'Selecione uma data', NULL, 'date', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, NULL, '2026-02-06 08:01:23', '2026-02-06 08:01:23'),
(17, 10, 'Pergunta de caixas de seleção', 'Pergunta de caixas de seleção', NULL, 'checkboxes', '[\"Opção 1\", \"Opção 2\", \"Opção 3\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2, NULL, NULL, NULL, '2026-02-06 08:01:23', '2026-02-06 08:01:23'),
(18, 15, NULL, 'Qual é o seu nível de proficiência no uso de ferramentas digitais?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Iniciante', 'Avançado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL),
(19, 15, NULL, 'Quais tecnologias você utiliza regularmente?', NULL, 'checkboxes', '[{\"label\": \"Computação em Nuvem\", \"value\": \"cloud\"}, {\"label\": \"Inteligência Artificial\", \"value\": \"ia\"}, {\"label\": \"Internet das Coisas\", \"value\": \"iot\"}, {\"label\": \"Blockchain\", \"value\": \"blockchain\"}, {\"label\": \"Big Data\", \"value\": \"bigdata\"}, {\"label\": \"Aplicações Móveis\", \"value\": \"mobile\"}]', 'Selecione as opções aplicáveis', NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, '{\"max_selections\": 3, \"min_selections\": 1}', NULL, NULL, NULL),
(20, 15, NULL, 'Na sua instituição, qual o principal desafio para a transformação digital?', NULL, 'dropdown', '[{\"label\": \"Infraestrutura inadequada\", \"value\": \"infraestrutura\"}, {\"label\": \"Falta de formação\", \"value\": \"formacao\"}, {\"label\": \"Custo elevado\", \"value\": \"custo\"}, {\"label\": \"Resistência à mudança\", \"value\": \"resistencia\"}, {\"label\": \"Preocupações com segurança\", \"value\": \"seguranca\"}]', 'Selecione uma opção', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL),
(21, 15, NULL, 'Como você avalia o potencial da transformação digital para o desenvolvimento de Moçambique?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Muito Baixo', 'Muito Alto', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 4, NULL, NULL, NULL, NULL, NULL),
(22, 15, NULL, 'Quais sugestões você daria para acelerar a transformação digital no país?', NULL, 'paragraph', NULL, 'Compartilhe suas ideias e recomendações...', NULL, 20, 1000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, NULL, NULL, NULL),
(23, 15, NULL, 'Você participaria de programas de capacitação em tecnologias emergentes?', NULL, 'multiple_choice', '[{\"label\": \"Sim\", \"value\": \"sim\"}, {\"label\": \"Não\", \"value\": \"nao\"}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 6, NULL, NULL, NULL, NULL, NULL),
(24, 16, NULL, 'Você conhece ou já experimentou metodologias ativas de aprendizagem?', NULL, 'dropdown', '[{\"label\": \"Sim, pratico regularmente\", \"value\": \"sim_pratico\"}, {\"label\": \"Sim, conheço mas não pratico\", \"value\": \"sim_conheco\"}, {\"label\": \"Não conheço\", \"value\": \"nao_conheco\"}, {\"label\": \"Tenho interesse em aprender\", \"value\": \"interessado\"}]', 'Selecione uma opção', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL),
(25, 16, NULL, 'Quais metodologias ativas você considera mais eficazes?', NULL, 'checkboxes', '[{\"label\": \"Aprendizagem Baseada em Problemas\", \"value\": \"pbl\"}, {\"label\": \"Sala de Aula Invertida\", \"value\": \"sala_invertida\"}, {\"label\": \"Gamificação\", \"value\": \"gamificacao\"}, {\"label\": \"Estudo de Caso\", \"value\": \"estudo_caso\"}, {\"label\": \"Aprendizagem por Projetos\", \"value\": \"aprendizagem_projetos\"}, {\"label\": \"Instrução por Pares\", \"value\": \"peer_instruction\"}]', 'Selecione até 3 opções', NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, '{\"max_selections\": 3, \"min_selections\": 1}', NULL, NULL, NULL),
(26, 16, NULL, 'Em uma escala de 1 a 5, como você avalia a contribuição das metodologias ativas para o aprendizado?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Não contribui', 'Contribui muito', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL),
(27, 16, NULL, 'Quais os principais desafios para implementar metodologias ativas?', NULL, 'checkboxes', '[{\"label\": \"Falta de formação docente\", \"value\": \"formacao\"}, {\"label\": \"Infraestrutura inadequada\", \"value\": \"infraestrutura\"}, {\"label\": \"Tempo de preparação\", \"value\": \"tempo\"}, {\"label\": \"Resistência de alunos\", \"value\": \"resistencia\"}, {\"label\": \"Currículo engessado\", \"value\": \"curriculo\"}]', 'Selecione os principais desafios', NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 4, NULL, '{\"max_selections\": 3, \"min_selections\": 1}', NULL, NULL, NULL),
(28, 16, NULL, 'Que recomendações você daria para melhorar a qualidade do ensino superior?', NULL, 'paragraph', NULL, 'Compartilhe suas recomendações...', NULL, 20, 1000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, NULL, NULL, NULL),
(29, 16, NULL, 'Você recomendaria a adoção de metodologias ativas em mais disciplinas?', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 6, NULL, NULL, NULL, NULL, NULL),
(30, 17, NULL, 'Com que frequência você utiliza os serviços de saúde públicos?', NULL, 'dropdown', '[{\"label\": \"Regularmente\", \"value\": \"regularmente\"}, {\"label\": \"Ocasionalmente\", \"value\": \"ocasionalmente\"}, {\"label\": \"Raramente\", \"value\": \"raramente\"}, {\"label\": \"Nunca\", \"value\": \"nunca\"}]', 'Selecione uma opção', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL),
(31, 17, NULL, 'Como você avalia a qualidade geral dos serviços de saúde?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Muito Ruim', 'Excelente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL),
(32, 17, NULL, 'Quais aspectos dos serviços de saúde precisam de mais atenção?', NULL, 'checkboxes', '[{\"label\": \"Infraestrutura\", \"value\": \"infraestrutura\"}, {\"label\": \"Equipamentos\", \"value\": \"equipamentos\"}, {\"label\": \"Disponibilidade de medicamentos\", \"value\": \"medicamentos\"}, {\"label\": \"Quantidade de profissionais\", \"value\": \"profissionais\"}, {\"label\": \"Tempo de espera\", \"value\": \"tempo_espera\"}, {\"label\": \"Qualidade do atendimento\", \"value\": \"atendimento\"}]', 'Selecione até 3 opções', NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, '{\"max_selections\": 3, \"min_selections\": 1}', NULL, NULL, NULL),
(33, 17, NULL, 'Quando foi sua última consulta médica?', NULL, 'date', NULL, 'Selecione a data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-01-01', '2026-02-13', NULL, NULL, 1, 4, NULL, NULL, NULL, NULL, NULL),
(34, 17, NULL, 'Qual o principal desafio que você enfrenta ao acessar serviços de saúde?', NULL, 'paragraph', NULL, 'Descreva o principal desafio...', NULL, 10, 500, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, NULL, NULL, NULL),
(35, 17, NULL, 'Você utiliza serviços de saúde privados como complemento?', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 6, NULL, NULL, NULL, NULL, NULL),
(36, 18, NULL, 'Como você avalia seu conhecimento sobre os direitos fundamentais dos cidadãos?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Nenhum conhecimento', 'Conhecimento aprofundado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL),
(37, 18, NULL, 'Você já precisou de assistência jurídica nos últimos 5 anos?', NULL, 'dropdown', '[{\"label\": \"Sim, e obtive assistência\", \"value\": \"sim_obteve\"}, {\"label\": \"Sim, mas não obtive\", \"value\": \"sim_nao_obteve\"}, {\"label\": \"Não precisei\", \"value\": \"nao_precisei\"}, {\"label\": \"Não confio no sistema\", \"value\": \"nao_confio\"}]', 'Selecione uma opção', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL),
(38, 18, NULL, 'Quais barreiras você identifica para o acesso à justiça em Moçambique?', NULL, 'checkboxes', '[{\"label\": \"Custos elevados\", \"value\": \"custo\"}, {\"label\": \"Distância dos tribunais\", \"value\": \"distancia\"}, {\"label\": \"Morosidade processual\", \"value\": \"morosidade\"}, {\"label\": \"Falta de informação\", \"value\": \"informacao\"}, {\"label\": \"Dificuldade de acesso a advogados\", \"value\": \"advogado\"}, {\"label\": \"Corrupção\", \"value\": \"corrupcao\"}]', 'Selecione até 3 opções', NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, '{\"max_selections\": 3, \"min_selections\": 1}', NULL, NULL, NULL),
(39, 18, NULL, 'Em sua opinião, o sistema judicial moçambicano é eficaz na resolução de conflitos?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Nada eficaz', 'Muito eficaz', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 4, NULL, NULL, NULL, NULL, NULL),
(40, 18, NULL, 'Que medidas poderiam melhorar o acesso à justiça no país?', NULL, 'paragraph', NULL, 'Compartilhe suas sugestões...', NULL, 20, 500, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, NULL, NULL, NULL),
(41, 18, NULL, 'Você confia nas instituições judiciais moçambicanas?', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 6, NULL, NULL, NULL, NULL, NULL),
(42, 19, NULL, 'Qual é seu nível de interesse em empreendedorismo?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Nenhum interesse', 'Muito interessado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL),
(43, 19, NULL, 'Quais os principais desafios para iniciar um negócio em Moçambique?', NULL, 'checkboxes', '[{\"label\": \"Acesso a crédito\", \"value\": \"acesso_credito\"}, {\"label\": \"Burocracia\", \"value\": \"burocracia\"}, {\"label\": \"Acesso ao mercado\", \"value\": \"mercado\"}, {\"label\": \"Falta de formação\", \"value\": \"formacao\"}, {\"label\": \"Infraestrutura\", \"value\": \"infraestrutura\"}, {\"label\": \"Concorrência desleal\", \"value\": \"concorrencia\"}]', 'Selecione até 3 opções', NULL, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, '{\"max_selections\": 3, \"min_selections\": 1}', NULL, NULL, NULL),
(44, 19, NULL, 'Você já participou de programas de capacitação empreendedora?', NULL, 'dropdown', '[{\"label\": \"Sim, já participei\", \"value\": \"sim\"}, {\"label\": \"Não, mas tenho interesse\", \"value\": \"nao_interesse\"}, {\"label\": \"Não, não tenho interesse\", \"value\": \"nao_desinteresse\"}, {\"label\": \"Estou planejando participar\", \"value\": \"planejando\"}]', 'Selecione uma opção', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, NULL, NULL),
(45, 19, NULL, 'Qual área de negócio você considera mais promissora?', NULL, 'dropdown', '[{\"label\": \"Tecnologia e Inovação\", \"value\": \"tecnologia\"}, {\"label\": \"Agronegócio\", \"value\": \"agronegocio\"}, {\"label\": \"Comércio\", \"value\": \"comercio\"}, {\"label\": \"Serviços\", \"value\": \"servicos\"}, {\"label\": \"Turismo\", \"value\": \"turismo\"}, {\"label\": \"Indústria\", \"value\": \"industria\"}]', 'Selecione uma opção', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 4, NULL, NULL, NULL, NULL, NULL),
(46, 19, NULL, 'Como você avalia o apoio governamental ao empreendedorismo juvenil?', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, 1, 'Muito insuficiente', 'Muito suficiente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, NULL, NULL, NULL, NULL, NULL),
(47, 19, NULL, 'Que sugestões você daria para fomentar o empreendedorismo juvenil?', NULL, 'paragraph', NULL, 'Compartilhe suas ideias...', NULL, 20, 500, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 6, NULL, NULL, NULL, NULL, NULL),
(48, 19, NULL, 'Você tem ou já teve um negócio próprio?', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 7, NULL, NULL, NULL, NULL, NULL),
(49, 20, 'Pergunta de texto curto', 'Pergunta de texto curto', NULL, 'text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, '2026-02-13 09:18:56', '2026-02-13 09:18:56'),
(50, 20, 'Pergunta de parágrafo', 'Pergunta de parágrafo', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, NULL, NULL, '2026-02-13 09:18:56', '2026-02-13 09:18:56'),
(51, 20, 'Pergunta de múltipla escolha', 'Pergunta de múltipla escolha', NULL, 'multiple_choice', '[\"Opção 1\", \"Opção 2\", \"Opção 3\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2, NULL, NULL, NULL, '2026-02-13 09:18:56', '2026-02-13 09:18:56'),
(52, 20, 'Pergunta de menu suspenso', 'Pergunta de menu suspenso', NULL, 'dropdown', '[\"Opção 1\", \"Opção 2\", \"Opção 3\", \"Nova opção\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 3, NULL, NULL, NULL, '2026-02-13 09:18:56', '2026-02-13 09:18:56'),
(53, 20, 'Avalie em uma escala', 'Avalie em uma escala', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 4, NULL, NULL, NULL, '2026-02-13 09:18:56', '2026-02-13 09:18:56'),
(54, 20, 'Selecione uma data', 'Selecione uma data', NULL, 'date', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, NULL, '2026-02-13 09:18:56', '2026-02-13 09:18:56'),
(55, 20, 'Selecione um horário', 'Selecione um horário', NULL, 'time', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 6, NULL, NULL, NULL, '2026-02-13 09:18:56', '2026-02-13 09:18:56'),
(56, 7, 'Qual sua opinião sobre tecnologia?', 'Pergunta 1', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-02-15 05:06:13', '2026-02-15 05:06:13'),
(57, 7, 'Com que frequência usa tecnologia?', 'Pergunta 2', NULL, 'multiple_choice', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, '2026-02-15 05:06:13', '2026-02-15 05:06:13'),
(58, 7, 'Sugestões para melhoria', 'Pergunta 3', NULL, 'text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 3, NULL, NULL, NULL, '2026-02-15 05:06:13', '2026-02-15 05:06:13'),
(59, 17, 'Como avalia o acesso à saúde?', 'Questão 1', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-02-15 05:06:25', '2026-02-15 05:06:25'),
(60, 17, 'Quais serviços utiliza?', 'Questão 2', NULL, 'checkboxes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, '2026-02-15 05:06:25', '2026-02-15 05:06:25'),
(61, 17, 'Sugestões para melhorar', 'Questão 3', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 3, NULL, NULL, NULL, '2026-02-15 05:06:25', '2026-02-15 05:06:25'),
(62, 7, 'Com que frequência você utiliza dispositivos tecnológicos no seu dia a dia?', 'Uso de Tecnologia', NULL, 'multiple_choice', '[\"Diariamente\", \"Várias vezes por semana\", \"Uma vez por semana\", \"Raramente\", \"Nunca\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-02-15 05:07:41', '2026-02-15 05:07:41'),
(63, 7, 'Quais ferramentas tecnológicas você mais utiliza?', 'Ferramentas Preferidas', NULL, 'checkboxes', '[\"Smartphone\", \"Computador\", \"Tablet\", \"Smart TV\", \"Assistente Virtual\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, '2026-02-15 05:07:41', '2026-02-15 05:07:41'),
(64, 7, 'Como você avalia seu nível de conhecimento em tecnologia?', 'Nível de Conhecimento', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, '2026-02-15 05:07:41', '2026-02-15 05:07:41'),
(65, 7, 'Quais são as principais dificuldades que encontra ao usar tecnologia?', 'Dificuldades', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 4, NULL, NULL, NULL, '2026-02-15 05:07:41', '2026-02-15 05:07:41'),
(66, 7, 'Que sugestões você daria para melhorar o acesso à tecnologia em Moçambique?', 'Sugestões', NULL, 'text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, NULL, '2026-02-15 05:07:41', '2026-02-15 05:07:41'),
(67, 17, 'Com que frequência você ou seus familiares utilizam os serviços de saúde públicos?', 'Frequência de Uso', NULL, 'multiple_choice', '[\"Regularmente (mais de uma vez por mês)\", \"Frequentemente (uma vez por mês)\", \"Ocasionalmente (a cada 3 meses)\", \"Raramente (uma vez por ano)\", \"Nunca\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-02-15 05:07:51', '2026-02-15 05:07:51'),
(68, 17, 'Como você avalia o tempo de espera para atendimento nas unidades de saúde?', 'Tempo de Espera', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, '2026-02-15 05:07:51', '2026-02-15 05:07:51'),
(69, 17, 'Quais serviços de saúde você considera mais problemáticos ou deficitários?', 'Serviços Problemáticos', NULL, 'checkboxes', '[\"Atendimento geral\", \"Consultas especializadas\", \"Urgência/emergência\", \"Internamento\", \"Farmácia/medicamentos\", \"Exames laboratoriais\", \"Maternidade\", \"Saúde infantil\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, '2026-02-15 05:07:51', '2026-02-15 05:07:51'),
(70, 17, 'Numa escala de 1 a 5, como você avalia a qualidade geral dos serviços de saúde em sua região?', 'Avaliação Geral', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 4, NULL, NULL, NULL, '2026-02-15 05:07:51', '2026-02-15 05:07:51'),
(71, 17, 'Considera os custos com saúde (medicamentos, exames, consultas) acessíveis?', 'Custos', NULL, 'multiple_choice', '[\"Sim, totalmente acessíveis\", \"Parcialmente acessíveis\", \"Pouco acessíveis\", \"Não, são inacessíveis\", \"Não sei avaliar\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 5, NULL, NULL, NULL, '2026-02-15 05:07:51', '2026-02-15 05:07:51'),
(72, 17, 'A distância até à unidade de saúde mais próxima é um problema para você?', 'Distância', NULL, 'multiple_choice', '[\"Sim, grande problema\", \"Sim, um problema moderado\", \"Pouco problema\", \"Não é problema\", \"Não se aplica\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 6, NULL, NULL, NULL, '2026-02-15 05:07:51', '2026-02-15 05:07:51'),
(73, 17, 'Como classifica a qualidade do atendimento dos profissionais de saúde?', 'Atendimento', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 7, NULL, NULL, NULL, '2026-02-15 05:07:51', '2026-02-15 05:07:51'),
(74, 17, 'Que melhorias específicas gostaria de ver nos serviços de saúde?', 'Melhorias', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 8, NULL, NULL, NULL, '2026-02-15 05:07:51', '2026-02-15 05:07:51'),
(75, 17, 'Compartilhe uma experiência pessoal (positiva ou negativa) com os serviços de saúde em Moçambique', 'Experiência Pessoal', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 9, NULL, NULL, NULL, '2026-02-15 05:07:51', '2026-02-15 05:07:51'),
(76, 17, 'Que sugestões você daria para melhorar o acesso e qualidade dos serviços de saúde no país?', 'Sugestões Finais', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 10, NULL, NULL, NULL, '2026-02-15 05:07:51', '2026-02-15 05:07:51'),
(77, 8, 'Como avalia sua satisfação geral?', 'Satisfação Geral', NULL, 'linear_scale', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, '2026-02-15 05:08:03', '2026-02-15 05:08:03'),
(78, 8, 'Recomendaria nossos serviços?', 'Recomendação', NULL, 'multiple_choice', '[\"Sim\", \"Não\", \"Talvez\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, NULL, NULL, NULL, '2026-02-15 05:08:03', '2026-02-15 05:08:03'),
(79, 8, 'O que poderia ser melhorado?', 'Melhorias', NULL, 'checkboxes', '[\"Atendimento\", \"Qualidade\", \"Preço\", \"Rapidez\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 4, NULL, NULL, NULL, '2026-02-15 05:08:03', '2026-02-15 05:08:03'),
(80, 8, 'Comentários adicionais', 'Comentários', NULL, 'paragraph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 5, NULL, NULL, NULL, '2026-02-15 05:08:03', '2026-02-15 05:08:03');

-- --------------------------------------------------------

--
-- Table structure for table survey_responses
--

DROP TABLE IF EXISTS survey_responses;
CREATE TABLE IF NOT EXISTS survey_responses (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  survey_id bigint UNSIGNED NOT NULL,
  user_id bigint UNSIGNED DEFAULT NULL,
  answers json DEFAULT NULL,
  feedback text COLLATE utf8mb4_unicode_ci COMMENT 'Feedback opcional do respondente',
  status enum('in_progress','completed','abandoned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in_progress',
  started_at timestamp NULL DEFAULT NULL,
  completed_at timestamp NULL DEFAULT NULL,
  completion_time int DEFAULT NULL,
  time_spent int DEFAULT NULL COMMENT 'Tempo gasto em segundos (alias para completion_time)',
  quality_score decimal(3,1) DEFAULT NULL COMMENT 'Pontuação de qualidade da resposta (0-10)',
  rating tinyint DEFAULT NULL COMMENT 'Avaliação da pesquisa (1-5 estrelas)',
  device_type varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  browser varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  browser_version varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  os varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  os_version varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ip_address varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  country varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  province varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  city varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  is_paid tinyint(1) NOT NULL DEFAULT '0',
  payment_amount decimal(10,2) DEFAULT NULL,
  payment_date timestamp NULL DEFAULT NULL,
  payment_method varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  payment_reference varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  metadata json DEFAULT NULL,
  created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY survey_responses_survey_id_user_id_unique (survey_id,user_id),
  KEY survey_responses_survey_id_index (survey_id),
  KEY survey_responses_user_id_index (user_id),
  KEY survey_responses_status_index (status),
  KEY survey_responses_completed_at_index (completed_at),
  KEY survey_responses_is_paid_index (is_paid),
  KEY survey_responses_payment_date_index (payment_date),
  KEY survey_responses_device_type_index (device_type),
  KEY survey_responses_province_index (province),
  KEY survey_responses_country_index (country)
) ENGINE=MyISAM AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table survey_responses
--

INSERT INTO survey_responses (id, survey_id, user_id, answers, feedback, status, started_at, completed_at, completion_time, time_spent, quality_score, rating, device_type, browser, browser_version, os, os_version, ip_address, country, province, city, is_paid, payment_amount, payment_date, payment_method, payment_reference, metadata, created_at, updated_at) VALUES
(106, 1, 10, '[3, null, null, null, null]', NULL, 'in_progress', '2026-02-13 04:23:56', NULL, 120, NULL, NULL, NULL, 'desktop', 'Chrome', NULL, NULL, NULL, '127.0.0.1', NULL, NULL, NULL, 0, 35.00, NULL, NULL, NULL, NULL, '2026-02-13 04:23:56', '2026-02-13 04:26:03'),
(107, 15, 15, '[3, [\"cloud\", \"ia\", \"iot\", \"blockchain\"], null, 3, null, null]', NULL, 'in_progress', '2026-02-13 07:03:43', NULL, 360, NULL, NULL, NULL, 'desktop', 'Chrome', NULL, NULL, NULL, '127.0.0.1', NULL, NULL, NULL, 0, 75.00, NULL, NULL, NULL, NULL, '2026-02-13 07:03:43', '2026-02-13 07:14:58'),
(108, 1, 15, '[2, 2, [\"price\", \"quality\", \"brand\", \"promotions\"], 3, \"jksbdkjbsjk\"]', NULL, 'completed', '2026-02-13 07:53:40', '2026-02-13 07:54:16', 32, NULL, NULL, NULL, 'mobile', 'Safari', NULL, NULL, NULL, '127.0.0.1', NULL, NULL, NULL, 1, 35.00, NULL, NULL, NULL, NULL, '2026-02-13 07:53:40', '2026-02-13 07:54:16'),
(109, 2, 15, '[null, null, null, null, null, null]', NULL, 'in_progress', '2026-02-13 07:54:25', NULL, 390, NULL, NULL, NULL, 'mobile', 'Safari', NULL, NULL, NULL, '127.0.0.1', NULL, NULL, NULL, 0, 45.00, NULL, NULL, NULL, NULL, '2026-02-13 07:54:25', '2026-02-13 08:16:46'),
(110, 15, 10, '[3, null, null, 3, null, 3]', NULL, 'in_progress', '2026-02-13 08:22:07', NULL, 540, NULL, NULL, NULL, 'desktop', 'Chrome', NULL, NULL, NULL, '127.0.0.1', NULL, NULL, NULL, 0, 75.00, NULL, NULL, NULL, NULL, '2026-02-13 08:22:07', '2026-02-13 08:50:16'),
(111, 2, 10, NULL, NULL, 'in_progress', '2026-02-13 09:35:30', NULL, NULL, NULL, NULL, NULL, 'desktop', 'Chrome', NULL, NULL, NULL, '127.0.0.1', NULL, NULL, NULL, 0, 45.00, NULL, NULL, NULL, NULL, '2026-02-13 09:35:30', '2026-02-13 09:35:30'),
(112, 16, 10, '[null, null, null, null, null, null]', NULL, 'in_progress', '2026-02-13 09:37:34', NULL, 990, NULL, NULL, NULL, 'desktop', 'Chrome', NULL, NULL, NULL, '127.0.0.1', NULL, NULL, NULL, 0, 60.00, NULL, NULL, NULL, NULL, '2026-02-13 09:37:34', '2026-02-13 10:41:20'),
(113, 17, 10, '[null, 2, 2, 3, null, 3, [\"infraestrutura\", \"equipamentos\"], null, [\"Atendimento geral\", \"Consultas especializadas\", \"Urgência/emergência\"], \"2026-02-18\", 4, \"hbhjbj\", 4, \"jhbjbjhbj\\n\\nConsidere dividir suas ideias em frases mais curtas.\", 4, 4, null, null, null]', NULL, 'in_progress', '2026-02-15 21:43:31', NULL, 360, NULL, NULL, NULL, 'desktop', 'Chrome', NULL, NULL, NULL, '127.0.0.1', NULL, NULL, NULL, 0, 80.00, NULL, NULL, NULL, NULL, '2026-02-15 21:43:31', '2026-02-15 21:50:48'),
(114, 17, 15, '[null, null, null, null, null, null, null, null, null, null]', NULL, 'in_progress', '2026-02-15 22:01:47', NULL, 1020, NULL, NULL, NULL, 'desktop', 'Chrome', NULL, NULL, NULL, '127.0.0.1', NULL, NULL, NULL, 0, 80.00, NULL, NULL, NULL, NULL, '2026-02-15 22:01:47', '2026-02-15 22:50:01'),
(115, 15, 12, '[4, [\"cloud\", \"iot\"], \"resistencia\", 3, null, 3]', NULL, 'in_progress', '2026-02-15 23:00:48', NULL, 900, NULL, NULL, NULL, 'desktop', 'Chrome', NULL, NULL, NULL, '127.0.0.1', NULL, NULL, NULL, 0, 75.00, NULL, NULL, NULL, NULL, '2026-02-15 23:00:48', '2026-02-15 23:58:27');

-- --------------------------------------------------------

--
-- Table structure for table survey_stats
--

DROP TABLE IF EXISTS survey_stats;
CREATE TABLE IF NOT EXISTS survey_stats (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  survey_id bigint UNSIGNED NOT NULL,
  total_views int NOT NULL DEFAULT '0',
  unique_visitors int NOT NULL DEFAULT '0',
  total_starts int NOT NULL DEFAULT '0',
  total_completions int NOT NULL DEFAULT '0',
  total_abandonments int NOT NULL DEFAULT '0',
  completion_rate decimal(5,2) NOT NULL DEFAULT '0.00',
  average_completion_time decimal(8,2) DEFAULT NULL,
  device_stats json DEFAULT NULL,
  location_stats json DEFAULT NULL,
  response_distribution json DEFAULT NULL,
  question_stats json DEFAULT NULL,
  stat_date date NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY survey_stats_survey_id_stat_date_unique (survey_id,stat_date),
  KEY survey_stats_survey_id_index (survey_id),
  KEY survey_stats_stat_date_index (stat_date)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table transactions
--

DROP TABLE IF EXISTS transactions;
CREATE TABLE IF NOT EXISTS transactions (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id bigint UNSIGNED NOT NULL,
  survey_id bigint UNSIGNED DEFAULT NULL,
  type enum('survey_earnings','withdrawal','refund','bonus') COLLATE utf8mb4_unicode_ci NOT NULL,
  amount decimal(10,2) NOT NULL,
  status enum('pending','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  description varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  payment_method varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  account_details text COLLATE utf8mb4_unicode_ci,
  completed_at timestamp NULL DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  deleted_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY transactions_survey_id_foreign (survey_id),
  KEY transactions_user_id_type_index (user_id,type),
  KEY transactions_status_created_at_index (status,created_at)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table transactions
--

INSERT INTO transactions (id, user_id, survey_id, type, amount, status, description, payment_method, account_details, completed_at, created_at, updated_at, deleted_at) VALUES
(1, 2, 4, 'survey_earnings', 150.75, 'completed', 'Ganhos da pesquisa sobre educação digital', NULL, NULL, NULL, '2026-02-05 06:09:23', '2026-02-05 06:09:23', NULL),
(2, 2, 4, 'survey_earnings', 89.50, 'completed', 'Ganhos da pesquisa sobre e-learning', NULL, NULL, NULL, '2026-02-05 06:09:23', '2026-02-05 06:09:23', NULL),
(3, 10, 10, 'withdrawal', 50.00, 'completed', 'Solicitação de saque', 'mpesa', '+258878612744', '2026-02-07 09:38:27', '2026-02-07 03:46:13', '2026-02-07 09:38:27', NULL),
(4, 10, 10, 'withdrawal', 70.00, 'completed', 'Solicitação de saque', 'mpesa', '+258496127446', '2026-02-07 09:38:28', '2026-02-07 05:51:59', '2026-02-07 09:38:28', NULL),
(5, 9, 2, 'survey_earnings', 45.00, 'pending', 'Participação na pesquisa: Satisfação com o Ensino Superior em Moçambique', NULL, NULL, NULL, '2026-02-07 08:49:06', '2026-02-07 08:49:06', NULL),
(6, 9, NULL, 'withdrawal', 50.00, 'completed', 'Solicitação de saque', 'mpesa', '+258840165527', '2026-02-07 09:38:28', '2026-02-07 09:08:05', '2026-02-07 09:38:28', NULL),
(7, 9, 1, 'survey_earnings', 35.00, 'pending', 'Participação na pesquisa: Hábitos de Consumo em Maputo - 2025', NULL, NULL, NULL, '2026-02-07 09:12:02', '2026-02-07 09:12:02', NULL),
(8, 10, 1, 'survey_earnings', 35.00, 'pending', 'Participação na pesquisa: Hábitos de Consumo em Maputo - 2025', NULL, NULL, NULL, '2026-02-07 10:09:57', '2026-02-07 10:09:57', NULL),
(9, 10, 2, 'survey_earnings', 45.00, 'pending', 'Participação na pesquisa: Satisfação com o Ensino Superior em Moçambique', NULL, NULL, NULL, '2026-02-07 10:15:50', '2026-02-07 10:15:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table universities
--

DROP TABLE IF EXISTS universities;
CREATE TABLE IF NOT EXISTS universities (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  name varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  acronym varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  type varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  location varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  website varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  email varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  phone varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  description text COLLATE utf8mb4_unicode_ci,
  logo_url varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  is_verified tinyint(1) NOT NULL DEFAULT '0',
  established_year int DEFAULT NULL,
  student_count int NOT NULL DEFAULT '0',
  `order` int NOT NULL DEFAULT '0',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY universities_name_index (name),
  KEY universities_type_index (type)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table universities
--

INSERT INTO universities (id, name, acronym, type, location, website, email, phone, description, logo_url, is_verified, established_year, student_count, `order`, created_at, updated_at) VALUES
(1, 'Universidade Eduardo Mondlane (UEM)', 'UEM', 'Universidade Pública', 'Maputo', 'https://www.uem.mz', 'info@uem.mz', '+25821490700', 'Principal universidade pública de Moçambique, fundada em 1962. Referência nacional em ensino superior e pesquisa.', '/storage/logos/universities/uem.png', 1, 1962, 45000, 1, '2026-02-03 15:31:22', '2026-02-03 15:49:06'),
(2, 'Universidade Pedagógica (UP)', 'UP', 'Universidade Pública', 'Maputo', 'https://www.up.ac.mz', 'reitoria@up.ac.mz', '+25821491400', 'Universidade pública especializada em ciências da educação e formação de professores.', '/storage/logos/universities/up.png', 1, 1985, 28000, 2, '2026-02-03 15:31:22', '2026-02-03 15:49:06'),
(3, 'Universidade Lúrio (UniLúrio)', 'UniLúrio', 'Universidade Pública', 'Nampula', 'https://www.unilurio.ac.mz', 'geral@unilurio.ac.mz', '+25827111000', 'Universidade pública do norte de Moçambique, com campus em Nampula, Cabo Delgado e Niassa.', '/storage/logos/universities/unilurio.png', 1, 2006, 15000, 3, '2026-02-03 15:31:22', '2026-02-03 15:49:06'),
(4, 'Universidade Zambeze (UniZambeze)', 'UniZambeze', 'Universidade Pública', 'Beira', 'https://www.unizambeze.ac.mz', 'secretaria@unizambeze.ac.mz', '+25823321000', 'Universidade pública na região centro de Moçambique, com sede na cidade da Beira.', '/storage/logos/universities/unizambeze.png', 1, 2006, 12000, 4, '2026-02-03 15:31:22', '2026-02-03 15:49:06'),
(5, 'Universidade Save (UniSave)', 'UniSave', 'Universidade Pública', 'Inhambane', 'https://www.unisave.ac.mz', 'info@unisave.ac.mz', '+25829320000', 'Universidade pública na província de Inhambane, focada em desenvolvimento regional.', '/storage/logos/universities/unisave.png', 1, 2006, 8000, 5, '2026-02-03 15:31:22', '2026-02-03 15:49:06'),
(6, 'Universidade Rovuma (UniRovuma)', 'UniRovuma', 'Universidade Pública', 'Nampula', 'https://www.unirovuma.ac.mz', 'contacto@unirovuma.ac.mz', '+25827210000', 'Universidade pública nas províncias de Cabo Delgado e Niassa.', '/storage/logos/universities/unirovuma.png', 1, 2006, 7000, 6, '2026-02-03 15:31:22', '2026-02-03 15:49:06'),
(7, 'Universidade Licungo (UniLicungo)', 'UniLicungo', 'Universidade Pública', 'Quelimane', 'https://www.unilicungo.ac.mz', 'info@unilicungo.ac.mz', '+25824220000', 'Universidade pública na província da Zambézia, sede em Quelimane.', '/storage/logos/universities/unilicungo.png', 1, 2006, 5000, 7, '2026-02-03 15:31:22', '2026-02-03 15:49:06'),
(8, 'Instituto Superior de Ciências e Tecnologia de Moçambique (ISCTEM)', 'ISCTEM', 'Universidade Privada', 'Maputo', 'https://www.isctem.ac.mz', 'secretaria@isctem.ac.mz', '+25821490000', 'Instituto superior público especializado em ciências e tecnologia.', '/storage/logos/universities/isctem.png', 1, 1996, 12000, 8, '2026-02-03 15:31:22', '2026-02-03 15:49:06'),
(9, 'Instituto Superior de Transportes e Comunicações (ISUTC)', 'ISUTC', 'Universidade Privada', 'Maputo', 'https://www.isutc.ac.mz', 'info@isutc.ac.mz', '+25821492000', 'Instituto superior público especializado em transportes e comunicações.', '/storage/logos/universities/isutc.png', 1, 1997, 8000, 9, '2026-02-03 15:31:22', '2026-02-03 15:49:06'),
(10, 'Universidade São Tomás de Moçambique (USTM)', 'USTM', 'Universidade Privada', 'Maputo', 'https://www.ustm.ac.mz', 'info@ustm.ac.mz', '+25821430000', 'Universidade privada com enfoque em ciências sociais e humanas.', '/storage/logos/universities/ustm.png', 1, 1996, 6000, 10, '2026-02-03 15:31:22', '2026-02-03 15:49:06'),
(11, 'Universidade Técnica de Moçambique (UDM)', 'UDM', 'Universidade Privada', 'Maputo', 'https://www.udm.ac.mz', 'geral@udm.ac.mz', '+25821435000', 'Universidade privada técnica e tecnológica.', '/storage/logos/universities/udm.png', 1, 2001, 5000, 11, '2026-02-03 15:31:22', '2026-02-03 15:49:06'),
(12, 'Universidade Politécnica (UniPoli)', 'UniPoli', 'Universidade Privada', 'Maputo', 'https://www.unipoli.ac.mz', NULL, NULL, NULL, NULL, 0, NULL, 0, 12, '2026-02-03 15:31:22', '2026-02-03 15:31:22'),
(13, 'Instituto Superior de Ciências de Saúde (ISCISA)', 'ISCISA', 'Instituto Superior', 'Maputo', 'https://www.iscisa.ac.mz', NULL, NULL, NULL, NULL, 0, NULL, 0, 13, '2026-02-03 15:31:22', '2026-02-03 15:31:22'),
(14, 'Instituto Superior de Tecnologias e Gestão (ISTEG)', 'ISTEG', 'Instituto Superior', 'Maputo', 'https://www.isteg.ac.mz', NULL, NULL, NULL, NULL, 0, NULL, 0, 14, '2026-02-03 15:31:22', '2026-02-03 15:31:22'),
(15, 'Instituto Superior Monitor (ISM)', 'ISM', 'Instituto Superior', 'Maputo', 'https://www.ism.ac.mz', 'secretaria@ism.ac.mz', '+25821456000', 'Instituto superior privado com várias unidades.', '/storage/logos/universities/ism.png', 1, 1995, 4000, 15, '2026-02-03 15:31:22', '2026-02-03 15:49:06'),
(16, 'Outra', NULL, 'Outra', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 99, '2026-02-03 15:31:22', '2026-02-03 15:31:22'),
(17, 'Universidade Púnguè (UniPúnguè)', 'UniPúnguè', 'Universidade Pública', 'Manica', 'https://www.unipungue.ac.mz', 'geral@unipungue.ac.mz', '+25825123000', 'Universidade pública na província de Manica, com campus em Chimoio.', '/storage/logos/universities/unipungue.png', 1, 2006, 6000, 17, '2026-02-03 15:32:32', '2026-02-03 15:49:06'),
(18, 'Instituto Superior Politécnico de Manica (ISPM)', 'ISPM', 'Instituto Superior Público', 'Manica', 'https://www.ispm.ac.mz', 'contacto@ispm.ac.mz', '+25825121000', 'Instituto politécnico público na província de Manica.', '/storage/logos/universities/ispm.png', 1, 1999, 3000, 18, '2026-02-03 15:32:32', '2026-02-03 15:49:06'),
(19, 'Universidade Católica de Moçambique (UCM)', 'UCM', 'Universidade Privada', 'Maputo', 'https://www.ucm.ac.mz', 'reitoria@ucm.ac.mz', '+25824212000', 'Universidade católica privada com várias unidades pelo país.', '/storage/logos/universities/ucm.png', 1, 1996, 10000, 19, '2026-02-03 15:32:32', '2026-02-03 15:49:06');

-- --------------------------------------------------------

--
-- Table structure for table users
--

DROP TABLE IF EXISTS users;
CREATE TABLE IF NOT EXISTS users (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  name varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  email varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  email_verified_at timestamp NULL DEFAULT NULL,
  password varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  phone varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  university_id bigint UNSIGNED DEFAULT NULL,
  course varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  role enum('student','participant','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'participant',
  verification_status enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  verified_at timestamp NULL DEFAULT NULL,
  balance decimal(10,2) NOT NULL DEFAULT '0.00',
  email_notifications tinyint(1) NOT NULL DEFAULT '1',
  whatsapp_notifications tinyint(1) NOT NULL DEFAULT '1',
  profile_info json DEFAULT NULL,
  remember_token varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY users_email_unique (email),
  UNIQUE KEY users_phone_unique (phone),
  KEY users_role_index (role),
  KEY users_verification_status_index (verification_status),
  KEY users_role_verification_status_index (role,verification_status),
  KEY users_email_index (email),
  KEY users_phone_index (phone),
  KEY users_university_id_foreign (university_id)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table users
--

INSERT INTO users (id, name, email, email_verified_at, password, phone, university_id, course, role, verification_status, verified_at, balance, email_notifications, whatsapp_notifications, profile_info, remember_token, created_at, updated_at) VALUES
(1, 'Administrador do Sistema', 'admin@mozpesquisa.ac.mz', '2026-02-03 19:34:58', '$2y$12$HXl1ka3ROiEQpJIzhLM2XuGu5u.pu/U2KuuXLQtC63Pw9zwtoh05u', '+258840001234', NULL, NULL, 'admin', 'approved', '2026-02-03 19:34:58', 10000.00, 1, 1, NULL, NULL, '2026-02-03 19:34:58', '2026-02-03 19:34:58'),
(2, 'Carlos Alberto Mondlane', 'carlos.mondlane@uem.ac.mz', '2026-02-03 19:34:58', '$2y$12$AySPqZJULDhH/obsaMhO1.cMU2lsOTyHpqVeNHN7yPhzrIJFMWmAy', '+258841112233', 1, 'Engenharia Informática', 'student', 'approved', '2026-02-03 19:34:58', 850.50, 1, 1, NULL, NULL, '2026-02-03 19:34:59', '2026-02-06 10:08:25'),
(3, 'Maria Fernanda Silva', 'maria.silva@up.ac.mz', '2026-02-03 19:34:58', '$2y$12$B7yBaoRk41qZnmhggu0rVejAJKBGyUcZ1aqJIVHEm3oaMMcWzeCcu', '+258842223344', 2, 'Ciências da Educação', 'student', 'approved', '2026-02-03 19:34:58', 725.00, 1, 0, NULL, NULL, '2026-02-03 19:34:59', '2026-02-03 19:34:59'),
(4, 'João Pedro Tembe', 'joao.tembe@unilurio.ac.mz', '2026-02-03 19:34:58', '$2y$12$t867uOk2VPtKIHON3Psk9eOFJLQ9kivPuYjVUsaQbFqRhjAJSMS1y', '+258843334455', 3, 'Medicina', 'student', 'approved', '2026-02-03 19:34:58', 1200.00, 0, 1, NULL, NULL, '2026-02-03 19:34:59', '2026-02-03 19:34:59'),
(5, 'Ana Paula Macuácua', 'ana.macuacua@isctem.ac.mz', NULL, '$2y$12$vS1eqcNyUxqP27tMUohIVeidOo3BtmnJEsOG9URD1GvzKIjf6we3W', '+258844445566', 8, 'Arquitetura', 'student', 'pending', NULL, 0.00, 1, 1, NULL, NULL, '2026-02-03 19:34:59', '2026-02-03 19:34:59'),
(6, 'David Jorge Cossa', 'david.cossa@ucm.ac.mz', '2026-02-03 19:34:59', '$2y$12$Z8JjPqF3EM/Ns2uhHGZRQeCNKTYYWLlww8GiaCOZiO8GtE.nhkMNG', '+258845556677', 19, 'Direito', 'student', 'approved', '2026-02-03 19:34:59', 550.25, 1, 1, NULL, NULL, '2026-02-03 19:34:59', '2026-02-03 19:34:59'),
(7, 'António Fernando Massinga', 'antonio.massinga@gmail.com', '2026-02-03 19:34:59', '$2y$12$4jy58kopR6GKOEHTWPUNc.KXwogxZ.vPOUIKWgljEvwHQ09Ocw5iK', '+258846667788', NULL, NULL, 'participant', 'approved', '2026-02-03 19:34:59', 325.75, 1, 1, NULL, NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(8, 'Célia Regina Nhaca', 'celia.nhaca@hotmail.com', '2026-02-03 19:34:59', '$2y$12$uktdYT.l.8IVZ.NxtkJZzunQcEkvDbclfKJSMOzco2i95ahrzhj0y', '+258847778899', NULL, NULL, 'participant', 'approved', '2026-02-03 19:34:59', 480.50, 1, 0, NULL, NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(9, 'Paulo José Matola', 'paulo.matola@yahoo.com', '2026-02-03 19:35:00', '$2y$12$RLm8SsH04YEKmK7pKG4e3.lw9P3vH2eNJTpyU670ZMkKBy/aWY33G', '+258848889900', NULL, NULL, 'participant', 'approved', '2026-02-03 19:35:00', 145.00, 0, 1, NULL, NULL, '2026-02-03 19:35:01', '2026-02-07 09:08:05'),
(10, 'Sofia Maria Chaúque', 'sofia.chauque@gmail.com', '2026-02-03 19:35:00', '$2y$12$q8MqfZjw5lWkJFvpCFPr4ePyfLNplsQJICrANXveWxj3X2SvjcOC2', '+258849990011', NULL, NULL, 'participant', 'approved', '2026-02-03 19:35:00', 500.25, 1, 1, NULL, NULL, '2026-02-03 19:35:01', '2026-02-07 05:51:59'),
(11, 'José Carlos Mucavel', 'jose.mucavel@outlook.com', '2026-02-03 19:35:00', '$2y$12$V4JDtr5tGAWlskE5bOy1suCQcE6.Zhaq4In2mYHD0lp7//xSI.kI6', '+258840011122', NULL, NULL, 'participant', 'approved', '2026-02-03 19:35:00', 275.50, 1, 1, NULL, NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(12, 'Teresa Alberto Nhantumbo', 'teresa.nhantumbo@gmail.com', '2026-02-03 19:35:00', '$2y$12$nHJ.rbvyNeWZG1UWwkSLfesf3cKdP9bqPSq3RrLBXjeGQ.Pls3sWu', '+258841122233', NULL, NULL, 'participant', 'approved', '2026-02-03 19:35:00', 150.00, 0, 0, NULL, NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(13, 'Rogério Francisco Sitoe', 'rogerio.sitoe@hotmail.com', NULL, '$2y$12$k9/XIAn3QxVDPmzpOmgKI.onSpcm7fPn8nyQ5OR51ovzBtlR9gdqG', '+258842233344', NULL, NULL, 'participant', 'pending', NULL, 0.00, 1, 1, NULL, NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(14, 'Luísa Domingos Muianga', 'luisa.muianga@yahoo.com', '2026-02-03 19:35:01', '$2y$12$nv3/h2j3o3YZ7Y6tNJrdbuSPwdA6i168u8vNWgMrLV1lpgM5.S3vK', '+258843344455', NULL, NULL, 'participant', 'approved', '2026-02-03 19:35:01', 385.75, 1, 1, NULL, NULL, '2026-02-03 19:35:01', '2026-02-03 19:35:01'),
(15, 'Fernando João Saíde', 'fernando.saide@gmail.com', '2026-02-03 19:35:01', '$2y$12$IZ0Uz2enH9xMlbJzQdKqVuor0fdokjcgIXgm0X.WlaVfRyn1hNXpu', '+258844455566', NULL, NULL, 'participant', 'approved', '2026-02-03 19:35:01', 330.00, 0, 1, NULL, NULL, '2026-02-03 19:35:01', '2026-02-13 07:54:16'),
(16, 'Marta José Amisse', 'marta.amisse@outlook.com', NULL, '$2y$12$5FSWaWkKfgC7aOBGoaBCo./u.kI58n3zC9IYPhJNlDX/hHTOAmNAC', '+258845566677', NULL, NULL, 'participant', 'approved', NULL, 0.00, 1, 0, NULL, NULL, '2026-02-03 19:35:01', '2026-02-12 10:37:02');

-- --------------------------------------------------------

--
-- Stand-in structure for view vw_participant_debug
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_participant_debug`;
CREATE TABLE IF NOT EXISTS `vw_participant_debug` (
`aprovadas` bigint
,`email` varchar(191)
,`ganhos_pendentes` decimal(32,2)
,`ganhos_totais` decimal(32,2)
,`id` bigint unsigned
,`name` varchar(191)
,`pendentes` bigint
,`qualidade_media` decimal(7,5)
,`rejeitadas` bigint
,`saldo_atual` decimal(10,2)
,`saques_totais` decimal(32,2)
,`total_respostas` bigint
);

-- --------------------------------------------------------

--
-- Table structure for table withdrawal_requests
--

DROP TABLE IF EXISTS withdrawal_requests;
CREATE TABLE IF NOT EXISTS withdrawal_requests (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id bigint UNSIGNED NOT NULL,
  survey_id bigint UNSIGNED DEFAULT NULL,
  amount decimal(10,2) NOT NULL,
  status enum('pending','processing','approved','rejected','paid','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  payment_method enum('mpesa','bank_transfer','cash') COLLATE utf8mb4_unicode_ci NOT NULL,
  account_details varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  transaction_id varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  processed_at timestamp NULL DEFAULT NULL,
  rejection_reason text COLLATE utf8mb4_unicode_ci,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY withdrawal_requests_user_id_foreign (user_id),
  KEY withdrawal_requests_survey_id_foreign (survey_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure for view vw_participant_debug
--
DROP TABLE IF EXISTS `vw_participant_debug`;

DROP VIEW IF EXISTS vw_participant_debug;
CREATE ALGORITHM=UNDEFINED DEFINER=root@localhost SQL SECURITY DEFINER VIEW vw_participant_debug  AS SELECT u.id AS `id`, u.`name` AS `name`, u.email AS `email`, u.balance AS `saldo_atual`, (select count(0) from survey_responses where (survey_responses.user_id = u.id)) AS `total_respostas`, (select count(0) from survey_responses where ((survey_responses.user_id = u.id) and (survey_responses.`status` = 'approved'))) AS `aprovadas`, (select count(0) from survey_responses where ((survey_responses.user_id = u.id) and (survey_responses.`status` = 'rejected'))) AS `rejeitadas`, (select count(0) from survey_responses where ((survey_responses.user_id = u.id) and (survey_responses.`status` = 'pending'))) AS `pendentes`, (select avg(survey_responses.quality_score) from survey_responses where (survey_responses.user_id = u.id)) AS `qualidade_media`, (select sum(transactions.amount) from transactions where ((transactions.user_id = u.id) and (transactions.`type` in ('payment','survey_earnings')))) AS `ganhos_totais`, (select sum(transactions.amount) from transactions where ((transactions.user_id = u.id) and (transactions.`type` = 'withdrawal') and (transactions.`status` = 'completed'))) AS `saques_totais`, (select sum(transactions.amount) from transactions where ((transactions.user_id = u.id) and (transactions.`type` in ('payment','survey_earnings')) and (transactions.`status` = 'pending'))) AS `ganhos_pendentes` FROM users AS `u` WHERE (u.`role` = 'participant') ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
