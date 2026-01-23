-- Create users table for authentication
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `role` ENUM('admin','operator','viewer') NOT NULL DEFAULT 'viewer',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  `last_login` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin account
-- Email: admin@example.com
-- Password: admin123
-- NOTE: Run setup_admin.php to create the admin user with proper password hashing
-- The password hash should be generated using PHP's password_hash() function
-- Example: password_hash('admin123', PASSWORD_DEFAULT)
