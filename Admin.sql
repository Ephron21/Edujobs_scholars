CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert admin accounts with hashed passwords
INSERT INTO `admins` (`firstname`, `lastname`, `email`, `password`, `created_at`) VALUES
('Esron', 'Admin', 'Esron221@gmail.com', '$2y$10$YourHashedPasswordHere', CURRENT_TIMESTAMP),
('Ephron', 'Tuyishime', 'ephrontuyishime21@gmail.com', '$2y$10$YourHashedPasswordHere', CURRENT_TIMESTAMP);