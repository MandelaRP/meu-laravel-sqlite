-- Exportação de SQLite para MySQL
-- Gerado em: 2025-11-11 16:25:16
-- 

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- Estrutura da tabela `migrations`
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `migrations`
LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_03_08_080554_create_groups_table', 1),
(5, '2025_03_22_152929_create_members_table', 1),
(6, '2025_03_23_002603_create_roles_table', 1),
(7, '2025_03_30_193101_create_role_member_table', 1),
(8, '2025_06_09_015523_create_user_addresses_table', 1),
(9, '2025_07_27_201635_create_transactions_table', 1),
(10, '2025_07_27_201708_create_financial_settings_table', 1),
(11, '2025_07_28_030200_create_categories_table', 1),
(12, '2025_07_28_030238_create_products_table', 1),
(13, '2025_07_29_024850_create_checkouts_table', 1),
(14, '2025_07_31_231907_create_order_bumps_table', 1),
(15, '2025_11_07_192406_add_is_sample_column_to_tables', 1),
(16, '2025_11_08_145345_create_acquirers_table', 1),
(17, '2025_11_08_152611_create_liberpay_sales_table', 1),
(18, '2025_11_08_160526_add_gateway_fee_percentage_to_acquirers_table', 1),
(19, '2025_11_08_190230_add_acquirer_id_to_users_table', 1),
(20, '2025_11_09_145822_create_system_settings_table', 1),
(21, '2025_11_09_150020_add_user_fee_settings_to_users_table', 1),
(22, '2025_11_09_161408_add_product_id_to_transactions_table', 1),
(23, '2025_11_09_161610_make_category_id_nullable_in_products_table', 1),
(24, '2025_11_09_192608_add_fee_fields_to_acquirers_table', 2),
(25, '2025_11_09_203340_create_fullpix_sales_table', 3),
(26, '2025_11_09_204303_add_preferred_acquirer_to_users_table', 4),
(27, '2025_11_09_215025_create_pix_keys_table', 5),
(28, '2025_11_09_215352_create_withdrawals_table', 5),
(29, '2025_11_09_221006_add_withdrawal_fee_to_acquirers_table', 6),
(30, '2025_11_09_221730_add_gateway_fee_to_withdrawals_table', 7),
(31, '2025_11_10_211915_drop_products_table_from_seller', 8),
(32, '2025_11_10_213802_create_products_table_again', 9);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `password_reset_tokens`
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` datetime,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `password_reset_tokens`: vazia


-- Estrutura da tabela `sessions`
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` INT,
  `ip_address` VARCHAR(255),
  `user_agent` TEXT,
  `payload` TEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `sessions`: vazia


-- Estrutura da tabela `cache`
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` TEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `cache`: vazia


-- Estrutura da tabela `cache_locks`
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `cache_locks`: vazia


-- Estrutura da tabela `jobs`
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `queue` VARCHAR(255) NOT NULL,
  `payload` TEXT NOT NULL,
  `attempts` INT NOT NULL,
  `reserved_at` INT,
  `available_at` INT NOT NULL,
  `created_at` INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `jobs`: vazia


-- Estrutura da tabela `job_batches`
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` TEXT NOT NULL,
  `options` TEXT,
  `cancelled_at` INT,
  `created_at` INT NOT NULL,
  `finished_at` INT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `job_batches`: vazia


-- Estrutura da tabela `failed_jobs`
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` TEXT NOT NULL,
  `exception` TEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `failed_jobs`: vazia


-- Estrutura da tabela `groups`
DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255),
  `created_at` datetime,
  `updated_at` datetime,
  FOREIGN KEY(`user_id`) references `users`(`id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `groups`
LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` (`id`, `user_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
('019a6978-41cf-726f-a753-cedffa7fa8dc', 2, 'ipsam', 'Grupo de teste', '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
('019a6978-41db-720c-9c54-2b38a1677974', 3, 'qui', 'Grupo de teste', '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
('019a6978-41eb-72b1-b648-96e839b1e79a', 4, 'cumque', 'Grupo de teste', '2025-11-09 16:34:40', '2025-11-09 16:34:40');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `members`
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `user_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(255) NOT NULL,
  `alert_type` VARCHAR(255) CHECK (`alert_type` IN ('error', 'warning', 'info', 'debug', 'critical', 'success', 'unknown', 'fatal', 'notice', 'alert', 'emergency')),
  `notifications` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `alert_channels` TEXT NOT NULL,
  `created_at` datetime,
  `updated_at` datetime,
  FOREIGN KEY(`user_id`) references `users`(`id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `members`: vazia


-- Estrutura da tabela `roles`
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `created_at` datetime,
  `updated_at` datetime,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `roles`
LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'admin', '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
(2, 'support', '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
(3, 'developer', '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
(4, 'owner', '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
(5, 'infrastructure', '2025-11-09 16:34:40', '2025-11-09 16:34:40');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `permissions`
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `created_at` datetime,
  `updated_at` datetime,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `permissions`
LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'view-user', '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
(2, 'edit-user', '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
(3, 'delete-user', '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
(4, 'create-user', '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
(5, 'update-user', '2025-11-09 16:34:40', '2025-11-09 16:34:40');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `permission_role`
DROP TABLE IF EXISTS `permission_role`;
CREATE TABLE `permission_role` (
  `permission_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  FOREIGN KEY(`permission_id`) references `permissions`(`id`),
  FOREIGN KEY(`role_id`) references `roles`(`id`),
  PRIMARY KEY (`permission_id`, `role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `permission_role`: vazia


-- Estrutura da tabela `role_user`
DROP TABLE IF EXISTS `role_user`;
CREATE TABLE `role_user` (
  `role_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  FOREIGN KEY(`role_id`) references `roles`(`id`),
  FOREIGN KEY(`user_id`) references `users`(`id`),
  PRIMARY KEY (`role_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `role_user`
LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
INSERT INTO `role_user` (`role_id`, `user_id`) VALUES
(1, 1),
(1, 6);
/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `permission_user`
DROP TABLE IF EXISTS `permission_user`;
CREATE TABLE `permission_user` (
  `user_id` INT NOT NULL,
  `permission_id` INT NOT NULL,
  FOREIGN KEY(`user_id`) references `users`(`id`),
  FOREIGN KEY(`permission_id`) references `permissions`(`id`),
  PRIMARY KEY (`user_id`, `permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `permission_user`
LOCK TABLES `permission_user` WRITE;
/*!40000 ALTER TABLE `permission_user` DISABLE KEYS */;
INSERT INTO `permission_user` (`user_id`, `permission_id`) VALUES
(1, 1),
(6, 1),
(6, 2),
(6, 3),
(6, 4),
(6, 5);
/*!40000 ALTER TABLE `permission_user` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `member_role`
DROP TABLE IF EXISTS `member_role`;
CREATE TABLE `member_role` (
  `member_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  FOREIGN KEY(`member_id`) references `members`(`id`),
  FOREIGN KEY(`role_id`) references `roles`(`id`),
  PRIMARY KEY (`member_id`, `role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `member_role`: vazia


-- Estrutura da tabela `addresses`
DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `user_id` INT NOT NULL,
  `zip_code` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `number` VARCHAR(255) NOT NULL,
  `city` VARCHAR(255) NOT NULL,
  `state` VARCHAR(255) NOT NULL,
  `created_at` datetime,
  `updated_at` datetime,
  FOREIGN KEY(`user_id`) references `users`(`id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `addresses`
LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` (`id`, `user_id`, `zip_code`, `address`, `number`, `city`, `state`, `created_at`, `updated_at`) VALUES
(1, 7, '57602-335', 'Travessa Vereador Zeca Paulo', '300', 'Maceió', 'AL', '2025-11-09 17:00:53', '2025-11-09 17:00:53'),
(2, 8, '57602-335', 'Travessa Vereador Zeca Paulo', '300', 'Maceió', 'AL', '2025-11-09 17:40:37', '2025-11-09 17:40:37');
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `financial_settings`
DROP TABLE IF EXISTS `financial_settings`;
CREATE TABLE `financial_settings` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `user_id` INT NOT NULL,
  `cash_in_percentage` DECIMAL(10,2) NOT NULL DEFAULT '5',
  `cash_in_fixed_value` DECIMAL(10,2) NOT NULL default '2.5',
  `cash_out_percentage` DECIMAL(10,2) NOT NULL default '3.5',
  `cash_out_fixed_value` DECIMAL(10,2) NOT NULL default '1.8',
  `minimum_cash_in_value` DECIMAL(10,2) NOT NULL DEFAULT '10',
  `maximum_cash_in_value` DECIMAL(10,2) NOT NULL DEFAULT '50',
  `minimum_cash_out_value` DECIMAL(10,2) NOT NULL DEFAULT '5',
  `maximum_cash_out_value` DECIMAL(10,2) NOT NULL DEFAULT '25',
  `created_at` datetime,
  `updated_at` datetime,
  FOREIGN KEY(`user_id`) references `users`(`id`) on delete cascade,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `financial_settings`: vazia


-- Estrutura da tabela `categories`
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255),
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime,
  `updated_at` datetime,
  FOREIGN KEY(`user_id`) references `users`(`id`) on delete cascade,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `categories`
LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`id`, `user_id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
('019a6978-4261-7297-b408-37f99858b313', 1, 'Eletrônicos', 'Produtos eletrônicos e tecnológicos', 1, '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
('019a6978-426c-7321-90df-9f945330722a', 1, 'Ebooks', 'Ebooks e materiais de leitura', 1, '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
('019a6978-4283-7288-bd5e-7b71f062085d', 1, 'Vestuário', 'Roupas e acessórios', 1, '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
('019a6978-428f-72a9-9bfb-261f62e8ea89', 1, 'Casa e Jardim', 'Produtos para casa e jardinagem', 1, '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
('019a6978-4299-7342-a3cc-44ee13a3bc27', 1, 'Esportes', 'Equipamentos e acessórios esportivos', 1, '2025-11-09 16:34:40', '2025-11-09 16:34:40'),
('019a6978-42a3-727a-b25e-0fb4698c3f11', 1, 'Livros', 'Livros e materiais de leitura', 1, '2025-11-09 16:34:40', '2025-11-09 16:34:40');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `order_bumps`
DROP TABLE IF EXISTS `order_bumps`;
CREATE TABLE `order_bumps` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `checkout_id` VARCHAR(255) NOT NULL,
  `product_id` VARCHAR(255) NOT NULL,
  `created_at` datetime,
  `updated_at` datetime,
  FOREIGN KEY(`checkout_id`) references `checkouts`(`id`) on delete cascade on update cascade,
  FOREIGN KEY(`product_id`) references `products`(`id`) on delete cascade on update cascade,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `order_bumps`: vazia


-- Estrutura da tabela `acquirers`
DROP TABLE IF EXISTS `acquirers`;
CREATE TABLE `acquirers` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255),
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `api_status` VARCHAR(255) CHECK (`api_status` IN ('online', 'offline', 'error', 'checking')) NOT NULL DEFAULT 'offline',
  `credentials` TEXT,
  `settings` TEXT,
  `logo_url` VARCHAR(255),
  `created_at` datetime,
  `updated_at` datetime,
  `gateway_fee_percentage` DECIMAL(10,2) NOT NULL default '2.99',
  `fixed_fee` DECIMAL(10,2) NOT NULL DEFAULT '0',
  `percentage_fee` DECIMAL(10,2) NOT NULL DEFAULT '0',
  `withdrawal_fee` DECIMAL(10,2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `acquirers`
LOCK TABLES `acquirers` WRITE;
/*!40000 ALTER TABLE `acquirers` DISABLE KEYS */;
INSERT INTO `acquirers` (`id`, `name`, `slug`, `description`, `is_active`, `api_status`, `credentials`, `settings`, `logo_url`, `created_at`, `updated_at`, `gateway_fee_percentage`, `fixed_fee`, `percentage_fee`, `withdrawal_fee`) VALUES
(1, 'Liberpay', 'liberpay', 'Adquirente de pagamento Liberpay', 0, 'online', '{\"chave_publica\":\"pk_b1oml4xC0wS9WB8BinrnxrFX_mr5ZuV0xn9-5GmupZUdDN5P\",\"chave_privada\":\"sk_V3x0bNVpnraBlY_kJxa9E9-pAVDgGCcXuiOSFnzn3K9L9cZi\",\"chave_saque_externo\":\"wk_qmaqxwFI3RgDKqI77B27XQPUZYamjg4lxeSpb74LMoP-OgaL\"}', '[]', NULL, '2025-11-09 16:45:03', '2025-11-09 21:34:47', 2.99, 1, 0, 0),
(2, 'FullPix', 'fullpix', 'Adquirente de pagamento FullPix', 1, 'online', '{\"secret_key\":\"sk_live_smT4HbAbBoPch2ScVjgpeZ0RtBhRGbXkILX2F0FBpHaQZJXX\",\"public_key\":\"559840a8-559b-4a25-8861-a4b4523a5030\"}', '[]', NULL, '2025-11-09 20:37:26', '2025-11-09 22:46:44', 0, 0.5, 0, 0.5);
/*!40000 ALTER TABLE `acquirers` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `liberpay_sales`
DROP TABLE IF EXISTS `liberpay_sales`;
CREATE TABLE `liberpay_sales` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `user_id` INT NOT NULL,
  `liberpay_sale_id` VARCHAR(255) NOT NULL,
  `reference_code` VARCHAR(255),
  `external_reference` VARCHAR(255),
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(255) NOT NULL DEFAULT 'BRL',
  `status` VARCHAR(255) CHECK (`status` IN ('pending', 'paid', 'expired', 'cancelled', 'refunded')) NOT NULL DEFAULT 'pending',
  `pix_qr_code` TEXT,
  `pix_qr_code_image` TEXT,
  `expires_at` datetime,
  `paid_at` datetime,
  `metadata` TEXT,
  `liberpay_response` TEXT,
  `created_at` datetime,
  `updated_at` datetime,
  FOREIGN KEY(`user_id`) references `users`(`id`) on delete cascade,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `liberpay_sales`: vazia


-- Estrutura da tabela `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255),
  `email_verified_at` datetime,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(255),
  `balance` DECIMAL(10,2) NOT NULL DEFAULT '0',
  `volume_transacionado` DECIMAL(10,2) NOT NULL DEFAULT '0',
  `approved_deposits` DECIMAL(10,2) NOT NULL DEFAULT '0',
  `approved_deposits_net` DECIMAL(10,2) NOT NULL DEFAULT '0',
  `profit_for_platform` DECIMAL(10,2) NOT NULL DEFAULT '0',
  `value_paid_in_taxes` DECIMAL(10,2) NOT NULL DEFAULT '0',
  `withdraw_amount` DECIMAL(10,2) NOT NULL DEFAULT '0',
  `deposit_amount` DECIMAL(10,2) NOT NULL DEFAULT '0',
  `average_monthly_income` DECIMAL(10,2) NOT NULL DEFAULT '0',
  `person_type` VARCHAR(255),
  `full_name` VARCHAR(255),
  `document` VARCHAR(255),
  `average_revenue` VARCHAR(255),
  `average_ticket` VARCHAR(255),
  `products` VARCHAR(255),
  `social_reason` VARCHAR(255),
  `social_contract` VARCHAR(255),
  `rg_cnh_frente` VARCHAR(255),
  `rg_cnh_verso` VARCHAR(255),
  `selfie` VARCHAR(255),
  `role` VARCHAR(255) NOT NULL DEFAULT 'user',
  `status` VARCHAR(255) NOT NULL DEFAULT 'recent_user',
  `remember_token` VARCHAR(255),
  `created_at` datetime,
  `updated_at` datetime,
  `is_sample` tinyint(1) NOT NULL DEFAULT '0',
  `acquirer_id` INT,
  `cash_in_percentage` DECIMAL(10,2),
  `cash_in_fixed` DECIMAL(10,2),
  `cash_out_percentage` DECIMAL(10,2),
  `cash_out_fixed` DECIMAL(10,2),
  `preferred_acquirer` VARCHAR(255),
  FOREIGN KEY(`acquirer_id`) references `acquirers`(`id`) on delete set null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `users`
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `name`, `email`, `avatar`, `email_verified_at`, `password`, `phone`, `balance`, `volume_transacionado`, `approved_deposits`, `approved_deposits_net`, `profit_for_platform`, `value_paid_in_taxes`, `withdraw_amount`, `deposit_amount`, `average_monthly_income`, `person_type`, `full_name`, `document`, `average_revenue`, `average_ticket`, `products`, `social_reason`, `social_contract`, `rg_cnh_frente`, `rg_cnh_verso`, `selfie`, `role`, `status`, `remember_token`, `created_at`, `updated_at`, `is_sample`, `acquirer_id`, `cash_in_percentage`, `cash_in_fixed`, `cash_out_percentage`, `cash_out_fixed`, `preferred_acquirer`) VALUES
(6, 'Admin', 'admin12@gmail.com', 'avatars/bDVRW8uzisvrK4biHE7YWIBOHNGDqRSRU6VimcAO.jpg', '2025-11-09 16:39:32', '$2y$12$4.I7HCnwiKcJjWXZgbsQm.qt/03gcrAGHVcoWBCLXoOmwUJGNYQhy', NULL, 0, 0, 0, 0, 13.5, 0, 41.88, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'admin', 'active', NULL, '2025-11-09 16:39:32', '2025-11-11 06:35:45', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'CLAILTON DIOGENS DA SILVA', 'dandan155@gmail.com', NULL, NULL, '$2y$12$VvlIN1GmIb0JfS.kYYzFhOw8xwfYRWTwQSyIk8Z7RwTFRhCe5okOO', '(19) 99749-6599', 0, 0, 0, 0, 0, 0, 0, 0, 0, 'pf', 'CLAILTON DIOGENS DA SILVA', '426.482.702-72', '10000', '5000', 'Roupas, Calçados, Acessórios', NULL, NULL, 'rg_cnh_frente/ITyuteGgFDbJJwPfl6tLmvquGPYPRyybndU098Gh.png', 'rg_cnh_verso/ur6UMxVXyDtcorzWWgtEMueS97r2VQ9zqW7aQeLi.jpg', 'selfie/2GuBBag4bXxa3kwj41mNmrkXbKhnOCWfF46p5qEe.jpg', 'user', 'active', NULL, '2025-11-09 17:40:11', '2025-11-11 04:34:22', 0, NULL, NULL, NULL, NULL, NULL, 'fullpix'),
(10, 'FERNANDO ANTONIO JAMBO MUNIZ FALCAO', 'tapaz@gmail.com', NULL, NULL, '$2y$12$XOM6c5J71zoyVpMNJ3AGVOu86jHqH8fV9EXfldDgnescdCr.jLbwy', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'user', 'recent_user', NULL, '2025-11-11 02:29:33', '2025-11-11 02:29:33', 0, NULL, NULL, NULL, NULL, NULL, NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `system_settings`
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `key` VARCHAR(255) NOT NULL,
  `value` TEXT,
  `type` VARCHAR(255) NOT NULL DEFAULT 'string',
  `description` TEXT,
  `created_at` datetime,
  `updated_at` datetime,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `system_settings`
LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` (`id`, `key`, `value`, `type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'gateway_pix_percentage', '2.99', 'decimal', 'Taxa percentual PIX (%)', '2025-11-09 16:34:39', '2025-11-10 02:23:23'),
(2, 'gateway_pix_fixed', '1.99', 'decimal', 'Taxa fixa PIX (R$)', '2025-11-09 16:34:39', '2025-11-10 02:23:23'),
(3, 'payment_method_pix', '1', 'boolean', 'Método de pagamento PIX ativo', '2025-11-09 16:34:39', '2025-11-09 21:02:54'),
(4, 'payment_method_credit_card', '1', 'boolean', 'Método de pagamento Cartão de Crédito ativo', '2025-11-09 16:34:39', '2025-11-09 21:02:54'),
(5, 'payment_method_boleto', '1', 'boolean', 'Método de pagamento Boleto ativo', '2025-11-09 16:34:39', '2025-11-09 21:02:54'),
(6, 'min_withdraw', '10', 'decimal', 'Valor mÃ­nimo para saque', '2025-11-09 22:45:40', '2025-11-10 04:53:59'),
(7, 'fixed_withdraw_fee', '5', 'decimal', 'Taxa fixa de saque (R$)', '2025-11-09 22:45:40', '2025-11-10 02:30:17'),
(8, 'percent_withdraw_fee', '0', 'decimal', 'Taxa percentual de saque (%)', '2025-11-09 22:45:40', '2025-11-09 22:45:40'),
(9, 'transfer_mode', 'automatico', 'string', 'Modo de transferência (manual/automatico)', '2025-11-09 22:45:40', '2025-11-09 23:35:50');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `fullpix_sales`
DROP TABLE IF EXISTS `fullpix_sales`;
CREATE TABLE `fullpix_sales` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `user_id` INT NOT NULL,
  `fullpix_transaction_id` VARCHAR(255) NOT NULL,
  `reference_code` VARCHAR(255),
  `external_reference` VARCHAR(255),
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(255) NOT NULL DEFAULT 'BRL',
  `status` VARCHAR(255) CHECK (`status` IN ('pending', 'waiting_payment', 'paid', 'refused', 'cancelled', 'refunded', 'expired')) NOT NULL DEFAULT 'waiting_payment',
  `pix_qrcode` TEXT,
  `pix_qrcode_image` TEXT,
  `expires_at` datetime,
  `paid_at` datetime,
  `metadata` TEXT,
  `fullpix_response` TEXT,
  `created_at` datetime,
  `updated_at` datetime,
  FOREIGN KEY(`user_id`) references `users`(`id`) on delete cascade,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `fullpix_sales`
LOCK TABLES `fullpix_sales` WRITE;
/*!40000 ALTER TABLE `fullpix_sales` DISABLE KEYS */;
INSERT INTO `fullpix_sales` (`id`, `user_id`, `fullpix_transaction_id`, `reference_code`, `external_reference`, `amount`, `currency`, `status`, `pix_qrcode`, `pix_qrcode_image`, `expires_at`, `paid_at`, `metadata`, `fullpix_response`, `created_at`, `updated_at`) VALUES
(7, 6, 'fcb9bc2c-a950-4537-b51f-871e82f3d92c', 'fcb9bc2c-a950-4537-b51f-871e82f3d92c', NULL, 5.5, 'BRL', 'paid', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/441602a9-94d2-492d-bc46-27585912d6f25204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***630435DF', NULL, '2025-11-12 00:00:00', '2025-11-10 00:52:31', '{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"user\":{\"id\":6,\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"avatar\":null,\"email_verified_at\":\"2025-11-09T16:39:32.000000Z\",\"phone\":null,\"balance\":0,\"volume_transacionado\":0,\"approved_deposits\":0,\"approved_deposits_net\":0,\"profit_for_platform\":0,\"value_paid_in_taxes\":0,\"withdraw_amount\":0,\"deposit_amount\":0,\"average_monthly_income\":0,\"person_type\":null,\"full_name\":null,\"document\":null,\"average_revenue\":null,\"average_ticket\":null,\"products\":null,\"social_reason\":null,\"social_contract\":null,\"rg_cnh_frente\":null,\"rg_cnh_verso\":null,\"selfie\":null,\"role\":\"admin\",\"status\":\"active\",\"created_at\":\"2025-11-09T16:39:32.000000Z\",\"updated_at\":\"2025-11-10T00:48:45.000000Z\",\"is_sample\":0,\"acquirer_id\":null,\"cash_in_percentage\":null,\"cash_in_fixed\":null,\"cash_out_percentage\":null,\"cash_out_fixed\":null,\"preferred_acquirer\":null},\"product_id\":null,\"checkout_product_id\":null}', '{\"id\":\"fcb9bc2c-a950-4537-b51f-871e82f3d92c\",\"amount\":550,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"product_id\":null,\"checkout_product_id\":null},\"traceable\":true,\"createdAt\":\"2025-11-09T21:50:27-03:00\",\"updatedAt\":\"2025-11-09T21:50:27-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"444b0ee5-6b12-4ee3-8609-c9d7d29ed6e0\",\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-09T21:50:26.80731\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/441602a9-94d2-492d-bc46-27585912d6f25204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***630435DF\",\"expirationDate\":\"2025-11-12T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Dep\\u00f3sito de R$ 5,50 - Usu\\u00e1rio #6\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":500}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":500}}', '2025-11-10 00:50:28', '2025-11-10 00:52:31'),
(8, 6, 'd6bd5124-77d4-4202-ab2d-8ded007a85e1', 'd6bd5124-77d4-4202-ab2d-8ded007a85e1', NULL, 1, 'BRL', 'paid', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/8a1a4442-ca90-4f9c-82ac-fbcad45a99cc5204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***63046EB4', NULL, '2025-11-12 00:00:00', '2025-11-10 01:08:31', '{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"user\":{\"id\":6,\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"avatar\":null,\"email_verified_at\":\"2025-11-09T16:39:32.000000Z\",\"phone\":null,\"balance\":5,\"volume_transacionado\":0,\"approved_deposits\":0,\"approved_deposits_net\":0,\"profit_for_platform\":0,\"value_paid_in_taxes\":0,\"withdraw_amount\":0,\"deposit_amount\":0,\"average_monthly_income\":0,\"person_type\":null,\"full_name\":null,\"document\":null,\"average_revenue\":null,\"average_ticket\":null,\"products\":null,\"social_reason\":null,\"social_contract\":null,\"rg_cnh_frente\":null,\"rg_cnh_verso\":null,\"selfie\":null,\"role\":\"admin\",\"status\":\"active\",\"created_at\":\"2025-11-09T16:39:32.000000Z\",\"updated_at\":\"2025-11-10T00:59:56.000000Z\",\"is_sample\":0,\"acquirer_id\":null,\"cash_in_percentage\":null,\"cash_in_fixed\":null,\"cash_out_percentage\":null,\"cash_out_fixed\":null,\"preferred_acquirer\":null},\"product_id\":null,\"checkout_product_id\":null}', '{\"id\":\"d6bd5124-77d4-4202-ab2d-8ded007a85e1\",\"amount\":100,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"product_id\":null,\"checkout_product_id\":null},\"traceable\":true,\"createdAt\":\"2025-11-09T22:07:19-03:00\",\"updatedAt\":\"2025-11-09T22:07:19-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"0792a511-56db-477d-a8d0-69acf3e6feef\",\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-09T22:07:18.814383\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/8a1a4442-ca90-4f9c-82ac-fbcad45a99cc5204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***63046EB4\",\"expirationDate\":\"2025-11-12T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Dep\\u00f3sito de R$ 1,00 - Usu\\u00e1rio #6\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":50}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":50}}', '2025-11-10 01:07:20', '2025-11-10 01:08:32'),
(9, 6, 'aa19e5c3-27c9-4049-aa01-166a553a3b1a', 'aa19e5c3-27c9-4049-aa01-166a553a3b1a', NULL, 6, 'BRL', 'paid', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/093f11d5-4313-46fa-bc6b-e8e5da2ee11f5204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***63049530', NULL, '2025-11-12 00:00:00', '2025-11-10 01:37:26', '{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"user\":{\"id\":6,\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"avatar\":null,\"email_verified_at\":\"2025-11-09T16:39:32.000000Z\",\"phone\":null,\"balance\":0,\"volume_transacionado\":0,\"approved_deposits\":0,\"approved_deposits_net\":0,\"profit_for_platform\":0,\"value_paid_in_taxes\":0,\"withdraw_amount\":0,\"deposit_amount\":0,\"average_monthly_income\":0,\"person_type\":null,\"full_name\":null,\"document\":null,\"average_revenue\":null,\"average_ticket\":null,\"products\":null,\"social_reason\":null,\"social_contract\":null,\"rg_cnh_frente\":null,\"rg_cnh_verso\":null,\"selfie\":null,\"role\":\"admin\",\"status\":\"active\",\"created_at\":\"2025-11-09T16:39:32.000000Z\",\"updated_at\":\"2025-11-10T01:13:02.000000Z\",\"is_sample\":0,\"acquirer_id\":null,\"cash_in_percentage\":null,\"cash_in_fixed\":null,\"cash_out_percentage\":null,\"cash_out_fixed\":null,\"preferred_acquirer\":null},\"product_id\":null,\"checkout_product_id\":null}', '{\"id\":\"aa19e5c3-27c9-4049-aa01-166a553a3b1a\",\"amount\":600,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"product_id\":null,\"checkout_product_id\":null},\"traceable\":true,\"createdAt\":\"2025-11-09T22:28:37-03:00\",\"updatedAt\":\"2025-11-09T22:28:37-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"2bb942a3-593d-4af2-9e59-e592ee93fb62\",\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-09T22:28:37.324101\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/093f11d5-4313-46fa-bc6b-e8e5da2ee11f5204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***63049530\",\"expirationDate\":\"2025-11-12T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Dep\\u00f3sito de R$ 6,00 - Usu\\u00e1rio #6\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":550}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":550}}', '2025-11-10 01:28:38', '2025-11-10 01:37:26'),
(10, 6, '384e71a2-ab1a-4d94-a5d1-c0b60ddf48ff', '384e71a2-ab1a-4d94-a5d1-c0b60ddf48ff', NULL, 1.5, 'BRL', 'paid', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/0bd79b8f-e990-493f-85bb-1bebbe6138025204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304E93F', NULL, '2025-11-12 00:00:00', '2025-11-10 01:42:38', '{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"user\":{\"id\":6,\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"avatar\":null,\"email_verified_at\":\"2025-11-09T16:39:32.000000Z\",\"phone\":null,\"balance\":5,\"volume_transacionado\":0,\"approved_deposits\":0,\"approved_deposits_net\":0,\"profit_for_platform\":0,\"value_paid_in_taxes\":0,\"withdraw_amount\":0,\"deposit_amount\":0,\"average_monthly_income\":0,\"person_type\":null,\"full_name\":null,\"document\":null,\"average_revenue\":null,\"average_ticket\":null,\"products\":null,\"social_reason\":null,\"social_contract\":null,\"rg_cnh_frente\":null,\"rg_cnh_verso\":null,\"selfie\":null,\"role\":\"admin\",\"status\":\"active\",\"created_at\":\"2025-11-09T16:39:32.000000Z\",\"updated_at\":\"2025-11-10T01:37:26.000000Z\",\"is_sample\":0,\"acquirer_id\":null,\"cash_in_percentage\":null,\"cash_in_fixed\":null,\"cash_out_percentage\":null,\"cash_out_fixed\":null,\"preferred_acquirer\":null},\"product_id\":null,\"checkout_product_id\":null}', '{\"id\":\"384e71a2-ab1a-4d94-a5d1-c0b60ddf48ff\",\"amount\":150,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"product_id\":null,\"checkout_product_id\":null},\"traceable\":true,\"createdAt\":\"2025-11-09T22:42:25-03:00\",\"updatedAt\":\"2025-11-09T22:42:25-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"2346e0ad-0960-4588-b657-5d49256596a5\",\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-09T22:42:25.107304\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/0bd79b8f-e990-493f-85bb-1bebbe6138025204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304E93F\",\"expirationDate\":\"2025-11-12T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Dep\\u00f3sito de R$ 1,50 - Usu\\u00e1rio #6\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":100}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":100}}', '2025-11-10 01:42:26', '2025-11-10 01:42:38'),
(11, 6, '548e6648-d074-4bc5-af93-42a1b1fda155', '548e6648-d074-4bc5-af93-42a1b1fda155', NULL, 10, 'BRL', 'paid', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/d292fdba-3a68-4ecd-96e9-c33660e18d275204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***63047422', NULL, '2025-11-12 00:00:00', '2025-11-10 02:28:18', '{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"user\":{\"id\":6,\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"avatar\":null,\"email_verified_at\":\"2025-11-09T16:39:32.000000Z\",\"phone\":null,\"balance\":5.5,\"volume_transacionado\":0,\"approved_deposits\":0,\"approved_deposits_net\":0,\"profit_for_platform\":0,\"value_paid_in_taxes\":0,\"withdraw_amount\":0,\"deposit_amount\":0,\"average_monthly_income\":0,\"person_type\":null,\"full_name\":null,\"document\":null,\"average_revenue\":null,\"average_ticket\":null,\"products\":null,\"social_reason\":null,\"social_contract\":null,\"rg_cnh_frente\":null,\"rg_cnh_verso\":null,\"selfie\":null,\"role\":\"admin\",\"status\":\"active\",\"created_at\":\"2025-11-09T16:39:32.000000Z\",\"updated_at\":\"2025-11-10T01:42:38.000000Z\",\"is_sample\":0,\"acquirer_id\":null,\"cash_in_percentage\":null,\"cash_in_fixed\":null,\"cash_out_percentage\":null,\"cash_out_fixed\":null,\"preferred_acquirer\":null},\"product_id\":null,\"checkout_product_id\":null}', '{\"id\":\"548e6648-d074-4bc5-af93-42a1b1fda155\",\"amount\":1000,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"product_id\":null,\"checkout_product_id\":null},\"traceable\":true,\"createdAt\":\"2025-11-09T23:27:10-03:00\",\"updatedAt\":\"2025-11-09T23:27:10-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"5d469bd9-ed11-45bf-8ee6-a282b9707e07\",\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-09T23:27:09.891074\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/d292fdba-3a68-4ecd-96e9-c33660e18d275204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***63047422\",\"expirationDate\":\"2025-11-12T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Dep\\u00f3sito de R$ 10,00 - Usu\\u00e1rio #6\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":950}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":950}}', '2025-11-10 02:27:15', '2025-11-10 02:28:18'),
(12, 6, '3f388c68-8eeb-4962-b898-45339595f174', '3f388c68-8eeb-4962-b898-45339595f174', NULL, 10, 'BRL', 'paid', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/402eb246-7771-4ece-8366-15b5d732ff8c5204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304D397', NULL, '2025-11-12 00:00:00', '2025-11-10 04:35:52', '{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"user\":{\"id\":6,\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"avatar\":null,\"email_verified_at\":\"2025-11-09T16:39:32.000000Z\",\"phone\":null,\"balance\":0,\"volume_transacionado\":0,\"approved_deposits\":0,\"approved_deposits_net\":0,\"profit_for_platform\":9,\"value_paid_in_taxes\":0,\"withdraw_amount\":26.44,\"deposit_amount\":0,\"average_monthly_income\":0,\"person_type\":null,\"full_name\":null,\"document\":null,\"average_revenue\":null,\"average_ticket\":null,\"products\":null,\"social_reason\":null,\"social_contract\":null,\"rg_cnh_frente\":null,\"rg_cnh_verso\":null,\"selfie\":null,\"role\":\"admin\",\"status\":\"active\",\"created_at\":\"2025-11-09T16:39:32.000000Z\",\"updated_at\":\"2025-11-10T04:12:39.000000Z\",\"is_sample\":0,\"acquirer_id\":null,\"cash_in_percentage\":null,\"cash_in_fixed\":null,\"cash_out_percentage\":null,\"cash_out_fixed\":null,\"preferred_acquirer\":null},\"product_id\":null,\"checkout_product_id\":null}', '{\"id\":\"3f388c68-8eeb-4962-b898-45339595f174\",\"amount\":1000,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"product_id\":null,\"checkout_product_id\":null},\"traceable\":true,\"createdAt\":\"2025-11-10T01:35:01-03:00\",\"updatedAt\":\"2025-11-10T01:35:01-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"7f7a4056-2b7c-488e-b988-13878d1588b4\",\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-10T01:35:01.521683\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/402eb246-7771-4ece-8366-15b5d732ff8c5204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304D397\",\"expirationDate\":\"2025-11-12T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Dep\\u00f3sito de R$ 10,00 - Usu\\u00e1rio #6\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":950}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":950}}', '2025-11-10 04:35:02', '2025-11-10 04:35:52'),
(13, 6, 'e5b7ff68-605c-4bae-906c-d48352da1a40', 'e5b7ff68-605c-4bae-906c-d48352da1a40', NULL, 10, 'BRL', 'paid', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/70eae7ad-c0c3-46d8-b3a3-ac7b258086915204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***63045875', NULL, '2025-11-12 00:00:00', '2025-11-10 04:56:14', '{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"user\":{\"id\":6,\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"avatar\":null,\"email_verified_at\":\"2025-11-09T16:39:32.000000Z\",\"phone\":null,\"balance\":7.72,\"volume_transacionado\":0,\"approved_deposits\":0,\"approved_deposits_net\":0,\"profit_for_platform\":9,\"value_paid_in_taxes\":0,\"withdraw_amount\":26.44,\"deposit_amount\":0,\"average_monthly_income\":0,\"person_type\":null,\"full_name\":null,\"document\":null,\"average_revenue\":null,\"average_ticket\":null,\"products\":null,\"social_reason\":null,\"social_contract\":null,\"rg_cnh_frente\":null,\"rg_cnh_verso\":null,\"selfie\":null,\"role\":\"admin\",\"status\":\"active\",\"created_at\":\"2025-11-09T16:39:32.000000Z\",\"updated_at\":\"2025-11-10T04:35:52.000000Z\",\"is_sample\":0,\"acquirer_id\":null,\"cash_in_percentage\":null,\"cash_in_fixed\":null,\"cash_out_percentage\":null,\"cash_out_fixed\":null,\"preferred_acquirer\":null},\"product_id\":null,\"checkout_product_id\":null}', '{\"id\":\"e5b7ff68-605c-4bae-906c-d48352da1a40\",\"amount\":1000,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"product_id\":null,\"checkout_product_id\":null},\"traceable\":true,\"createdAt\":\"2025-11-10T01:55:03-03:00\",\"updatedAt\":\"2025-11-10T01:55:03-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"8ea37a2e-e62d-4a18-b3a4-04364bc6923e\",\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-10T01:55:02.934631\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/70eae7ad-c0c3-46d8-b3a3-ac7b258086915204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***63045875\",\"expirationDate\":\"2025-11-12T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Dep\\u00f3sito de R$ 10,00 - Usu\\u00e1rio #6\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":950}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":950}}', '2025-11-10 04:55:04', '2025-11-10 04:56:14'),
(14, 6, '8f8a91b1-c421-4acc-a593-defb571d96b2', '8f8a91b1-c421-4acc-a593-defb571d96b2', NULL, 349.9, 'BRL', 'waiting_payment', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/2aaf09f4-9a1d-4f82-ae90-efeea3217aaf5204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304DC4C', NULL, '2025-11-12 00:00:00', NULL, '{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"user\":{\"id\":6,\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"avatar\":null,\"email_verified_at\":\"2025-11-09T16:39:32.000000Z\",\"phone\":null,\"balance\":0,\"volume_transacionado\":0,\"approved_deposits\":0,\"approved_deposits_net\":0,\"profit_for_platform\":13.5,\"value_paid_in_taxes\":0,\"withdraw_amount\":41.88,\"deposit_amount\":0,\"average_monthly_income\":0,\"person_type\":null,\"full_name\":null,\"document\":null,\"average_revenue\":null,\"average_ticket\":null,\"products\":null,\"social_reason\":null,\"social_contract\":null,\"rg_cnh_frente\":null,\"rg_cnh_verso\":null,\"selfie\":null,\"role\":\"admin\",\"status\":\"active\",\"created_at\":\"2025-11-09T16:39:32.000000Z\",\"updated_at\":\"2025-11-10T04:56:14.000000Z\",\"is_sample\":0,\"acquirer_id\":null,\"cash_in_percentage\":null,\"cash_in_fixed\":null,\"cash_out_percentage\":null,\"cash_out_fixed\":null,\"preferred_acquirer\":null},\"product_id\":null,\"checkout_product_id\":null}', '{\"id\":\"8f8a91b1-c421-4acc-a593-defb571d96b2\",\"amount\":34990,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"product_id\":null,\"checkout_product_id\":null},\"traceable\":true,\"createdAt\":\"2025-11-10T14:46:38-03:00\",\"updatedAt\":\"2025-11-10T14:46:38-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"eaa065a6-7109-48cc-b1b3-78a1f5aa2d53\",\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-10T14:46:38.052813\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/2aaf09f4-9a1d-4f82-ae90-efeea3217aaf5204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304DC4C\",\"expirationDate\":\"2025-11-12T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Dep\\u00f3sito de R$ 349,90 - Usu\\u00e1rio #6\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":34940}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":34940}}', '2025-11-10 17:46:39', '2025-11-10 17:46:39'),
(15, 6, '6d545486-991b-429f-a656-e294882fac7a', '6d545486-991b-429f-a656-e294882fac7a', NULL, 10.5, 'BRL', 'waiting_payment', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/64224df5-ad66-4557-903b-95fc28c45da05204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***630412F6', NULL, '2025-11-12 00:00:00', NULL, '{\"checkout_id\":\"019a701c-00bb-7373-a9b9-91fdc69bb651\",\"product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"checkout_product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"order_bump_ids\":[],\"form_data\":{\"name\":\"Ana Caroline Da Silva\",\"email\":\"bd457794@gmail.com\",\"phone\":null,\"cpf\":null,\"zip_code\":null,\"address\":null,\"city\":null,\"state\":null,\"number\":null,\"complement\":null},\"user\":{\"id\":null,\"name\":\"Ana Caroline Da Silva\",\"email\":\"bd457794@gmail.com\",\"phone\":null,\"document\":null}}', '{\"id\":\"6d545486-991b-429f-a656-e294882fac7a\",\"amount\":1050,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"checkout_id\":\"019a701c-00bb-7373-a9b9-91fdc69bb651\",\"product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"checkout_product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"order_bump_ids\":[],\"form_data\":{\"name\":\"Ana Caroline Da Silva\",\"email\":\"bd457794@gmail.com\",\"phone\":null,\"cpf\":null,\"zip_code\":null,\"address\":null,\"city\":null,\"state\":null,\"number\":null,\"complement\":null}},\"traceable\":true,\"createdAt\":\"2025-11-10T20:34:44-03:00\",\"updatedAt\":\"2025-11-10T20:34:44-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"2c06e44d-8549-4fd8-926a-adc196ad73ae\",\"name\":\"Ana Caroline Da Silva\",\"email\":\"bd457794@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-10T20:34:43.754209\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/64224df5-ad66-4557-903b-95fc28c45da05204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***630412F6\",\"expirationDate\":\"2025-11-12T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Checkout: Tropa galera\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":1000}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":1000}}', '2025-11-10 23:34:45', '2025-11-10 23:34:45'),
(16, 6, '910d27b0-843d-461a-bfe9-01e60e0c5e3c', '910d27b0-843d-461a-bfe9-01e60e0c5e3c', NULL, 10.5, 'BRL', 'waiting_payment', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/d8e80d98-d00f-43f0-8d84-809bd15ac5d45204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***630463B6', NULL, '2025-11-12 00:00:00', NULL, '{\"checkout_id\":\"019a701c-00bb-7373-a9b9-91fdc69bb651\",\"product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"checkout_product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"order_bump_ids\":[],\"form_data\":{\"name\":\"Ana Caroline Da Silva\",\"email\":\"bd457794@gmail.com\",\"phone\":null,\"cpf\":null,\"zip_code\":null,\"address\":null,\"city\":null,\"state\":null,\"number\":null,\"complement\":null},\"user\":{\"id\":null,\"name\":\"Ana Caroline Da Silva\",\"email\":\"bd457794@gmail.com\",\"phone\":null,\"document\":null}}', '{\"id\":\"910d27b0-843d-461a-bfe9-01e60e0c5e3c\",\"amount\":1050,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"checkout_id\":\"019a701c-00bb-7373-a9b9-91fdc69bb651\",\"product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"checkout_product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"order_bump_ids\":[],\"form_data\":{\"name\":\"Ana Caroline Da Silva\",\"email\":\"bd457794@gmail.com\",\"phone\":null,\"cpf\":null,\"zip_code\":null,\"address\":null,\"city\":null,\"state\":null,\"number\":null,\"complement\":null}},\"traceable\":true,\"createdAt\":\"2025-11-10T20:43:38-03:00\",\"updatedAt\":\"2025-11-10T20:43:38-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"ed70abe5-3813-41c2-a2a0-970a09a2ccd3\",\"name\":\"Ana Caroline Da Silva\",\"email\":\"bd457794@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-10T20:43:38.118488\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/d8e80d98-d00f-43f0-8d84-809bd15ac5d45204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***630463B6\",\"expirationDate\":\"2025-11-12T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Checkout: Tropa galera\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":1000}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":1000}}', '2025-11-10 23:43:39', '2025-11-10 23:43:39'),
(17, 6, '7cf693a7-fe49-408b-92c7-4a4499beb7b6', '7cf693a7-fe49-408b-92c7-4a4499beb7b6', NULL, 10.5, 'BRL', 'waiting_payment', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/ef3c6714-406c-4c1d-835c-85fef8e4e2925204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304C4A2', NULL, '2025-11-12 00:00:00', NULL, '{\"checkout_id\":\"019a701c-00bb-7373-a9b9-91fdc69bb651\",\"product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"checkout_product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"order_bump_ids\":[],\"form_data\":{\"name\":\"Bruninho\",\"email\":\"tapaz@gmail.com\",\"phone\":null,\"cpf\":null,\"zip_code\":null,\"address\":null,\"city\":null,\"state\":null,\"number\":null,\"complement\":null},\"user\":{\"id\":null,\"name\":\"Bruninho\",\"email\":\"tapaz@gmail.com\",\"phone\":null,\"document\":null}}', '{\"id\":\"7cf693a7-fe49-408b-92c7-4a4499beb7b6\",\"amount\":1050,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"checkout_id\":\"019a701c-00bb-7373-a9b9-91fdc69bb651\",\"product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"checkout_product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"order_bump_ids\":[],\"form_data\":{\"name\":\"Bruninho\",\"email\":\"tapaz@gmail.com\",\"phone\":null,\"cpf\":null,\"zip_code\":null,\"address\":null,\"city\":null,\"state\":null,\"number\":null,\"complement\":null}},\"traceable\":true,\"createdAt\":\"2025-11-10T20:48:58-03:00\",\"updatedAt\":\"2025-11-10T20:48:58-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"5be75dc6-4b67-4c44-a8dc-710ce60d774a\",\"name\":\"Bruninho\",\"email\":\"tapaz@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-10T20:48:58.002351\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/ef3c6714-406c-4c1d-835c-85fef8e4e2925204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304C4A2\",\"expirationDate\":\"2025-11-12T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Checkout: Tropa galera\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":1000}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":1000}}', '2025-11-10 23:49:00', '2025-11-10 23:49:00'),
(18, 6, '1a585524-3249-4d5d-b848-b0fdaacad384', '1a585524-3249-4d5d-b848-b0fdaacad384', NULL, 10.5, 'BRL', 'waiting_payment', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/4f9d0f6f-50d6-4740-af76-20a87131e3c55204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***63044170', NULL, '2025-11-13 00:00:00', NULL, '{\"checkout_id\":\"019a7046-265a-7389-9c3f-f515c9bc9a95\",\"product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"checkout_product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"order_bump_ids\":[],\"form_data\":{\"name\":\"Bruninho\",\"email\":\"tapaz@gmail.com\",\"phone\":\"1999746599\",\"cpf\":\"18233848700\",\"zip_code\":\"21832345\",\"address\":\"Rua Rit\\u00e1polis\",\"city\":\"Rio de Janeiro\",\"state\":\"RJ\",\"number\":\"6\",\"complement\":null},\"user\":{\"id\":null,\"name\":\"Bruninho\",\"email\":\"tapaz@gmail.com\",\"phone\":\"1999746599\",\"document\":\"18233848700\"}}', '{\"id\":\"1a585524-3249-4d5d-b848-b0fdaacad384\",\"amount\":1050,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"checkout_id\":\"019a7046-265a-7389-9c3f-f515c9bc9a95\",\"product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"checkout_product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"order_bump_ids\":[],\"form_data\":{\"name\":\"Bruninho\",\"email\":\"tapaz@gmail.com\",\"phone\":\"1999746599\",\"cpf\":\"18233848700\",\"zip_code\":\"21832345\",\"address\":\"Rua Rit\\u00e1polis\",\"city\":\"Rio de Janeiro\",\"state\":\"RJ\",\"number\":\"6\",\"complement\":null}},\"traceable\":true,\"createdAt\":\"2025-11-10T21:19:11-03:00\",\"updatedAt\":\"2025-11-10T21:19:11-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"1981ad01-d788-499f-af5d-4cc4abbb50c7\",\"name\":\"Bruninho\",\"email\":\"tapaz@gmail.com\",\"phone\":\"1999746599\",\"birthdate\":null,\"createdAt\":\"2025-11-10T21:19:11.410594\",\"document\":{\"number\":\"18233848700\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/4f9d0f6f-50d6-4740-af76-20a87131e3c55204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***63044170\",\"expirationDate\":\"2025-11-13T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Checkout: Tropa galera\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":1000}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":1000}}', '2025-11-11 00:19:13', '2025-11-11 00:19:13'),
(19, 6, 'e842a0c1-519b-40a8-a2c7-0b962f7f9f28', 'e842a0c1-519b-40a8-a2c7-0b962f7f9f28', NULL, 349, 'BRL', 'waiting_payment', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/d71a9f40-0b5e-4107-b36a-8a2e3b33016c5204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304A67A', NULL, '2025-11-13 00:00:00', NULL, '{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"user\":{\"id\":6,\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"avatar\":null,\"email_verified_at\":\"2025-11-09T16:39:32.000000Z\",\"phone\":null,\"balance\":0,\"volume_transacionado\":0,\"approved_deposits\":0,\"approved_deposits_net\":0,\"profit_for_platform\":13.5,\"value_paid_in_taxes\":0,\"withdraw_amount\":41.88,\"deposit_amount\":0,\"average_monthly_income\":0,\"person_type\":null,\"full_name\":null,\"document\":null,\"average_revenue\":null,\"average_ticket\":null,\"products\":null,\"social_reason\":null,\"social_contract\":null,\"rg_cnh_frente\":null,\"rg_cnh_verso\":null,\"selfie\":null,\"role\":\"admin\",\"status\":\"active\",\"created_at\":\"2025-11-09T16:39:32.000000Z\",\"updated_at\":\"2025-11-10T04:56:14.000000Z\",\"is_sample\":0,\"acquirer_id\":null,\"cash_in_percentage\":null,\"cash_in_fixed\":null,\"cash_out_percentage\":null,\"cash_out_fixed\":null,\"preferred_acquirer\":null},\"product_id\":null,\"checkout_product_id\":null}', '{\"id\":\"e842a0c1-519b-40a8-a2c7-0b962f7f9f28\",\"amount\":34900,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"product_id\":null,\"checkout_product_id\":null},\"traceable\":true,\"createdAt\":\"2025-11-10T21:28:46-03:00\",\"updatedAt\":\"2025-11-10T21:28:46-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"591f805b-2b69-4b85-ab41-ec4831b77205\",\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-10T21:28:46.503702\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/d71a9f40-0b5e-4107-b36a-8a2e3b33016c5204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304A67A\",\"expirationDate\":\"2025-11-13T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Dep\\u00f3sito de R$ 349,00 - Usu\\u00e1rio #6\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":34850}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":34850}}', '2025-11-11 00:28:48', '2025-11-11 00:28:48'),
(20, 6, '48687f22-4a05-48cc-92c0-660d71d17286', '48687f22-4a05-48cc-92c0-660d71d17286', NULL, 340, 'BRL', 'waiting_payment', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/dcc161c6-e3bd-40bc-aec5-4f710da500295204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304EDF2', NULL, '2025-11-13 00:00:00', NULL, '{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"user\":{\"id\":6,\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"avatar\":null,\"email_verified_at\":\"2025-11-09T16:39:32.000000Z\",\"phone\":null,\"balance\":0,\"volume_transacionado\":0,\"approved_deposits\":0,\"approved_deposits_net\":0,\"profit_for_platform\":13.5,\"value_paid_in_taxes\":0,\"withdraw_amount\":41.88,\"deposit_amount\":0,\"average_monthly_income\":0,\"person_type\":null,\"full_name\":null,\"document\":null,\"average_revenue\":null,\"average_ticket\":null,\"products\":null,\"social_reason\":null,\"social_contract\":null,\"rg_cnh_frente\":null,\"rg_cnh_verso\":null,\"selfie\":null,\"role\":\"admin\",\"status\":\"active\",\"created_at\":\"2025-11-09T16:39:32.000000Z\",\"updated_at\":\"2025-11-10T04:56:14.000000Z\",\"is_sample\":0,\"acquirer_id\":null,\"cash_in_percentage\":null,\"cash_in_fixed\":null,\"cash_out_percentage\":null,\"cash_out_fixed\":null,\"preferred_acquirer\":null},\"product_id\":null,\"checkout_product_id\":null}', '{\"id\":\"48687f22-4a05-48cc-92c0-660d71d17286\",\"amount\":34000,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"product_id\":null,\"checkout_product_id\":null},\"traceable\":true,\"createdAt\":\"2025-11-10T21:48:16-03:00\",\"updatedAt\":\"2025-11-10T21:48:16-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"f4452567-a3a5-4ab1-9389-00acce703f03\",\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-10T21:48:16.033901\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/dcc161c6-e3bd-40bc-aec5-4f710da500295204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304EDF2\",\"expirationDate\":\"2025-11-13T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Dep\\u00f3sito de R$ 340,00 - Usu\\u00e1rio #6\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":33950}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":33950}}', '2025-11-11 00:48:17', '2025-11-11 00:48:17'),
(21, 6, '30cb3f3a-2b53-4abe-858a-833b26fde37f', '30cb3f3a-2b53-4abe-858a-833b26fde37f', NULL, 10, 'BRL', 'waiting_payment', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/157111c7-38cd-4569-bb91-ae63819b54595204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***63041654', NULL, '2025-11-13 00:00:00', NULL, '{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"user\":{\"id\":6,\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"avatar\":null,\"email_verified_at\":\"2025-11-09T16:39:32.000000Z\",\"phone\":null,\"balance\":0,\"volume_transacionado\":0,\"approved_deposits\":0,\"approved_deposits_net\":0,\"profit_for_platform\":13.5,\"value_paid_in_taxes\":0,\"withdraw_amount\":41.88,\"deposit_amount\":0,\"average_monthly_income\":0,\"person_type\":null,\"full_name\":null,\"document\":null,\"average_revenue\":null,\"average_ticket\":null,\"products\":null,\"social_reason\":null,\"social_contract\":null,\"rg_cnh_frente\":null,\"rg_cnh_verso\":null,\"selfie\":null,\"role\":\"admin\",\"status\":\"active\",\"created_at\":\"2025-11-09T16:39:32.000000Z\",\"updated_at\":\"2025-11-10T04:56:14.000000Z\",\"is_sample\":0,\"acquirer_id\":null,\"cash_in_percentage\":null,\"cash_in_fixed\":null,\"cash_out_percentage\":null,\"cash_out_fixed\":null,\"preferred_acquirer\":null},\"product_id\":null,\"checkout_product_id\":null}', '{\"id\":\"30cb3f3a-2b53-4abe-858a-833b26fde37f\",\"amount\":1000,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"product_id\":null,\"checkout_product_id\":null},\"traceable\":true,\"createdAt\":\"2025-11-10T22:30:21-03:00\",\"updatedAt\":\"2025-11-10T22:30:21-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"37443f0b-e696-49a3-8a87-d5fd0de5c47e\",\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-10T22:30:21.05662\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/157111c7-38cd-4569-bb91-ae63819b54595204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***63041654\",\"expirationDate\":\"2025-11-13T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Dep\\u00f3sito de R$ 10,00 - Usu\\u00e1rio #6\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":950}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":950}}', '2025-11-11 01:30:22', '2025-11-11 01:30:22'),
(22, 6, 'e1eae1f9-f561-4349-98f7-5ca04ced75a4', 'e1eae1f9-f561-4349-98f7-5ca04ced75a4', NULL, 10, 'BRL', 'waiting_payment', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/923206e6-3ff0-4f74-9246-e354427acc225204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304A98A', NULL, '2025-11-13 00:00:00', NULL, '{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"user\":{\"id\":6,\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"avatar\":null,\"email_verified_at\":\"2025-11-09T16:39:32.000000Z\",\"phone\":null,\"balance\":0,\"volume_transacionado\":0,\"approved_deposits\":0,\"approved_deposits_net\":0,\"profit_for_platform\":13.5,\"value_paid_in_taxes\":0,\"withdraw_amount\":41.88,\"deposit_amount\":0,\"average_monthly_income\":0,\"person_type\":null,\"full_name\":null,\"document\":null,\"average_revenue\":null,\"average_ticket\":null,\"products\":null,\"social_reason\":null,\"social_contract\":null,\"rg_cnh_frente\":null,\"rg_cnh_verso\":null,\"selfie\":null,\"role\":\"admin\",\"status\":\"active\",\"created_at\":\"2025-11-09T16:39:32.000000Z\",\"updated_at\":\"2025-11-10T04:56:14.000000Z\",\"is_sample\":0,\"acquirer_id\":null,\"cash_in_percentage\":null,\"cash_in_fixed\":null,\"cash_out_percentage\":null,\"cash_out_fixed\":null,\"preferred_acquirer\":null},\"product_id\":null,\"checkout_product_id\":null}', '{\"id\":\"e1eae1f9-f561-4349-98f7-5ca04ced75a4\",\"amount\":1000,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"product_id\":null,\"checkout_product_id\":null},\"traceable\":true,\"createdAt\":\"2025-11-10T22:35:45-03:00\",\"updatedAt\":\"2025-11-10T22:35:45-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"c7160528-4fcf-4349-be67-a129f2766107\",\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-10T22:35:44.941435\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/923206e6-3ff0-4f74-9246-e354427acc225204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304A98A\",\"expirationDate\":\"2025-11-13T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Dep\\u00f3sito de R$ 10,00 - Usu\\u00e1rio #6\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":950}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":950}}', '2025-11-11 01:35:46', '2025-11-11 01:35:46'),
(23, 6, 'eff42367-659b-4541-acac-a816630c4469', 'eff42367-659b-4541-acac-a816630c4469', NULL, 1, 'BRL', 'waiting_payment', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/e8127d5a-cd3c-4f35-987a-09d5b07736765204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***63044CB7', NULL, '2025-11-13 00:00:00', NULL, '{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"user\":{\"id\":6,\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"avatar\":null,\"email_verified_at\":\"2025-11-09T16:39:32.000000Z\",\"phone\":null,\"balance\":0,\"volume_transacionado\":0,\"approved_deposits\":0,\"approved_deposits_net\":0,\"profit_for_platform\":13.5,\"value_paid_in_taxes\":0,\"withdraw_amount\":41.88,\"deposit_amount\":0,\"average_monthly_income\":0,\"person_type\":null,\"full_name\":null,\"document\":null,\"average_revenue\":null,\"average_ticket\":null,\"products\":null,\"social_reason\":null,\"social_contract\":null,\"rg_cnh_frente\":null,\"rg_cnh_verso\":null,\"selfie\":null,\"role\":\"admin\",\"status\":\"active\",\"created_at\":\"2025-11-09T16:39:32.000000Z\",\"updated_at\":\"2025-11-10T04:56:14.000000Z\",\"is_sample\":0,\"acquirer_id\":null,\"cash_in_percentage\":null,\"cash_in_fixed\":null,\"cash_out_percentage\":null,\"cash_out_fixed\":null,\"preferred_acquirer\":null},\"product_id\":null,\"checkout_product_id\":null}', '{\"id\":\"eff42367-659b-4541-acac-a816630c4469\",\"amount\":100,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"product_id\":null,\"checkout_product_id\":null},\"traceable\":true,\"createdAt\":\"2025-11-10T22:44:29-03:00\",\"updatedAt\":\"2025-11-10T22:44:29-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"3ad9d2a1-7f49-434e-a58b-a155ef3d5cca\",\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-10T22:44:29.693811\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/e8127d5a-cd3c-4f35-987a-09d5b07736765204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***63044CB7\",\"expirationDate\":\"2025-11-13T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Dep\\u00f3sito de R$ 1,00 - Usu\\u00e1rio #6\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":50}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":50}}', '2025-11-11 01:44:31', '2025-11-11 01:44:31'),
(24, 6, 'b3778a59-c7a5-463a-a696-fb96139f5a00', 'b3778a59-c7a5-463a-a696-fb96139f5a00', NULL, 10.5, 'BRL', 'waiting_payment', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/e18c6682-4a17-4e98-a61b-9db5c3153e515204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304814B', NULL, '2025-11-13 00:00:00', NULL, '{\"checkout_id\":\"019a7046-265a-7389-9c3f-f515c9bc9a95\",\"product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"checkout_product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"order_bump_ids\":[],\"form_data\":{\"name\":\"Bruninho\",\"email\":\"tapaz@gmail.com\",\"phone\":null,\"cpf\":null,\"zip_code\":null,\"address\":null,\"city\":null,\"state\":null,\"number\":null,\"complement\":null},\"user\":{\"id\":null,\"name\":\"Bruninho\",\"email\":\"tapaz@gmail.com\",\"phone\":null,\"document\":null}}', '{\"id\":\"b3778a59-c7a5-463a-a696-fb96139f5a00\",\"amount\":1050,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"checkout_id\":\"019a7046-265a-7389-9c3f-f515c9bc9a95\",\"product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"checkout_product_id\":\"019a701b-95aa-719a-bcf9-4b918108ffa7\",\"order_bump_ids\":[],\"form_data\":{\"name\":\"Bruninho\",\"email\":\"tapaz@gmail.com\",\"phone\":null,\"cpf\":null,\"zip_code\":null,\"address\":null,\"city\":null,\"state\":null,\"number\":null,\"complement\":null}},\"traceable\":true,\"createdAt\":\"2025-11-11T01:47:33-03:00\",\"updatedAt\":\"2025-11-11T01:47:33-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"5f6c60c8-9be5-4da0-94c2-af1cfee5f34f\",\"name\":\"Bruninho\",\"email\":\"tapaz@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-11T01:47:33.060309\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/e18c6682-4a17-4e98-a61b-9db5c3153e515204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***6304814B\",\"expirationDate\":\"2025-11-13T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Checkout: Tropa galera\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":1000}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":1000}}', '2025-11-11 04:47:34', '2025-11-11 04:47:34'),
(25, 6, '47428486-d2f6-45dc-b908-45464f10980d', '47428486-d2f6-45dc-b908-45464f10980d', NULL, 1, 'BRL', 'paid', '00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br/pix/2043497a-6791-4451-aecd-68b13e1a9a795204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***630407FD', NULL, '2025-11-13 00:00:00', '2025-11-11 04:55:17', '{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"user\":{\"id\":6,\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"avatar\":null,\"email_verified_at\":\"2025-11-09T16:39:32.000000Z\",\"phone\":null,\"balance\":0,\"volume_transacionado\":0,\"approved_deposits\":0,\"approved_deposits_net\":0,\"profit_for_platform\":13.5,\"value_paid_in_taxes\":0,\"withdraw_amount\":41.88,\"deposit_amount\":0,\"average_monthly_income\":0,\"person_type\":null,\"full_name\":null,\"document\":null,\"average_revenue\":null,\"average_ticket\":null,\"products\":null,\"social_reason\":null,\"social_contract\":null,\"rg_cnh_frente\":null,\"rg_cnh_verso\":null,\"selfie\":null,\"role\":\"admin\",\"status\":\"active\",\"created_at\":\"2025-11-09T16:39:32.000000Z\",\"updated_at\":\"2025-11-11T03:00:56.000000Z\",\"is_sample\":0,\"acquirer_id\":null,\"cash_in_percentage\":null,\"cash_in_fixed\":null,\"cash_out_percentage\":null,\"cash_out_fixed\":null,\"preferred_acquirer\":null},\"product_id\":null,\"checkout_product_id\":null}', '{\"id\":\"47428486-d2f6-45dc-b908-45464f10980d\",\"amount\":100,\"refundedAmount\":0,\"companyId\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"installments\":1,\"paymentMethod\":\"PIX\",\"status\":\"waiting_payment\",\"postbackUrl\":\"http:\\/\\/localhost\\/webhook\\/fullpix\",\"metadata\":{\"user_id\":6,\"user_email\":\"admin12@gmail.com\",\"product_id\":null,\"checkout_product_id\":null},\"traceable\":true,\"createdAt\":\"2025-11-11T01:54:51-03:00\",\"updatedAt\":\"2025-11-11T01:54:51-03:00\",\"paidAt\":null,\"ip\":null,\"externalRef\":null,\"customer\":{\"id\":\"558fb2a5-18ba-48fe-9374-93cf13318c9e\",\"name\":\"Admin\",\"email\":\"admin12@gmail.com\",\"phone\":\"11999999999\",\"birthdate\":null,\"createdAt\":\"2025-11-11T01:54:51.524952\",\"document\":{\"number\":\"12345678901\",\"type\":\"CPF\"},\"address\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"}},\"card\":null,\"boleto\":null,\"pix\":{\"qrcode\":\"00020101021226820014br.gov.bcb.pix2560qrcode.pagsm.com.br\\/pix\\/2043497a-6791-4451-aecd-68b13e1a9a795204000053039865802BR5918PAGAMENTOCERTOLTDA6008SaoPaulo62070503***630407FD\",\"expirationDate\":\"2025-11-13T00:00:00-03:00\",\"end2EndId\":null,\"receiptUrl\":null},\"shipping\":{\"street\":\"Rua X\",\"streetNumber\":\"1\",\"complement\":\"\",\"zipCode\":\"11050100\",\"neighborhood\":\"Centro\",\"city\":\"Santos\",\"state\":\"SP\",\"country\":\"BR\"},\"refusedReason\":null,\"items\":[{\"title\":\"Dep\\u00f3sito de R$ 1,00 - Usu\\u00e1rio #6\",\"quantity\":1,\"tangible\":false}],\"splits\":[{\"recipientId\":\"7154ba9d-4795-4bf0-b56c-fed1ed91cf31\",\"netAmount\":50}],\"fee\":{\"fixedAmount\":0.5,\"spreadPercentage\":0,\"estimatedFee\":50,\"netAmount\":50}}', '2025-11-11 04:54:53', '2025-11-11 04:55:17');
/*!40000 ALTER TABLE `fullpix_sales` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `pix_keys`
DROP TABLE IF EXISTS `pix_keys`;
CREATE TABLE `pix_keys` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `user_id` INT NOT NULL,
  `type` VARCHAR(255) CHECK (`type` IN ('CPF', 'CNPJ', 'EMAIL', 'PHONE', 'EVP')) NOT NULL DEFAULT 'CPF',
  `key` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255),
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime,
  `updated_at` datetime,
  FOREIGN KEY(`user_id`) references `users`(`id`) on delete cascade,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `pix_keys`
LOCK TABLES `pix_keys` WRITE;
/*!40000 ALTER TABLE `pix_keys` DISABLE KEYS */;
INSERT INTO `pix_keys` (`id`, `user_id`, `type`, `key`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 6, 'EMAIL', 'agenciapagamentoseguro@gmail.com', 'Banco Inter', 1, '2025-11-09 23:25:39', '2025-11-09 23:25:39'),
(2, 6, 'EVP', '8637fe7f-b450-4661-a442-ac8903aafe6f', NULL, 1, '2025-11-10 04:42:09', '2025-11-10 04:42:09');
/*!40000 ALTER TABLE `pix_keys` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `withdrawals`
DROP TABLE IF EXISTS `withdrawals`;
CREATE TABLE `withdrawals` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `user_id` INT NOT NULL,
  `fullpix_withdrawal_id` VARCHAR(255),
  `pix_type` VARCHAR(255) CHECK (`pix_type` IN ('CPF', 'CNPJ', 'EMAIL', 'PHONE', 'EVP')) NOT NULL DEFAULT 'CPF',
  `pix_key` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `fee` DECIMAL(10,2) NOT NULL DEFAULT '0',
  `net_amount` DECIMAL(10,2) NOT NULL,
  `status` VARCHAR(255) CHECK (`status` IN ('pending', 'approved', 'processing', 'done', 'done_manual', 'failed', 'refused', 'cancelled')) NOT NULL DEFAULT 'pending',
  `description` TEXT,
  `error_message` TEXT,
  `paid_at` datetime,
  `fullpix_response` TEXT,
  `is_sample` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime,
  `updated_at` datetime,
  `gateway_fee` DECIMAL(10,2) NOT NULL DEFAULT '0',
  FOREIGN KEY(`user_id`) references `users`(`id`) on delete cascade,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `withdrawals`
LOCK TABLES `withdrawals` WRITE;
/*!40000 ALTER TABLE `withdrawals` DISABLE KEYS */;
INSERT INTO `withdrawals` (`id`, `user_id`, `fullpix_withdrawal_id`, `pix_type`, `pix_key`, `amount`, `fee`, `net_amount`, `status`, `description`, `error_message`, `paid_at`, `fullpix_response`, `is_sample`, `created_at`, `updated_at`, `gateway_fee`) VALUES
(4, 6, NULL, 'EMAIL', 'agenciapagamentoseguro@gmail.com', 5, 0.5, 4.5, 'done', 'Saque via LuckPay', 'Erro ao processar saque na adquirente. O saque foi criado como pendente para análise manual.', '2025-11-10 04:19:46', NULL, 0, '2025-11-10 00:56:38', '2025-11-10 00:56:38', 0),
(5, 6, NULL, 'EMAIL', 'agenciapagamentoseguro@gmail.com', 5, 0.5, 4.5, 'done', 'Saque via LuckPay', 'Erro ao processar saque na adquirente. O saque foi criado como pendente para análise manual.', '2025-11-10 04:19:46', NULL, 0, '2025-11-10 00:59:56', '2025-11-10 00:59:56', 0),
(6, 6, '3cd67482-df77-4442-9017-674026c6216e', 'EMAIL', 'agenciapagamentoseguro@gmail.com', 5.5, 0.5, 5, 'done', 'Saque via LuckPay', NULL, '2025-11-10 01:13:11', '{\"event\":\"withdrawal.created\",\"timestamp\":\"09\\/11\\/2025 22:13:11\",\"withdrawal\":{\"id\":\"3cd67482-df77-4442-9017-674026c6216e\",\"company_id\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"requested_amount\":5,\"currency\":\"BRL\",\"status\":\"approved\",\"created_at\":\"2025-11-09T22:13:04.362161\",\"updated_at\":\"2025-11-09T22:13:04.362161\",\"pix\":{\"key_type\":\"EMAIL\",\"key_value\":\"agenciapagamentoseguro@gmail.com\"},\"fee\":0.5,\"net_amount\":5},\"metadata\":{\"source\":\"withdrawals_service\",\"version\":\"1.0.0\"}}', 0, '2025-11-10 01:13:11', '2025-11-10 01:13:11', 0),
(7, 6, NULL, 'EMAIL', 'agenciapagamentoseguro@gmail.com', 13.22, 5, 8.22, 'done', 'Saque via LuckPay', 'Erro ao processar saque na adquirente. O saque foi criado como pendente para análise manual.', '2025-11-10 04:19:46', NULL, 0, '2025-11-10 04:12:39', '2025-11-10 04:12:39', 4.5),
(11, 6, NULL, 'EMAIL', 'agenciapagamentoseguro@gmail.com', 7.72, 5, 2.72, 'cancelled', 'Saque via LuckPay', 'Erro ao processar saque na adquirente. O saque foi criado como pendente para análise manual.', NULL, NULL, 0, '2025-11-10 04:47:46', '2025-11-11 02:05:09', 4.5),
(12, 6, 'a0ab253e-8fd2-4413-b102-69380ad01aa6', 'EVP', '8637fe7f-b450-4661-a442-ac8903aafe6f', 15.44, 5, 10.44, 'done', 'Saque via LuckPay', NULL, '2025-11-10 04:57:13', '{\"event\":\"withdrawal.created\",\"timestamp\":\"10\\/11\\/2025 01:57:13\",\"withdrawal\":{\"id\":\"a0ab253e-8fd2-4413-b102-69380ad01aa6\",\"company_id\":\"559840a8-559b-4a25-8861-a4b4523a5030\",\"requested_amount\":10.44,\"currency\":\"BRL\",\"status\":\"approved\",\"created_at\":\"2025-11-10T01:57:07.332978\",\"updated_at\":\"2025-11-10T01:57:07.332978\",\"pix\":{\"key_type\":\"EVP\",\"key_value\":\"8637fe7f-b450-4661-a442-ac8903aafe6f\"},\"fee\":0.5,\"net_amount\":10.44},\"metadata\":{\"source\":\"withdrawals_service\",\"version\":\"1.0.0\"}}', 0, '2025-11-10 04:57:13', '2025-11-10 04:57:13', 4.5);
/*!40000 ALTER TABLE `withdrawals` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `system_images`
DROP TABLE IF EXISTS `system_images`;
CREATE TABLE `system_images` (
  `id` INT AUTO_INCREMENT,
  `key` TEXT UNIQUE NOT NULL,
  `value` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `system_images`
LOCK TABLES `system_images` WRITE;
/*!40000 ALTER TABLE `system_images` DISABLE KEYS */;
INSERT INTO `system_images` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'banner_promocional', 'system/banner/QHT37BoZF6iS5YoU6sd6iPtlweTvNIa29gB1ymLw.png', '2025-11-10 01:58:48', '2025-11-10 01:58:48');
/*!40000 ALTER TABLE `system_images` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `checkouts`
DROP TABLE IF EXISTS `checkouts`;
CREATE TABLE `checkouts` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` INT NOT NULL,
  `product_id` VARCHAR(255) NOT NULL,
  `discount_percentage` INT NOT NULL DEFAULT '0',
  `layout` VARCHAR(255) NOT NULL DEFAULT 'single',
  `banner` VARCHAR(255),
  `countdown_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `countdown_icon` VARCHAR(255),
  `countdown_duration` INT,
  `countdown_bg_color` VARCHAR(255) NOT NULL DEFAULT '#dc2626',
  `countdown_text_color` VARCHAR(255) NOT NULL DEFAULT '#ffffff',
  `countdown_message` VARCHAR(255),
  `countdown_expired` tinyint(1) NOT NULL DEFAULT '0',
  `button_primary_color` VARCHAR(255) NOT NULL DEFAULT '#10b981',
  `button_secondary_color` VARCHAR(255) NOT NULL DEFAULT '#059669',
  `button_hover_primary_color` VARCHAR(255) NOT NULL DEFAULT '#059669',
  `button_hover_secondary_color` VARCHAR(255) NOT NULL DEFAULT '#047857',
  `form_fields_config` TEXT,
  `form_requirements` TEXT,
  `background_color` VARCHAR(255) NOT NULL DEFAULT '#f8fafc',
  `text_color` VARCHAR(255) NOT NULL DEFAULT '#0f172a',
  `stepped_form_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `steps` TEXT,
  `payment_methods` TEXT,
  `order_bump_bg_color` VARCHAR(255) NOT NULL DEFAULT '#ffffff',
  `order_bump_text_color` VARCHAR(255) NOT NULL DEFAULT '#0f172a',
  `order_bump_border_color` VARCHAR(255) NOT NULL DEFAULT '#fbbf24',
  `order_bump_description` TEXT,
  `order_bump_cta_text` VARCHAR(255) NOT NULL DEFAULT 'Quero comprar também!',
  `order_bump_cta_bg_color` VARCHAR(255) NOT NULL DEFAULT '#10b981',
  `order_bump_cta_text_color` VARCHAR(255) NOT NULL DEFAULT '#ffffff',
  `order_bump_recommended_text` VARCHAR(255) NOT NULL DEFAULT '(Recomendado)',
  `order_bump_recommended_color` VARCHAR(255) NOT NULL DEFAULT '#fbbf24',
  `order_bump_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime,
  `updated_at` datetime,
  FOREIGN KEY(`user_id`) references users(`id`) on delete no action on update no action,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `checkouts`
LOCK TABLES `checkouts` WRITE;
/*!40000 ALTER TABLE `checkouts` DISABLE KEYS */;
INSERT INTO `checkouts` (`id`, `user_id`, `product_id`, `discount_percentage`, `layout`, `banner`, `countdown_enabled`, `countdown_icon`, `countdown_duration`, `countdown_bg_color`, `countdown_text_color`, `countdown_message`, `countdown_expired`, `button_primary_color`, `button_secondary_color`, `button_hover_primary_color`, `button_hover_secondary_color`, `form_fields_config`, `form_requirements`, `background_color`, `text_color`, `stepped_form_enabled`, `steps`, `payment_methods`, `order_bump_bg_color`, `order_bump_text_color`, `order_bump_border_color`, `order_bump_description`, `order_bump_cta_text`, `order_bump_cta_bg_color`, `order_bump_cta_text_color`, `order_bump_recommended_text`, `order_bump_recommended_color`, `order_bump_enabled`, `created_at`, `updated_at`) VALUES
('019a700e-1612-7178-91cb-ced18ae807fb', 6, '019a6fd1-51a4-73d1-8c89-a80dfe89794b', 0, 'single', NULL, 0, '🔥', 3600, '#dc2626', '#ffffff', 'Oferta por tempo limitado!', 0, '#2563eb', '#6b7280', '#1d4ed8', '#4b5563', '{\"name\":{\"required\":true,\"visible\":true,\"order\":1,\"label\":\"Nome completo\"},\"email\":{\"required\":true,\"visible\":true,\"order\":2,\"label\":\"E-mail\"},\"phone\":{\"required\":false,\"visible\":false,\"order\":3,\"label\":\"Telefone\"},\"cpf\":{\"required\":false,\"visible\":false,\"order\":4,\"label\":\"CPF\"},\"zip_code\":{\"required\":false,\"visible\":false,\"order\":5,\"label\":\"CEP\"},\"address\":{\"required\":false,\"visible\":false,\"order\":6,\"label\":\"Endere\\u00e7o\"},\"city\":{\"required\":false,\"visible\":false,\"order\":7,\"label\":\"Cidade\"},\"state\":{\"required\":false,\"visible\":false,\"order\":8,\"label\":\"Estado\"},\"number\":{\"required\":false,\"visible\":false,\"order\":9,\"label\":\"N\\u00famero\"},\"complement\":{\"required\":false,\"visible\":false,\"order\":10,\"label\":\"Complemento\"}}', '[\"name\",\"email\"]', '#ffffff', '#000000', 1, '[{\"step\":1,\"title\":\"Passo 1\",\"description\":\"Descri\\u00e7\\u00e3o do passo 1\",\"bg_color\":\"#f3f4f6\",\"text_color\":\"#374151\",\"border_color\":\"#d1d5db\",\"icon_check_bg\":\"#2ecc71\",\"icon_check_color\":\"#ffffff\"},{\"step\":2,\"title\":\"Passo 2\",\"description\":\"Descri\\u00e7\\u00e3o do passo 2\",\"bg_color\":\"#f3f4f6\",\"text_color\":\"#374151\",\"border_color\":\"#d1d5db\",\"icon_check_bg\":\"#2ecc71\",\"icon_check_color\":\"#ffffff\"},{\"step\":3,\"title\":\"Passo 3\",\"description\":\"Descri\\u00e7\\u00e3o do passo 3\",\"bg_color\":\"#f3f4f6\",\"text_color\":\"#374151\",\"border_color\":\"#d1d5db\",\"icon_check_bg\":\"#2ecc71\",\"icon_check_color\":\"#ffffff\"}]', '[{\"name\":\"pix\",\"label\":\"PIX\",\"icon\":\"pix\",\"image\":\"\\/images\\/icons\\/icon-pix.png\",\"show_image\":true,\"icon_color\":\"#ffffff\",\"icon_bg_color\":\"#dbdbdb\",\"enabled\":true},{\"name\":\"credit_card\",\"label\":\"Cart\\u00e3o de Cr\\u00e9dito\",\"icon\":\"credit_card\",\"image\":\"\\/images\\/icons\\/icon-credit-card.png\",\"show_image\":false,\"icon_color\":\"#ffffff\",\"icon_bg_color\":\"#2980b9\",\"enabled\":false},{\"name\":\"boleto\",\"label\":\"Boleto\",\"icon\":\"boleto\",\"image\":\"\\/images\\/icons\\/icon-boleto.png\",\"show_image\":false,\"icon_color\":\"#ffffff\",\"icon_bg_color\":\"#e67e22\",\"enabled\":false}]', '#ffffff', '#0f172a', '#fbbf24', NULL, 'Quero comprar também!', '#10b981', '#ffffff', '(Recomendado)', '#fbbf24', 1, '2025-11-10 23:16:02', '2025-11-10 23:16:02'),
('019a7046-265a-7389-9c3f-f515c9bc9a95', 6, '019a701b-95aa-719a-bcf9-4b918108ffa7', 0, 'single', NULL, 0, '🔥', 3600, '#dc2626', '#ffffff', 'Oferta por tempo limitado!', 0, '#2563eb', '#6b7280', '#1d4ed8', '#4b5563', '{\"name\":{\"required\":true,\"visible\":true,\"order\":\"1\",\"label\":\"Nome completo\"},\"email\":{\"required\":true,\"visible\":true,\"order\":\"2\",\"label\":\"E-mail\"},\"phone\":{\"required\":false,\"visible\":false,\"order\":\"3\",\"label\":\"Telefone\"},\"cpf\":{\"required\":false,\"visible\":false,\"order\":\"4\",\"label\":\"CPF\"},\"zip_code\":{\"required\":false,\"visible\":false,\"order\":\"5\",\"label\":\"CEP\"},\"address\":{\"required\":false,\"visible\":false,\"order\":\"6\",\"label\":\"Endere\\u00e7o\"},\"city\":{\"required\":false,\"visible\":false,\"order\":\"7\",\"label\":\"Cidade\"},\"state\":{\"required\":false,\"visible\":false,\"order\":\"8\",\"label\":\"Estado\"},\"number\":{\"required\":false,\"visible\":false,\"order\":\"9\",\"label\":\"N\\u00famero\"},\"complement\":{\"required\":false,\"visible\":false,\"order\":\"10\",\"label\":\"Complemento\"}}', '[\"name\",\"email\"]', '#ffffff', '#000000', 1, '[{\"step\":\"1\",\"title\":\"Passo 1\",\"description\":\"Descri\\u00e7\\u00e3o do passo 1\",\"bg_color\":\"#f3f4f6\",\"text_color\":\"#374151\",\"border_color\":\"#d1d5db\",\"icon_check_bg\":\"#2ecc71\",\"icon_check_color\":\"#ffffff\"},{\"step\":\"2\",\"title\":\"Passo 2\",\"description\":\"Descri\\u00e7\\u00e3o do passo 2\",\"bg_color\":\"#f3f4f6\",\"text_color\":\"#374151\",\"border_color\":\"#d1d5db\",\"icon_check_bg\":\"#2ecc71\",\"icon_check_color\":\"#ffffff\"},{\"step\":\"3\",\"title\":\"Passo 3\",\"description\":\"Descri\\u00e7\\u00e3o do passo 3\",\"bg_color\":\"#f3f4f6\",\"text_color\":\"#374151\",\"border_color\":\"#d1d5db\",\"icon_check_bg\":\"#2ecc71\",\"icon_check_color\":\"#ffffff\"}]', '[{\"name\":\"pix\",\"label\":\"PIX\",\"icon\":\"pix\",\"image\":\"\\/images\\/icons\\/icon-pix.png\",\"show_image\":\"1\",\"icon_color\":\"#ffffff\",\"icon_bg_color\":\"#dbdbdb\",\"enabled\":\"1\"},{\"name\":\"credit_card\",\"label\":\"Cart\\u00e3o de Cr\\u00e9dito\",\"icon\":\"credit_card\",\"image\":\"\\/images\\/icons\\/icon-credit-card.png\",\"show_image\":\"0\",\"icon_color\":\"#ffffff\",\"icon_bg_color\":\"#2980b9\",\"enabled\":\"0\"},{\"name\":\"boleto\",\"label\":\"Boleto\",\"icon\":\"boleto\",\"image\":\"\\/images\\/icons\\/icon-boleto.png\",\"show_image\":\"0\",\"icon_color\":\"#ffffff\",\"icon_bg_color\":\"#e67e22\",\"enabled\":\"0\"}]', '#ffffff', '#0f172a', '#fbbf24', NULL, 'Quero comprar também!', '#10b981', '#ffffff', '(Recomendado)', '#fbbf24', 1, '2025-11-11 00:17:16', '2025-11-11 04:47:21');
/*!40000 ALTER TABLE `checkouts` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `transactions`
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `user_id` INT NOT NULL,
  `invoice` VARCHAR(255) NOT NULL,
  `payment_status` VARCHAR(255) NOT NULL DEFAULT 'Pending',
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(255) NOT NULL,
  `net_deposit` DECIMAL(10,2) NOT NULL,
  `acquirer_ref` VARCHAR(255),
  `date` date NOT NULL,
  `fee` DECIMAL(10,2) NOT NULL,
  `created_at` datetime,
  `updated_at` datetime,
  `is_sample` tinyint(1) NOT NULL DEFAULT '0',
  `product_id` VARCHAR(255),
  FOREIGN KEY(`user_id`) references users(`id`) on delete cascade on update no action,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `transactions`
LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` (`id`, `user_id`, `invoice`, `payment_status`, `total_amount`, `payment_method`, `net_deposit`, `acquirer_ref`, `date`, `fee`, `created_at`, `updated_at`, `is_sample`, `product_id`) VALUES
(7, 6, 'FPX-fcb9bc2c-a950-4537-b51f-871e82f3d92c', 'Paid', 5.5, 'PIX', 5, 'fcb9bc2c-a950-4537-b51f-871e82f3d92c', '2025-11-10 00:52:31', 0, '2025-11-10 00:52:31', '2025-11-10 00:52:31', 0, NULL),
(8, 6, 'FPX-d6bd5124-77d4-4202-ab2d-8ded007a85e1', 'Paid', 1, 'PIX', 0.5, 'd6bd5124-77d4-4202-ab2d-8ded007a85e1', '2025-11-10 01:08:31', 0, '2025-11-10 01:08:31', '2025-11-10 01:08:31', 0, NULL),
(9, 6, 'FPX-aa19e5c3-27c9-4049-aa01-166a553a3b1a', 'Paid', 6, 'PIX', 5, 'aa19e5c3-27c9-4049-aa01-166a553a3b1a', '2025-11-10 01:37:26', 0.5, '2025-11-10 01:37:26', '2025-11-10 01:37:26', 0, NULL),
(10, 6, 'FPX-384e71a2-ab1a-4d94-a5d1-c0b60ddf48ff', 'Paid', 1.5, 'PIX', 0.5, '384e71a2-ab1a-4d94-a5d1-c0b60ddf48ff', '2025-11-10 01:42:38', 0.5, '2025-11-10 01:42:38', '2025-11-10 01:42:38', 0, NULL),
(11, 6, 'FPX-548e6648-d074-4bc5-af93-42a1b1fda155', 'Paid', 10, 'PIX', 7.72, '548e6648-d074-4bc5-af93-42a1b1fda155', '2025-11-10 02:28:18', 1.78, '2025-11-10 02:28:18', '2025-11-10 02:28:18', 0, NULL),
(12, 6, 'FPX-3f388c68-8eeb-4962-b898-45339595f174', 'Paid', 10, 'PIX', 7.72, '3f388c68-8eeb-4962-b898-45339595f174', '2025-11-10 04:35:52', 1.78, '2025-11-10 04:35:52', '2025-11-10 04:35:52', 0, NULL),
(13, 6, 'FPX-e5b7ff68-605c-4bae-906c-d48352da1a40', 'Paid', 10, 'PIX', 7.72, 'e5b7ff68-605c-4bae-906c-d48352da1a40', '2025-11-10 04:56:14', 1.78, '2025-11-10 04:56:14', '2025-11-10 04:56:14', 0, NULL),
(14, 6, 'FPX-47428486-d2f6-45dc-b908-45464f10980d', 'Paid', 1, 'PIX', 0, '47428486-d2f6-45dc-b908-45464f10980d', '2025-11-11 00:00:00', 1.51, '2025-11-11 04:55:17', '2025-11-11 04:55:17', 0, NULL);
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;


-- Estrutura da tabela `products`
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` INT NOT NULL,
  `category_id` VARCHAR(255),
  `name` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255),
  `image` VARCHAR(255),
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `type` VARCHAR(255) CHECK (`type` IN ('FISICAL', 'DIGITAL')) NOT NULL DEFAULT 'DIGITAL',
  `price` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT '0',
  `is_sample` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime,
  `updated_at` datetime,
  FOREIGN KEY(`user_id`) references `users`(`id`),
  FOREIGN KEY(`category_id`) references `categories`(`id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `products`
LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` (`id`, `user_id`, `category_id`, `name`, `description`, `image`, `status`, `type`, `price`, `stock`, `is_sample`, `created_at`, `updated_at`) VALUES
('019a701b-95aa-719a-bcf9-4b918108ffa7', 6, NULL, 'Tropa galera', NULL, 'products/fd3SITX75yhRH9IwWwCvDvTJfrlJ2IVjAFw20w26.jpg', 1, 'DIGITAL', 10.5, 0, 0, '2025-11-10 23:30:47', '2025-11-10 23:30:47');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
