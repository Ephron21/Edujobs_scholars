-- SQL Script to create consultation_requests table

CREATE TABLE IF NOT EXISTS `consultation_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','contacted','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add indexes for faster searching (remove IF NOT EXISTS as it's not supported for indexes in MySQL)
CREATE INDEX idx_status ON consultation_requests(status);
CREATE INDEX idx_service_type ON consultation_requests(service_type); 